@extends('layouts.master')

@section('content')
    <h2>People</h2>
    <form action="/admin/participants" method="POST" role="form">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <input type="text" class="form-control" name="last_name" placeholder="Last Name" value="<?=$last_name?>">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <input type="text" class="form-control" name="first_name" placeholder="First Name" value="<?=$first_name?>">
            </div>
          </div>
          <div class="col-md-4">
            <input type="submit" class="btn btn-success" value="Go">
              <a href="/admin/participants/create" class="btn btn-primary">Add New</a>
              <a href="/admin/participants/update-grade" class="btn btn-primary">Update Grades</a>
          </div>
        </div>
        </form>
    <div class="container"><?php
    if($showResults){
        ?><table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>E-Mail Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                    <?php
                    $id_count = 0;
                    foreach ($participants as $p)
                    {
                    ?>
                <tr>
                        <?php $id_count += 1; ?>
                    <td><?=$id_count?></td>
                    <td><?=$p->person_last_name?></td>
                    <td><?=$p->person_first_name?></td>
                    <td><?=strtoupper($p->person_sex)?></td>
                    <!--<td><?
                    if ($p->person_birthday != '0000-00-00'){
                    	$dob = strtotime($p->person_birthday);
                    	$final_date = date('M j, Y',$dob);
                    	echo $final_date;
                    }
                    ?></td>-->
                    <td><?=SILTools\Helpers::age($p->person_birthday)?></td>
                    <td><?=strtolower($p->contact_email_address_1)?></td>
                    <td><a href="/admin/participants/<?=$p->id?>/info" class="btn btn-sm btn-info">Detailed Info</a>
                    <a href="/admin/participants/<?=$p->id?>/edit" class="btn btn-sm btn-primary">Edit</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table><?php
      } else {
        ?><p>Enter a search to begin!</p><?php
      }
        ?></div>


@stop
