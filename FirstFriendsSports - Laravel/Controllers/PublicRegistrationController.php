<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use App\Models\Registration;
use App\Models\Participant;
use App\Models\SubmittedRegistration;
use App\Models\History;
use App\Models\Payment;


class PublicRegistrationController extends Controller {
	public function __construct(){
		if(Session::get('registration_id')){
			$this->registration = Registration::find(Session::get('registration_id'));
		}
		//print_r($form_order);
		$this->route_array = Str::parseCallback(Route::currentRouteAction(), null);
		$this->review = Input::get('review');
		$this->set_up_form_steps();
		if($this->route_array[1] == 'start'){
			Session::flush();
		}
		if ( $this->registration ){
			if ( !$this->registration->isActive() ){
				echo  View::make('public.registration.inactive_registration',array('registration'=>$this->registration));
				exit;
			}
		}
	}

	public function set_up_form_steps(){
		if ( !$this->registration ){
			return;
		}
		$form_order = array(
			array('name'=>'participant-info','display_name'=>'Player Information','form_prefix'=>'player','label_prefix'=>'Player'), //0
			array('name'=>'find-player-record','display_name'=>'Player Information','form_prefix'=>''), //1
			array('name'=>'parent-guardian-info','display_name'=>'Parent / Guardian Information','form_prefix'=>'parentguardian','label_prefix'=>'Parent / Guardian'), //2
			array('name'=>'contact-info','display_name'=>'Contact Information','form_prefix'=>'contact','label_prefix'=>'Contact'), //3
			array('name'=>'school-info','display_name'=>'School Information','form_prefix'=>'school','label_prefix'=>'School'), //4
			array('name'=>'clothes','display_name'=>'Clothes','form_prefix'=>'clothes','label_prefix'=>''), //5
			array('name'=>'emergency-medical-info','display_name'=>'Emergency / Medical Information','form_prefix'=>'emergency'), //6
			array('name'=>'misc','display_name'=>'Miscellaneous Information','form_prefix'=>'misc'), //7
			array('name'=>'payment','display_name'=>'Enter Payment'), //8
			array('name'=>'information-review','display_name'=>'Review Your Information'), //9
			array('name'=>'registration-confirmation','display_name'=>'Registration Confirmation'), //10
			//array('name'=>'success','display_name'=>'Success'), //11
			//array('name'=>'to-waiting-list','display_name'=>'Waiting List'), //12
		);
		if( !$this->registration->hasClothing() ){ //if we're lacking clothing (haha)
			unset($form_order[5]);
		}

		//IF WE ARE IN A REG THAT HAS A WAITING LIST
		if ( $this->registration->waitingList() ){ // remove payments, add waiting list
			unset($form_order[8]);
			unset($form_order[10]);
			//$form_order[] = array('name'=>'to-waiting-list');
		} else {
			unset($form_order[12]);
		}
		if ( !$this->registration->isYouthRegistration() ){ //if we're an adult league
			unset($form_order[2]);
			unset($form_order[4]); //remove the steps we don't need
		}
		$form_order = array_merge($form_order); //put the order back together
		//get current step
		$cur_form = Request::segment(2);
		//print_r($form_order);
		//run through the steps and see what steps are next and previous relative to our current position
		$cur_form_step_num = 0;
		$count = 0;
		foreach ($form_order as $step){
			if ( $step['name'] == $cur_form ){
				$cur_form_step_num = $count;
			}
			$count++;
		}
		$this->next_step = $form_order[$cur_form_step_num+1];
		if( $this->review == 1){
			foreach ($form_order as $k=>$info){
				if ( $info['name'] == 'information-review' ){
					$this->next_step = $form_order[$k];
				}
			}
			//$this->next_step = $form_order[9];
		}
		$this->prev_step = FALSE;
		if ( $cur_form_step_num >  0 ){
			$this->prev_step = $form_order[$cur_form_step_num-1];
		}
	}

	public function go_to_next_step(){
		return Redirect::to('/registration/'.$this->next_step['name']);
	}

