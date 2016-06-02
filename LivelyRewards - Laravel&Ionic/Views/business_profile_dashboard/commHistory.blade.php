<div class="table-responsive">
	<table class="table table-striped table-hover" id="commTable">
		<thead>
			<tr>
				<th>Type</th>
				<th>Subject</th>
				<th>Recipients</th>
				<th>Date Sent</th>
			</tr>
		</thead>
		<tbody>
			@foreach($history as $hist)
				<tr>
					@if($hist['comm_type'] == "email")
						<td><i class="fa fa-envelope"></i></td>
					@else
						<td><i class="fa fa-mobile"></i></td>
					@endif
					<td><?=$hist['subject']?></td>
					<td><?=$hist['count']?> Members</td>
					<td><?=$hist['date']?></td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>
<script>
$("#commTable").DataTable({
  "searching": false,
  "pageLength": 10,
  "lengthChange": false
});
</script>
