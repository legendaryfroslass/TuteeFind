<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
      <div class="pull-left image">
        <img src="<?php echo (!empty($user['photo'])) ? '../images/'.$user['photo'] : '../images/TuteeFindLogoName.jpg'; ?>" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p><?php echo $user['firstname'].' '.$user['lastname']; ?></p>
        <a><i class="fa fa-circle text-success"></i> Admin</a>
      </div>
    </div>
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" data-widget="tree">
    <!-- REPORTS Section -->
    <li class="treeview">
        <a href="#">
            <i class="fa fa-dashboard"></i> <span>Reports</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="home"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
            <li><a href="matches"><i class="fa fa-code-fork"></i> <span>Matches</span></a></li>
        </ul>
    </li>

    <!-- MANAGE Section -->
    <li class="treeview">
        <a href="#">
            <i class="fa fa-list"></i> <span>Manage</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="professor"><i class="fa fa-user"></i> <span>Professor</span></a></li>
            <li><a href="tutor"><i class="fa fa-users"></i> <span>Tutor</span></a></li>
            <li><a href="tutee"><i class="fa fa-child"></i> <span>Tutee</span></a></li>
        </ul>
    </li>

    

    <!-- ARCHIVE RECORDS Section -->
    <li class="treeview">
        <a href="#">
            <i class="fa fa-archive"></i> <span>Archive Records</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="archive_professor"><i class="fa fa-user"></i> <span>Professor</span></a></li>
            <li><a href="archive_tutor"><i class="fa fa-users"></i> <span>Tutor</span></a></li>
            <li><a href="archive_tutee"><i class="fa fa-child"></i> <span>Tutee</span></a></li>
        </ul>
    </li>

     <!-- System Logs  Section -->
     <li class="treeview">
        <a href="#">
            <i class="fa fa-list-alt"></i> <span>System Logs</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="logs_professor"><i class="fa fa-user"></i> <span>Professor</span></a></li>
            <li><a href="logs_tutor"><i class="fa fa-users"></i> <span>Tutor</span></a></li>
            <li><a href="logs_tutee"><i class="fa fa-child"></i> <span>Tutee</span></a></li>
        </ul>
    </li>
</ul>


  </section>
  <!-- /.sidebar -->
</aside>
