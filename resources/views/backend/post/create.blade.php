@extends('backend/layout/layout')
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> Post <small> | Add Post</small> </h1>
    <ol class="breadcrumb">
        <li><a href="{!! url('/admin/post') !!}"><i class="fa fa-book"></i> Post</a></li>
        <li class="active">Add Post</li>
    </ol>
</section>
<br>
<br>
<div class="content">
    <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">Upload a new video</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          {!! Form::open(array('action' => '\App\Http\Controllers\Admin\PostController@store', 'files'=>true)) !!}
            <!-- text input -->
            <div class="form-group">
              <label>Title</label>
              <input name="title" type="text" placeholder="Enter a title..." class="form-control" required="">
            </div>

            <!-- textarea -->
            <div class="form-group">
              <label>Caption</label>
              <textarea name="content" placeholder="Enter a caption..." rows="3" class="form-control" required=""></textarea>
            </div>
            <div class="form-group">
              <label for="attachments">File input</label>
              <input name="attachments" type="file" id="videoFile" accept="video/*" required="">

              <p class="help-block">Upload your video.</p>
            </div>
          {!! Form::submit('Create', array('class' => 'btn btn-success')) !!}  
          {!! Form::close() !!}
        </div>
        <!-- /.box-body -->
    </div>
</div>
@stop

@section ('ext')
{!! HTML::style('jasny-bootstrap/css/jasny-bootstrap.min.css') !!}
{!! HTML::script('jasny-bootstrap/js/jasny-bootstrap.min.js') !!}
{!! HTML::script('assets/js/jquery.slug.js') !!}
<script type="text/javascript">
    $(document).ready(function () {
        $("#title").slug();
    });
</script>
@stop