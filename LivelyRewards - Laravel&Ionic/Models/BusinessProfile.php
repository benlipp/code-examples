<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Rewards;
use App\Models\Coupon;
use App\Models\User;
use App\Models\Image;
use App\Models\CommunicationCredits;
use App\Models\Point;
use App\Models\PointsSpent;
use App\Models\UserBusinessAssociation as UserBA;
use App\Models\EmailHistory as CommHistory;

use Carbon\Carbon;
use \DB;

class BusinessProfile extends Model {
	protected $table = 'business_profiles';

	public function locations()
	{
		return $this->hasMany('App\Models\Location','prof_id');
	}

	public function hasEnoughData()
	{
		$req_fields = [
			'address_line_1',
			'public_url_prefix',
			'city',
			'state',
			'zip',
			'phone',
			'email'
		];
		foreach ($req_fields as $r){
			if ( $this->$r == '' ){
				return FALSE;
			}
		}
		return TRUE;
	}

	public function scopeFromIdentifier($query, $identifier){
		return $query->where('public_url_prefix',$identifier);
	}

	public function getUserAttribute()
	{
		$user = User::where('entity_id',$this->id)->where('type','business-profiles')->first();
		return $user;
	}

	public function publicUsers(){
		return $this->belongsToMany('App\Models\User','user_business_assoc','business_profile_id','user_id')->withPivot('points')->withTimestamps();
	}

	public function rewards()
	{
		return Reward::where('type','business-profiles')->where('entity_id',$this->id)->get();
	}

	public function getRewardsAttribute()
	{
		return Reward::where('type','business-profiles')->where('entity_id',$this->id)->get();
	}

	public function pointsLock()
	{
		return ($this->points_lock == 1);
	}

	public function image()
	{
		return $this->hasOne('App\Models\Image','id','image_id');
	}

	public function rewardsLock()
	{
		return ($this->rewards_lock == 1);
	}

	public function couponsLock()
	{
		return ($this->coupons_lock == 1);
	}


	public function getBusinessProfileAttribute(){
		return $this;
	}

	public function isBusinessProfile(){
		return TRUE;
	}

	public function isLocation(){
		return FALSE;
	}

	public function isSingleLocation()
	{
		return $this->locations->count() == 1;
	}

	public function editCommCredits($credits,$reason)
	{
		$myCredits = $this->current_comm_credits;
		$myCredits += $credits;
		$this->current_comm_credits = $myCredits;
		$this->save();

		$commCredits = new CommunicationCredits();
		$commCredits->bprof_id = $this->id;
		$commCredits->credits_added = $credits;
		$commCredits->reason = $reason;
		$commCredits->save();
	}

	public function getCouponsAttribute()
	{
		return Coupon::where('type','business-profiles')->where('entity_id',$this->id)->get();
	}

	function monthlyPointsAwarded()
	{
		$past_time = Carbon::today()->startOfMonth()->subMonths(5)->toDateTimeString(); //6 months ago to now
		$points = Point::where('business_profile_id', $this->id)
			->where('created_at','>=',$past_time)
			->orderBy('created_at','asc')
		->get();
		$monthly_data = [];
		foreach ($points as $pt)
		{
			$carbon_time = Carbon::parse($pt->created_at);
			$my_date = $carbon_time->format('My');
			$my_points = $pt->points;
			if(! array_key_exists($my_date,$monthly_data)){
				$monthly_data[$my_date]= 0;
			}
			$monthly_data[$my_date] += $my_points;
		}
		return $monthly_data;
	}

	function monthlyPointsSpent()
	{
		$past_time = Carbon::today()->startOfMonth()->subMonths(5)->toDateTimeString(); //6 months ago to now
		$points = PointsSpent::where('business_profile_id', $this->id)
		->where('created_at','>=',$past_time)
		->orderBy('created_at','asc')
	->get();
	$monthly_data = [];
	foreach ($points as $pt)
	{
		$carbon_time = Carbon::parse($pt->created_at);
		$my_date = $carbon_time->format('My');
		$my_points = $pt->points;
		if(! array_key_exists($my_date,$monthly_data)){
			$monthly_data[$my_date]= 0;
		}
		$monthly_data[$my_date] += $my_points;
	}
	return $monthly_data;
	}


