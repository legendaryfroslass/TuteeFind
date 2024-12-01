<?php
include 'includes/session.php'; // Ensure session is included to access $professor_id

if(isset($_SESSION['professor_id'])) {
    $professor_id = $_SESSION['professor_id'];
    
    // Retrieve professor details from the database
    $sql = "SELECT * FROM professor WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1) {
        $user = $result->fetch_assoc(); // Assign professor details to $user
    }
} else {
    // Handle case when professor is not logged in
    header('location: index.php');
    exit();
}

?>
<header class="main-header">
<!-- Logo -->
<a href="#" class="logo">
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <span class="logo-mini"><b>T</b>F</span>
    <!-- logo for regular state and mobile devices -->
    <span class="logo-lg"><b>TuteeFind</b> Professor</span>
</a>
<!-- Header Navbar: style can be found in header.less -->
<nav class="navbar navbar-static-top">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
    <span class="sr-only">Toggle navigation</span>
    </a>

    <div class="navbar-custom-menu">
    <ul class="nav navbar-nav">
        <!-- User Account: style can be found in dropdown.less -->
        <li class="dropdown user user-menu">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <img src="<?php echo (!empty($user['prof_photo'])) ? '../images/'.$user['prof_photo'] : '../assets/TuteeFindLogoName.jpg'; ?>" class="user-image" alt="User Image">
            <span class="hidden-xs"><?php echo $user['firstname'].' '.$user['lastname']; ?></span>
        </a>
        <ul class="dropdown-menu">
            <!-- User image -->
            <li class="user-header">
            <img src="<?php echo (!empty($user['prof_photo'])) ? '../images/'.$user['prof_photo'] : '../assets/TuteeFindLogoName.jpg'; ?>" class="img-circle" alt="User Image">
            

            <p>
                <?php echo $user['firstname'].' '.$user['lastname']; ?>
                
              </p>
            </li>
            
            <li class="user-footer">
            <div class="pull-left">
                <a href="#profile" data-toggle="modal" class="btn btn-default btn-flat" id="professor_profile">Update</a>
            </div>
            <div class="pull-right">
                <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
            </div>
            </li>
        </ul>
        </li>
    </ul>
    </div>
</nav>
</header>
<?php include 'includes/profile_modal.php'; ?>
