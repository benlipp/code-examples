<html>
<head>
  <link href="/css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/css/standardPrint.css">
</head>
<body>
  <div class="container">
		<div class="row">
		@foreach($teams as $team)
			<div class="col-md-4">
        <div style="border: 1px solid black;">
  				<table class="table table-bordered">
  					<thead>
  						<tr>
  							<th>{{$team->name}}</th>
  							<th>{{$team->practice_day}}, {{\Carbon\Carbon::parse($team->practice_time)->format("g:ia")}}</th>
  						</tr>
  					</thead>
  					<tbody>
  						<tr>
  							<td>Field: </td>
  							<td>{{$team->practice_location}}</td>
  						</tr>
  						@foreach($team->coaches as $coach)
  							<tr>
  								<td>Coach: </td>
  								<td>{{$coach->name}}</td>
  							</tr>
  						@endforeach
  					</tbody>
  				</table>
  				<table class="table table-bordered">
  					<thead>
  						<tr>
  							<th>Last Name</th>
  							<th>First Name</th>
  							<th>Size</th>
  						</tr>
  					</thead>
  					<tbody>
  						@foreach($team->players as $player)
  							<tr>
  								<td>{{$player->person_last_name}}</td>
  								<td>{{$player->person_first_name}}</td>
  								<td>{{$player->person_size}}</td>
  							</tr>
  						@endforeach
  					</tbody>
  				</table>
        </div>
			</div>
		@endforeach
  </div>
</body>
</html>
