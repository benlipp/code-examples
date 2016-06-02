@extends('layouts.master')
@section('content')

<?php
$schools = [
	'JACKSON LOCAL SCHOOLS'=>[
		'Jackson High School',
		'Jackson Middle School',
		'Amherst Elementary School',
		'Lake Cable Elementary School',
		'Sauder Elementary School',
		'Strausser Elementary School'
	],
	'NORTH CANTON CITY SCHOOLS'=>[
		'Hoover High School',
		'North Canton Middle School',
		'Greentown Intermediate School',
		'Orchard Hill Intermediate School',
		'Clearmont Elementary School',
		'Northwood Elementary School',
	],
	'PLAIN LOCAL SCHOOLS'=>[
		'GlenOak High School',
		'Oakwood Middle School',
		'Glenwood Intermediate School',
		'Warstler Elementary School',
		'Taft Elementary School',
		'Middlebranch Elementary School',
		'Frazer Elementary School',
		'Barr Elementary School',
		'Avondale Elementary School',
	],
	'LAKE LOCAL SCHOOLS'=>[
		'Lake High School',
		'Lake Middle School',
		'Lake Elementary',
		'Hartville Elementary',
		'Uniontown Elementary',
	],
	'CANTON CITY SCHOOLS'=>[
		'Allen Elementary School',
		'Dueber Elementary School',
		'Harter Elementary School',
		'McGregor Elementary School',
		'Schreiber Elementary School',
		'Stone Elementary School',
		'Worley Elementary School',
		'Belden Elementary School',
		'Cedar Elementary School',
		'Fairmont Elementary School',
		'Gibbs Elementary School',
		'Mason Elementary School',
		'Youtz Elementary School',
		'Crenshaw Middle School',
		'Summit Middle School',
		'Lehman Middle School',
		'Souers Middle School',
		'Hartford Middle School',
		'Early College',
		'McKinley High School',
		'Timken High School'
	],
	'MARLINGTON LOCAL SCHOOLS'=>[
		'Marlington High School',
		'Marlington Middle School',
		'Lexington Elementary School',
		'Washington Elementary School',
		'Marlboro Elementary School',
	],
	'PERRY LOCAL SCHOOLS'=>[
		'Perry High School',
		'Edison Middle School',
		'Pfeiffer Intermediate School',
		'Genoa Elementary School',
		'Knapp Elementary School',
		'Lohr Elementary School',
		'Watson Elementary School',
		'Whipple Elementary School',
	],
	'CANTON LOCAL SCHOOLS (CANTON SOUTH)'=>[
		'Canton South High School',
		'Faircrest Memorial Middle School',
		'H.R. Walker Elementary',
	],
	'GREEN LOCAL SCHOOLS'=>[
		'Green High School',
		'Green Middle School',
		'Green Intermediate School',
		'Green Primary School',
		'Greenwood Early Learning Center',
	],
	'LAKE CENTER CHRISTIAN SCHOOL'=>[
		'Lake Center Christian School'
	],
	'HERITAGE CHRISTIAN SCHOOL'=>[
		'Heritage Christian School'
	],
	'MASSILLON CITY SCHOOLS'=>[
		'Washington High School',
		'Massillon Junior High School',
		'Massillon Intermediate School',
		'Franklin Elementary School',
		'Gorrell Elementary School',
		'Whittier Elementary School',
	],
	'ALLIANCE CITY SCHOOLS'=>[
		'Alliance High School',
		'Alliance Middle School',
		'Alliance Early Learning School',
		'Northside Intermediate School',
		'Parkway Elementary School',
		'Rockhill Elementary School',
	],
	'LOUISVILLE CITY SCHOOLS'=>[
		'Louisville High School',
		'Louisville Middle School',
		'Louisville Elementary School',
		'North Nimishillen Elementary School',
	],
	'TUSLAW LOCAL SCHOOLS'=>[
		'Tuslaw High School',
		'Tuslaw Middle School',
		'Tuslaw Elementary School',
	],
	'NORTHWEST LOCAL SCHOOLS'=>[
		'Northwest High School',
		'Northwest Middle School',
		'W.S. Stinson Elementary',
		'Northwest Primary',
	],
	'OSNABURG LOCAL SCHOOLS'=>[
		'East Canton High School',
	],
	'MINERVA LOCAL SCHOOLS'=>[
		'Minerva High School',
		'Minerva Middle School',
		'Minerva Elementary School',
	],
	'SANDY VALLEY LOCAL SCHOOLS'=>[
		'Sandy Valley High School',
		'Sandy Valley Middle School',
		'Sandy Valley Elementary School',
	],
	'FAIRLESS LOCAL SCHOOLS'=>[
		'Fairless High School',
		'Fairless Middle School',
		'Fairless Elementary School',
	],
	'DALTON LOCAL SCHOOLS'=>[
		'Dalton High School',
		'Dalton Middle School',
		'Dalton Elementary School',
	],
	'PORTAGE COLLABORATIVE MONTESSORI SCHOOL'=>[
		'Portange Collaborative Montessori School'
	],
];
//print_r(Session::all());
foreach ($schools as $key=>$school_disctrict){
	sort($schools[$key]);
}

