@extends('backend/layout/layout')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> Post <small> | Publish Post</small> </h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/post') !!}"><i class="fa fa-book"></i> Post</a></li>
        <li class="active">Update Post</li>
    </ol>
</section>
<br>
<br>
<div class="content">
    <div class="row">
        @if (isset($authUrl))
        <div class="alert alert-info">
            <a href="{{ $authUrl }}" class="btn btn-primary">You need create a new access_token</a>
        </div>
        @elseif ($post->youtube_id)
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <label class="control-label" for="name">Video preview</label>
            <iframe width="100%" height="315" src="https://youtube.com/embed/{{ $post->youtube_id }}" frameborder="0" allowfullscreen></iframe>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-8 col-lg-8">
            <div class="alert alert-danger">
                <p> This post has already on the youtube <a class="btn btn-info" href="./publishtoyoutube">Publish to the facebook</a></p>
            </div>
        </div>
        @else
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <label class="control-label" for="name">Video preview</label>
            <iframe width="100%" height="315" src="{{ $attachment->src }}" frameborder="0" allowfullscreen></iframe>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-8 col-lg-8">
            {!! Form::open( array( 'route' => array('admin.post.uploadyoutube'), 'method' => 'POST', 'files'=>true)) !!}

                {{ Form::hidden('id', $post->id, array('class' => 'form-control')) }}
                <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">

                    {{ Form::label('title', 'title', array('class' => 'control-label')) }}

                    {{ Form::text('title', $post->title, array('class' => 'form-control', 'placeholder' => 'title')) }}

                    @if ($errors->has('title'))
                        <span class="help-block">{{ $errors->first('title') }}</span>
                    @endif

                </div>

                <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">

                    {{ Form::label('description', 'description', array('class' => 'control-label')) }}

                    {{ Form::textarea('description', $post->content, array('class' => 'form-control', 'placeholder' => 'description', 'rows'=>3)) }}

                    @if ($errors->has('description'))
                        <span class="help-block">{{ $errors->first('description') }}</span>
                    @endif

                </div>

                <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">

                    {{ Form::label('status', 'status', array('class' => 'control-label')) }}

                    {{ Form::select('status', array('public' => 'Public', 'unlisted' => 'Unlisted', 'private' => 'Private'), Input::old('status')) }}

                    @if ($errors->has('status'))
                        <span class="help-block">{{ $errors->first('status') }}</span>
                    @endif

                </div>

                <div class="form-group{{ $errors->has('video') ? ' has-error' : '' }}">

                    {{ Form::label('video', 'video', array('class' => 'control-label')) }}

                    {{ Form::text('videoPath', $attachment->src, array('class' => 'form-control', 'placeholder' => 'video path')) }}

                    @if ($errors->has('video'))
                        <span class="help-block">{{ $errors->first('video') }}</span>
                    @endif

                </div>

                {{ Form::submit('Post video to youtube', array('class' => 'btn btn-primary')) }}

            {{ Form::close() }}
        </div>
        @endif
    </div>
</div>
@stop

@section ('ext')

{!! HTML::style('jasny-bootstrap/css/jasny-bootstrap.min.css') !!}
{!! HTML::script('jasny-bootstrap/js/jasny-bootstrap.min.js') !!}
@stop