<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="_token" content="{!! csrf_token() !!}" />
    <title>{{ config('cc.app_name') }} Admin | Dashboard</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="{!! url('backend/bootstrap/css/bootstrap.min.css') !!}" rel="stylesheet" type="text/css"/>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <!-- jvectormap -->
    <link href="{!! url('backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css') !!}" rel="stylesheet" type="text/css"/>
    <!-- Theme style -->
    <link href="{!! url('backend/css/AdminLTE.min.css') !!}" rel="stylesheet" type="text/css"/>
    <link href="{!! url('backend/css/style.css') !!}" rel="stylesheet" type="text/css"/>
    {!! HTML::style("assets/css/github-left.css") !!}
    <!-- DataTables -->
    <link rel="stylesheet" href="{!! url('backend/plugins/datatables/dataTables.bootstrap.css') !!}">
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
         folder instead of downloading all of them to reduce the load. -->
    <link href="{!! url('backend/css/skins/_all-skins.min.css') !!}" rel="stylesheet" type="text/css"/>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// --><!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script><![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <header class="main-header">
        <!-- Logo -->
        <a href="{!! url('/admin') !!}" class="logo"><b>{{ config('cc.app_name')}}</b> Admin</a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"> <span class="sr-only">Toggle navigation</span>
            </a>
            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                  <li class="dropdown messages-menu">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="{{ url('/admin/contact') }}" aria-expanded="false">
                      <i class="fa fa-envelope-o"></i>
                      <span class="label label-danger">{{$contactNotView}}</span>
                    </a>
                  </li>
                  <!-- Messages: style can be found in dropdown.less-->
                  <li class="dropdown messages-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <i class="fa fa-newspaper-o"></i>
                      <span class="label label-danger">{{$newPostNotApprove}}</span>
                    </a>
                    <ul class="dropdown-menu">
                      <li class="header"><i class="fa fa-newspaper-o text-green"></i> Total: {{$allPosts}} Posts</li>
                      <li>
                        <ul class="menu">
                          <li><a href="javascript:void(0)"><i class="fa fa-newspaper-o text-red"></i> {{$newPostNotApprove}} posts not been approved today</a></li>
                          <li><a href="javascript:void(0)"><i class="fa fa-newspaper-o text-yellow"></i> {{$postNotApprove}} posts not been approved</a></li>    
                        </ul>
                      </li>
                      
                      <li class="footer"><a href="/admin/post">See All Messages</a></li>
                    </ul>
                  </li>
                  <!-- Notifications: style can be found in dropdown.less -->
                  <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <i class="fa fa-users"></i>
                      <span class="label label-green">{{$todayUsers}}</span>
                    </a>
                    <ul class="dropdown-menu">
                      <li class="header"><i class="fa fa-users text-green"></i> Total: {{$allUsers}} Users</li>
                      <li>
                        <ul class="menu">
                          <li><a href="javascript:void(0)"><i class="fa fa-users text-red"></i> {{$todayUsers}} users register today</a></li>
                          <li><a href="javascript:void(0)"><i class="fa fa-users text-yellow"></i> {{$userNotActive}} users not active</a></li>    
                        </ul>
                      </li>
                      <li class="footer"><a href="/admin/user">See All User</a></li>
                    </ul>
                  </li>
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ (Sentinel::getUser()->avatar) ? Sentinel::getUser()->avatar : config('cc.app_avatar') }}" class="user-image" alt="User Image"/>
                            <span class="hidden-xs">{{ (Sentinel::getUser()->name == 'NULL')?Sentinel::getUser()->email:Sentinel::getUser()->name }}</span> </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="{{ (Sentinel::getUser()->avatar) ? Sentinel::getUser()->avatar : config('cc.app_avatar') }}" class="img-circle" alt="User Image"/>

                                <p>
                                <p> {{ (Sentinel::getUser()->name == 'NULL')?Sentinel::getUser()->email:Sentinel::getUser()->name }}
                                    <!-- <small>Member since Nov. 2012</small> -->
                                </p>
                            </li>
                            <!-- Menu Body -->
                            <li class="user-body">
                                <div class="col-xs-4 text-center">

                                </div>
                                <div class="col-xs-4 text-center">

                                </div>
                                <div class="col-xs-4 text-center">

                                </div>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="{{ url('/admin/user/' . Sentinel::getUser()->id) }}" class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="{{ url('/admin/logout') }}" class="btn btn-default btn-flat">Sign out</a></div>

                            </li>
                        </ul>
                     </li>
                  </ul>
            </div>
        </nav>
    </header>

    @include('backend/layout/menu')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        @yield('content')
    </div><!-- /.content-wrapper -->

    @include('backend/layout/footer')
</div>
<!-- ./wrapper -->
<!-- jQuery 2.1.3 -->
<script src="{!! url('backend/plugins/jQuery/jquery-2.2.3.min.js') !!}"></script>
<!-- Bootstrap 3.3.2 JS -->
<script src="{!! url('backend/bootstrap/js/bootstrap.min.js') !!}" type="text/javascript"></script>
<!-- FastClick -->
<script src="{!! url('backend/plugins/fastclick/fastclick.min.js') !!}"></script>
<!-- AdminLTE App -->
<script src="{!! url('backend/js/app.min.js') !!}" type="text/javascript"></script>
<!-- Sparkline -->
<script src="{!! url('backend/plugins/sparkline/jquery.sparkline.min.js') !!}" type="text/javascript"></script>
<!-- jvectormap -->
<script src="{!! url('backend/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js') !!}" type="text/javascript"></script>
<script src="{!! url('backend/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') !!}" type="text/javascript"></script>
<!-- SlimScroll 1.3.0 -->
<script src="{!! url('backend/plugins/slimScroll/jquery.slimscroll.min.js') !!}" type="text/javascript"></script>
<!-- ChartJS 1.0.1 -->
<script src="{!! url('backend/plugins/chartjs/Chart.min.js') !!}" type="text/javascript"></script>
@yield('ext')
</body>
</html>