	public function start($id=false){
		Session::put('registration_id', $id);
		return Redirect::to('/registration/participant-info');
	}
	public function participant_info(){
		if($_POST){
			Session::put('participant.participant_info', array('first_name'=>Input::get('first_name'),'last_name'=>Input::get('last_name'), 'birth_date'=>date('Y-m-d',strtotime(Input::get('birthdate'))),'age'=>floor((time() - strtotime(Input::get('birthdate'))) / 31556926),'gender'=>Input::get('gender')));
			if(Input::get('review') == 1){
				return Redirect::to('/registration/find-player-record?review=1');
			} else {
				return Redirect::to('/registration/find-player-record');
			}
		}
		//print_r(Session::all());
		$registration = Registration::find(Session::get('registration_id'));
		return View::make('public.registration.registration_participant_info',array(
			'registration'=>$registration,
			'participant'=>$participant,
			'next'=>$this->next_step
		));
	}

	public function find_player_record(){
		//print_r(Session::all());
		if(Session::get('registration_id') == ''){
			return Redirect::to('/');
		}
		$matchparticipant = Participant::where('person_first_name',Session::get('participant.participant_info.first_name'))->where('person_last_name',Session::get('participant.participant_info.last_name'))->where('person_birthday',date('Y-m-d',strtotime(Session::get('participant.participant_info.birth_date'))))->get();
		if($_POST){
			if(Input::get('review') == 1){
				Session::put('participant.participant_info.id', Input::get('participantID'));
				return Redirect::to('/registration/information-review');
			} else {
				if(Input::get('participantID') != ''){
					$participant = Participant::where('id', Input::get('participantID'))->first();
					Session::put('participant.participant_info.first_name',$participant->person_first_name);
					Session::put('participant.participant_info.last_name',$participant->person_last_name);
					Session::put('participant.participant_info.birth_date',$participant->person_birthday);
					Session::put('participant.participant_info.id', Input::get('participantID'));
				}
				//ready to go to the next step
				return $this->go_to_next_step();
			}
		}
		return View::make('public.registration.registration_find_player_record',array(
			'registration'=>$this->registration,
			'participant'=>$participant,
			'matchparticipant'=>$matchparticipant,
			'review'=>$review,
			'next'=>$this->next_step
		));
	}

	public function parent_guardian_info(){
		//print_r(Session::all());
		if(Session::get('registration_id') == ''){
			return Redirect::to('/');
		}
		$adult_sizes = array('adult-small'=>'Small','adult-medium'=>'Medium','adult-large'=>"Large",'adult-xl'=>'XL','adult-xxl'=>'XXL','adult-xxxl'=>'XXXL');
		if($_POST){
			Session::put('participant.parent_guardian.parent_guardian_1_name',Input::get('guardian1_name'));
			Session::put('participant.parent_guardian.parent_guardian_1_relation',Input::get('guardian1_relation'));
			Session::put('participant.parent_guardian.parent_guardian_1_coach',Input::get('guardian1_coach'));
			Session::put('participant.parent_guardian.parent_guardian_1_coach_shirt',Input::get('guardian1_coach_shirt'));
			Session::put('participant.parent_guardian.parent_guardian_2_name',Input::get('guardian2_name'));
			Session::put('participant.parent_guardian.parent_guardian_2_relation',Input::get('guardian2_relation'));
			Session::put('participant.parent_guardian.parent_guardian_2_coach',Input::get('guardian2_coach'));
			Session::put('participant.parent_guardian.parent_guardian_2_coach_shirt',Input::get('guardian2_coach_shirt'));
			return $this->go_to_next_step();
		}
		// 		if(Input::get('review') == 1){
		// 		$next = 'Information Confirmation';
		// 	}else{
		// 	$next = 'Contact Information';
		// }
		return View::make('public.registration.registration_parent_guardian_info',array(
			'adult_sizes'=>$adult_sizes,
			'registration'=>$this->registration,
			'participant'=>$participant,
			'matchparticipant'=>$matchparticipant,
			'prev'=>$this->prev_step,
			'next'=>$this->next_step
		));
	}