ksort($schools);
$schools['OTHER'] = [];
$grades_array = array('k'=>'Kindergarten','1'=>'1st Grade','2'=>'2nd Grade','3'=>'3rd Grade','4'=>'4th Grade','5'=>'5th Grade','6'=>'6th Grade','7'=>'7th Grade','8'=>'8th Grade','9'=>'9th Grade','10'=>'10th Grade','11'=>'11th Grade','12'=>'12th Grade');
?>

<h2>New Walkup <small><?=$reg->name?></small></h2>
<hr>
<form action="/admin/participants/walkup/save" method="POST" role="form">
	<input type="hidden" name="registration_id" value="<?=$reg->id?>">
	<h3>Participant Info</h3>
	<div class="row">

		<div class="col-md-4">
			<div class="form-group">
				<label>First Name</label>
				<input type="text" name="walkup[participant_info][first_name]" class="form-control" required>
			</div>
		</div>

		<div class="col-md-4">
			<div class="form-group">
				<label>Last Name</label>
				<input type="text" name="walkup[participant_info][last_name]" class="form-control" required>
			</div>
		</div>

	</div>

	@if($reg->isYouthRegistration())
		<h3>School Info</h3>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label>School System</label>
					<select id="schoolSystem" class="form-control required_field" name="walkup[school_info][school_system]">
						<option value="">Select a School System
						@foreach($schools as $k=>$v)
							<option value="<?=$k?>"><?=$k?></option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<div id="schoolSelectContainer">
						<label>School</label>
						<select id="schoolSelect" class="form-control" name="walkup[school_info][school]">
							<option value=""></option>
						</select>
					</div>
						<div id="schoolTextContainer" style="display: none">
							<label>School Name</label>
							<input class="form-control" id="schoolText" type="text" name="walkup[school_info][school_text]">
						</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label>Played For School (OHSAA)</label>
					<select name="walkup[school_info][playing_experience_bool]" class="form-control">
						<option value="1" {{$walkup->playing_experience_bool ? "selected" : ""}}>Yes</option>
						<option value="0" {{$walkup->playing_experience_bool ? "selected" : ""}}>No</option>
					</select>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label>Grade</label>
					<select name="walkup[school_info][current_grade]" class="form-control">
						<option value="">Choose A Grade</option>
						@foreach($grades_array as $key => $value)
							<option value="<?=$key?>"><?=$value?></option>
						@endforeach
						<option value="N/A">N/A</option>
					</select>
				</div>
			</div>
		</div>

		<div id="volunteer-btn">
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<button class="btn btn-default" id="volunteer-add">Add a Volunteer</button>
					</div>
				</div>
			</div>
		</div>

		<div id="volunteer-form" style="display: none;">
			<h3>Volunteer Info</h3>
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label>Volunteer Name</label>
						<input name="walkup[parent_guardian][parent_guardian_1_name]" class="form-control" type="text">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label>Shirt Size</label>
						<select class="form-control" name="walkup[parent_guardian][parent_guardian_1_coach_shirt]">
							<option value="">No Shirt</option>
							@foreach($shirt_sizes as $key=>$val)
								<option value="{{$key}}">{{$val}}</option>
							@endforeach
						</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label>Phone</label>
					<input name="walkup[contact_info][phone_number_1]" class="form-control" type="text">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label>Email</label>
					<input name="walkup[contact_info][primary_email]" class="form-control" type="text">
				</div>
			</div>
		</div>
	</div>
	@endif
	<h3>Custom Questions</h3>
		@for($i=1; $i <= 3; $i++)
			<?php
				$question = "custom_text_".$i;
				$type = "custom_".$i."_type";
				$answer = "answer_".$i;
			?>
			@if($reg->$question)
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>{{$reg->$question}}</label>
							@if($reg->$type == "yesno")
								<select name="walkup[misc][custom_question_{{$i}}]" class="form-control">
									<option value="yes">Yes</option>
									<option value="no">No</option>
								</select>
							@else
								<input type="text" name="walkup[misc][custom_question_{{$i}}]" class="form-control">
							@endif
						</div>
					</div>
				</div>
			@endif
		@endfor


<hr>
	<div class="row">
		<div class="col-md-4">
			<div class="form-group">
				<input type="submit" value="Submit" class="btn btn-primary">
	  	</div>
		</div>
	</div>

</form>

<script>
var schools = <?=json_encode($schools)?>;
$(document).ready(function(){

	$("#volunteer-add").on('click',function(){
		$("#volunteer-btn").hide();
		$("#volunteer-form").show();
		return false;
	});


	$("#schoolSystem").on('change',function(){
		var selectedSchool = $(this).val();

		if (selectedSchool == ''){
			$("#schoolSelect").val('');
			$("#schoolSelectContainer").hide();

			$("#schoolText").val('');
			$("#schoolTextContainer").hide();
			return;
		}

		else if ( selectedSchool == "OTHER" ){
			$("#schoolSelect").val('');
			$("#schoolSelectContainer").hide();
			$("#schoolTextContainer").show();
		}

		else {
			$("#schoolText").val('');
			$("#schoolTextContainer").hide();
			$("#schoolSelectContainer").show();
			$("#schoolSelect").empty();
			$("#schoolSelect").append("<option value=''>Choose School</option>");
			for (var i in schools[selectedSchool]){
				$("#schoolSelect").append("<option value='"+schools[selectedSchool][i]+"'>"+schools[selectedSchool][i]+"</option>");
			}
		}
	})
});

</script>
@endsection
