<div>
	<span class="pull-right text-right">
	<!--<small><strong>All Locations</strong></small>-->
	<br/>
	</span>
	<div class="pull-right" id="legendDiv">
    <div class="pull-right" id="legendDiv">
      <span style="background-color:rgba(220,220,220,1); color:black; padding: 3px">&nbsp;&nbsp; Awarded &nbsp;&nbsp;</span>
      &nbsp;&nbsp;
      <span style="background-color:rgba(32,98,113,1); color:white; padding: 3px">&nbsp;&nbsp; Redeemed &nbsp;&nbsp;</span>
    </div>
  </div>

	<small>Awarded<?=Auth::user()->isBusinessProfile()?" and Redeemed":""?></small>
</div>
<div class="m-t-sm">
	<div class="row">
		<div class="col-md-12">
			<div>
				<canvas id="rewardHist" height="175" width="550"></canvas>
			</div>
		</div>
	</div>
</div>

<script>
var context = $("#rewardHist").get(0).getContext("2d");
var chartData = <?=$chart_data?>;
var chart1 = chartData.datasets[0];
chart1.fillColor = "rgba(220,220,220,0.5)";
chart1.strokeColor = "rgba(220,220,220,1)";
chart1.pointColor = "rgba(220,220,220,1)";
chart1.pointStrokeColor = "#fff";
chart1.pointHighlightFill = "#fff";
chart1.pointHighlightStroke = "rgba(220,220,220,1)";


var chart2 = chartData.datasets[1];
chart2.fillColor = "rgba(32, 98, 113,0.5)";
chart2.strokeColor = "rgba(32, 98, 113,0.7)";
chart2.pointColor = "rgba(32, 98, 113,1)";
chart2.pointStrokeColor = "#fff";
chart2.pointHighlightFill = "#fff";
chart2.pointHighlightStroke = "rgba(32, 98, 113,1)";

var rewardChart = new Chart(context).Line(chartData,{
			animationSteps: 6,
        responsive: true,
			maintainAspectRatio: true,
			multiTooltipTemplate: "<%= datasetLabel %> - <%= value %>"
});

</script>