	public function contact_info(){
		//print_r(Session::all());
		if(Session::get('registration_id') == ''){
			return Redirect::to('/');
		}
		if($_POST){
			Session::put('participant.contact_info.primary_email',Input::get('primary_email'));
			Session::put('participant.contact_info.secondary_email',Input::get('secondary_email'));
			Session::put('participant.contact_info.address',Input::get('address'));
			Session::put('participant.contact_info.address2',Input::get('address2'));
			Session::put('participant.contact_info.city',Input::get('city'));
			Session::put('participant.contact_info.state',Input::get('state'));
			Session::put('participant.contact_info.zip',Input::get('zip'));
			for($i=1;$i<=3;$i++){
				Session::put('participant.contact_info.phone_number_'.$i,Input::get('phone_number_'.$i));
				Session::put('participant.contact_info.phone_number_'.$i.'_type',Input::get('phone_number_'.$i.'_type'));
				Session::put('participant.contact_info.phone_number_'.$i.'_texts',Input::get('phone_number_'.$i.'_texts'));
				Session::put('participant.contact_info.phone_number_'.$i.'_name',Input::get('phone_number_'.$i.'_name'));
			}

			// 		if(Input::get('review') == 1){
			// 		return Redirect::to('/registration/information-confirmation');
			// 	} else {
			// 	if($this->registration->check_school){
			// 	return Redirect::to('/registration/school-info');
			// } else {
			// if($this->registration->clothes_1_name){
			// return Redirect::to('/registration/clothes');
			// } else {
			// return Redirect::to('/registration/emergency-medical-info');
			// }
			// }
			// }
			return $this->go_to_next_step();
		}
		return View::make('public.registration.registration_contact_info',array(
			'registration'=>$this->registration,
			'prev'=>$this->prev_step,
			'next'=>$this->next_step
		));
	}

	public function school_info(){
		//print_r(Session::all());
		if(Session::get('registration_id') == ''){
			return Redirect::to('/');
		}
		if($_POST){

			//Session::put('participant.school_info.school_name',Input::get('school_name'));
			Session::put('participant.school_info.current_grade',Input::get('current_grade'));
			Session::put('participant.school_info.school_system',Input::get('school_system'));
			Session::put('participant.school_info.school',Input::get('school'));
			Session::put('participant.school_info.school_text',Input::get('school_text'));
			Session::put('participant.school_info.playing_experience_bool',Input::get('playing_experience_bool'));
			Session::put('participant.school_info.playing_experience',Input::get('playing_experience'));

			return $this->go_to_next_step();
		}
		$grades = array('k'=>'Kindergarten','1'=>'1st Grade','2'=>'2nd Grade','3'=>'3rd Grade','4'=>'4th Grade','5'=>'5th Grade','6'=>'6th Grade','7'=>'7th Grade','8'=>'8th Grade','9'=>'9th Grade','10'=>'10th Grade','11'=>'11th Grade','12'=>'12th Grade');
		return View::make('public.registration.registration_school_info',array(
			'registration'=>$this->registration,
			'grades'=>$grades,
			'prev'=>$this->prev_step,
			'next'=>$this->next_step
		));
	}

	public function clothes(){
		if(Session::get('registration_id') == ''){
			return Redirect::to('/');
		}
		$adult_sizes = array('adult-small'=>'Small','adult-medium'=>'Medium','adult-large'=>"Large",'adult-xl'=>'XL','adult-xxl'=>'XXL','adult-xxxl'=>'XXXL');
		$youth_sizes = array('youth-small'=>'Small','youth-medium'=>'Medium','youth-large'=>"Large");
		if($_POST){
			for($i=1;$i<=5;$i++){
				Session::put('participant.clothes.clothes_'.$i.'_name',Input::get('clothes_'.$i.'_name'));
				Session::put('participant.clothes.clothes_'.$i.'_size',Input::get('clothes_'.$i.'_size'));
			}

			return $this->go_to_next_step();
		}
		return View::make('public.registration.registration_clothes',array(
			'registration'=>$this->registration,
			'adult_sizes'=>$adult_sizes,
			'youth_sizes'=>$youth_sizes,
			'prev'=>$this->prev_step,
			'next'=>$this->next_step
		));
	}

