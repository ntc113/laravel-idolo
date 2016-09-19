@extends('backend/layout/layout')
@section('content')
    <section class="content-header">
        <h1> Post
            <small> | Control Panel</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{!! url(getLang() . '/admin') !!}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Post</li>
        </ol>
    </section>
    <br>

    <div class="content">
        <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">All posts</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            @include('flash::message')
            <br>

            <div class="pull-left">
                <div class="btn-toolbar">
                    <a href="{!! langRoute('admin.post.create') !!}" class="btn btn-primary">
                        <span class="glyphicon glyphicon-plus"></span>&nbsp;Add Post </a>
                    <!-- <a href="{!! langRoute('admin.category.create') !!}" class="btn btn-primary">
                        <span class="glyphicon glyphicon-plus"></span>&nbsp;Add Category </a> -->
                </div>
            </div>
            <br> <br> <br>
            @if($posts->count())
                <div class="">
                    <table id="full-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Caption</th>
                            <th>User</th>
                            <!-- <th>Category</th> -->
                            <th>Created</th>
                            <th>Action</th>
                            <!-- <th>Publish</th> -->
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $posts as $post )
                            <tr>
                                <td>
                                    <a href="./post/{{ $post->id }}" class="btn btn-link btn-xs">
                                        {!! subwords(strip_tags($post->content), 10) !!}</a>
                                </td>
                                <td>{!! $post->display_name !!}</td>
                                <!-- <td>{!! $post->category_name !!}</td> -->
                                <td>{!! $post->created_at !!}</td>
                                <td>
                                    <a href="{!! langRoute('admin.post.edit', array($post->id)) !!}" title="Edit">
                                                    <span class="ion-edit"></span></a>
                                    <a href="{!! URL::route('admin.post.delete', array($post->id)) !!}" title="Delete">
                                                    <span class="ion-trash-a"></span></a>

                                    @if ($post->youtube_id)
                                        <a target="_blank" href="https://youtube.com/watch?v={{$post->youtube_id}}" title="View the post on Youtube"><span class="ion-social-youtube"></span></a>
                                    @else
                                        <a href="{!! URL::route('admin.post.publishtoyoutube', array($post->id)) !!}" title="Publish to Youtube"><span class="ion-social-youtube-outline"></span></a>
                                    @endif

                                    @if ($post->fb_post_id)
                                        <a target="_blank" href="https://facebook.com/{{$post->fb_post_id}}" title="View the post on facebook"><span class="ion-social-facebook"></span></a>
                                        <a target="_blank" href="{{url('/post/'.$post->id)}}" title="View the {{config('cc.app_name')}}"><span class="ion-ios-world"></span></a>
                                    @else
                                        <a href="{!! URL::route('admin.post.publishtofacebook', array($post->id)) !!}" title="Publish to Facebook"><span class="ion-social-facebook-outline"></span></a>
                                    @endif
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
            <!-- /.box-body -->
        </div>
    </div>
@stop

@section ('ext')
<!-- DataTables -->
<script src="{!! url('backend/plugins/datatables/jquery.dataTables.min.js') !!}"></script>
<script src="{!! url('backend/plugins/datatables/dataTables.bootstrap.min.js') !!}"></script>

<script type="text/javascript">
    $(function () {
        $("#full-table").DataTable({
            "order": [[ 2, "desc" ]]
        });
    });
    $(document).ready(function () {

        $('#notification').show().delay(4000).fadeOut(700);

        // publish settings
        $(".publish").bind("click", function (e) {
            var id = $(this).attr('id');
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "{!! url(getLang() . '/admin/post/" + id + "/toggle-publish/') !!}",
                headers: {
                    'X-CSRF-Token': $('meta[name="_token"]').attr('content')
                },
                success: function (response) {
                    if (response['result'] == 'success') {
                        var imagePath = (response['changed'] == 1) ? "{!!url('/')!!}/assets/images/publish.png" : "{!!url('/')!!}/assets/images/not_publish.png";
                        $("#publish-image-" + id).attr('src', imagePath);
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