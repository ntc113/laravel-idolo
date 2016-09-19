@extends('backend/layout/layout')
@section('content')
{!! HTML::style('jasny-bootstrap/css/jasny-bootstrap.min.css') !!}

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> Post <small> | Post to facebook</small> </h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/post') !!}"><i class="fa fa-book"></i> Post</a></li>
        <li class="active">Facebook Post</li>
    </ol>
</section>
<br>
<br>
<div class="content">
    <div class="row">
        @if (!$post->youtube_id)
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <label class="control-label" for="name">Video preview</label>
            <iframe width="100%" height="315" src="{{ $attachment->src }}" frameborder="0" allowfullscreen></iframe>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-8 col-lg-8">
            <div class="alert alert-danger">
                <p> This post must be published on YouTube before <a class="btn btn-info" href="./publishtoyoutube">Publish to the youtube</a></p>
            </div>
        </div>
        @elseif ($post->fb_post_id)  
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <label class="control-label" for="name">Video preview</label>
            <iframe width="100%" height="315" src="http://youtube.com/embed/{{ $post->youtube_id }}" frameborder="0" allowfullscreen></iframe>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-8 col-lg-8">
            <div class="alert alert-info">
                <p> This post has been exist on the facebook <a class="btn btn-default" href="http://facebook.com/{{$post->fb_post_id}}}">View this post on the facebook</a></p>
            </div>
        </div>
        @else
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <label class="control-label" for="name">Video preview</label>
            <iframe width="100%" height="315" src="http://youtube.com/embed/{{ $post->youtube_id }}" frameborder="0" allowfullscreen></iframe>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-8 col-lg-8">
            {!! Form::open( array('method' => 'POST', 'enctype'=>'multipart/form-data')) !!}
            {{ Form::hidden('id', $post->id, array('class' => 'form-control')) }}
                <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">

                    {{ Form::label('name', 'name', array('class' => 'control-label')) }}

                    {{ Form::text('name', $post->title, array('class' => 'form-control', 'placeholder' => 'name')) }}

                    @if ($errors->has('title'))
                        <span class="help-block">{{ $errors->first('title') }}</span>
                    @endif

                </div>

                <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">

                    {{ Form::label('description', 'description', array('class' => 'control-label')) }}

                    {{ Form::textarea('message', $post->content, array('class' => 'form-control', 'placeholder' => 'description', 'rows'=>3)) }}

                    @if ($errors->has('description'))
                        <span class="help-block">{{ $errors->first('description') }}</span>
                    @endif

                </div>

                <div class="form-group{{ $errors->has('link') ? ' has-error' : '' }}">

                    {{ Form::label('Youtube video', 'youtube video', array('class' => 'control-label')) }}

                    {{ Form::text('link', 'https://www.youtube.com/watch?v=' . $post->youtube_id, array('class' => 'form-control', 'placeholder' => 'youtube link')) }}

                    @if ($errors->has('youtube_id'))
                        <span class="help-block">{{ $errors->first('youtube_id') }}</span>
                    @endif

                </div>

                <div class="form-group{{ $errors->has('access_token') ? ' has-error' : '' }}">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            {{ Form::label('page access_token', 'Page token', array('class' => 'control-label')) }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        {{ Form::text('access_token', null, array('id' => 'page_access_token', 'class' => 'form-control', 'placeholder' => 'The page access token', 'required'=>'required')) }}
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <span id="" class="btn btn-primary" onclick="loginFB()">Get Fanpage Access_token</span>
                        </div>
                    </div>                            
                    @if ($errors->has('access_token'))
                        <div class="row">
                            <span class="help-block">{{ $errors->first('access_token') }}</span>
                        </div>
                    @endif

                </div>

                {{ Form::submit('Post to facebook', array('class' => 'btn btn-primary disabled', 'id'=>'submit_btn')) }}

            {{ Form::close() }}
        </div>
        @endif
    </div>

</div>
@stop
@section ('ext')
{!! HTML::script('jasny-bootstrap/js/jasny-bootstrap.min.js') !!}
<script>
      window.fbAsyncInit = function() {
        FB.init({
          appId      : {{config('cc.facebook_api.app_id')}},
          xfbml      : true,
          version    : 'v2.7'
        });
      };

      (function(d, s, id){
         var js, fjs = d.getElementsByTagName(s)[0];
         if (d.getElementById(id)) {return;}
         js = d.createElement(s); js.id = id;
         js.src = "//connect.facebook.net/en_US/sdk.js";
         fjs.parentNode.insertBefore(js, fjs);
       }(document, 'script', 'facebook-jssdk'));


        function loginFB() {
            FB.login(function(response) {
                if (response.authResponse) {
                    var access_token = FB.getAuthResponse()['accessToken'];
                    $.get("https://graph.facebook.com/me/accounts?access_token=" + access_token, function(data, status){
                        if (status == 'success') {
                            $.each(data.data, function(index, val) {
                                if (val.id == {{config('cc.facebook_api.fanpage_id')}}) {//'583524488486094') {
                                    $('input#page_access_token').val(val.access_token);
                                    $('#submit_btn').removeClass('disabled');
                                }
                            });
                        }
                    });
                } else {
                    console.log('User cancelled login or did not fully authorize.');
                }
            }, {
                scope: 'publish_actions,publish_pages', 
                return_scopes: true
            });
        }
    </script>
@stop