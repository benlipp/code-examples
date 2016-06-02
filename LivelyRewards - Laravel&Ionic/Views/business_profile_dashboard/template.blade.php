@extends('admin.template')
@section('content')

<div class="row">
  <div class="col-md-8">
    [[-- <div class="ibox float-e-margins">
      <div class="ibox-title">
        <h2>Quick Snapshot</h2>
      </div>
    </div> --]]
    <div class="row">
      @foreach($quick_info as $info)
        <div class="col-md-3">
    			<div class="ibox float-e-margins">
    				<div class="ibox-title">
    					<h5><?=$info['title']?></h5>
    				</div>
    				<div class="ibox-content">
    					<h1 class="no-margins"><?=$info['data']?></h1>
    					<small><?=$info['info']?></small>
    				</div>
    			</div>
    		</div>
      @endforeach
    </div>
    <div class="row">
    	<div class="col-lg-8">
    		<div class="ibox float-e-margins">
          <div class="ibox-title">
        		<h5>Reward Point History</h5>
        	</div>
    			<div class="ibox-content">
    				<div id="dashboard-rewardHistSection"></div>
    			</div>
    		</div>
    	</div>
      <?php
      if (Auth::user()->businessProfile->isSingleLocation() ){
        ?>
        @include('admin.location_dashboard.pin_widget')
        <?php
      } else {
        ?>
        @include('admin.business_profile_dashboard.pin_widget')
        <?php
      }
      ?>

    </div>
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-title">
            <h2>Communication History</h2>
          </div>
          <div class="ibox-content">
            <div id="dashboard-commHistory"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="row">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h2>Membership Metrics</h2>
        </div>
        <div class="ibox-content">
          <div id="dashboard-metricsSection"></div>
        </div>
      </div>
    </div>
    <div class="row">
      @include('admin.business_profile_dashboard.points_widget')
    </div>
  </div>
</div>

<?php
if ( !Auth::user()->businessProfile->isSingleLocation() ){
  ?>
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h2>Locations</h2>
        </div>
        <div class="ibox-content">
          <div id="dashboard-locationInfoSection"></div>
        </div>
      </div>
    </div>
  </div>
  <?php
}
?>

<script>
function loadBprofileInfo(){
  var section = $("#dashboard-bprofileInfoSection");
  section.showLoading();
  $.get('/dashboard/bp-info-section',function(response){
    section.html(response);
  });
}
function loadBprofilePoints(){
  var section = $("#dashboard-bprofilePointsSection");
  section.showLoading();
  $.get('/dashboard/bp-points-section',function(response){
    section.html(response);
  });
}
function loadLocationInfo(){
  var section = $("#dashboard-locationInfoSection");
  section.showLoading();
  $.get('/dashboard/location-info-section',function(response){
    section.html(response);
  });
}
function loadMetricsInfo(){
  var section = $("#dashboard-metricsSection");
  section.showLoading();
  $.get('/dashboard/metrics-section',function(response){
    section.html(response);
  });
}
function loadRewardHist(){
  var section = $("#dashboard-rewardHistSection");
  section.showLoading();
  $.get('/dashboard/reward-hist-section',function(response){
    section.html(response);
  });
}
function loadCommHist(){
  var section = $("#dashboard-commHistory");
  section.showLoading();
  $.get('/dashboard/comm-hist-section',function(response){
    section.html(response);
  });
}

var locationsInterval = false;
function updateTitleHeights(){
    var largest = 0;
    $(".location-titles").css('height','auto');
    $(".location-titles").each(function(){
        if ( $(this).height() > largest ){
            largest = $(this).height()
        }
    })
    $(".location-titles").height(largest);
}

$().ready(function(){
  loadBprofileInfo();
  loadBprofilePoints();
  loadLocationInfo();
  loadMetricsInfo();
  loadRewardHist();
  loadCommHist();
  clearInterval(locationsInterval);
  locationsInterval = setInterval(updateTitleHeights,500);
    updateTitleHeights();
});
</script>
@stop
