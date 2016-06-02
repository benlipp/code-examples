@extends('layouts.master')
@section('content')

<h2>{!!$activity->name!!} Programs</h2>

<a href="/admin/programs/create?activity_id={!!$activity->id!!}" class="btn-primary btn">Create New Program</a>
<br /><br />
<table class="table">
@foreach (App\Models\Program::genders() as $key=>$gender)
    <tr>
        <td colspan="3"><h3><?=$gender?> Programs</h3></td>
    </tr>
    <tr>
        <th>ID</th>
        <th>Program Name</th>
        <th>Actions</th>
    </tr>
        @foreach ($programs as $program)
            @if ( $program->gender == $key )
    <tr>
        <td>{{$program->id}}</id>
        <td>
            {!!$program->name!!} <span style="color: #CCC; font-size: 60%">{!!$program->slug!!}</span>
        </td>
        <td>
            <a href="/admin/programs/{!!$program->id!!}/edit" class="btn btn-sm btn-default">Edit</a>
            <a href="/admin/programs/{!!$program->id!!}" class="btn btn-sm btn-primary">View</a>
        </td>
    </tr>
        @endif
    @endforeach

@endforeach
</table>
@stop
