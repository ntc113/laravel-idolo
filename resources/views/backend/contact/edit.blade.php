@extends('backend/layout/layout')
@section('message')
<!-- Content Header (Page header) -->
<section class="message-header">
    <h1> Contact
        <small> | Update Contact</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang(). '/admin/contact') !!}"><i class="fa fa-contact"></i> Contact</a></li>
        <li class="active">Update Contact</li>
    </ol>
</section>
<br>
<br>
<div class="message">

    {!! Form::open( array( 'route' => array(getLang(). 'admin.contact.update', $contact->id), 'method' => 'PATCH')) !!}
    <!-- Contact Name -->
    <div class="control-group {!! $errors->has('name') ? 'has-error' : '' !!}">
        <label class="control-label" for="name">Name</label>

        <div class="controls">
            {!! Form::text('name', $contact->name, array('class'=>'form-control', 'id' => 'name', 'placeholder'=>'Name', 'value'=>Input::old('name'))) !!}
            @if ($errors->first('name'))
            <span class="help-block">{!! $errors->first('name') !!}</span>
            @endif
        </div>
    </div>
    <br>
    <!-- Email -->
    <div class="control-group {!! $errors->has('email') ? 'has-error' : '' !!}">
        <label class="control-label" for="email">Email</label>

        <div class="controls">
            {!! Form::text('email', $contact->email, array('class'=>'form-control', 'id' => 'email', 'placeholder'=>'Email', 'value'=>Input::old('email'))) !!}
            @if ($errors->first('email'))
            <span class="help-block">{!! $errors->first('email') !!}</span>
            @endif
        </div>
    </div>
    <br>
    <!-- Subject -->
    <div class="control-group {!! $errors->has('subject') ? 'has-error' : '' !!}">
        <label class="control-label" for="subject">Subject</label>

        <div class="controls">
            {!! Form::text('subject', $contact->subject, array('class'=>'form-control', 'id' => 'subject', 'placeholder'=>'Subject', 'value'=>Input::old('subject'))) !!}
            @if ($errors->first('subject'))
            <span class="help-block">{!! $errors->first('subject') !!}</span>
            @endif
        </div>
    </div>
    <br>

    <!-- Message -->
    <div class="control-group {!! $errors->has('message') ? 'has-error' : '' !!}">
        <label class="control-label" for="groups">Message</label>
        <div class="controls">
            {!! Form::textarea('message', $post->message, array('class'=>'form-control', 'id' => 'message', 'placeholder'=>'Input a caption', 'value'=>Input::old('message'))) !!}
            @if ($errors->first('message'))
            <span class="help-block">{!! $errors->first('message') !!}</span>
            @endif

        </div>
    </div>
    <br>

    <!-- Form actions -->
    {!! Form::submit('Save Changes', array('class' => 'btn btn-success')) !!}
    <a href="{!! url(getLang() . '/admin/contact') !!}"
       class="btn btn-default">
        &nbsp;Cancel
    </a>
    {!! Form::close() !!}
</div>

@stop