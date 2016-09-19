@extends('backend/layout/layout')
@section('content')
<section class="content-header">
    <h1> Contact
        <small> | Delete Contact</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.contact.index') !!}"><i class="fa fa-contact"></i> Contact</a></li>
        <li class="active">Delete Contact</li>
    </ol>
</section>
<br>
<br>
<br>
<div class="col-lg-10">
    {!! Form::open( array(  'route' => array(getLang(). 'admin.contact.destroy', $contact->id ) ) ) !!}
    {!! Form::hidden( '_method', 'DELETE' ) !!}
    <div class="alert alert-warning">
        <div class="pull-left"><b> Be Careful!</b> Are you sure you want to delete <b>{!! $contact->subject !!} </b> ?
        </div>
        <div class="pull-right">
            {!! Form::submit( 'Yes', array( 'class' => 'btn btn-danger' ) ) !!}
            {!! link_to( URL::previous(), 'No', array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        <div class="clearfix"></div>
    </div>
    {!! Form::close() !!}
</div>
@stop