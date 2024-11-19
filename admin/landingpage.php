<?php
  session_start();
  if(isset($_SESSION['admin'])){
    header('location:home');
  }
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition login-page" style="background-image: url('LoginBackground/plv1.jpg'); background-size: cover; background-position: center;">
<head>
<link rel="icon" href="LoginBackground/ltslogo.png" type="image/png">
</head>
<div class="login-overlay"></div> <!-- Overlay to darken the background -->

<div class="login-box">
    <div class="login-logo">
        <b style="color: white; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); font-size: 48px;">Admin</b> <!-- Text shadow added for better readability -->
    </div>
  
    <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>

        <form action="login.php" method="POST">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="username" placeholder="Username" required>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <button type="submit" class="btn btn-primary btn-block btn-flat" name="login"><i class="fa fa-sign-in"></i> Sign In</button>
                </div>
                <div class="col-xs-6">
                    <a href="forgot_password" class="btn btn-default btn-block btn-flat"><i class="fa fa-question-circle"></i> Forgot Password?</a>
                </div>
            </div>
        </form>
    </div>
    <?php
        if(isset($_SESSION['error'])){
            echo "
                <div class='callout callout-danger text-center mt20'>
                    <p>".$_SESSION['error']."</p> 
                </div>
            ";
            unset($_SESSION['error']);
        }
    ?>
</div>
    
<?php include 'includes/scripts.php' ?>
</body>
</html>

<style>
/* Overlay effect */
.login-overlay {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background: rgba(0, 0, 0, 0.5); /* Semi-transparent black overlay */
    z-index: -1; /* Behind the login box */
}
</style>
