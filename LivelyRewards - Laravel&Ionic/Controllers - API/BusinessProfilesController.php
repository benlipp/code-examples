<?php

namespace App\Http\Controllers\ApiControllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\BusinessProfile as BProfile;
use App\Models\Location;
use App\Models\User;

use App\Jobs\RewardCommCredits;

use \Recurly_Client;
use \Recurly_Account;
use \Recurly_PlanList;
use \Recurly_BillingInfo;
use \Recurly_Subscription;
use \Recurly_ValidationError;

class BusinessProfilesController extends Controller
{
  function all(Response $response)
  {
    $profiles = BProfile::all();
    $big_array = array();
    foreach ($profiles as $prof){
      $big_array[] = $prof->toArray();
    }
    $data = array('data'=>$big_array);
    return $response->header('Status',200)->setContent(json_encode($data));
  }


  function locations($id, Response $response)
  {
    $profiles = BProfile::find($id);
    $big_array = array();
    $locations =  $profiles->locations()->get();
    foreach($locations as $loc){
      $big_array[] = $loc->toArray();
    }
    $data = array('data'=>$big_array);
    return $response->header('Status',200)->setContent(json_encode($data));
  }


  function store(Request $request, Response $response)
  {
    $inputData = $request->input('data');
    // Do some checking for errors and sanitization
    $bprof = new BProfile();
    foreach ($inputData as $key => $value){
      if (in_array($key,
      [
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'phone',
        'public_url_prefix'
      ]
      )){ // make sure we only grab the values we want
        $bprof->$key = $value;
      }
    }
    $bprof->points_lock = 1;
    $bprof->rewards_lock = 1;
    //CREATE THE USER
    $user = new User;
    if ( $inputData['password'] == '' ){
      $error = array('error'=>array('code'=>'GEN-BADREQEUST','message'=>'Password is required','payload'=>$request->input('data')));
      return $response->header('Status',400)->setContent(json_encode($error));
    }
    if ( $inputData['password'] != $inputData['confirm_password'] ){ //if the passwords don't match
      $error = array('error'=>array('code'=>'GEN-BADREQEUST','message'=>'Passwords Don\'t Match','payload'=>$request->input('data')));
      return $response->header('Status',400)->setContent(json_encode($error));
    }
    $user->password = bcrypt($inputData['password']);
    $user->email = $inputData['email'];
    $user->name = $inputData['name'];
    $user->type = 'business-profiles';
    // save the profile so we know if we can save the user
    $success = $bprof->save();
    if($success){
      $user->entity_id = $bprof->id;
      $user->save();
      //CREATE FIRST LOCATION
      $location = new Location();
      foreach ($inputData as $key => $value){
        if (in_array($key,
        [ // make sure we only grab the values we want
          'name',
          'address_line_1',
          'address_line_2',
          'city',
          'state',
          'zip',
          'phone'
        ]
        )){
          $location->$key = $value;
        }
      }
      $location->prof_id = $bprof->id;
      $location->save();
      //Recurly
      try {
        Recurly_Client::$subdomain = config('recurly.subdomain');
        Recurly_Client::$apiKey = config('recurly.api_key');
        $account = new Recurly_Account($inputData['public_url_prefix']);
        $account->email = $inputData['email'];
        $account->company_name = $inputData['name'];
        $account->address = [
          "address1" => $inputData['address_line_1'],
          "address2" => $inputData['address_line_2'],
          "city" => $inputData['city'],
          "state" => $inputData['state'],
          "zip" => $inputData['zip'],
          "country" => "US",
          "phone" => $inputData['phone']
        ];
        $account->create();
      } catch (Recurly_ValidationError $e){
        $messages = explode(',', $e->getMessage());
        $errData = ["error"=>"Validation error","details"=>$messages];
        return $response->header('Status',400)->setContent(json_encode($errData));
      }
      $prof_id = $bprof->id;
      return redirect('/api/payment/'.$prof_id.'/newsubscription');
    }
    $error = array('error'=>array('code'=>'GEN-BADREQUEST','message'=>'Bad Request','payload'=>$request->input('data')));
    return $response->header('Status',400)->setContent(json_encode($error));
  }

