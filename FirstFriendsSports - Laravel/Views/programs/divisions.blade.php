@extends('layouts.master')

@section('content')
<h2>Divisions</h2>

<table class="table">
<?php
foreach ($seshion->divisions as $division){
    ?>
    <tr>
        <td>
            <?=$division->name?>
        </td>
        <td>
            <a href="/admin/programs/<?=$program->id?>?division_id=<?=$division->id?>" class="btn btn-primary">Choose Division</a>
        </td>
    </tr>
    <?php
}
?>
</table>
@stop