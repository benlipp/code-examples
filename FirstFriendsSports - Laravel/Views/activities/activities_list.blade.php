@extends('layouts.master')

@section('content')
  <div class="row">
    <div class="col-md-12">
      <h2>Activities</h2>
      <button id="createbtn" onclick="create();" class="btn btn-primary">Create New Activity</button>
      <br /><br />

      <table id="activityTable" class="table">
        <tr>
          <th>Activity Name</th>
          <th>Actions</th>
        </tr>
        <?php
        foreach($activities as $activity){
          ?>
          <tr>
            <td class="myTD" rel="<?=$activity->id?>">
              <?=$activity->name?>
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-default editbtn" rel="<?=$activity->id?>"
                data-activity-name="<?=$activity->name?>">Edit</button>
                <a href="/admin/activities/<?=$activity->id?>/programs"
                  class="btn btn-sm btn-primary">View Programs</a>
                </td>
              </tr>
              <?
            }
            ?>
          </table>
        </div>
      </div>

      <table id="myTemplate" style="display: none">
        <tr class="myTR" rel="ACTIVITYID">
          <td class="myTD" rel="ACTIVITYID">ACTIVITYNAME</td>
          <td>
            <button type="button" class="btn btn-sm btn-default editbtn" rel="ACTIVITYID"
            data-activity-name="ACTIVITYNAME">Edit</button>
            <a href="/admin/activities/ACTIVITYID/programs"
            class="btn btn-sm btn-primary">View Programs</a>
          </td>
        </tr>
      </div>

      <div class="modal fade" id="my-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="myModalLabel">...</h4>
            </div>
            <div class="modal-body">
              <input class="form-control" type="text" id="actname" value="">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" id="savebtn" onclick="save();">Save changes</button>
            </div>
          </div>
        </div>
      </div>


      <script type="text/javascript">
      var create_or_edit;
      var activityID;

      $().ready(function(){

        $('.editbtn').click(function(){

          $('#myModalLabel').html('Edit Activity')
          activityName = $(this).data('activityName');
          activityID = $(this).attr('rel');
          $('#my-modal').modal();
          $('#actname').val(activityName);
          create_or_edit = "edit";
        });
      });
      function create(){
        create_or_edit = "create";
        console.log("Clicked Create");
        $('#actname').val("");
        $('#myModalLabel').html('Create Activity');
        $('#my-modal').modal();
      }

      function save(){
        if(create_or_edit == "edit"){
          saved_name = $('#actname').val();
          $('#my-modal').modal('hide');
          $('.myTD[rel='+activityID+']').html(saved_name);
          $('.editbtn[rel='+activityID+']').data('activityName',saved_name);
          var activityObject = {
            "activity_id" : activityID,
            "activity_name" : saved_name
          };
          $.post( "/ajax/activityname",{data:activityObject},function(){});
        }
        else{
          // Create was clicked
          saved_name = $('#actname').val();
          $('#my-modal').modal('hide');
          var activityObject = {
            "activity_name" : saved_name
          };
          $.post("/admin/activities/create",{data:activityObject},function(response){
            rowHtml = $("#myTemplate").html().replace(/ACTIVITYID/g,response).replace(/ACTIVITYNAME/g,saved_name);
            $("#activityTable").append(rowHtml);
          });
        }
      }
      </script>


    @stop
