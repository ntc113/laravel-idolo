@extends('backend/layout/layout')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> User
        <small> | Update User</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang(). '/admin/user') !!}"><i class="fa fa-user"></i> User</a></li>
        <li class="active">Update User</li>
    </ol>
</section>
<br>
<br>
<div class="content">

    {!! Form::open( array( 'route' => array(getLang(). 'admin.user.update', $user->id), 'method' => 'PATCH')) !!}
    <!-- User Name -->
    <div class="control-group {!! $errors->has('name') ? 'has-error' : '' !!}">
        <label class="control-label" for="name">Name</label>

        <div class="controls">
            {!! Form::text('name', $user->name, array('class'=>'form-control', 'id' => 'name', 'placeholder'=>'Name', 'value'=>Input::old('name'))) !!}
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
            {!! Form::text('email', $user->email, array('class'=>'form-control', 'id' => 'email', 'placeholder'=>'Email', 'value'=>Input::old('email'))) !!}
            @if ($errors->first('email'))
            <span class="help-block">{!! $errors->first('email') !!}</span>
            @endif
        </div>
    </div>
    <br>
    <!-- Email -->
    <div class="control-group {!! $errors->has('phone_number') ? 'has-error' : '' !!}">
        <label class="control-label" for="email">Phone number</label>

        <div class="controls">
            {!! Form::text('phone_number', $user->phone_number, array('class'=>'form-control', 'id' => 'phone_number', 'placeholder'=>'Phone Number', 'value'=>Input::old('phone_number'))) !!}
            @if ($errors->first('phone_number'))
            <span class="help-block">{!! $errors->first('phone_number') !!}</span>
            @endif
        </div>
    </div>
    <br>

    <!-- Role -->
    <div class="control-group {!! $errors->has('is_published') ? 'has-error' : '' !!}">
        <label class="control-label" for="groups">Roles</label>
        <div class="controls">

            @foreach($roles as $id=>$role)
            <label><input {!! ((in_array($role, $userRoles)) ? 'checked' : '') !!} type="checkbox" value="{!! $id !!}" name="groups[{!! $role !!}]">  {!! $role !!}</label>
            @endforeach

        </div>
    </div>
    <br>

    <!-- Form actions -->
    {!! Form::submit('Save Changes', array('class' => 'btn btn-success')) !!}
    <a href="{!! url(getLang() . '/admin/user') !!}"
       class="btn btn-default">
        &nbsp;Cancel
    </a>
    {!! Form::close() !!}
</div>

@stop