	public function emergency_medical_info(){
		//print_r(Session::all());
		if(Session::get('registration_id') == ''){
			return Redirect::to('/');
		}
		if($_POST){
			for($i=1;$i<=2;$i++){
				Session::put('participant.emergency_medical.emergency_contact_'.$i.'_name',Input::get('emergency_contact_'.$i.'_name'));
				Session::put('participant.emergency_medical.emergency_contact_'.$i.'_phone1',Input::get('emergency_contact_'.$i.'_phone1'));
				Session::put('participant.emergency_medical.emergency_contact_'.$i.'_phone2',Input::get('emergency_contact_'.$i.'_phone2'));
			}
			Session::put('participant.emergency_medical.preferred_hospital',Input::get('preferred_hospital'));
			Session::put('participant.emergency_medical.doctor',Input::get('doctor'));
			Session::put('participant.emergency_medical.dentist',Input::get('dentist'));
			Session::put('participant.emergency_medical.medical_conditions',stripslashes(Input::get('medical_conditions')));
			Session::put('participant.emergency_medical.emergency_action_permission',Input::get('emergency_action_permission'));

			return $this->go_to_next_step();
		}
		return View::make('public.registration.registration_emergency_medical_info',array(
			'registration'=>$this->registration,
			'prev'=>$this->prev_step,
			'next'=>$this->next_step
		));
	}

	public function team(){
		return Redirect::to('/registration/misc');
	}

	public function misc(){
		if(Session::get('registration_id') == ''){
			return Redirect::to('/');
		}
		//print_r(Session::all());
		if($_POST){
			Session::put('participant.misc.church_affiliation', Input::get('church_affiliation'));
			Session::put('participant.misc.playing_experience', Input::get('playing_experience'));
			for($i=1;$i<=3;$i++){
				Session::put('participant.misc.custom_question_'.$i,Input::get('custom_question_'.$i));
			}
			Session::put('participant.misc.photo_permission', Input::get('photo_permission'));
			Session::put('participant.misc.waiver_informed_consent', Input::get('waiver_informed_consent'));

			Session::put('participant.misc.height_feet',Input::get('height_feet'));
			Session::put('participant.misc.height_inches',Input::get('height_inches'));
			Session::put('participant.misc.weight',Input::get('weight'));

			return $this->go_to_next_step();
		}

		return View::make('public.registration.registration_misc',array(
			'registration'=>$this->registration,
			'prev'=>$this->prev_step,
			'next'=>$this->next_step
		));
	}

	public function payment(){
		//print_r(Session::all());
		require_once(app_path().'/../config/stripe.php');
		require_once(app_path()."/../vendor/stripe/stripe-php/lib/Stripe.php");
		\Stripe::setApiKey($stripe_config['secret_key']);
		if($_POST){
			Session::put('participant.payment.stripe_token',Input::get('stripe_token'));
			return $this->go_to_next_step();

		}
		return View::make('public.registration.registration_payment',array(
			'registration'=>$this->registration,
			'prev'=>$this->prev_step,
			'next'=>$this->next_step
		));

	}

