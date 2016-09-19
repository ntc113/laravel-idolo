@extends('backend/layout/layout')
@section('content')
        <!-- Content Header (Page header) -->
<section class="content-header">
    <h1> User
        <small> | Show User</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.contact.index') !!}"><i class="fa fa-contact"></i> User</a></li>
        <li class="active">Show User</li>
    </ol>
</section>
<br>
<br>
<div class="content">
    <div class="pull-left">
        <div class="btn-toolbar">
            <a href="{!! langRoute('admin.contact.index') !!}"
               class="btn btn-primary"> <span class="glyphicon glyphicon-arrow-left"></span>&nbsp;Back </a>
            <a class="btn btn-danger" href="{!! langRoute('admin.contact.delete', ['id'=>$contact->id])!!}"><i class="ion-trash-a"></i></a>
            <a class="btn btn-default checkViewed" href="javascript:;" id="{{$contact->id}}"><i id="contact-{{$contact->id}}" class="ion-eye {{ ($contact->is_viewed) ? 'text-green': 'text-red' }}"></i></a>
        </div>
    </div>
    <br> <br> <br>
    <table class="table table-striped">
        <tbody>
        <tr>
            <td><strong>Full Name</strong></td>
            <td>{!! $contact->name !!}</td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td>{!! $contact->email !!}</td>
        </tr>
        <tr>
            <td><strong>Subject</strong></td>
            <td>{!! $contact->subject !!}</td>
        </tr>
        <tr>
            <td><strong>Message</strong></td>
            <td>{!! $contact->message !!}</td>
        </tr>
        <tr>
            <td><strong>Date Created</strong></td>
            <td>{!! $contact->created_at !!}</td>
        </tr>
        </tbody>
    </table>
</div>
@stop

@section ('ext')
<script>
$(function () {
    // answer settings
    $(".checkViewed").bind("click", function (e) {
        var id = $(this).attr('id');
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "{!! url( getLang() . '/admin/contact/" + id + "/toggle-view/') !!}",
            headers: {
                'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            },
            success: function (response) {
                if (response['error'] == 0) {
                    if (response['is_viewed'] == 1) {
                        $("#contact-" + id).addClass('text-green');
                        $("#contact-" + id).removeClass('text-red');
                    } else {
                        $("#contact-" + id).removeClass('text-green');
                        $("#contact-" + id).addClass('text-red');
                    }
                }
            },
            error: function () {
                alert("error");
            }
        })
    });
});
</script>
@stop