@extends ('layouts.master')
@section('content')


<h2><?=$history->id?"Edit":"Create"?> History <small><?=$participant->name?></small></h2>

<form action="/admin/history/store" method="POST" role="form">
<div class="col-md-12">
	<input type="hidden" name="id" value="<?=$history->id?>">
	<input type="hidden" name="participant_id" value="<?=$participant->id?>">

<div class="form-group">
    <label>Activity</label>
    	<select name="history[activity_id]" class="form-control" id="activity_select" value="<?=$history->activity_id?>">
    		<option>Please select an activity.</option>
        <?php
        $activity = App\Models\Activity::All();
        foreach($activity as $l){ ?>
            <option value="<?=$l->id?>"<?=$history->activity_id==$l->id?" SELECTED":""?>><?=$l->name?></option>
        <?php } ?>
    </select>
</div>

<div class="form-group">
	<label>Program</label>
	    <select name="history[program_id]" class="form-control" value="<?=$history->program_id?>" id="program_select">
            <option value="<?=$history->program_id?>" SELECTED><?=$history->programName()?></option>
	    </select>
</div>

<div class="form-group">
	<label>Session</label>
		<select name="history[seshion_id]" value="<?=$history->seshion_id?>" id="seshion_select" class="form-control">
	    	 <option value="<?=$history->seshion_id?>" SELECTED><?=$history->seshionName()?></option>
		</select>
</div>

<div class="form-group">
	<label>Division</label>
		<select name="history[division_id]" value="<?=$history->division_id?>" id="division_select" class="form-control"><?
		if (!($history->division_id)){
			?><option value="" SELECTED>No divisions.</option><?
		} else{
			?><option value="<?=$history->division_id?>" SELECTED><?=$history->divisionName()?></option><?
		}

		?></select>
</div>

<div class="form-group">
	<label>Amount Paid</label>
	<div class="input-group">
		<span class="input-group-addon glyphicon glyphicon-usd"></span>
		<input type="text" class="form-control" name="history[amount_paid]" value="<?=$history->amount_paid?>">
	</div>
</div>

<div class="form-group">
	<div class="input-group">
		<?=Form::datepicker('Last Date Paid','history[last_date_paid]','col-md-12',$history->last_date_paid)?>
	</div>
</div>


<div class="form-group">
	<label>Amount Due</label>
	<div class="input-group">
		<span class="input-group-addon glyphicon glyphicon-usd"></span>
		<input type="text" class="form-control" name="history[amount_due]" value="<?=$history->amount_due?>">
	</div>
</div>

<div class="form-group">
	<label>Status</label>
	<select name="history[status]" value="<?=$history->status?>" class="form-control">
		<option value="active"<?=$history->status=='active'?" SELECTED":""?>>Active</option>
		<option value="interested"<?=$history->status=='interested'?" SELECTED":""?>>Interested</option>
		<option value="withdrawn"<?=$history->status=='withdrawn'?" SELECTED":""?>>Withdrawn</option>
	</select>
</div>

<div class="form-group">
	<label>Role</label>
	<select name="history[type]" value="<?=$history->role?>" class="form-control"><?
		$activity_types = array(
			'player'=>'Player',
			'coach'=>'Coach',
			'official'=>'Official',
			'score_keeper'=>'Score keeper',
			'player_coach'=>'Player and Coach',
			'assistant_coach'=>'Assistant Coach');
		foreach ($activity_types as $key => $value) {
			?><option value="<?=$key?>"<?=$history->role==$key?" SELECTED":""?>><?=$value?></option><?
		}
	?></select>
</div>

<div class="form-group">
    <input type="submit" value="Submit" class="btn btn-success">
</div>
</div>
</form>

<script>
	$.ready( $('.btn').button() );

	$( '#activity_select' ).on( 'change',  function () {
        $('#program_select').empty();
        $('#program_select').append("<option>Loading...</option>");
        var option_selected = $("option:selected", this);
        var activity_id = this.value;
        $.getJSON('/ajax/activities/'+activity_id, function(result){
            $("#program_select").empty();
            $.each(result, function( key, value){
                $("#program_select").append("<option value='"+key+"'>"+value+"</option>");
            });
						$("#program_select").change();
        });
    });

    $('#program_select').on('change', function(){
    	$('#seshion_select').empty();
    	$('#seshion_select').append("<option>Loading...</option>");
    	var option_selected = $("option:selected",this);
    	var program_id = this.value;
    	$.getJSON('/ajax/seshions/'+program_id, function(result){

            $("#seshion_select").empty();
            $.each(result, function(key, value){
                $("#seshion_select").append("<option value='"+key+"'>"+value+"</option>");
            });
						$("#seshion_select").change();
        });
    });

    $('#seshion_select').on('change', function(){
    	$('#division_select').empty();
    	$('#division_select').append("<option>Loading...</option>");
    	var option_selected = $("option:selected",this);
    	var seshion_id = this.value;
    	$.getJSON('/ajax/divisions/'+seshion_id, function(result){
    		console.log(result);
    		$('#division_select').empty();
    		$.each(result, function(key, value){
                $("#division_select").append("<option value='"+key+"'>"+value+"</option>");
            });
    	});
    });
</script>

@stop
