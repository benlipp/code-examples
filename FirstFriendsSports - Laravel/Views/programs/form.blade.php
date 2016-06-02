@extends('layouts.master')
@section('content')
  <h2><?=($program->id ? "Edit" : "Create")?> Program <small><?=$activity_name?></small></h2>
    <form action="/admin/programs/store" method="POST" role="form" enctype="multipart/form-data">
      <input type="hidden" name="program[activity_id]" value="<?=$program->activity_id?>">
      @if($program->id)
        <input type="hidden" name="id" value="<?=$program->id?>">
      @endif

      <div class="row">
        <div class="col-md-8">

          <div class="col-md-12">
            <div class="form-group">
              <label>Name</label>
              <input type="text" name="program[name]" value="<?=$program->name?>" class="form-control">
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Gender</label>
              <select name="program[gender]" class="form-control">
                @foreach (App\Models\Program::genders() as $key=>$value)
                  <option value="<?=$key?>" <?=$program->gender == $key?"SELECTED":""?>><?=$value?></option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Age Group</label>
              <input name="program[age_group]" class="form-control" value="{{$program->age_group}}">
            </div>
          </div>

        </div>
        <div class="col-md-4">
          <div class="col-md-12">
            <div class="form-group">
              <label>Location</label>
              <textarea type="text" name="program[location]" class="form-control" rows="5" style="resize: none"><?=$program->location?></textarea>
            </div>
          </div>

        </div>
      </div>

      <div class="row">
        <div class="col-md-8">

          <div class="col-md-12">
            <div class="form-group">
              <label>Description</label>
              <?=Form::textarea('program[description]',$program->description,array('class'=>'wysiwyg','rows'=>10,))?>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <label>Misc Info</label>
              <?=Form::textarea('program[misc_info]',$program->misc_info,array('class'=>'wysiwyg'))?>
            </div>
          </div>
        </div>

        <div class="col-md-4">

          <div class="col-md-6">
            <div class="form-group">
              <label>Account Number</label>
              <div class="input-group">
                <div class="input-group-addon">780</div>
                <input class="form-control" name="program[account_id]" value="{{$program->account_id}}">
              </div>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <label>Start Date</label>
              <input class="form-control" name="program[start_date]" value="{{$program->start_date}}">
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <label>End Date</label>
              <input class="form-control" name="program[end_date]" value="{{$program->end_date}}">
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <label>Fee Structure</label>
              <textarea type="text" name="program[fee_details]" style="resize: none;" rows="5" class="form-control">{{$program->fee_details}}</textarea>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <label>Cover Image</label>
              <?=Form::dropzone('program[cover_image]',$program->cover_image);?>
              <div class="clearfix"></div>
              (must be 800px wide and 200px tall)
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <label>Cover Image 2</label>
              <?=Form::dropzone('program[cover_image2]',$program->cover_image2);?>
              <div class="clearfix"></div>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="program[track_win_loss]" {{$program->track_win_loss ? "checked":""}}> Track Win/Loss
                </label>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="row">
        @for($i=1;$i<=3;$i++)
          <div class="col-md-4">
            <div class="form-group">
              <label>Document {{$i}} Name</label>
              <input type="text" name="program[document_{{$i}}_name]" class="form-control">
            </div>
          </div>
        @endfor
      </div>

      <div class="row">
        @for($i=1;$i<=3;$i++)
          <div class="col-md-4">
            <div class="form-group">
              <label>Document {{$i}} File</label>
              <input type="file" name="document_{{$i}}_file" class="form-control">
            </div>
          </div>
        @endfor
      </div>

      <div class="row">
        <div class="col-md-12">
          <input type="submit" value="Submit" class="btn btn-primary">
        </div>
      </div>
    </form>
  </div>

@stop
