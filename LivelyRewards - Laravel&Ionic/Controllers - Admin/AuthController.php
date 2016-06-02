<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\UserAssoc;
use App\Models\BusinessProfile;
use App\Models\Location;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Point;
use App\Models\Coupon;
use App\Models\UserReward;
use \Recurly_Subscription;
use \Recurly_Client;

use Session;
use Redirect;
use Auth;
use Debugbar;

class AuthController extends Controller
{
  /*
  |--------------------------------------------------------------------------
  | Registration & Login Controller
  |--------------------------------------------------------------------------
  |
  | This controller handles the registration of new users, as well as the
  | authentication of existing users. By default, this controller uses
  | a simple trait to add these behaviors. Why don't you explore it?
  |
  */

  use AuthenticatesAndRegistersUsers;

  /**
  * Create a new authentication controller instance.
  *
  * @return void
  */
  public function __construct()
  {
    $this->middleware('guest', ['except' => 'getLogout']);
  }

  /**
  * Get a validator for an incoming registration request.
  *
  * @param  array  $data
  * @return \Illuminate\Contracts\Validation\Validator
  */
  protected function validator(array $data)
  {
    return Validator::make($data,[
      'first_name' => 'required|max:255',
      'last_name' => 'required|max:255',
      'email' => 'required|email|max:255|unique:users,email',
      'password' => 'required|confirmed|min:6',
    ]);
  }

  protected function public_validator(array $data)
  {
    return Validator::make($data,[
      'password' => 'required|confirmed|min:6',
      'mobile_number' => 'required|integer',
      'email_address'=>'required|unique:users,email'
    ],[
      'password.confirmed'=>'The password entries do not match, please try again.'
    ]);
  }

  protected function oauth_validator(array $data)
  {
    return Validator::make($data,[
      'full_name' => 'required|max:255',
      'email_address' => 'required|email|max:255|unique:users,email',
      'mobile_number' => 'required|integer',
    ],[
      "email_address.unique" => "There is already a user in the system with that email address."
    ]);
  }

  /**
  * Create a new user instance after a valid registration.
  *
  * @param  array  $data
  * @return User
  */
  protected function create(Request $request)
  {
    $data = $request->all();
    $user = User::create([
      'name' => $data['name'],
      'email' => $data['email'],
      'password' => bcrypt($data['password']),
    ]);
    $assoc = new UserAssoc();
    if(array_key_exists('location',$data))
    {
      $assoc->loc_id = $data['location'];
    }
    if(array_key_exists('profile',$data))
    {
      $assoc->loc_id = $data['profile'];
    }
    $assoc->user_id = $user->id;
    $assoc->save();



    $token = JWTAuth::fromUser($user);
    return $token;
  }

  public function publicUserExists(Request $request){
    header("Access-Control-Allow-Origin: *");
    $mobile_number = $request->mobile_number;
    $oauth = $request->input('oauth');

    if(@$oauth['provider']){
      $user = User::where('oauth_provider',$oauth['provider'])->where('provider_id',$oauth['id'])->first();
    } else {
      $user = User::where('mobile_number',$mobile_number)->orwhere('mobile_number',preg_replace( '/[^0-9]/', '', $mobile_number ))->first();
    }

    if ( $user ){
      return ["exists"=>TRUE];
    }
    return ['exists'=>FALSE];

  }

