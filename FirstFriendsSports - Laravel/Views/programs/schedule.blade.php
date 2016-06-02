<html>
<head>
	<link href="/css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
	<div class="container">
		<h1>{{$program->name}} Program</h1>
		<h2>{{$seshion->name}} Game Schedule</h2>
		<table class="table">
			<thead>
				<tr>
					<th>Team:</th>
					<th>Color:</th>
					<th>Coaches:</th>
				</tr>
			</thead>
			<tbody>
				@foreach($teams as $team)
					<tr>
						<td>{{$team->name}}</td>
						<td>team color</td>
						<td>{{$team->coaches[0]->name}}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		<table class="table table-striped">
			<thead>
				<tr>
					<th>Game:</th>
					<th>Date:</th>
					<th>Time:</th>
					<th>Location</th>
					<th>Away Team</th>
					<th>Home Team</th>
				</tr>
			</thead>
			<tbody>
				@foreach($games as $game)
					<tr>
						<td>Game Name</td>
						<td>{{$game->gameDate()}}</td>
						<td>{{$game->gameTime()}}</td>
						<td>{{$game->venue}}</td>
						<td>{{$game->away_team->name}}</td>
						<td>{{$game->home_team->name}}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</body>
</html>
