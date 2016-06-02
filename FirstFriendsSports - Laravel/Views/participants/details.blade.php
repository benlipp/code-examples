@extends('layouts.master')
@section('content')
<?php
$fname = $participant->person_first_name;
$lname = $participant->person_last_name;
$fname = ucfirst(strtolower($fname));
$lname = ucfirst(strtolower($lname));
$fullname = $fname." ".$lname;
$activity_types = array(
	'player'=>'Player',
	'coach'=>'Coach',
	'official'=>'Official',
	'score_keeper'=>'Score keeper',
	'player_coach'=>'Player and Coach',
	'assistant_coach'=>'Assistant Coach');
	?>


	<h2>Detailed Info <small><?=$fullname?></small></h2>
	<a href="/admin/participants">&laquo; Back To Search</a><br /><br />
	<br>
	<ul class="nav nav-tabs custom" role="tablist" id="myTab">
		<li class="active"><a href="#personal-info" role="tab" data-toggle="tab" class="btn-primary">Personal Info</a></li>
		<li><a href="#emergency-info" role="tab" data-toggle="tab" class="btn-danger">Emergency Info</a></li>
		<li><a href="#payment-info" role="tab" data-toggle="tab" class="btn-success">Payment Info</a></li>
		<li><a href="#contact-info" role="tab" data-toggle="tab" class="btn-info">Contact Info</a></li>
		<li><a href="#history" role="tab" data-toggle="tab" class="btn-warning">History</a></li>
		<li><a href="#registrations" role="tab" data-toggle="tab" class="btn-success">Online Registrations</a></li>
		<li><a href="/admin/participants/<?=$participant->id?>/edit" id="edit-tab" class="btn-primary">Edit Participant</a></li>
	</ul>

	<div class="tab-content">

		<div class="tab-pane active" id="personal-info">

			<h3>Personal Info</h3>
			<table class="table table-striped">
				<tbody>
					<tr>
						<th>Participant ID</th>
						<td><?=$participant->id?></td>
					</tr>
					<tr>
						<th>Name</th>
						<td><?=$fullname?></td>
					</tr>
					<tr>
						<th>Date of Birth</th>
						<td><?
						if ($participant->person_birthday != '0000-00-00'){
							$dob = strtotime($participant->person_birthday);
							$final_date = date('M j, Y',$dob);
							echo $final_date;
						}
						?></td>
					</tr>
					<tr>
						<th>Age</th>
						<td><?=$participant->age?></td>
					</tr>
					<tr>
						<th>Sex</th>
						<td><?=$participant->person_sex == "M" ? "Male":"Female"?></td>
					</tr>
					<tr>
						<th>Shirt/Uniform Size</th>
						<td><?=$participant->person_size?></td>
					</tr>
					<tr>
						<th>Parent/Guardian 1</th>
						<td><?=$participant->person_parent_guardian_1?></td>
					</tr>
					<tr>
						<th>Parent/Guardian 2</th>
						<td><?=$participant->person_parent_guardian_2?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="tab-pane" id="emergency-info">

			<h3>Emergency Info</h3>
			<table class="table table-striped">
				<thead>
					<tr>
						<th> </th>
						<th>Name</th>
						<th>Emergency Phone 1</th>
						<th>Emergency Phone 2</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Primary</th>
						<td><?=ucwords(strtolower($participant->emergency_primary_name))?></td>
						<td><?=$participant->emergency_primary_phone_1?></td>
						<td><?=$participant->emergency_primary_phone_2?></td>
					</tr>
					<tr>
						<th>Secondary</th>
						<td><?=$participant->emergency_secondary_name?></td>
						<td><?=$participant->emergency_secondary_phone_1?></td>
						<td><?=$participant->emergency_secondary_phone_2?></td>
					</tr>
				</tbody>
			</table>
			<br/>
			<h4>Doctor Information</h4>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Preferred Hospital</th>
						<th>Doctor</th>
						<th>Dentist</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?=$participant->other_hospital_pref?></td>
						<td><?=$participant->other_doctor?></td>
						<td><?=$participant->other_dentist?></td>
					</tr>
				</tbody>
			</table>
			<h4>Medical Conditions</h4>
			<p><?=$participant->other_medical_conditions?></p>
		</div>

		<div class="tab-pane" id="payment-info">
			<h3>Payment Info</h3>
			<table class="table table-striped">
				<tbody>
					<?php
					?>
					<tr><th>Last Scholarship Year</th><td><?=$participant->money_last_scholarship_year?></td></tr>
					<tr><th>Scholarship Amount</th><td>$<?=$participant->money_scholarship_amount?></td></tr>
					<tr><th>Amount Due</th><td>$<?=$participant->amountDue?></td></tr>
					</tbody>
				</table>
			</div>

			<div class="tab-pane" id="contact-info">
				<h3>Contact Info</h3>
				<table class="table table-striped">
					<tbody>
						<tr>
							<th>E-Mail Address</th>
							<td><?=$participant->contact_email_address_1?></td>
							<td><?=($participant->contact_email_address_2 != "" ? $participant->contact_email_address_2 : "")?></td>
							<td></td>
						</tr>
						<tr>
							<th>Phone Info</th><?
							// check if new style or old style:
							if ($participant->contact_home_phone != "" || $participant->contact_work_phone != "" || $participant->contact_cell_phone != ""){ // old style
								$phoneTypes = ['home','work','cell'];
								$participantArray = $participant->toArray();
								$blankSpaces = 0; // I'll write your name
								foreach ($phoneTypes as $type){
									if ($participantArray['contact_'.$type.'_phone'] != ""){
										?><td><?=ucfirst($type)?>: <?=$participantArray['contact_'.$type.'_phone']?></td><?
									} else {
										$blankSpaces++;
										continue;
									}
								}
								for($i = 0; $i < $blankSpaces; $i++){
									?><td></td><?
								}
							} else { // new style
								if ($participant->contact_phone_1_number){
									?><td><?=ucfirst($participant->contact_phone_1_type)?> Phone: <?=$participant->contact_phone_1_number?> Texts: <?=($participant->contact_phone_1_texts) ? 'Yes':'No'?></td><?
								} else {
									?><td></td><?
								} if ($participant->contact_phone_2_number){
									?><td><?=ucfirst($participant->contact_phone_2_type)?> Phone: <?=$participant->contact_phone_2_number?> Texts: <?=($participant->contact_phone_2_texts) ? 'Yes':'No'?></td><?
								} else {
									?><td></td><?
								} if ($participant->contact_phone_3_number){
									?><td><?=ucfirst($participant->contact_phone_3_type)?> Phone: <?=$participant->contact_phone_3_number?> Texts: <?=($participant->contact_phone_3_texts) ? 'Yes':'No'?></td><?
								} else {
									?><td></td><?
								}
							}
							?></tr>
							<tr>
								<th>Address</th>
								<td><?=$participant->contact_address_line_1?> <?=$participant->contact_address_line_2?><br />
									<?=$participant->contact_city.", ".strtoupper($participant->contact_state)." ".$participant->contact_zip_code?></td>
									<td></td>
									<td></td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="tab-pane" id="history">
						<div class="row">
							<h3 class="col-md-2">History</h3>
							<br />
							<a href="/admin/history/<?=$participant->id?>/create" class="btn btn-sm btn-warning col-md-2 col-md-offset-7">Create History</a>
						</div>
						<table class="table table-striped table-condensed">
							<thead>
								<tr>
									<th>Activity</th>
									<th>Program</th>
									<th>Session</th>
									<th>Division</th>
									<th>Status</th>
									<th>Type</th>
									<th>Amount Due</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($histories as $a){ ?>
									<tr>
										<td><?=$a->activityName()?></td>
										<td><?=$a->programName()?></td>
										<td><?=$a->seshionName()?></td>
										<td><?=$a->divisionName()=='' ? "No division." :$a->divisionName()?></td>
										<td><?=ucfirst($a->status)?></td>
										<td><?=$activity_types[$a->type]?></td>
										<td><?=$a->amount_due == 0 ? "None" : "$".$a->amount_due?></td>
										<td><a href="/admin/history/<?=$a->id?>/edit" class="btn btn-sm btn-warning">Edit</a></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>

						<div class="tab-pane" id="registrations">
							<div class="row">
								<h3 class="col-md-2">Registrations</h3>
							</div>
							<?php
							foreach ($registrations as $r){
								?><a href="/admin/signups/<?=$r->id?>/view" target="_blank"><?=$r->registration->program->name;?></a> - <?=date("F j, Y",strtotime($r->date_time))?><br /><?
							}
							/*
							<table class="table table-striped table-condensed">
							<thead>
							<tr>
							<th>Activity</th>
							<th>Program</th>
							<th>Session</th>
							<th>Division</th>
							<th>Status</th>
							<th>Type</th>
							<th>Amount Due</th>
							<th>Actions</th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($histories->orderBy('id','DESC')->get() as $a){ ?>
							<tr>
							<td><?=$a->activityName()?></td>
							<td><?=$a->programName()?></td>
							<td><?=$a->seshionName()?></td>
							<td><?=$a->divisionName()=='' ? "No division." :$a->divisionName()?></td>
							<td><?=ucfirst($a->status)?></td>
							<td><?=$activity_types[$a->type]?></td>
							<td><?=$a->amount_due == 0 ? None : "$".$a->amount_due?></td>
							<td><a href="/admin/history/<?=$a->id?>/edit" class="btn btn-sm btn-warning">Edit</a></td>
							</tr>
							<?php } ?>
							</tbody>
							</table>
							*/
							?>
						</div>

					</div>

					<script>
					$().ready(function (){
						$('#myTab a').click(function (e) {
							if ($(this).hasId('edit-tab')){
								window.location.href = "/admin/participants/<?=$participant->id?>/edit";
							}else{
								e.preventDefault();
								$(this).tab('show');
							}
						});
						var my_hash = window.location.hash;
						if (my_hash){
							$('#myTab a[href="'+my_hash+'"]').tab('show');
						}
					});
					</script>

					@stop
