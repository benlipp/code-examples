<?php

namespace App\Http\Controllers;

use SILTools\Helpers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

use App\Models\Participant;
use App\Models\SubmittedRegistration;
use App\Models\Registration;
use App\Models\History;

use Carbon\Carbon;

class ParticipantsController extends AppController
{
  protected $layout = 'layouts.master';

  public function index()
  {
    if ($_POST) {
      $showResults = true;
      $first_name = Input::get('first_name');
      $last_name = Input::get('last_name');
      $searchQuery = Input::get('search');
      //       /*
      //       if (strpos($searchQuery, " ") > 0 && strpos($searchQuery,",") == 0){ //if we have space and no comma
      //       // we have first and last names, in order
      //       $searchArray = explode(" ",$searchQuery);
      //       $participants = Participant::where('person_first_name','like',$searchArray[0])
      //       ->where('person_last_name','like',$searchArray[1])
      //       ->orderBy('person_last_name','asc')
      //       ->get();
      //     }
      //     else if (strpos($searchQuery, ",") > 0){ // if we have commma
      //     // we have last followed by first
      //     $searchQuery = str_replace(" ","",$searchQuery); // remove any potential spaces
      //     $searchArray = explode(",",$searchQuery);
      //     $participants = Participant::where('person_last_name','like',$searchArray[0]) // last name comes first here
      //     ->where('person_first_name','like',$searchArray[1])
      //     ->orderBy('person_last_name','asc')
      //     ->get();
      //   } else {
      //   $participants = Participant::where('person_first_name', 'like', $searchQuery)
      //   ->orwhere('person_last_name', 'like', Input::get('search'))
      //   ->orderBy('person_last_name','asc')
      //   ->get();
      // }
      // */

      $participants = Participant::where('person_first_name', 'like', $first_name.'%')
      ->where('person_last_name', 'like', $last_name.'%')
      ->orderBy('person_last_name', 'ASC')
      ->orderBy('person_first_name', 'ASC')
      ->get();
    } else {
      $showResults = false;
      $first_name = '';
      $last_name = '';
      $searchQuery = '';
      $participants = [];
    }
    return View::make('participants.participants_list', array(
      'participants'=>$participants,
      'showResults'=>$showResults,
      'query'=>$searchQuery,
      'first_name'=>$first_name,
      'last_name'=>$last_name
    ));
  }

  public function edit($participant_id)
  {
    $participant = Participant::find($participant_id);
    $fields = array_keys(Participant::first()->toArray());
    return View::make('participants.form', array('participant' => $participant));
  }

  public function create()
  {
    $participant = new Participant;
    //$fields = array_keys(Participant::first()->toArray());
    return View::make('participants.form', array('participant' => $participant));
  }

  public function store()
  {
    if (Input::get('id')) {
      $participant = Participant::find(Input::get('id'));
    } else {
      $participant = new Participant;
    }
    $person_birthday = Helpers::assemble_date_time(Input::get('person_birthday'));
    $participant->person_birthday = $person_birthday;

    $money_date_paid = Helpers::assemble_date_time(Input::get('money_date_paid'));
    $participant->money_date_paid = $money_date_paid;

    $money_due_date = Helpers::assemble_date_time(Input::get('money_due_date'));
    $participant->money_due_date = $money_due_date;

    $data = Input::get('participant');
    foreach ($data as $key => $value) {
      $participant->$key = stripslashes($value);
    }

    $participant->save();
    Session::flash('message', 'Participant Successfully Updated');
    return Redirect::to('/admin/participants/'.$participant->id.'/edit');
  }

  // /*public function historiesList($participant_id)
  // {
  // $participant = Participant::find($participant_id);
  // $histories = $participant->histories();
  // return View::make('participants.histories_list', array('participant' => $participant, 'histories' => $histories,));
  // }
  // */


  public function delete()
  {
    $participant_id = Input::get('participant_id');
    $part = Participant::find($participant_id);

    $part->delete();
    return "deleted";
  }

  public function walkupStore(Request $request)
  {
    $reg_id = $request->registration_id;
    $walkup = $request->walkup;
    $submit_reg = new SubmittedRegistration;

    if($walkup['parent_guardian']['parent_guardian_1_name'] != ""){
      $walkup['parent_guardian']['parent_guardian_1_coach'] = "Yes";
    } else {
      $walkup['parent_guardian']['parent_guardian_1_coach'] = "No";
    }

    $submit_reg->walkup = '1';
    $submit_reg->status = '1';
    $submit_reg->registration_id = $reg_id;
    $submit_reg->date_time = Carbon::now()->toDateTimeString();
    $myData = $walkup;

    foreach ($myData as $myKey => $subData){
      foreach ($subData as $key => $data){
        $myData[$myKey][$key] = str_replace("\u00a0"," ",$data); // change non-breaking spaces to regular
        $myData[$myKey][$key] = htmlentities($data, ENT_QUOTES); // get rid of nasty quotes
        $myData[$myKey][$key] = str_replace("\\","",$data); // bye bye backslashes
      }
    }
    $submit_reg->data = json_encode($myData);
    $submit_reg->save();

    Session::flash('success_message', 'Walkup Successfully Created!');
    return Redirect::to('/admin/registrations');
  }

  public function walkup($reg_id)
  {
    $registration = Registration::find($reg_id);
    $adult_sizes = array('adult-small'=>'Small','adult-medium'=>'Medium','adult-large'=>"Large",'adult-xl'=>'XL','adult-xxl'=>'XXL','adult-xxxl'=>'XXXL');
    return View::make('participants.walkup', [
      "reg"=>$registration,
      "shirt_sizes"=>$adult_sizes
    ]);
  }

  public function detailedInfo($participant_id)
  {
    $participant = Participant::find($participant_id);
    $histories = $participant->histories()->orderBy('id','DESC')->get();
    $registrations = $participant->registrations()->get();

    return View::make('participants.details', array(
      'participant' => $participant,
      'histories'=>$histories,
      'registrations'=>$registrations
    ));
  }

  public function historyEdit($history_id)
  {
    $history = History::find($history_id);
    $participant = $history->participant();
    return View::make('participants.histories_form', array('participant' => $participant, 'history' => $history));
  }

  public function historyCreate($participant_id)
  {
    $history = new History;
    $participant = Participant::find($participant_id);
    return View::make('participants.histories_form', array('participant' => $participant, 'history' => $history));
  }

  public function historyStore()
  {
    if (Input::get('id')) {
      $history = History::find(Input::get('id'));
    } else {
      $history = new History;
    }
    $participant_id = Input::get('participant_id');

    $data = Input::get('history');

    $data['last_date_paid'] = Helpers::assemble_date_time(Input::get('history.last_date_paid'));

    foreach ($data as $key => $value) {
      $history->$key = $value;
    }

    $history->participant_id = $participant_id;
    $history->save();
    Session::flash('message', 'History Successfully Updated');
    return Redirect::to('/admin/participants/'.$participant_id."/info#history");
  }

  function updateGrade(Request $request){
    $from_post = false;
    if ($request->isMethod('post')){
      Participant::where('person_grade','!=',"")->chunk(50,function($participants){
        // let's fix messed up grades
        //$valid_grades = ['k'];
        foreach($participants as $p){

          if($p->person_grade === 12){
            $p->person_grade = "";
          } else if (strtoupper($p->person_grade) === "K"){
            $p->person_grade = 1;
          } else if (is_numeric($p->person_grade)){
            $p->person_grade++;
          }
          $p->save();
          $from_post = true;
        }
      });
    }
    return view('participants.update_grade',['from_post'=>$from_post]);
  }

}
