@extends('layouts.master')
@section('content')
  <h2><?=$program->name?> <small><?=$seshion->name?></small></h2>
  <a href="/admin/programs/<?=$program->id?>/sessions">View or Edit Sessions</a>
</br>
<br>
  <a href="/admin/programs/{{$program->id}}/schedule?seshion_id=<?=$seshion->id?>">View Schedule</a><br>
  <a href="/admin/programs/{{$program->id}}/practice-schedule?seshion_id=<?=$seshion->id?>">View Practice Schedule</a>
  <br/>


  <?php
  if ($division)
  {
    ?>
    <br /><br /><?=$division->name?> Division<br />
    <a href="/admin/programs/<?=$program->id?>">Choose Another Division</a>
    <?php
  }
  ?><h3>Teams</h3>
  <a href="/admin/teams/create?program_id=<?=$program->id?>&session_id=<?=$seshion->id?><?=$division?"&division_id=".$division->id:""?>" class='btn btn-primary'>Create New Team</a><br /><br />

  <table class="table">
    <tr>
      <th>Team</th>
      <th>Actions</th>
    </tr>
    <?php
    if ($teams){
      foreach ($teams as $team)
      {?>
        <tr>
          <td class="myTD" rel="<?=$team->id?>"><?=$team->name?></td>
          <td><!--<button class="btn btn-sm btn-primary editbtn" rel="<?=$team->id?>" data-team-name="<?=$team->name?>">Edit Team</button>-->
            <a href="/admin/teams/{!!$team->id!!}/edit" class="btn btn-primary btn-sm">Edit</a>
            <a href="/admin/teams/<?=$team->id?>/roster" class="btn btn-warning btn-sm">Edit Roster</a>
            <a href="/admin/teams/{!!$team->id!!}/contactinfo" class="btn btn-default btn-sm">Contact Info</a>
            <a href="/admin/standard-reports/youth/{{$team->id}}" class="btn btn-primary btn-sm">Team Report</a>
          </td>
        </tr>
        <?php
      }} ?>
    </table>

    <h3>Games</h3>
    <a href="/admin/games/create?program_id=<?=$program->id?>&session_id=<?=$seshion->id?><?=$division?"&division_id=".$division->id:""?>" class="btn btn-primary">Create Game</a>
    <br /><br />
    <table class="table">
      <tr>
        <th>Game Time</th>
        <th>Game Date</th>
        <th>Location</th>
        <th>Teams</th>
        <th>Score</th>
        <th>Actions</th>
      </tr>
      <?php
      if ($games != ''){
        foreach ($games as $game)
        { if ($game->home_team == "") {
          $home_team = "TBA";
        } else {
          $home_team = $game->home_team->name;
        }
        if ($game->away_team == "") {
          $away_team = "TBA";
        } else
        {
          $away_team = $game->away_team->name;
        }

        ?>
        <tr>
          <td><?=$game->gameTime()?></td>
          <td><?=$game->gameDate()?></td>
          <td><?=$game->venue?></td>
          <td><?=$away_team?> @ <?=$home_team?></td>
          <td>
            <?
            if ( $game->away_team_score_1 ){
              if ( $program->activity->slug == 'volleyball' ){
                ?>
                <?=$game->away_team_score_1?>-<?=$game->home_team_score_1?> /
                <?=$game->away_team_score_2?>-<?=$game->home_team_score_2?> /
                <?=$game->away_team_score_3?>-<?=$game->home_team_score_3?>
                <?
              } else {
                ?>
                <?=$game->away_team_score_1?> - <?=$game->home_team_score_1?>
                <?
              }
            }
            ?>
          </td>
          <td>
            <a href="/admin/games/<?=$game->id?>/edit?program_id=<?=$program->id?>&session_id=<?=$seshion->id?><?=$division?"&division_id=".$division->id:""?>" class="btn btn-sm btn-primary">Edit Game</a>
            <button class="btn btn-sm btn-danger delete_game" rel="{!! $game->id !!}">
              <span class="glyphicon glyphicon-trash"></span>
            </button>
          </td>
        </tr>
        <?php
      }
    }
    ?>
    <?/*
    <div class="modal fade" id="edit-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
    <div class="modal-content">
    <div class="modal-header">
    <h4 class="modal-title" id="myModalLabel">Edit Team Name</h4>
    </div>
    <div class="modal-body">
    <input class="form-control" type="text" id="teamname" value="">
    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" id="savebtn">Save changes</button>
    </div>
    </div>
    </div>
    </div>
    */?>
    <script type="text/javascript">
    $().ready(function(){
      var teamID;
      $('.delete_game').on('click',function(){
        var game_id = $(this).attr('rel');
        var game_object = { "game_id" : game_id};
        $(this).parents('tr').remove();
        $.post('/admin/games/delete',game_object);
      });


      /*$('.editbtn').click(function(){
      teamName = $(this).data('teamName');
      teamID = $(this).attr('rel');
      $('#edit-modal').modal();
      $('#teamname').val(teamName);
    });
    $('#savebtn').click(function(){
    saved_name = $('#teamname').val();
    $('#edit-modal').modal('hide');
    $('.myTD[rel='+teamID+']').html(saved_name);
    $('.editbtn[rel='+teamID+']').data('teamName',saved_name);
    var teamObject = {
    "team_id": teamID,
    "team_name": saved_name
  };
  $.post("/ajax/teamname",teamObject)
}); */
});
</script>
@stop