	public function information_review(){
		if(Session::get('registration_id') == ''){
			return Redirect::to('/');
		}

		require_once('../config/stripe.php');
		$stripe_config = array('secret_key'=>'sk_test_4euT8aEjh5ALOjGBikdkV294','publishable_key'=>'pk_test_UlXfr1b5HiM1X0Vo9wUf83ND');
		require_once("../vendor/stripe/stripe-php/lib/Stripe.php");

		\Stripe::setApiKey($stripe_config['secret_key']);

		if($_POST){
			Session::put('participant.payment.registration_cost_total',Input::get('registration_cost_total'));
			Session::put('participant.costs.registration_cost_total',Input::get('registration_cost_total'));
			foreach (Input::get('payment') as $key => $value){
				Session::put('participant.costs.'.$key,$value);

			}
			$stripe_token = Session::get('participant.payment.stripe_token');
			try {
				if ( $this->registration->waitingList() ) {
					$charge = "lel";
				} else {
					$customer = \Stripe\Customer::create([
						'card' => $stripe_token,
						'email'=>Session::get('participant.contact_info.primary_email'),
						'description'=>Session::get('participant.participant_info.first_name').' '.Session::get('participant.participant_info.last_name') . ' - '.Session::get('participant.contact_info.phone_number_1'),
					]);

					$charge = \Stripe\Charge::create(array('customer' => $customer->id, 'amount' => Session::get('participant.payment.registration_cost_total')*100, 'currency' => 'usd'));
					//Session::put('participant.payment.cc_trans_id', $charge->id);
					Session::put('participant.payment.stripe_trans_id',$charge->balance_transaction);
					Session::put('participant.payment.stripe_charge_id',$charge->id);
				}


				/**
				We've made it past payment successfully so time to get these records in place
				**/

				/**
				CREATE OR EDIT PARTICIPANT RECORD
				**/
				if(Session::get('participant.participant_info.id')){
					$participant = Participant::find(Session::get('participant.participant_info.id'));
				} else {
					$participant = new Participant;
				}
				$participant->person_first_name = Session::get('participant.participant_info.first_name');
				$participant->person_last_name = Session::get('participant.participant_info.last_name');
				$today = new \DateTime(date('Y/m/d'));
				$birthday = new \DateTime(Session::get('participant.participant_info.birth_date'));
				$person_age = $today->diff($birthday)->y;
				$participant->person_age = $person_age;
				$participant->person_grade = Session::get('participant.school_info.current_grade');
				$participant->person_birthday = Session::get('participant.participant_info.birth_date');
				$participant->person_sex = Session::get('participant.participant_info.gender');
				$participant->person_parent_guardian_1 = Session::get('participant.parent_guardian.parent_guardian_1_name');
				$participant->person_parent_guardian_2 = Session::get('participant.parent_guardian.parent_guardian_2_name');
				$participant->emergency_primary_name = Session::get('participant.emergency_medical.emergency_contact_1_name');
				$participant->emergency_primary_phone_1 = Session::get('participant.emergency_medical.emergency_contact_1_phone1');
				$participant->emergency_primary_phone_2 = Session::get('participant.emergency_medical.emergency_contact_1_phone2');
				$participant->emergency_secondary_name = Session::get('participant.emergency_medical.emergency_contact_2_name');
				$participant->emergency_secondary_phone_1 = Session::get('participant.emergency_medical.emergency_contact_2_phone1');
				$participant->emergency_secondary_phone_2 = Session::get('participant.emergency_medical.emergency_contact_2_phone2');
				$participant->contact_email_address_1 = Session::get('participant.contact_info.primary_email');
				$participant->contact_email_address_2 = Session::get('participant.contact_info.secondary_email');
				$participant->contact_address_line_1 = Session::get('participant.contact_info.address');
				$participant->contact_address_line_2 = Session::get('participant.contact_info.address2');
				$participant->contact_city = Session::get('participant.contact_info.city');
				$participant->contact_state = Session::get('participant.contact_info.state');
				$participant->contact_zip_code = Session::get('participant.contact_info.zip');
				$participant->other_doctor = Session::get('participant.emergency_medical.doctor');
				$participant->other_dentist = Session::get('participant.emergency_medical.dentist');
				$participant->other_hospital_pref = Session::get('participant.emergency_medical.preferred_hospital');
				$participant->other_medical_conditions = Session::get('participant.emergency_medical.medical_conditions');
				$participant->other_church = Session::get('participant.misc.church_affiliation');

				if ( Session::get('participant.misc.height_feet') != '' ){
					$participant->person_height_feet = Session::get('participant.misc.height_feet');
				}
				if ( Session::get('participant.misc.height_inches') != '' ){
					$participant->person_height_inches = Session::get('participant.misc.height_inches');
				}
				if ( Session::get('participant.misc.weight') != '' ){
					$participant->person_weight = Session::get('participant.misc.weight');
				}


				//money
				$participant->money_amt_paid = Session::get('participant.payment.registration_cost_total');
				$participant->money_date_paid = date("Y-m-d");


				if (Session::get('participant.school_info.school')){
					$participant->other_employer_school = Session::get('participant.school_info.school');
				}
				if (Session::get('participant.school_info.school_text')){
					$participant->other_employer_school = Session::get('participant.school_info.school_text');
				}

				//Adding clothing size to participant record
				$participant->person_size = Session::get('participant.clothes.clothes_1_size');

				for ($i=1;$i<=3;$i++){
					$mainField = "contact_phone_".$i;
					$numberField = $mainField."_number";
					$typeField = $mainField."_type";
					$textsField = $mainField."_texts";
					$nameField = $mainField."_name";
					$participant->$numberField = Session::get('participant.contact_info.phone_number_'.$i);
					$participant->$typeField = Session::get('participant.contact_info.phone_number_'.$i.'_type');
					$participant->$textsField = Session::get('participant.contact_info.phone_number_'.$i.'_texts');
					$participant->$nameField = Session::get('participant.contact_info.phone_number_'.$i.'_name');
				}

				$participant->save();

				/**
				CREATE SUBMITTED REG RECORD
				**/
				$submitted_reg = new SubmittedRegistration;
				$submitted_reg->registration_id = Session::get('registration_id');
				$submitted_reg->player_id = $participant->id;
				$submitted_reg->status = 1;

				if(Session::get('participant.payment.transaction_id')){
					$submitted_reg->paid_with_cc = 1;
					// here it gets hacky

					$submitted_reg->stripe_trans_id = Session::get('participant.payment.stripe_trans_id');
					$submitted_reg->stripe_charge_id = Session::get('participant.payment.stripe_charge_id');
					$submitted_reg->cc_amt_paid = Session::get('participant.payment.registration_cost_total');
				}
				$submitted_reg->playing_experience_bool = Session::get('participant.school_info.playing_experience_bool');
				$submitted_reg->playing_experience = Session::get('participant.misc.playing_experience');

				for ( $i=1; $i<=5; $i++ ){
					$clothes_field = "clothes_".$i;
					$size_field = "clothes_".$i."_size";
					if(Session::get('participant.clothes.clothes_'.$i.'_name')){
						$submitted_reg->$clothes_field = Session::get('participant.clothes.clothes_'.$i.'_name');
						$submitted_reg->$size_field = Session::get('participant.clothes.clothes_'.$i.'_size');
					}
				}
				$date_time = date('Y-m-d H:i:s');
				Session::put('participant.payment.transaction_date_time',$date_time);
				$myData = Session::get('participant');
				$total_clothes_cost = 0;
        $cost_section = &$myData['costs']; //gonna reference so we can "edit" the array
  			$clothesArray = [];
  			if($cost_section){
  				foreach ($cost_section as $key => $val){
  					if($key == "activity_fee" || $key == "registration_cost_total" || $key =="clothes"){
  						continue;
  					} else {
  						$clothesArray[] = [
  							"name"=>$key,
  							"cost"=>$val
  						];
							$total_clothes_cost += $val;
  						unset($cost_section[$key]);
  					}
  				}
  				$cost_section['clothes'] = $clothesArray;
  				//this should now be edited
  				unset($cost_section);
				foreach ($myData as $myKey => $subData){
					foreach ($subData as $key => $data){
						$myData[$myKey][$key] = str_replace("\u00a0"," ",$data); // change non-breaking spaces to regular
						$myData[$myKey][$key] = htmlentities($data, ENT_QUOTES); // get rid of nasty quotes
						$myData[$myKey][$key] = str_replace("\\","",$data); // bye bye backslashes
					}
				}
				$cc_trans_id = $submitted_reg->generateTransactionId($this->registration,$total_clothes_cost);
				$submitted_reg->cc_trans_id = $cc_trans_id;
				$myData['payment']['cc_trans_id'] = $cc_trans_id;
				$jsonData = json_encode($myData);
				$submitted_reg->date_time = $date_time;
				$submitted_reg->data = $jsonData;

				if ( $this->registration->waitingList()){
					$submitted_reg->waitlist = 1;
				}

				$submitted_reg->save();
				/**
				ENTER HISTORY RECORD
				**/
				$history = new History;
				$history->participant_id = $participant->id;
				$history->activity_id = $this->registration->activity_id;
				$history->program_id = $this->registration->program_id;
				$history->seshion_id = $this->registration->program->currentSeshionID();
				$history->amount_paid = $submitted_reg->cc_amt_paid;
				$history->last_date_paid = date("Y-m-d");
				$history->type = "player";
				$history->status = "Active";
				if ( $this->registration->waitingList() ){
					$history->status = "Inactive";
				}
				$history->save();

				/**
				ENTER PAYMENT RECORD
				**/

				$charge = json_decode($charge);
				$payment = new \App\Models\Payment;
				$payment->participant_id = $participant->id;
				$payment->registration_id = $this->registration->id;
				$payment->amount = Session::get('participant.payment.registration_cost_total');
				$payment->date = date("Y-m-d");
				if ( !$this->registration->waitingList() ){
					$payment->save();
				}


				/**
				Send email
				*/
				$people_to_send = array($this->registration->email_1, $this->registration->email_2, $this->registration->email_3);
				$my_email_reg = $this->registration;
				foreach ($people_to_send as $email){
					if ($email != '')
					Mail::send('emails.admin', array('registration' => Session::get('participant') ), function($message) use ($my_email_reg,$email)
					{
						$message->to($email)->subject($my_email_reg->name);
					});
				}

				/**
				DONE, send them back
				*/
				return Redirect::to('/registration/registration-confirmation');

				//make sure we don't error first
			} catch(\Stripe_CardError $e) {
				$body = $e->getJsonBody();
				$error  = $body['error'];
				//print_r($error);
				Session::put('participant.cc_error',$error['message']);
				return Redirect::to('/registration/payment');
				exit;
			}

		}
		return View::make('public.registration.registration_information_review',array(
			'participant'=>Session::get('participant'),
			'registration'=>$this->registration,
			'prev'=>$this->prev_step,
			'next'=>$this->next_step
		));
	}
	public function registration_confirmation(){

		$participant = Session::get('participant');
		if ( !$participant ){
			return Redirect::to('/');
		}
		Session::flush(); //make sure user can't register again

		return View::make('public.registration.registration_confirmation',array(
			'registration'=>$this->registration,
			'participant'=>$participant
		));
	}

		/*
		EXAMPLE OF SUCCESSFUL CHARGE OBJECT
		{
		"id": "ch_3OV4DXYP9FlErh",
		"object": "charge",
		"created": 1390939352,
		"livemode": false,
		"paid": true,
		"amount": 2000,
		"currency": "usd",
		"refunded": false,
		"card": {
		"id": "card_3OV4DIUmeNIq51",
		"object": "card",
		"last4": "4242",
		"type": "Visa",
		"exp_month": 1,
		"exp_year": 2015,
		"fingerprint": "LFFofvgKIwWEMA5o",
		"customer": null,
		"country": "US",
		"name": "Ryan Solida",
		"address_line1": null,
		"address_line2": null,
		"address_city": null,
		"address_state": null,
		"address_zip": null,
		"address_country": null,
		"cvc_check": "pass",
		"address_line1_check": null,
		"address_zip_check": null
	},
	"captured": true,
	"balance_transaction": "txn_3OV4DsqlpTGQPK",
	"failure_message": null,
	"failure_code": null,
	"amount_refunded": 0,
	"customer": null,
	"invoice": null,
	"description": null,
	"dispute": null,
	"metadata": [

],
"fee": 88,
"fee_details": [
{
"amount": 88,
"currency": "usd",
"type": "stripe_fee",
"description": "Stripe processing fees",
"application": null,
"amount_refunded": 0
}
],
"disputed": false
}
*/
}