  public function createPublicUser(Request $request, Response $response){
    header("Access-Control-Allow-Origin: *");
    $data = $request->all();


    //$business = BusinessProfile::fromIdentifier($request->business_identifier)->first(); //get the business info... we're going to need it
    $business = FALSE;
    if ( @$data['business_code'] ){
      $business = BusinessProfile::fromIdentifier($data['business_code'])->first();
    }
    if ( @$data['business_code'] && @$data['coupon_code'] ){
      $coupon = Coupon::where('type','business-profiles')->where('entity_id',$business->id)->where('code',$data['coupon_code'])->first();
      if ( !$coupon ){
        return response(['coupon_code'=>['Invalid Coupon Code']],422);
      }
    }
    /*
    if (!$business ){
    //return response(['data'=>''])
    return response(['business_code'=>['Invalid Business Code']],422);
  }
  */
  if (array_key_exists('full_name',$data)){
    $validator = $this->oauth_validator($data);
  } else {
    $validator = $this->public_validator($data);
  }

  if ($validator->fails()){ //if our signin info is bad

    $this->throwValidationException(
    $request, $validator
  );

  $error = array('error'=>array('code'=>'GEN-ERROR','message'=>$validator->errors()->all(),'payload'=>$request->input('data')));
  //return $response->header('Status',422)->setContent(json_encode($error));
  return response($error,422);
} else { //we have good sign in info
  //if we already have the user in the system, get them attached
  //CREATE THE USER ACCOUNT AND ATTACH TO BUSINESS
  $user = new User();

  if (array_key_exists('oauth',$data)){ // if we have oauth
    $user->oauth_provider = $data['oauth']['provider'];
    $user->provider_id = $data['oauth']['id'];
    $name = explode(' ',$data['full_name']);
    $user->first_name = $name[0];
    $user->last_name = array_pop($name);
  } else { // otherwise password
    $user->password = bcrypt($data['password']);
    $user->first_name = $data['first_name'];
    $user->last_name = $data['last_name'];

  }
  $user->email = @$data['email_address'];
  $user->mobile_number = $data['mobile_number'];
  $user->type = 'consumer';

  $user->save();

  if ( $business ){
    if ( !$user->businesses->contains($business->id) ){
      $user->businesses()->attach($business);
    }

    //give signup points
    $point = new Point();
    $point->user_id = $user->id;
    $point->business_profile_id = $business->id;
    $point->type = 'signup';
    $point->points = $business->points_for_signup;
    $point->breakdown = json_encode([
      'signup_points'=>$business->points_for_signup,
    ]);
    $point->save();

    $user->recalculateBusinessPoints($business->id);
    $mailer = \App::make('lcmailer');
    if (@$data['coupon_code']){
      $coupon = Coupon::where('type','business-profiles')->where('entity_id',$business->id)->where('code',$data['coupon_code'])->first();
      if ( $coupon ){
        //add new record to user rewards
        $user_reward = new UserReward;
        $user_reward->user_id = $user->id;
        $user_reward->business_profile_id = $business->id;
        $user_reward->reward_id = $coupon->id;
        $user_reward->type = 'coupon';
        $user_reward->reward_data = json_encode($coupon->toArray());

        if ( $coupon->expire_time != '' && $coupon->expire_time > 0 ){
          $user_reward->expire_date = date("Y-m-d H:i:s",strtotime("+ ".$coupon->expire_time." ".$coupon->expire_unit));
        }

        $mailer->sendMail($user->id,$business->id,'welcome_with_coupon',"","");

        $user_reward->save();
      } else {
        $mailer->sendMail($user->id,$business->id,'welcome_no_coupon',"","");
      }
    } else {
      $mailer->sendMail($user->id,$business->id,'welcome_no_coupon',"","");
    }
  }
}
return;
}

public function signInTest(Request $request){
  print_r($request->all());
}

public function oauthCheck(Request $request, Response $response){
  header("Access-Control-Allow-Origin: *");
  $request_data = $request->only('oauth_provider','provider_id','business_identifier');
  $business = BusinessProfile::where('public_url_prefix',$request_data['business_identifier'])->first();
  $user = User::where('oauth_provider',$request_data['oauth_provider'])->where('provider_id',$request_data['provider_id'])->first();
  if ( !$user){
    //return $response->header('Status',404)->setContent('newaccount');
    return response('newaccount',404);
  } else if (!$user->businesses()->where('business_profile_id',$business->id)->first()){
    // let's do the assoc
    if ( !$user->businesses->contains($business->id) ){
      $user->businesses()->attach($business);
    }
    // return $response->header('Status',200)->setContent('success');
    return response('successs',200);
  }
  //return $response->header('Status',200)->setContent('success');
  return response('success',200);
}

public function signIn(Request $request, Response $response)
{
  header("Access-Control-Allow-Origin: *");
  $request_data = $request->only('email','password','mobile_number','public','business_identifier','oauth','provider_id','provider');
  $error = array('error'=>array('code'=>'GEN-MAYBGTFO','message'=>'Unauthorized.'));

  $public = $request_data['public']?TRUE:FALSE;
  $oauth = $request_data['oauth']?TRUE:FALSE;
  if (!$oauth){
    $data['password'] = $request_data['password'];
    if ( $public ){
      $data['mobile_number'] = preg_replace( '/[^0-9]/', '', $request_data['mobile_number'] );
    } else {
      $data['email'] = $request_data['email'];
    }

    if(!$token = JWTAuth::attempt($data)){
      if ( $public ){
        //$error = array('error'=>array('code'=>'GEN-BADREQEUST','message'=>'Mobile Number and Password don\'t match','payload'=>$request->input('data')));
        //return $response->header('Status',401)->setContent(json_encode($error));
        return response('Mobile Number and Password don\'t match a profile in our system. Please try again.', 401);
      } else {
        Session::flash('loginError', 'Bad Username / Password');
        return Redirect::to('/login');
      }
    }

    $user = JWTAuth::authenticate($token);
  } // end if(!$oauth)
  if ($oauth){
    $public = TRUE;
    $user = User::where('oauth_provider',$request_data['provider'])->where('provider_id',$request_data['provider_id'])->first();
    $token = JWTAuth::fromUser($user);
    if ($request_data['business_identifier']){
      $business = BusinessProfile::where('public_url_prefix',$request_data['business_identifier'])->first();

      if ( !$user->businesses->contains($business->id) ){
        $user->businesses()->attach($business);
      }
    }
  }
  if ( $public ){
    // if ( $request_data['business_identifier'] != '' ){
    //   $business = BusinessProfile::where('public_url_prefix',$request_data['business_identifier'])->first();
    //   if ( !$user->businesses()->where('business_profile_id',$business->id)->first()){
    //     abort(401);
    //   }
    // }

    return $token;
  } else {
    if($user->type == "admin"){ // if we are admin
      Session::put('token',$token);
      $user_auth = Auth::loginUsingId($user->id);
      return Redirect::to('/');
    } else {
      $business_profile = $user->businessProfile;
      Recurly_Client::$subdomain = config('recurly.subdomain');
      Recurly_Client::$apiKey = config('recurly.api_key');
      try {
        $subscription = Recurly_Subscription::get($business_profile->subscription_uuid);
        $business_profile->subscription_status = $subscription->state;
        $business_profile->save();
      } catch (\Recurly_NotFoundError $e){
        $business_profile->subscription_status = "";
        Session::flash('loginError', 'No subscription was found. Please contact LivelyClick.');
        return Redirect::to('/login');
      }
      if($business_profile->subscription_status == "expired"){
        $business_profile->plan_code = '';
        $business_profile->save();
        Session::flash('loginError', 'Your subscription has expired, please contact LivelyClick.');
        return Redirect::to('/login');
      }
      if($business_profile->subscription_status == "active"){
        Session::put('token',$token);
        $user_auth = Auth::loginUsingId($user->id);
        return Redirect::to('/');
      } else { // will only happen if it doesn't match
        Session::flash('loginError', 'Your account has been locked. Please contact LivelyClick.');
        return Redirect::to('/login');
      }
    }
  }
}

private function signInAsUser($new_user_id,$entity_type,$entity_id=FALSE){

  $cur_user = Auth::user();

  $user_session_arr = Session::get('logged_in_as'); //we keep an array of all the users this user has logged in as
  if ( !$user_session_arr ){ //if this is our first time logging in as a user
    $user_session_arr = array(array(
      'user_id'=>$cur_user->id,
      'entity_type'=>$cur_user->type,
      'entity_id'=>$cur_user->entity_id
    )); //set up the array with our current user being the first in the list
  }

  //we don't want to login as the same user twice so let's look through the array and see if we already have that user.  If we have that user, we will remove all other logins AFTER that point
  $user_id_key = FALSE;
  foreach ($user_session_arr as $key=>$u){
    if ( $u['user_id'] == $new_user_id ){
      $user_id_key = $key;
    }
  }


  if ( $user_id_key !== FALSE ){
    $user_session_arr = array_slice($user_session_arr, 0, $user_id_key);
  }

  $new_user = User::find($new_user_id);
  if ( $new_user ){ //if we have an actual user

    //TO DO - AUTH CHECK FOR THIS FUNCTION

    $token = JWTAuth::fromUser($new_user); //get that user a new token
    Session::put('token',$token); //put the token in the session
    Auth::loginUsingId($new_user->id); //login as said user

    $user_session_arr[] = array(
      'user_id'=>$new_user->id,
      'entity_type'=>$entity_type,
      'entity_id'=>$entity_id
    );

    if ( count($user_session_arr) == 1 && $user_session_arr[0]['user_id'] == $new_user->id ){ //if we are back to our original user
      Session::forget('logged_in_as'); //drop the session var
    } else {
      Session::put('logged_in_as',$user_session_arr); //update the session var
    }

    //TODO - get this to redirect to the right place
    //          print_r(Session::all());exit;
    if ( Session::get('redirect_route') != '' ){
      return Redirect::to(Session::get('redirect_route'));
    }

    return Redirect::to('/');
  }
}

public function signInAsBusinessProfile($bp_id){
  $bp_admin_user = User::where('type','business-profiles')->where('entity_id',$bp_id)->first();
  return $this->signInAsUser($bp_admin_user->id, 'business-profiles',$bp_id);
}

public function signInAsLocation($location_id){
  $location_user = User::where('type','locations')->where('entity_id',$location_id)->first();
  if ( !$location_user && Auth::user()->isBusinessProfile() ){
    $new_user = Auth::user()->replicate();
    $new_user->type = 'locations';
    $new_user->email = 'locuser'.$location_id.'@system.com';
    $new_user->entity_id = $location_id;
    $new_user->save();
    $location_user = $new_user;
  }
  return $this->signInAsUser($location_user->id, 'locations',$location_id);
}

public function signInAsAdmin($admin_id){

  $user_session_arr = Session::get('logged_in_as'); //we keep an array of all the users this user has logged in as

  //loop through to make sure we have an admin in our list
  $admin_exists = FALSE;
  foreach ($user_session_arr as $key=>$u){
    if ( $u['entity_type'] == 'admin' ){
      $admin_exists = TRUE;
    }
  }

  if ( $admin_exists ){
    return $this->signInAsUser($admin_id,'admin');
  } else {
    return Redirect::to('/');
  }

}

public function logout(){
  //JWTAuth::invalidate(Session::get('token'));
  Auth::logout();
  Session::flush();
  return Redirect::to('/login');
}
}
