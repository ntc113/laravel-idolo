@extends('backend/layout/layout')
@section('content')
<section class="content-header">
    <h1> User
        <small> | Control Panel</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang(). '/admin/user') !!}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">User</li>
    </ol>
</section>
<br>
<div class="content">
    <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">All users</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
        @include('flash::message')
        <br>

        <div class="pull-left">
            <div class="btn-toolbar">
                <a href="{!! langRoute('admin.user.create') !!}" class="btn btn-primary">
                    <span class="glyphicon glyphicon-plus"></span>&nbsp;New User
                </a>
            </div>
        </div>
        <br>
        <br>
        <br>
        @if($users->count())
        <div class="">
            <table id="full-table" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined</th>
                    <th>Last Login</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach( $users as $user )
                <tr>
                    <td>
                    <img class="avatar" alt="{{ $user->name }}" src="{!! ($user->avatar) ? $user->avatar : config('cc.app_avatar') !!}">
                     {!! link_to_route(getLang(). 'admin.user.show', $user->name, $user->id, array( 'class' => 'btn btn-link btn-xs' )) !!}</td>
                    <td>{!! $user->email !!}</td>
                    <td>{!! $user->created_at !!}</td>
                    <td>{!! $user->last_login !!}</td>
                    <td>
                        <a href="{!! langRoute('admin.user.edit', array($user->id)) !!}">
                            <span class="ion-edit"></span>
                        </a>
                        <a href="{!! URL::route('admin.user.delete', array($user->id)) !!}">
                            <span class="ion-trash-a"></span>
                        </a>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-danger">No results found</div>
        @endif
        </div>
    </div>
</div>
@stop

@section ('ext')
<!-- DataTables -->
<script src="{!! url('backend/plugins/datatables/jquery.dataTables.min.js') !!}"></script>
<script src="{!! url('backend/plugins/datatables/dataTables.bootstrap.min.js') !!}"></script>
<script>
  $(function () {
    $("#full-table").DataTable();
  });
</script>
@stop