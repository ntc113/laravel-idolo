@extends('backend/layout/layout')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> Post
        <small> | Show Post</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.post.index') !!}"><i class="fa fa-book"></i> Post</a></li>
        <li class="active">Show Post</li>
    </ol>
</section>
<br>
<br>
<div class="content">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            @if ($post->youtube_id)
                <iframe width="100%" height="315" src="https://youtube.com/embed/{{ $post->youtube_id }}" frameborder="0" allowfullscreen></iframe>
            @else
                <video width="100%" controls>
                    <source src="{!! url($attachment->src) !!}" type="video/mp4">
                    Your browser does not support HTML5 video.
                </video>
            @endif
            
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3>{!! ($post->title) ? $post->title : 'The tite of post ' . $post->id !!}</h3>
                </div>
                <div class="box-body">
                @include('flash::message')
                    <span class="user-avatar">
                        <img alt="{!! $user->name !!}" src="{!! ($user->avatar) ? $user->avatar : config('cc.app_avatar') !!}">
                    </span>

                    <div class="user-info">
                      <span class="user-name">{!! $user->name !!}</span>
                      <span class=" ">I'm using {!! config('cc.app_name') !!}.</span>

                    </div>
                  </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-red"><i class="ion ion-social-youtube"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Youtube</span>
                            @if ($post->youtube_id)
                                <span class="btn btn-default"><a target="_blank" href="https://youtube.com/watch?v={{$post->youtube_id}}">View on Youtube</a></span>
                            @else
                                <a class="btn btn-sm btn-info btn-flat pull-left" href="{{url('/admin/post/' . $post->id . '/publishtoyoutube')}}">Upload to Youtube</a>
                            @endif
                        </div>
                    <!-- /.info-box-content -->
                    </div>
                <!-- /.info-box -->
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-aqua"><i class="ion ion-social-facebook"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Facebook</span>
                            @if ($post->fb_post_id)
                                <span class="btn btn-default"><a target="_blank" href="https://facebook.com/{{$post->fb_post_id}}">View  on Facebook</a></span>
                            @else
                                <a class="btn btn-sm btn-info btn-flat pull-left" href="{{url('/admin/post/'.$post->id.'/publishtofacebook')}}">Upload to Facebook</a>
                            @endif
                        </div>
                    <!-- /.info-box-content -->
                    </div>
                <!-- /.info-box -->
                </div>
            </div>
        </div>
    </div>
</div>
@stop
