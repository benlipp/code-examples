@extends('layouts.master')
@section('content')
    <h2>View Sessions <small>{!!$program->name!!}</small></h2>
    <p>&nbsp;</p>
    <button class="btn btn-success" id="createbtn">Create Session</button>
    <br>
    <table class="table" id="seshionTable">
	<tr>
		<th>Name</th>
		<th>Actions</th>
	</tr>
    @foreach ($seshions as $seshion)
    <tr>
        <td class="myTD" rel="{!!$seshion->id!!}">{!!$seshion->name!!}</td>
        <td><button class="btn btn-info btn-sm editbtn" data-seshion-name="{!!$seshion->name!!}"
        rel="{!!$seshion->id!!}">Edit</button>
        @if ( $seshion->has_divisions )
            <a href="/admin/sessions/{!!$seshion->id!!}/divisions" class="btn btn-sm btn-default">Divisions</a>
        @endif
          <a href="/admin/programs/<?=$program->id?>?seshion_id=<?=$seshion->id?>" class='btn btn-sm btn-primary'>View</a>
        	<button class="btn btn-danger delete_seshion" rel="{!!$seshion->id!!}">
                <span class="glyphicon glyphicon-trash"></span>
            </button>
        </td>
    </tr>
    @endforeach

<div class="modal fade" id="edit-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-dialog modal-sm">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Edit Session Name</h4>
        </div>
    <div class="modal-body">
        <input class="form-control" type="text" id="seshionname" value="">
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="savebtn">Save changes</button>
    </div>
    </div>
</div>
</div>

<div class="modal fade" id="create-modal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="createModalLabel">Create Session</h4>
        </div>
    <div class="modal-body">
      	<div class="form-group col-md-12">
      		<label>Session Name</label>
        	<input class="form-control" type="text" id="create_seshionname" value="">
        </div>
        <div class="form-group col-md-12">
        	<label>Division Type</label>
        	<select class="form-control" id="division_type">
        		<option value="no">No divisions</option>
        		<option value="age_based">Age-based</option>
        		<option value="talent_based">Talent-based</option>
        	</select>
        </div>
        &nbsp;
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="create-savebtn">Save</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$().ready(function(){
  prepActionButtons();
})
	var programID = "<?=$program->id?>";
	var seshionID;
	var seshionName;
		$('#createbtn').click(function(){
			$('#create-modal').modal()
		});
		$('#create-savebtn').click(function(){
			var seshionName = $('#create_seshionname').val();
			var division_type = $('#division_type').val();
			$('#create-modal').modal('hide');
			var seshionObject = {
				"seshion_name" : seshionName,
				"division_type" : division_type,
				"program_id" : programID
			};
			$.post('/admin/sessions/create',seshionObject,function(response){
				seshionID = response
        if (division_type == "no")
  			{
  				add_to_url = "teams";
  				btn_name = "Teams";
  			}
  			else
  			{
  				add_to_url = "divisions";
  				btn_name = "Divisions";
  			};
        /*
  			$('#seshionTable > tbody:first').append('<tr><td class="myTD" rel="'+seshionID+'">'+seshionName+
  					'</td><td><button class="btn btn-info btn-sm editbtn" data-seshion-name="'+seshionName+'"rel="'+seshionID+
  					'">Edit</button> <a href="/admin/sessions/'+seshionID+'/'+add_to_url+
  					'" class="btn btn-sm btn-default">'+btn_name+'</a><button class="btn btn-danger delete_seshion" rel="'+
  					seshionID+'"><span class="glyphicon glyphicon-trash"></span></button></td></tr>'
  			);
        */
        window.location = "/admin/programs/<?=$program->id?>";

        prepActionButtons();
			});

		});

    function prepActionButtons(){
        $('.editbtn').off('click').on('click',function(){
            seshionName = $(this).data('seshionName');
            seshionID = $(this).attr('rel');
            $('#edit-modal').modal();
            $('#seshionname').val(seshionName);
        });

        $('.delete_seshion').off('click').on('click',function(){
          if ( confirm("Are you sure you want to delete this Session?" ) ){
          	seshionID = $(this).attr('rel');
          	$(this).parents('tr').remove();
          	$.post('/admin/sessions/delete',{"seshion_id":seshionID});
          }
        });
      }

        $('#savebtn').click(function(){
            saved_name = $('#seshionname').val();
            $('#edit-modal').modal('hide');
            $('.myTD[rel='+seshionID+']').html(saved_name);
            $('.editbtn[rel='+seshionID+']').data('seshionName',saved_name);
            var seshionObject = {
                "seshion_id": seshionID,
                "seshion_name": saved_name
            };
            $.post("/ajax/sessions/name",seshionObject);
        });
</script>
@stop
