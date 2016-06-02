@extends('layouts.master')
@section('content')
  <h2>Update Grades</h2>
  @if($from_post)
    <p>Grades have been updated.</p>
  @else
    <h3>ARE YOU SURE YOU WANT TO DO THIS? THERE IS NO WAY TO UNDO!</h3>
    <form action="/admin/participants/update-grade" method="post">
      <input type="submit" value="Update" class="btn btn-default">
    </form>
  @endif

@endsection
