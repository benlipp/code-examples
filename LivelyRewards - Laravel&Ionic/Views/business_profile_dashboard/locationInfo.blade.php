<div class="row">
	@foreach($locations as $loc)
		<div class="col-lg-4">
			<div class="widget navy-bg p-lg text-center">
				<div class="m-b-md">
					<h1 class="location-titles"><?=$loc['name']?></h1>
					<h2><br> Members: <?=$loc['members']?></h2>
				</div>
				<a class="btn btn-default btn-lg" href="/account/locations/<?=$loc['id']?>">
					<strong>Details</strong>
				</a>
				<br />
				<a class="btn btn-sm btn-primary" href="/login-as-loc/<?=$loc['id']?>">
					<strong>Location Dashboard</strong>
				</a>
			</div>
		</div>
	@endforeach
</div>