	function mostActiveUsers()
	{
		$past_time = Carbon::today()->subDays(30)->toDateTimeString();
		$points = Point::where('business_profile_id', $this->id)
			->where('created_at','>',$past_time)
			->getQuery()
			->select('user_id','points',DB::raw("SUM(points) AS points"))
			->groupBy('user_id')
			->orderBy('points','desc')
			->take(3)
		->get();
		$users = [];
		foreach($points as $pt)
		{
			$my_user = User::find($pt->user_id);
      if($my_user){
        $users[] = [
  				"name"=>$my_user->name,
  				"points"=>$pt->points
  			];
      }
		}
		return $users;
	}

	function mostUsedRewards()
	{
		$past_time = Carbon::today()->subDays(30)->toDateTimeString();
		$points_spent = PointsSpent::where('business_profile_id', $this->id)
			->where('created_at','>',$past_time)
			->getQuery() // get the direct DB query
			->select('reward_id',DB::raw('count(*) as total'))
			->groupBy('reward_id')
			->orderBy('total','desc')
			->take(3)
		->get(); // gives us an array of stdclass objects
		$rewards = [];
		foreach ($points_spent as $point){
			$rewards[] = Reward::find($point->reward_id);
		}
		return $rewards;
	}

	function newestUsers()
	{
		$past_time = Carbon::today()->subDays(30)->toDateTimeString();
		$user_assoc = UserBA::where('business_profile_id',$this->id)
			->where('created_at','>',$past_time)
			->orderBy('created_at','desc')
			->take(3)
			->select('user_id')
		->get();
		$users = [];
		foreach ($user_assoc as $ua){
			$users[] = User::find($ua->user_id);
		}
		return $users;
	}

	function getPointsPinAttribute(){
		if (!$this->isSingleLocation() ){
			return FALSE;
		}

		return $this->locations->first()->points_pin;
	}

	function totalMembers()
	{
		$number_of_members = UserBA::where('business_profile_id',$this->id)->count();
		return $number_of_members;
	}

	public function members(){
		return $this->belongsToMany('App\Models\User','user_business_assoc','business_profile_id','user_id')->withPivot('points')->withTimestamps();
	}

	function newMembers()
	{
		$past_time = Carbon::today()->subDays(7)->toDateTimeString();
		$number_of_members = UserBA::where('business_profile_id',$this->id)
			->where('created_at','>',$past_time)
		->count();
		return $number_of_members;
	}

	function totalPointsAwarded()
	{
		$points_entries = Point::where('business_profile_id',$this->id)
			->select('points')
		->get();
		$number_of_points = 0;
		foreach ($points_entries as $entry){
			$number_of_points += $entry->points;
		}
		return $number_of_points;
	}

	function totalPointsRedeemed()
	{
		$points_entries = PointsSpent::where('business_profile_id',$this->id)
			->select('points')
		->get();
		$number_of_points = 0;
		foreach ($points_entries as $entry){
			$number_of_points += $entry->points;
		}
		return $number_of_points;
	}

	function activeRewardsCount()
	{
		$rewards = Reward::where('type','business-profiles')
			->where('entity_id',$this->id)
			->where('active',1)
		->count();
		return $rewards;
	}

	function inactiveRewardsCount()
	{
		$rewards = Reward::where('type','business-profiles')
			->where('entity_id',$this->id)
			->where('active',0)
		->count();
		return $rewards;
	}

	function commHistory()
	{
		$communications = CommHistory::where('business_profile_id',$this->id)
			->orderBy('created_at','desc')
      ->limit(10)
		->get();
		return $communications;
	}

	function pointsAwarded(){
		return $this->hasMany('\App\Models\Point','business_profile_id');
	}

	function pointsSpent(){
		return $this->hasMany('\App\Models\PointsSpent','business_profile_id');
	}

	function userRewards(){
		return $this->hasMany('\App\Models\UserReward','business_profile_id');
	}

	function getImageAttribute(){
		$image = Image::find($this->image_id);
		if($image){
			return $image->url;
		}
	}

	public function usesCoupons(){
        if( in_array("coupons",config('lc.plans.'.$this->plan_code.'.features')) ){
            return TRUE;
        }
        return FALSE;
    }
}
