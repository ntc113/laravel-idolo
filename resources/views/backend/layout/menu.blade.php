<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{!! (Sentinel::getUser()->avatar) ? Sentinel::getUser()->avatar : config('cc.app_avatar') !!}" class="img-circle" alt="User Image" />

            </div>
            <div class="pull-left info">
                <p>{{ (Sentinel::getUser()->name == 'NULL')?Sentinel::getUser()->email:Sentinel::getUser()->name }}</p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
              </span>
            </div>
        </form>
        <!-- /.search form -->
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu">
            <li class="header">MAIN NAVIGATION</li>
            <li class="{{ setActive('admin') }}"><a href="{{ url('/admin') }}"> <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a></li>
            <li class="treeview {{ setActive('admin/post*') }}"><a href="#"> <i class="fa fa-book"></i> <span>Posts</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                        <small class="label pull-right bg-green" title="Total Posts">{{$allPosts}}</small>
                        <small class="label pull-right bg-red" title="Not Approved">{{$postNotApprove}}</small>
                    </span> </a>
                <ul class="treeview-menu">
                    <li><a href="{{ url('/admin/post') }}"><i class="fa fa-archive"></i> All Posts</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/post/create') }}"><i class="fa fa-plus-square"></i> Add Post</a>
                    </li>
                </ul>
            </li>
            <li class="treeview {{ setActive(['admin/user*', 'admin/group*']) }}"><a href="#"> <i class="fa fa-user"></i> <span>Users</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                        <small class="label pull-right bg-green" title="Total Users">{{$allUsers}}</small>
                        <small class="label pull-right bg-red" title="Users not active">{{ $userNotActive }}</small>
                    </span> </a>
                <ul class="treeview-menu">
                    <li><a href="{{ url('/admin/user') }}"><i class="fa fa-user"></i> All Users</a>
                    </li>
                    <li><a href="{{ url('/admin/user/create') }}"><i class="fa fa-user"></i> Add User</a>
                    </li>
                </ul>
            </li>
            <li>
              <a href="{{ url('/admin/contact') }}">
                <i class="fa fa-envelope"></i> <span>Mailbox</span>
                <span class="pull-right-container">
                  <small class="label pull-right bg-green">{{$allContacts}}</small>
                  <small class="label pull-right bg-red">{{$contactNotView}}</small>
                </span>
              </a>
            </li>
            <li class="header">ACCOUNT</li>
            <li class="{{ setActive('admin/logout*') }}">
                <a href="{{ url('/admin/logout') }}"> <i class="fa fa-sign-out"></i> <span>Logout</span> </a>
            </li>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>