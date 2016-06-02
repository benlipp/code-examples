@extends('layouts.master')

@section('content')
    <h1>Activity</h1>
    <form action="/admin/activities" method="POST" role="form">
        @if ($item->id)
            <input type="hidden" name="id" value="{!!$item->id!!}">
        @endif
        <label>Name</label>
        <input type="text" name="data[name]" class="form-control" value="<?=$item->name?>">
        <br />
        <input type="submit" value="Submit" class="btn btn-default">
    </form>

@stop