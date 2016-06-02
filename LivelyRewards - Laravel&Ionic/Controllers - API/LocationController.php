<?php

namespace App\Http\Controllers\ApiControllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\BusinessProfile as BProfile;
use App\Models\User;
use App\Events\LocationUpdated;

use Auth;
use Event;

class LocationController extends Controller
{

  function rewards($id, Response $response)
  {
    $location = Location::find($id);
    $bprof = $location->businessProfile()->first();
    $rewardsLoc = $location->rewards();
    $locRewardsArray = array();
    foreach($rewardsLoc as $rew){
      $locRewardsArray[] = $rew->toArray();
    }
    if ($bprof->rewardsLock()){
      $bprofRewardsArray = array();
      $rewardsBprof = $bprof->rewards();
      foreach ($rewardsBprof as $rew){
        $bprofRewardsArray[] = $rew->toArray();
      }
      $finalArray = array("bpRewards"=>$bprofRewardsArray,"locRewards"=>$locRewardsArray,"lock"=>TRUE);
      $data = array('data'=>$finalArray);
      return $response->header('Status',200)->setContent(json_encode($data));
    }
    $finalArray = array("locRewards"=>$locRewardsArray,"lock"=>FALSE);
    $data = array('data'=>$finalArray);
    return $response->header('Status',200)->setContent(json_encode($data));
  }


  function store(Request $request, Response $response)
  {
    $data = $request->except('id','updated_at','created_at','user_email','password', 'confirm_password');
    // Do some checking for errors and sanitization
    $location = new Location();
    foreach ($data as $key => $value){
      $location->$key = $value;
    }
    //CREATE THE USER
    $user = new User;
    if ( $request->input('password') == '' ){
      $error = array('error'=>array('code'=>'GEN-BADREQEUST','message'=>'Password is required','payload'=>$request->input('data')));
      return $response->header('Status',400)->setContent(json_encode($error));
    }
    if ( $request->input('password') != $request->input('confirm_password') ){ //if the passwords don't match
      $error = array('error'=>array('code'=>'GEN-BADREQEUST','message'=>'Passwords Don\'t Match','payload'=>$request->input('data')));
      return $response->header('Status',400)->setContent(json_encode($error));
    }
    $user->password = bcrypt($request->input('password'));
    $user->email = $request->input('user_email');
    $user->name = $data['name'];
    $user->type = 'locations';
    $success = $location->save(); //now that we're past the error points, go ahead and save
    if ( $success){
      $user->entity_id = $location->id;  //update user entity_id with new record
      $user->save(); //save the user
      $data = array('data'=>$location->toArray());
      return $response->header('Status',201)->setContent(json_encode($data));
    }
    $error = array('error'=>array('code'=>'GEN-BADREQUEST','message'=>'Bad Request','payload'=>$request->input('data')));
    return $response->header('Status',400)->setContent(json_encode($error));
  }


  function show($id, Response $response)
  {
    $location = Location::find($id);
    if($location != ""){
      $location_arr = $location->toArray();
      //attach user email address to the call
      $location_arr['user_email'] = $location->user->email;
      //let's get the business profile lock settings in Here
      $location_arr['business_profile'] = $location->businessProfile->toArray();
      $data = array('data'=>$location_arr);
      return $response->header('Status',200)->setContent(json_encode($data));
    }
    $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid ID','payload'=>$id));
    return $response->header('Status',404)->setContent(json_encode($error));
  }


  function update($id, Request $request, Response $response)
  {
    $data = $request->except('id','updated_at','created_at', 'user_email', 'password', 'confirm_password','business_profile');
    $location = Location::find($id);
    if($location != ''){
      foreach ($data as $key => $value){
        $location->$key = $value;
      }
      $user = $location->user;
      $user->email = $request->input('user_email');
      if ( $request->input('password') != '' ){
        if ( $request->input('password') != $request->input('confirm_password') ){ //if the passwords don't match
          $error = array('error'=>array('code'=>'GEN-BADREQEUST','message'=>'Passwords Don\'t Match','payload'=>$request->input('data')));
          return $response->header('Status',400)->setContent(json_encode($error));
        }
        $user->password = bcrypt($request->input('password'));
      }
      $user->save();
      $success = $location->save();
      if($success){
        Event::fire(new LocationUpdated($location));
        $data = array('data'=>$location->toArray());
        return $response->header('Status',201)->setContent(json_encode($data));
      }
      $error = array('error'=>array('code'=>'GEN-BADREQEUST','message'=>'Bad Request','payload'=>$request->input('data')));
      return $response->header('Status',400)->setContent(json_encode($error));
    }
    $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid  ID','payload'=>$id));
    return $response->header('Status',404)->setContent(json_encode($error));
  }


  function destroy($id)
  {
    $location = Location::find($id);
    if($location != ""){
      $location->delete();
      return $response->header('Status',200);
    }
    $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid ID','payload'=>$id));
    return $response->header('Status',404)->setContent(json_encode($error));
  }


  function pointsGet($id, Request $request, Response $response)
  {
    $location = Location::find($id);
    if ($location == ""){
      $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid ID','payload'=>$id));
      return $response->header('Status',404)->setContent(json_encode($error));
    }
    $points = $location->points;
    $data = array("data"=>$points);
    return $response->header('Status',200)->setContent(json_encode($data));
  }


  function pointsPin($id, Request $request, Response $response)
  {
    $location = Location::find($id);
    if($location == ""){
      $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid ID','payload'=>$id));
      return $response->header('Status',404)->setContent(json_encode($error));
    }
    $pointsPin = $request->input('points_pin');
    $location->points_pin = $pointsPin;
    $success = $location->save();
    if($success){
      return $response->header('Status',201)->setContent(json_encode($location));
    }
    $error = array('error'=>array('code'=>'GEN-ERROR','message'=>'Something went wrong :/','payload'=>$id));
    return $response->header('Status',500)->setContent(json_encode($error));
  }


  function pointsUpdate($id, Request $request, Response $response)
  {
    $location = Location::find($id);
    if($location == ""){
      $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid ID','payload'=>$id));
      return $response->header('Status',404)->setContent(json_encode($error));
    }
    $loyalty_arr = array(
      'points_dollar'=>$request->input('points_dollar'),
      'points_visit'=>$request->input('points_visit'),
      'hours_between_visits'=>$request->input('hours_between_visits')
    );
    foreach ($loyalty_arr as $k=>$v){
      $location->$k = $v;
    }
    $success = $location->save();
    if($success){
      return $response->header('Status',201)->setContent(json_encode($location));
    }
    $error = array('error'=>array('code'=>'GEN-ERROR','message'=>'Something went wrong :/','payload'=>$id));
    return $response->header('Status',500)->setContent(json_encode($error));
  }

}
