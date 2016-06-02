<?php

namespace App\Http\Controllers\AdminControllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\BusinessProfile as BProfile;

use Auth;
use View;

use Carbon\Carbon;

class DashboardController extends Controller
{

  private $entity;

  function __construct(){
    if (Auth::user()->isBusinessProfile() ){
      $this->entity = Auth::user()->businessProfile;
    } elseif ( Auth::user()->isLocation() ){
      $this->entity = Auth::user()->location;
    }
  }

  function template()
  {
    //$bprofile = BProfile::find(Auth::user()->businessProfileId());
    $quick_info = [
      "total" => [
        "title" => "Total Members",
        "data" => $this->entity->totalMembers(),
        "info" => Auth::user()->isLocation()?"This Location":"All Locations"
      ],
      "new" => [
        "title" => "New Members",
        "data" => $this->entity->newMembers(),
        "info" => "Past 7 Days"
      ],
      "awarded" => [
        "title" => "Points Awarded",
        "data" => $this->entity->totalPointsAwarded(),
        "info" => "Total"
      ]
    ];

    if ( Auth::user()->isBusinessProfile() ){
      $quick_info["redeemed"] = [
        "title" => "Points Redeemed",
        "data" => $this->entity->totalPointsRedeemed(),
        "info" => "Total"
      ];
    }

    $template = 'admin.business_profile_dashboard.template';
    if ( Auth::user()->isLocation() ){
      $template = 'admin.location_dashboard.template';
    }

    if ( Auth::user()->isBusinessProfile() ){
      $title = Auth::user()->businessProfile->name.' Dashboard';
    } elseif (Auth::user()->isLocation()) {
      $title = Auth::user()->location->name ." Dashboard";
    }
    return view($template,[
      'entity'=>$this->entity,
      "title"=>$title,
      "quick_info"=>$quick_info
    ]);
  }

  function bprofileInfo()
  {
    $bprofile = BProfile::find(Auth::user()->businessProfileId())->toArray();
    return view('admin.business_profile_dashboard.bprofileInfo',[
      'business_profile' => $bprofile
    ]);
  }

  function bprofilePoints()
  {
    $bprofile = BProfile::find(Auth::user()->businessProfileId());
    return view('admin.business_profile_dashboard.bprofilePoints',[
      'business_profile' => $bprofile
    ]);
  }

  function locationInfo()
  {
    $bprofile = BProfile::find(Auth::user()->businessProfileId());
    $locations = $bprofile->locations()->get();
    $locations_final = [];
    foreach ($locations as $loc){
      $locations_final[] = [
        'id' => $loc->id,
        'name' => $loc->name,
        'members' => $loc->numberOfMembers()
      ];
    }
    return view('admin.business_profile_dashboard.locationInfo',[
      'business_profile' => $bprofile,
      "locations" => $locations_final
    ]);
  }

  function rewardInfo()
  {
    $bprofile = BProfile::find(Auth::user()->businessProfileId());
    $rewards = $bprofile->rewards();
    return view('admin.business_profile_dashboard.rewardInfo',[
      'rewards' => $rewards
    ]);
  }

  function metrics()
  {
    //$bprofile = BProfile::find(Auth::user()->businessProfileId());
    $most_active = $this->entity->mostActiveUsers();
    $top_rewards = $this->entity->mostUsedRewards();
    $reward_info = [
      [
        "name" => "Number of Active Rewards",
        "data" => $this->entity->activeRewardsCount()
      ],
      [
        "name" => "Number of Inactive Rewards",
        "data" => $this->entity->inactiveRewardsCount()
      ]
    ];



    if ( Auth::user()->isBusinessProfile() ){
      $reward_info[] = [
        "name" => "Most Claimed Reward",
        "data" => @$top_rewards[0]->name
      ];
      $reward_info[] = [
        "name" => "2nd Most Claimed Reward",
        "data" => @$top_rewards[1]->name
      ];
    }

    return view('admin.business_profile_dashboard.metrics',[
      'most_active'=>$most_active,
      'reward_info'=>$reward_info
    ]);
  }

