<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\Team;
use App\Models\Roster;
use App\Models\Participant;

use SILTools\Reports\GeneralReport;
use SILTools\Reports\TeamReport;
use SILTools\Reports\VolunteerReport;

use App\Models\SubmittedRegistration;
use App\Models\Registration;

use \App;
use \View;
use Carbon\Carbon;

class StandardReportsController extends Controller {
  function youth($team_id)
  {
    $my_report = new TeamReport($team_id);
    return View::make('standard-reports.youth',['standardReport'=>$my_report])->render();
  }

  function website($team_id)
  {
    $my_report = new TeamReport($team_id);
    return View::make('standard-reports.youth',[
      'standardReport'=>$my_report,
      'website'=>true
    ])->render();
  }

  function general(Request $request,$registration_id)
  {
    $registration = Registration::find($registration_id);
    $submitted = SubmittedRegistration::where('registration_id',$registration_id)->where('data','!=','')->get()->toArray();
    //risa wants this ordered by last name
    usort($submitted,function($sub_a,$sub_b){
      $a_data = json_decode($sub_a['data'],true);
      $b_data = json_decode($sub_b['data'],true);
      $my_value = strcasecmp($a_data['participant_info']['last_name'],$b_data['participant_info']['last_name']);
      if($my_value == 0){
        $my_value = strcasecmp($a_data['participant_info']['first_name'],$b_data['participant_info']['first_name']);
      }
      return $my_value;
    });
    //dd($submitted);
    $reports = [];
    foreach ($submitted as $s){
      $reports[] = new GeneralReport($s['id']);
    }
    $excel = App::make('excel');
    $report_name = Carbon::now()->toDateString()." GeneralReport ".$registration->name;
    $excel->create($report_name,function($excel) use ($reports){
      $excel->sheet("sheet1",function($sheet) use ($reports){
        $sheet->appendRow($reports[0]->getHeaders());
        foreach ($reports as $rep){
          $sheet->appendRow($rep->getData());
        }
      });
    })->export('csv');
  }

  function volunteer(Request $request,$registration_id)
  {
    $registration = Registration::find($registration_id);
    $submitted = SubmittedRegistration::where('registration_id',$registration_id)->get(['id']);
    $ids = [];
    foreach ($submitted as $s){
      $ids[] = $s->id;
    }
    $pg_arrays = [];
    foreach ($ids as $id){
      $vol = new VolunteerReport($id);
      $pg_arrays[] = $vol->getData();
      //pg_arrays contains arrays where each key has up to 2 report objects. lets pull them out
    }
    $my_headers = $vol->getHeaders();
    $reports = [];
    foreach ($pg_arrays as $pgs){
      if(isset($pgs)){
        foreach($pgs as $pg){ // now we're in each object
          $reports[] = $pg;
        }
      }
    }
    usort($reports,function($rep_a,$rep_b){
      $a_name = implode("",array_reverse(explode(" ",$rep_a['Volunteer Name'])));//Ryan Solida -> SolidaRyan
      $b_name = implode("",array_reverse(explode(" ",$rep_b['Volunteer Name'])));//Ben Lippincott -> LippincottBen
      return strcasecmp($a_name,$b_name);//solidaryan > lippincottben, so it puts the $rep_b value first :)
    });

    $excel = App::make('excel');
    $excel->create('volunteer_report',function($excel) use ($reports,$my_headers){
      $excel->sheet("Sheet1",function($sheet) use ($reports,$my_headers){
        $sheet->appendRow($my_headers);
        $sheet->rows($reports);
      });
    })->export('csv');
  }
}
