<?php foreach($rewards as $reward){
	?><div class="col-sm-4">
	<div class="widget reward-widget lazur-bg p-sm">
		<div class="row m-b-xs">
			<div class="col-xs-12">
				<div class="pull-right">
					<a id="dashboard-editBusinessProfileReward" rel="<?=$reward->id?>" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i> Edit</a>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="col-sm-4 col-xs-12">
				<div class="text-center"><?php
				if ($reward->image_id){
					?><img src="<?=$reward->image->url?>"><?php
				}
				?></div>
			</div>
			<div class="col-sm-8 col-xs-12">
				<h3><strong><?=$reward->name?></strong></h3>
				<p>Points Required: <?=$reward->point_cost?></p>
				<p>Number Claimed: x</p>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
</div><?php
	}
	?>
