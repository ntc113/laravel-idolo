@extends('backend/layout/layout')
@section('content')
<section class="content-header">
    <h1> Contact
        <small> | Control Panel</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang(). '/admin/contact') !!}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Contact</li>
    </ol>
</section>
<br>
<div class="content">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"> All contact</h3>
        </div>
        <div class="box-body">
            @include('flash::message')
            <br>

            @if($contacts->count())
            <div class="">
                <table id="full-table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Created time</th>
                        <th>Action</th>
                        <th>Viewed</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach( $contacts as $contact )
                    <tr>
                        <td>
                         {!! link_to_route(getLang(). 'admin.contact.show', $contact->name, $contact->id, array( 'class' => 'btn btn-link btn-xs' )) !!}</td>
                        <td>{!! $contact->email !!}</td>
                        <td>{!! $contact->subject !!}</td>
                        <td>{!! $contact->created_at !!}</td>
                        <td>
                            <a href="{!! URL::route('admin.contact.delete', array($contact->id)) !!}" title="Delete contact">
                                <span class="ion-trash-a"></span>
                            </a>
                        </td>
                        <td>
                            <a class="checkViewed" href="javascript:;" title="View contact" id="{{$contact->id}}">
                                <span id="contact-{{$contact->id}}" class="ion-eye {{ ($contact->is_viewed) ? 'text-green': 'text-red' }}"></span>
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