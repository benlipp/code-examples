<!-- Bprofile Box -->
<div class="widget lazur-bg p-sm animated fadeIn">
	<div class="pull-right">
		<button class="btn btn-warning btn-sm" type="button" title="Edit" id="dashboard-bprofileInfoEdit">
			<i class="fa fa-pencil"></i> Edit
		</button>
	</div>
	<div class="m-b-md">
		<h2 class="font-bold no-margins"><?=$business_profile["name"]?></h2>
	</div>
	<div class="row">
		<div class="col-sm-4 col-xs-12">
		</div>
		<div class="col-sm-8 col-xs-12">
			<ul class="list-unstyled m-t-md">
				<?php
				$business_profile_show_keys = [
					"address_line_1" => "Address Line 1",
					"address_line_2" => "Address Line 2",
					"city" => "City",
					"state" => "State",
					"zip" => "Zip Code",
					"phone" => "Phone"
				];
				foreach ($business_profile as $key => $value){
					if (array_key_exists($key,$business_profile_show_keys) && $value){
						?><li>
							<label><?=$business_profile_show_keys[$key]?>: </label>
							<?=$value?>
						</li><?php
					}
				}
			?></ul>
		</div>
	</div>
</div>
	<!-- End Bprofile Box -->