  function show($id)
  {
    $profile = BProfile::find($id);
    if($profile){
      $profile_arr = $profile->toArray();
      //attach user email address to the call
      $profile_arr['user_email'] = $profile->user->email;
      if ( $profile->image ){
        $profile_arr['image_url'] = $profile->image->url;
      }
      $data = array('data'=>$profile_arr);
      return json_encode($data);
    }
    $error = array('error'=>array('code'=>'GEN-PIDINVAL','http_code'=>'404','message'=>'Invalid ID','payload'=>$id));
    return json_encode($error);
  }


  function update($id, Request $request, Response $response)
  {
    $data = $request->only('name','address_line_1','address_line_2','city','state','zip','phone','image_id');
    $bprof = BProfile::find($id);
    if($bprof ){
      foreach ($data as $key => $value){
        $bprof->$key = $value;
      }
      if ( $request->input('user_email') ){
        $user = $bprof->user;
        $user->email = $request->input('user_email');
        if ( $request->input('password') != '' ){
          if ( $request->input('password') != $request->input('confirm_password') ){ //if the passwords don't match
            $error = array('error'=>array('code'=>'GEN-BADREQEUST','message'=>'Passwords Don\'t Match','payload'=>$request->input('data')));
            return $response->header('Status',400)->setContent(json_encode($error));
          }
          $user->password = bcrypt($request->input('password'));
        }
        $user->save();
      }
      $success = $bprof->save();
      if ( $success ){
        //if single location, get the location updated
        if ( $bprof->isSingleLocation() ){
          //CREATE FIRST LOCATION
          $location = $bprof->locations->first(); //and only
          foreach ($data as $key => $value){
            $location->$key = $value;
          }
          $location->save();
        }
        $data = array('data'=>$bprof->toArray());
        return $response->header('Status',201)->setContent(json_encode($data));
      }
      $error = array('error'=>array('code'=>'GEN-BADREQEUST','message'=>'Bad Request','payload'=>$request->input('data')));
      return $response->header('Status',400)->setContent(json_encode($error));
    }
    $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid  ID','payload'=>$id));
    return $response->header('Status',404)->setContent(json_encode($error));
  }


  function rewards($id, Response $response)
  {
    $rewards = BProfile::find($id)->rewards();
    $big_array = array();
    foreach($rewards as $rew){
      $big_array[] = $rew->toArray();
    }
    $data = array('data'=>$big_array);
    return $response->header('Status',200)->setContent(json_encode($data));
  }


  function destroy($id) // API Delete
  {
    $bprof = BProfile::find($id);
    if($bprof != ""){
      $bprof->delete();
      return $response->header('Status',200);
    }
    $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid ID','payload'=>$id));
    return $response->header('Status',404)->setContent(json_encode($error));
  }


  function points($id, Request $request, Response $response)
  {
    $bprof = BProfile::find($id);
    if($bprof == ""){
      $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid ID','payload'=>$id));
      return $response->header('Status',404)->setContent(json_encode($error));
    }
    $loyalty_arr = array(
      'points_dollar'=>$request->input('points_dollar'),
      'points_visit'=>$request->input('points_visit'),
      'points_lock'=>$request->input('points_lock'),
      'hours_between_visits'=>$request->input('hours_between_visits')
    );
    foreach ($loyalty_arr as $k=>$v){
      $bprof->$k = $v;
    }
    $success = $bprof->save();
    if($success){
      return $response->header('Status',201)->setContent(json_encode($bprof));
    }
    $error = array('error'=>array('code'=>'GEN-ERROR','message'=>'Something went wrong :/','payload'=>$id));
    return $response->header('Status',500)->setContent(json_encode($error));
  }


  function rewardsLock($id, Request $request, Response $response)
  {
    $bprof = BProfile::find($id);
    if($bprof == ""){
      $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid ID','payload'=>$id));
      return $response->header('Status',404)->setContent(json_encode($error));
    }
    $rewardsLock = $request->input('rewardsLock');
    if ($rewardsLock == "true"){
      $bprof->rewards_lock = 1;
    } else if ($rewardsLock == "false"){
      $bprof->rewards_lock = 0;
    } else if (is_int($rewardsLock)){
      $bprof->rewards_lock = $rewardsLock;
    }
    $success = $bprof->save();
    if($success){
      return $response->header('Status',201)->setContent(json_encode($bprof));
    }
    $error = array('error'=>array('code'=>'GEN-ERROR','message'=>'Something went wrong :/','payload'=>$id));
    return $response->header('Status',500)->setContent(json_encode($error));
  }


  function rewardCommCredits()
  {
    $this->dispatch(new RewardCommCredits());
  }
}
