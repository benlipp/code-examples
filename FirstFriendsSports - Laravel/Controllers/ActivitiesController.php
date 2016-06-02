<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Program;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class ActivitiesController extends Controller
{
  protected $layout = 'layouts.master';

  public function index()
  {
    $activities = Activity::orderBy('name','asc')->get();
    return View::make('activities.activities_list', array('activities' => $activities));
  }

  public function create()
  {
    $activity_info = Input::get('data');
    $activity_name = $activity_info['activity_name'];
    $activity = new Activity;
    $activity->name = $activity_name;
    $activity->save();
    return $activity->id;
  }

  public function programs($activity_id)
  {
    $activity = Activity::find($activity_id);
    $programs = $activity->programs()->get();
    return View::make('activities.program_list', array('programs' => $programs, 'activity' => $activity));
  }

  public function store()
  {
    if (Input::get('id')) {
      $activity = Activity::find(Input::get('id'));
    } else {
      $activity = new Activity;
    }
    foreach (Input::get('data') as $key => $value) {
      $activity->$key = stripslashes($value);
    }
    $activity->save();
    Session::flash('message', 'Activity Successfully Updated');
    return Redirect::to('/admin/activities');
  }

  public function activitiesJSON($activity_id)
  {
    $programs = Program::where('activity_id', $activity_id)->get();
    foreach ($programs as $p) {
      $program_names[$p->id] = $p->name;
    }
    return json_encode($program_names);
  }

  public function editActivityName()
  {
    $activity_info = Input::get('data');
    $activity_id = $activity_info['activity_id'];
    $activity_name = $activity_info['activity_name'];
    $activity = Activity::find($activity_id);
    $activity->name = $activity_name;
    $activity->save();
    return "";
  }
}