  function rewardPointHistory()
  {
    $awarded_hist = $this->entity->monthlyPointsAwarded();
    $spent_hist = $this->entity->monthlyPointsSpent();
    // we gotta format this freaking data now
    // start by making an array of keys
    $hist_keys = [];
    for ($i=5; $i >= 0; $i--){
      $hist_keys[] = Carbon::today()->subMonths($i)->format('My');
    }
    // awarded data
    $awarded_hist_final = [];
    foreach ($hist_keys as $hkey){
      if(array_key_exists($hkey,$awarded_hist)){
        $awarded_hist_final[] = $awarded_hist[$hkey];
      } else {
        $awarded_hist_final[] = 0;
      }
    }
    $spent_hist_final = [];
    if ( Auth::user()->isBusinessProfile() ){
      // spent data
      $spent_hist_final = [];
      foreach ($hist_keys as $hkey){
        if(array_key_exists($hkey,$spent_hist)){
          $spent_hist_final[] = $spent_hist[$hkey];
        } else {
          $spent_hist_final[] = 0;
        }
      }
    }

    //make our labels
    $chart_labels = [];
    for ($i=5; $i >= 0; $i--){
      $chart_labels[] = Carbon::today()->startOfMonth()->subMonths($i)->format('F');
    }
    $datasets = [
      [
        "label"=>"Awarded",
        "data"=>$awarded_hist_final
      ],
      [
        "label"=>"Spent",
        "data"=>$spent_hist_final
      ]
    ];
    $final_data = [
      "labels"=>$chart_labels,
      "datasets" => $datasets
    ];

    return view('admin.business_profile_dashboard.rewardPointHistory',[
      'chart_data'=>json_encode($final_data)
    ]);
  }

  function commHistory()
  {
    $bprofile = BProfile::find(Auth::user()->businessProfileId());
    $raw_hist = $bprofile->commHistory();
    $comm_hist = [];
    foreach ($raw_hist as $hist){
      $comm_hist[] = [
        "comm_type" => $hist->comm_type,
        "subject" => $hist->subject,
        "count" => $hist->person_count,
        "date" => Carbon::parse($hist->created_at)->format("M. d, Y")
      ];
    }
    return view('admin.business_profile_dashboard.commHistory',[
      'history'=>$comm_hist
    ]);
  }

  function updateLocationPin(Request $request){

    if ( Auth::user()->businessProfile->isSingleLocation() ){
      $this->entity = Auth::user()->businessProfile->locations->first();
    }

    if (!$this->entity->checkPinFormat($request->pin)){
      $request->session()->flash('error','Pin must be 4 numbers and be unique amongst your locations');
    } else {
      $this->entity->points_pin = $request->pin;
      $this->entity->save();
      $request->session()->flash('success','Pin has been updated');
    }

    return Redirect('/dashboard');
  }

  function pointsProcess(Request $request){

    if ( Auth::user()->isLocation() ){
      $l = Auth::user()->location;
      $l->points_visit = $request->points_per_visit;
      $l->points_dollar = $request->points_per_dollar;
      $l->hours_between_visits = $request->hours_between_visits;
      $l->save();
    } else {
      $business_profile = Auth::user()->businessProfile;
      $business_profile->points_for_signup = $request->points_for_signup;
      $business_profile->points_visit = $request->points_per_visit;
      $business_profile->points_dollar = $request->points_per_dollar;
      $business_profile->hours_between_visits = $request->hours_between_visits;
      $business_profile->points_lock = $request->points_lock;
      $business_profile->save();

      if ( $business_profile->points_lock || $business_profile->isSingleLocation() ){
        foreach ($business_profile->locations as $l){
          $l->points_visit = $request->points_per_visit;
          $l->points_dollar = $request->points_per_dollar;
          $l->hours_between_visits = $request->hours_between_visits;
          $l->save();
        }
      }
    }

    $request->session()->flash('success','Points have been updated');
    return Redirect('/dashboard');

  }

}
