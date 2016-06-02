@extends('layouts.master')

@section('content')
<h2><?=$participant->id?"Edit":"Create"?> Participant <small><?=$participant->name?></small></h2>
<a href="/admin/participants">&laquo; Back To Search</a><br /><br />
<?php
$personalArray = [
  "person_first_name"=>["name"=>"First Name","type"=>"text"],
  "person_last_name"=>["name"=>"Last Name","type"=>"text"],
  "person_age"=>["name"=>"Age","type"=>"special"],
  "person_birthday"=>["name"=>"Birthday","type"=>"special"],
  "person_grade"=>["name"=>"Grade","type"=>"special"],
  "person_sex"=>["name"=>"Sex","type"=>"special"],
  "person_size"=>["name"=>"Shirt Size","type"=>"text"],
  "person_height_feet"=>['name'=>'Height (feet)','type'=>'text'],
  "person_height_inches"=>['name'=>'Height (inches)','type'=>'text'],
  "person_weight"=>['name'=>'Weight','type'=>'text'],
  "person_parent_guardian_1"=>["name"=>"Parent/Guardian 1","type"=>"text"],
  "person_parent_guardian_2"=>["name"=>"Parent/Guardian 2","type"=>"text"],
];

$emergencyArray = [
  "emergency_primary_name"=>["name"=>"Primary Name","type"=>"text"],
  "emergency_primary_phone_1"=>["name"=>"Primary Phone 1","type"=>"text"],
  "emergency_primary_phone_2"=>["name"=>"Primary Phone 2","type"=>"text"],
  "emergency_secondary_name"=>["name"=>"Secondary Name","type"=>"text"],
  "emergency_secondary_phone_1"=>["name"=>"Secondary Phone 1","type"=>"text"],
  "emergency_secondary_phone_2"=>["name"=>"Secondary Phone 2","type"=>"text"],
  "other_doctor"=>["name"=>"Doctor","type"=>"text"],
  "other_dentist"=>["name"=>"Dentist","type"=>"text"],
  "other_hospital_pref"=>["name"=>"Hospital Preference","type"=>"text"],
  "other_medical_conditions"=>["name"=>"Medical Conditions","type"=>"textarea"],
];

$moneyArray = [
  "money_scholarship_amount"=>["name"=>"Scholarship Amount","type"=>"text"],
  "money_last_scholarship_year"=>["name"=>"Last Scholarship Year","type"=>"text"],
  //"money_amt_paid"=>["name"=>"Amount Paid","type"=>"text"],
  //"money_date_paid"=>["name"=>"Date Paid","type"=>"special"],
  //"money_amt_due"=>["name"=>"Amount Due","type"=>"text"],
  //"money_due_date"=>["name"=>"Date Due","type"=>"special"],
  //"money_past_due"=>["name"=>"Amount Past Due","type"=>"text"]
];

$contactArray = [
  "contact_email_address_1"=>["name"=>"Email Address 1","type"=>"text"],
  "contact_email_address_2"=>["name"=>"Email Address 2","type"=>"text"],
  "contact_home_phone"=>["name"=>"Home Phone","type"=>"text","style"=>"old"],
  "contact_work_phone"=>["name"=>"Work Phone","type"=>"text","style"=>"old"],
  "contact_cell_phone"=>["name"=>"Cell Phone","type"=>"text","style"=>"old"],
  "contact_phone_1_type"=>["name"=>"Phone 1 Type","type"=>"special","style"=>"new"],
  "contact_phone_1_number"=>["name"=>"Phone 1 Number","type"=>"text","style"=>"new"],
  "contact_phone_1_texts"=>["name"=>"Phone 1 Texts","type"=>"special","style"=>"new"],
  "contact_phone_2_type"=>["name"=>"Phone 2 Type","type"=>"special","style"=>"new"],
  "contact_phone_2_number"=>["name"=>"Phone 2 Number","type"=>"text","style"=>"new"],
  "contact_phone_2_texts"=>["name"=>"Phone 2 Texts","type"=>"special","style"=>"new"],
  "contact_phone_3_type"=>["name"=>"Phone 3 Type","type"=>"special","style"=>"new"],
  "contact_phone_3_number"=>["name"=>"Phone 3 Number","type"=>"text","style"=>"new"],
  "contact_phone_3_texts"=>["name"=>"Phone 3 Texts","type"=>"special","style"=>"new"],
  "contact_address_line_1"=>["name"=>"Address Line 1","type"=>"text"],
  "contact_address_line_2"=>["name"=>"Address Line 2","type"=>"text"],
  "contact_city"=>["name"=>"City","type"=>"text"],
  "contact_state"=>["name"=>"State","type"=>"text"],
  "contact_zip_code"=>["name"=>"Zip Code","type"=>"text"]
];

