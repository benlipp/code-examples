<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\History;

class Participant extends Model
{
  protected $table = 'participants';

  public function teams()
  {
    return $this->belongsToMany('Team', 'roster_assoc');
  }

  public function getNameAttribute()
  {
    return $this->person_first_name." ".$this->person_last_name;
  }

  public function nameLastFirst()
  {
    return $this->person_last_name.", ".$this->person_first_name;
  }

  public function getAmountDueAttribute()
  {
    $histories = $this->histories;
    $amount_due = 0;
    foreach ($histories as $h) {
      if ($h->amount_due > 0) {
        $amount_due += $h->amount_due;
      }
    }
    return $amount_due;
  }

  public function getPersonSexAttribute()
  {
    if (stripos($this->attributes['person_sex'], 'f') !== false) {
      return 'F';
    } else {
      return 'M';
    }
  }

  public function histories()
  {
    return $this->hasMany('App\Models\History');
  }

  public function registrations()
  {
    return $this->hasMany('App\Models\SubmittedRegistration', 'player_id');
  }

  public function payments()
  {
    return $this->hasMany('App\Models\Payment');
  }

  public function getHistory()
  {
    $participant_id = $this->id;
    $history = History::where('participant_id', $participant_id);
    return $history;
  }

  public function getAgeAttribute()
  {
    $date = new \DateTime($this->person_birthday);
    $now = new \DateTime();
    $interval = $now->diff($date);
    return $interval->y;

  }
}
