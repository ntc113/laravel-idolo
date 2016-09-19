@extends('backend/layout/layout')
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> User
        <small> | Add User</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang(). '/admin/user') !!}"><i class="fa fa-user"></i> User</a></li>
        <li class="active">Add User</li>
    </ol>
</section>
<br>
<br>
<div class="container">
    {!! Form::open(array('action' => '\App\Http\Controllers\Admin\UserController@store')) !!}
    
    <!-- Name -->
    <div class="control-group {!! $errors->has('name') ? 'has-error' : '' !!}">
        <label class="control-label" for="name">Full Name</label>

        <div class="controls">
            {!! Form::text('name', null, array('class'=>'form-control', 'id' => 'name', 'placeholder'=>'Input Full Name', 'value'=>Input::old('name'))) !!}
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
            {!! Form::text('email', null, array('class'=>'form-control', 'id' => 'email', 'placeholder'=>'Email', 'value'=>Input::old('email'))) !!}
            @if ($errors->first('email'))
            <span class="help-block">{!! $errors->first('email') !!}</span>
            @endif
        </div>
    </div>
    <br>

    <!-- Role -->
    <div class="control-group {!! $errors->has('is_published') ? 'has-error' : '' !!}">
        <label class="control-label" for="groups">Roles</label>
        <div class="controls">

            @foreach($roles as $id=>$role)
            <label><input type="checkbox" value="{!! $id !!}" name="roles[{!! $role !!}]">  {!! $role !!}</label>
            @endforeach

        </div>
    </div>
    <br>

    <legend>Password</legend>
    <!-- Password -->
    <div class="control-group {!! $errors->has('password') ? 'has-error' : '' !!}">
        <label class="control-label" for="password">Password</label>

        <div class="controls">
            {!! Form::password('password', array('class'=>'form-control', 'id' => 'password', 'placeholder'=>'Password', 'value'=>Input::old('password'))) !!}
            @if ($errors->first('password'))
            <span class="help-block">{!! $errors->first('password') !!}</span>
            @endif
        </div>
    </div>
    <br>
    <!-- Confirm Password -->
    <div class="control-group {!! $errors->has('confirm-password') ? 'has-error' : '' !!}">
        <label class="control-label" for="confirm-password">Confirm Password</label>

        <div class="controls">
            {!! Form::password('confirm_password', array('class'=>'form-control', 'id' => 'confirm_password', 'placeholder'=>'Confirm Password', 'value'=>Input::old('confirm_password'))) !!}
            @if ($errors->first('confirm-password'))
            <span class="help-block">{!! $errors->first('confirm-password') !!}</span>
            @endif
        </div>
    </div>
    <br>
    <!-- Form actions -->
    {!! Form::submit('Save Changes', array('class' => 'btn btn-success')) !!}
    <a href="{!! url('admin/user') !!}"
       class="btn btn-default">
        &nbsp;Cancel
    </a>
    {!! Form::close() !!}
</div>
@stop