$miscArray = [
  "other_employer_school"=>["name"=>"Employer/School","type"=>"text"],
  "other_church"=>["name"=>"Church Affiliation","type"=>"special"],
  "other_comments"=>["name"=>"Comments","type"=>"special"]
];
?>
<br>
<?php if ($participant->id != ''){
  ?><ul class="nav nav-tabs custom" role="tablist" id="myTab">
    <li><a href="/admin/participants/<?=$participant->id?>/info#personal-info" role="tab" class="btn-primary">Personal Info</a></li>
    <li><a href="/admin/participants/<?=$participant->id?>/info#emergency-info" role="tab" class="btn-danger">Emergency Contact Info</a></li>
    <li><a href="/admin/participants/<?=$participant->id?>/info#payment-info" role="tab" class="btn-success">Payment Info</a></li>
    <li><a href="/admin/participants/<?=$participant->id?>/info#contact-info" role="tab" class="btn-info">Contact Info</a></li>
    <li><a href="/admin/participants/<?=$participant->id?>/info#history" role="tab" class="btn-warning">History</a></li>
    <li><a href="/admin/participants/<?=$participant->id?>/info#registrations" role="tab" class="btn-success">Registrations</a></li>
    <li class="active"><a href="/admin/participants/<?=$participant->id?>/edit" role="tab" class="btn-primary">Edit Participant</a></li>
  </ul><?
} ?>
<br>
<form action="/admin/participants/store" method="POST" role="form">
  <div class="row">
    <input type="hidden" name="id" value="<?=$participant->id?>">
    <div class="col-md-6">

      <!-- PERSONAL INFO -->
      <div class="categoryBreak" style="border: 6px solid #EEE; padding: 10px; margin-bottom: 10px">
        <h3>Personal Info</h3><?
        foreach($personalArray as $key=>$person){
          if ($person['type'] != "special" ){
            ?><div class="form-group">
              <label><?=$person['name']?></label>
              <input type="<?=$person['type']?>" name="participant[<?=$key?>]" class="form-control" value="<?=$participant->$key?>">
            </div><?
          } elseif ($key == "person_sex"){
            ?><div class="form-group">
              <label><?=$person['name']?></label>
              <select class="form-control" name="participant[<?=$key?>]">
                <option value="M" <?=($participant->person_sex == "M" ? " selected" : "")?>>Male</option>
                <option value="F" <?=($participant->person_sex == "F" ? " selected" : "")?>>Female</option>
              </select>
            </div><?
          }
          elseif ($key == "person_age"){
            ?><div class="form-group">
              <label><?=$person["name"]?></label>
              <p class="form-control-static"><?=$participant->age?></p>
            </div><?
          }
          elseif ($key == "person_birthday"){
            echo Form::datepicker('Birthday','person_birthday','col-md-12',$participant->person_birthday);
          }
          elseif ($key == "person_grade"){
            $locale = 'en_US';
            //$nf = new NumberFormatter($locale, NumberFormatter::ORDINAL);
            ?><div class="form-group">
              <label><?=$person['name']?></label>
              <select name="participant[<?=$key?>]" class="form-control"><?php
                $grades = [
                  ""=>"Not in School",
                  'k'=>'Kindergarten',
                  '1'=>'1st Grade',
                  '2'=>'2nd Grade',
                  '3'=>'3rd Grade',
                  '4'=>'4th Grade',
                  '5'=>'5th Grade',
                  '6'=>'6th Grade',
                  '7'=>'7th Grade',
                  '8'=>'8th Grade',
                  '9'=>'9th Grade',
                  '10'=>'10th Grade',
                  '11'=>'11th Grade',
                  '12'=>'12th Grade'
                ];
                foreach($grades as $key => $val){
                  ?><option value="{{$key}}" {{($participant->person_grade == $key ? " selected" : "")}}>{{$value}}</option><?php
                }
              ?></select>
            </div><?
          }
        }
        ?></div>
      <!-- Emergency Contact Info-->
      <div class="categoryBreak" style="border: 6px solid #EEE; padding: 10px; margin-bottom: 10px">
        <h3>Emergency Info</h3><?
        foreach ($emergencyArray as $key=>$emergency){
          ?><div class="form-group">
            <label><?=$emergency['name']?></label>
            <?
            if ( $emergency['type'] == 'textarea' ){
              ?><textarea name="participant[<?=$key?>]" class="form-control" rows="4"><?=$participant->$key?></textarea><?
            } else {
              ?><input type="<?=$emergency['type']?>" name="participant[<?=$key?>]" class="form-control" value="<?=$participant->$key?>"><?
            }
            ?>
          </div><?
        }
      ?></div>
      <!-- Money Info -->
      <div class="categoryBreak" style="border: 6px solid #EEE; padding: 10px; margin-bottom: 10px">
        <h3>Money Info</h3><?
        foreach ($moneyArray as $key=>$money){
          if ($money['type'] != "special"){
            ?><div class="form-group">
              <label><?=$money['name']?></label>
              <input type="<?=$money['type']?>" name="participant[<?=$key?>]" class="form-control" value="<?=$participant->$key?>">
            </div><?
          }
          elseif ($key == "money_date_paid"){
            echo Form::datepicker('Date Paid','money_date_paid','col-md-12',$participant->money_date_paid);
          }
          elseif ($key == "money_due_date"){
            echo Form::datepicker('Date Due','money_due_date','col-md-12',$participant->money_due_date);
          }
        }
      ?>
        <div>
          <strong>Amount Due: </strong>
          $<?=$participant->amount_due?>
        </div>

      </div>
    </div> <!-- on side 2 -->
    <div class="col-md-6">
      <!-- Contact Info -->
      <div class="categoryBreak" style="border: 6px solid #EEE; padding: 10px; margin-bottom: 10px">
        <h3>Contact Info</h3><?

        $styleType = "new";
        if($participant->contact_home_phone != "" || $participant->contact_work_phone != "" || $participant->contact_cell_phone != ""){ //old data style
          $styleType = "old";
        }
        foreach ($contactArray as $key=>$contact){
          if ($contact['type'] != "special"  && !isset($contact["style"])){
            ?><div class="form-group">
              <label><?=$contact['name']?></label>
              <input type="<?=$contact['type']?>" name="participant[<?=$key?>]" class="form-control" value="<?=$participant->$key?>">
            </div><?
          }
          elseif (preg_match("/contact_phone_\d_type/i",$key) && $contact["style"] == $styleType){
            ?><div class="form-group">
              <label><?=$contact['name']?></label>
              <select name="participant[<?=$key?>]" class="form-control">
                <option value="" <?=($participant->$key == "" ? " selected" : "")?>>--</option>
                <option value="cell" <?=($participant->$key == "cell" ? " selected" : "")?>>Cell</option>
                <option value="home" <?=($participant->$key == "home" ? " selected" : "")?>>Home</option>
                <option value="work" <?=($participant->$key == "work" ? " selected" : "")?>>Work</option>
              </select>
            </div><?
          }
          elseif (preg_match("/contact_phone_\d_number/i",$key) && $contact["style"] == $styleType){
            ?><div class="form-group">
              <label><?=$contact['name']?></label>
              <input type="<?=$contact['type']?>" name="participant[<?=$key?>]" class="form-control" value="<?=$participant->$key?>">
            </div><?
          }
          elseif (preg_match("/contact_phone_\d_texts/i",$key) && $contact['style'] == $styleType){
            ?><div class="form-group">
              <label><?=$contact['name']?></label>
              <!-- use a hidden input for 0 -->
              <input type="hidden" name="participant[<?=$key?>]" value="0">
              <div class="checkbox">
                <label><input type="checkbox" name="participant[<?=$key?>]" value="1" <?=($participant->$key == "1" ? " checked" : "")?>>Texts?</label>
              </div>
            </div><?
          }
        }
      ?></div>
      <!-- Miscellaneous Info -->
      <div class="categoryBreak" style="border: 6px solid #EEE; padding: 10px; margin-bottom: 10px">
        <h3>Miscellaneous Info</h3><?
        foreach ($miscArray as $key=>$misc){
          if ($misc['type'] != "special"){
            ?><div class="form-group">
              <label><?=$misc['name']?></label>
              <input type="<?=$misc['type']?>" name="participant[<?=$key?>]" class="form-control" value="<?=$participant->$key?>">
            </div><?
          }
          elseif ($key == "other_church"){
            echo Form::churches($misc['name'],$key,stripslashes($participant->$key));
          }
          elseif ($key == "other_comments"){
            ?><div class="form-group">
              <label><?=$misc['name']?></label>
              <textarea class="form-control" name="participant[other_comments]" rows="4"><?=$participant->$key?></textarea>
            </div><?
          }
        }
      ?></div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-10">
      <input type="submit" value="Submit" class="btn btn-primary">
    </div>
    <div class="col-md-2 pull-right">
      <a href="javascript:;" id="delete" style="color: red">Delete Record</button>
    </div>
  </div>
</form>

<script type="text/javascript">
$('#delete').on('click',function(event){
  var participant_id = <?=$participant->id?>;
  var person = {"participant_id":participant_id};
  var confirmDelete = confirm('Are you sure you want to delete?');
  if (confirmDelete){
    $.post('/admin/participants/delete',person,function(response){
      console.log(response);
    });

    window.location="<?=URL::to('admin/participants')?>"
    return false;
  }
});
</script>
@stop
