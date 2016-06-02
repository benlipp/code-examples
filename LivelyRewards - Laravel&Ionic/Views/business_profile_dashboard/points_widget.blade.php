<div class="ibox float-e-margins">
  <div class="ibox-title">
    <h5>Points Management</h5>
  </div>
  <div class="ibox-content">
    <div id="pointsDisplay">
      <div class="col-lg-12">
        <div class="row">
          <strong>Points Per Visit: </strong><?=Auth::user()->businessProfile->points_visit?><br />
          <strong>Points Per Dollar Spent: </strong><?=Auth::user()->businessProfile->points_dollar?><br />
          <!-- <strong>Signup Points: </strong><?=Auth::user()->businessProfile->points_for_signup?><br /> -->
          <strong>Min. Time Between Visits (hours): </strong><?=Auth::user()->businessProfile->hours_between_visits?><br />
          <strong>Points Lock: </strong><?=Auth::user()->businessProfile->points_lock?"ON":"OFF"?><br />
          <strong>Signup Points: </strong> <?=Auth::user()->businessProfile->points_for_signup?><br/><br/>
        </div>
      </div>
      <a href="javascript:;" onclick="showPointsFormContainer()" class="btn btn-primary btn-lg">Edit Points Settings</a>
    </div>
    <div id="pointsFormContainer" style="display: none">
      <form id="pointsForm" action="/dashboard/points" method="POST">
        <div class="row">
          <div class="col-md-6">
            <strong>Points Per Visit</strong>
            <input type="text" class="form-control" name="points_per_visit" value="<?=Auth::user()->businessProfile->points_visit?>">
          </div>
          <div class="col-md-6">
            <strong>Min. Time Between Visits (hours)</strong>
            <input type="text" class="form-control" name="hours_between_visits" value="<?=Auth::user()->businessProfile->hours_between_visits?>">
          </div>

        </div>
        <hr />
        <div class="row">
          <div class="col-md-6">
            <strong>Points Per Dollar Spent</strong>
            <input type="text" class="form-control" name="points_per_dollar" value="<?=Auth::user()->businessProfile->points_dollar?>">
          </div>
          <div class="col-md-6">
            <strong>Points Lock</strong>
            <select name="points_lock" class="form-control">
              <option value="0" <?=!Auth::user()->businessProfile->points_lock?"SELECTED":""?>>Off</option>
              <option value="1" <?=Auth::user()->businessProfile->points_lock?"SELECTED":""?>>On</option>
            </select>
          </div>
        </div>
        <hr />
        <div class="row">
          <div class="col-md-12">
            <strong>Sign Up Points</strong>
            <input type="text" class="form-control" name="points_for_signup" value="<?=Auth::user()->businessProfile->points_for_signup?>">
          </div>
        </div>
        <hr/>
        <button type="submit" class="btn btn-primary btn-lg">Update</button>
        <a href="javascript:;" class="btn btn-cancel btn-lg" onclick="cancelPoints()">Cancel</a>
      </form>
    </div>
  </div>
</div>

<script>
function showPointsFormContainer(){
  $("#pointsDisplay").hide();
  $("#pointsFormContainer").fadeIn();
}
function cancelPoints(){
  $("#pointsFormContainer").fadeOut();
  $("#pointsDisplay").show();
  return false;
}
</script>
