<?php
namespace App\Http\Controllers\PublicControllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\BusinessProfile as BProfile;
use App\Models\Location;
use App\Models\User;
use \Recurly_Client;
use \Recurly_Account;
use \Recurly_PlanList;
use \Recurly_Plan;
use \Recurly_BillingInfo;
use \Recurly_Subscription;
use \Recurly_ValidationError;
use \Recurly_Coupon;

use Auth;
use View;
use Debugbar;
use Validator;

class BusinessProfilesController extends Controller
{
  public function getPointsVisitDetails(Request $request){
    $business = BProfile::find($request->business_profile_id);

    return [
      'points_per_visit'=>$business->points_visit,
      'dollar_multiplier'=>$business->points_dollar
    ];
  }


  public function getBusinessDetails(Request $request, Response $response){
    if ( $request->has('business_code') ){
      $request->business_identifier = $request->business_code;
    }
    if($request->business_identifier == ""){
      header("Access-Control-Allow-Origin: *");
      return "";
    }
    header("Access-Control-Allow-Origin: *");
    $business = BProfile::where('public_url_prefix',$request->business_identifier)->first();
    if (!$business){
      $error = array('error'=>array('code'=>'GEN-NOBUSINESS','message'=>"Not a valid business. Try again."));
      http_response_code(280);
      return response($error, 280);
    }
    return $business;
  }

  function signUpTemplate(Request $request, $plan_code=null)
  {
    $uri = $request->path();
    if ($plan_code === null){
      $plan_code = config('lc.default_plan');
    }
    Recurly_Client::$subdomain = config('recurly.subdomain');
    Recurly_Client::$apiKey = config('recurly.api_key');
    $plans = Recurly_PlanList::get();
    foreach ($plans as $plan){
      if($plan->plan_code == $plan_code){
        $my_plan = $plan;
      }
    }
    $plan_details = config('lc.plans.'.$plan_code.'.detail_list');
    return view('public.business_profiles.signUpTemplate',[
      'error'=>$request->session()->has('error')?$request->session()->get('error'):FALSE,
      "plan" => $my_plan,
      "plan_details"=>$plan_details,
      'uri'=>$uri
    ]);
  }
  function salesTemplate(Request $request){
    return view('public.business_profiles.salesTemplate');
  }

  function createAndSubscribe(Request $request, Response $response)
  {
    $plan_choice = $request->input('plan_choice');
    $request->flash();
    // DON'T LEAVE THE FUNCTION LIKE THIS FOR PRODUCTION! THEY NEED TO CHOOSE A SUBSCRIPTION!
    $account_data = $request->input('account');
    $acc_validator = Validator::make($account_data, [
      'first_name' => 'required',
      'last_name' => 'required',
      'email' => 'required|confirmed|unique:users,email',
      'password' => 'required|confirmed'
    ]);
    if ($acc_validator->fails()) {
      return redirect($request->input('uri'))->withErrors($acc_validator)->withInput();
    }
    $bprofile_data = $request->input('bprofile');
    $bprof_validator = Validator::make($bprofile_data,[
      'public_url_prefix' => 'required|unique:business_profiles,public_url_prefix'
    ]);
    if ($bprof_validator->fails()) {
      return redirect($request->input('uri'))->withErrors($bprof_validator)->withInput();
    }
    $billing_token = $request->input('billing_token');
    $bprofile = new BProfile;
    foreach ($bprofile_data as $key => $value)
    {
      $bprofile->$key = $value;
    }
    // set defaults too!
    foreach (config('lc.default_values.business_profile') as $key => $value)
    {
      $bprofile->$key = $value;
    }
    $bprofile->save();
    $user = new User;
    $user->first_name = $account_data['first_name'];
    $user->last_name = $account_data['last_name'];
    $user->email = $account_data['email'];
    $user->password = bcrypt($account_data['password']);
    $user->type = 'business-profiles';
    $user->entity_id = $bprofile->id;
    $user->save();

    /**
    FIX THIS!
    */

    if ( $billing_token == 'frsbsbscbr' ){

    } else {
      // we should also award credits on first signup
      Recurly_Client::$subdomain = config('recurly.subdomain');
      Recurly_Client::$apiKey = config('recurly.api_key');
      $account = new Recurly_Account();
      $account->billing_info = new Recurly_BillingInfo();
      $account->billing_info->token_id = $billing_token;
      $account->account_code = $bprofile_data['public_url_prefix'];
      $account->company_name = $bprofile_data['name'];
      $account->email = $account_data['email'];
      $subscription = new Recurly_Subscription();
      $subscription->plan_code = $plan_choice;
      $subscription->account = $account;
      $subscription->currency = "USD";

      try {
        $subscription->create();

      } catch (\Recurly_ValidationError $e){
        $request->session()->flash('error', $e->getMessage());
        return redirect('/bp-signup')->withInput();
      }

      $my_plan = Recurly_Plan::get($subscription->plan_code);
      $bprofile->plan_name = $my_plan->name;
      $bprofile->plan_code = $my_plan->plan_code;
      $bprofile->subscription_uuid = $subscription->uuid;
      $bprofile->subscription_status = $subscription->state;
      $bprofile->save();
      $bprofile->editCommCredits(config('lc.plans.'.$bprofile->plan_code.'.initial_credits'),"initial credit add");
    }

    Auth::login($bprofile->user);
    $mailer = \App::make('lcmailer');
    $mailer->sendMail($user->id,$bprofile->id,'welcome_client',"","Welcome to ".config('lc.system_name')."!");

    return redirect('/');
  }

  function checkCoupon(Request $request){
    Recurly_Client::$subdomain = config('recurly.subdomain');
    Recurly_Client::$apiKey = config('recurly.api_key');

    if ( $request->coupon_code == 'LCFREEACCT' ){
      $coupon_arr = [
        'type'=>'Free',
        'name'=>'Free',
        'description'=>'Free Account',
        'discount_percent'=>100,
        'discount_in_cents'=>2000,
        'token'=>'frsbsbscbr'
      ];
      return $coupon_arr;
    }

    try{
      $coupon = Recurly_Coupon::get($request->coupon_code);
      $coupon_arr = [
        'type'=>$coupon->discount_type,
        'name'=>$coupon->name,
        'description'=>$coupon->description,
        'discount_percent'=>@$coupon->discount_percent,
        'discount_in_cents'=>@$coupon->discount_in_cents->USD->amount_in_cents
      ];
      return response($coupon_arr);
    } catch (\Exception $e){
      return response('',404);
    }
  }

  public function checkPin(Request $request){
    //check the pin
    $business = BProfile::find($request->business_profile_id);

    $location = $business->locations->where('points_pin',(string)$request->pin)->first();

    if (!$location){
      return response('Bad Pin Number',401);
    }

    return response('',201);
  }
}
