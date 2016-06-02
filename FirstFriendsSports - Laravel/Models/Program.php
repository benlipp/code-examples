<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use App\Models\Announcement;
use App\Models\Game;
use App\Models\Seshion;

class Program extends Model
{
  protected $table = 'programs';

  public function activity()
  {
    return $this->belongsTo('App\Models\Activity');
  }

  public function seshions()
  {
    return $this->hasMany('App\Models\Seshion');
  }

  public function setNameAttribute($value)
  {
    $this->attributes['name'] = $value;
    $this->attributes['slug'] = Str::slug($value);
  }

  public function getAnnouncementsAttribute()
  {
    $announcements = Announcement::all();
    $ret_announcements = [];
    foreach ($announcements as $a) {
      $ann = json_decode($a->applicable_to);
      if (in_array($this->id, $ann->programs) || in_array($this->activity->id, $ann->activities)) {
        $ret_announcements[] = $a;
      }
    }
    if (count($ret_announcements) == 0) {
      return false;
    }
    return $ret_announcements;
  }

  public static function genders()
  {
    return array(
      'men'=>"Men's",
      'women'=>"Women's",
      'coed'=>"Coed"
    );
  }

  public function currentSeshionID()
  {
    $seshion = $this->seshions()->orderBy('id')->get()->last();
    return $seshion->id;
  }

  public function currentSeshion()
  {
    $seshion = $this->seshions()->orderBy('id')->get()->last();
    return $seshion;
  }

  public function registration()
  {
    return $this->hasOne('App\Models\Registration');
  }

  public function hasRecentGame()
  {
    $seshions = $this->seshions()->get();
    if (count($seshions) == 0) {
      return false;
    } else {
      $currentSeshion = $this->currentSeshionID();
      $one_month_ago = strtotime('- 1 month');
      $one_month_ago_sql = date('Y-m-d H:i:s', $one_month_ago);

      $recent_game = Game::where('game_datetime', '>', $one_month_ago_sql)
      ->where('seshion_id', '=', $currentSeshion);

      $upcoming_game = Game::where('game_datetime', '>', date("Y-m-d H:i:s"))
      ->where('seshion_id', '=', $currentSeshion);

      if ($recent_game->count() > 0 || $upcoming_game->count() > 0) {
        return true;
      } else {
        return false;
      }
    }
  }

  public function upcomingGames()
  {
    $currentSeshion = $this->currentSeshionID();
    $games = Game::where('game_datetime', '>', date("Y-m-d H:i:s"))->where('seshion_id', '=', $currentSeshion)->orderby('game_datetime', 'asc')->get();
    return $games;
  }

  public function recentGameInfo()
  {
    $currentSeshion = $this->currentSeshionID();
    $one_month_ago = strtotime('- 1 month');
    $one_month_ago_sql = date('Y-m-d H:i:s', $one_month_ago);
    $most_recent_game = Game::where('game_datetime', '>', $one_month_ago_sql)
    ->where('seshion_id', '=', $currentSeshion)
    ->get();


    return $most_recent_game;
  }

  public function pastGameInfo()
  {
    $currentSeshion = $this->currentSeshionID();
    $games = Game::where('seshion_id', '=', $currentSeshion)->orderby('game_datetime', 'asc')->get();

    $past_games = array();
    foreach ($games as $g) {
      if ($g->game_datetime < date("Y-m-d 00:00:00")) {
        $past_games[] = $g;
      }
    }
    return $past_games;
  }

  public function getRosters()
  {
    $current_session_id = $this->currentSeshionID();
    $session = Seshion::find($current_session_id);

    $teams = array();
    foreach ($session->teams as $t) {
      $teams[$t->name] = array();

      foreach ($t->roster_data as $p) {
        $last_name = strtoupper($p->last_name);
        $name = strtoupper($p->first_name.' '.$p->last_name);

        if ($p->role == 'player_coach') {
          $teams[$t->name]['player'][$last_name.rand(0, 100)] = $name;
          $teams[$t->name]['coach'][$last_name.rand(0, 100)] = $name;
          ksort($teams[$t->name]['player']);
          ksort($teams[$t->name]['coach']);
        } else {
          $teams[$t->name][$p->role][$last_name.rand(0, 100)] = $name;
          ksort($teams[$t->name][$p->role]);
        }
      }
    }
    return $teams;
  }


  public function standings()
  {
    if ($this->currentSeshion()){
      $teams_raw = $this->currentSeshion()->teams;
      $teams = [];
      foreach ($teams_raw as $t) {
        $teams[$t->id] = [
          'team_name'=>$t->name,
          'wins'=>0,
          'losses'=>0,
          'ties'=>0
        ];
      }

      $games = $this->currentSeshion()->games;
      foreach ($games as $g) {
        if ($g->home_team_score_1 == 0) {
          continue;
        }

        $results = $g->results();
        if ($results == 'tie') {
          $teams[$g->home_team_id]['ties']++;
          $teams[$g->away_team_id]['ties']++;
        } else {
          $teams[$results['winner']]['wins']++;
          $teams[$results['loser']]['losses']++;
        }
      }

      usort($teams, function ($a, $b) {
        $a_index = $a['wins'] - ($a['losses']*.5);
        $b_index = $b['wins'] - ($b['losses']*.5);
        return $b_index - $a_index;
      });

      return $teams;
    }


  }
  function documents()
  {
    $document_array = [];
    for ($i=1; $i <=3 ; $i++) {
      $prop_name = "document_".$i."_name";
      $prop_file = "document_".$i."_file";
      if($this->$prop_name && $this->$prop_file){
        $document_array[$this->$prop_name] = $this->$prop_file;
      }
    }
    return $document_array;
  }
}
