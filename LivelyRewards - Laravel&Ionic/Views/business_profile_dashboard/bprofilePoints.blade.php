<div class="widget yellow-bg p-sm">
	<h2 class="font-bold no-margins">Loyalty Points Settings</h2>
	<ul class="list-unstyled m-t-xs">
		<li>
			<span class="fa fa-check-circle-o m-r-xs"></span>
			<label>Points per visit:</label>
			<?php if(! $business_profile->pointsLock()){
				?><span><?=(int)$business_profile->points_visit?></span><?php
			} ?>
		</li>
		<li>
			<span class="fa fa-money m-r-xs"></span>
			<label>Points per dollar spent:</label>
			<?php if(! $business_profile->pointsLock()){
				?><span><?=(int)$business_profile->points_dollar?></span><?php
			} ?>
		</li>
		<li>
			<span class="fa fa-clock-o m-r-xs"></span>
			<label>Minimum Hours Between Visits:</label>
			<?php if(! $business_profile->pointsLock()){
				?><span><?=(int)$business_profile->hours_between_visits?></span><?php
			} ?>
		</li>
		<li>
			<span class="fa fa-key m-r-xs"></span>
			<label>Points Lock:</label>
			<?php if($business_profile->pointsLock()){
				?><span>On</span><?php
			} else {
				?><span>Off</span><?php
			}?>
		</li>
	</ul>
	<div class="col-md-6 col-md-offset-4">
		<button class="btn btn-info" type="button">Edit Settings</button>
	</div>
	<div class="clearfix"></div>
</div>
