<h4>Most Active Members (past 30 days)</h4>
<div class="table-responsive">
	<table class="table table-striped table-hover">
		<tbody>
			@foreach($most_active as $member)
				<tr>
					<td><?=$member['name']?></td>
					<td><?=$member['points']?> points</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>

<h4>Reward Activity</h4>
<div class="table-responsive">
	<table class="table table-hover">
		<tbody>
			@foreach($reward_info as $info)
			<tr>
				<td><strong><?=$info['name']?>:</strong></td>
				<td><?=$info['data']?></td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
