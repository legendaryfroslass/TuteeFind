<?php
session_start();
require_once '../tutor.php';
$user = new TUTOR();
session_start();
 if(isset($_SESSION["tutorSession"])){
	  session_unset();
// Destroying session
session_destroy();
	header( "Location:login");
 }
if($user->is_logged_in()!="")
{
	$_SESSION[ 'tutorSession' ];
	session_unset();
	$user->logout();	
	$user->redirect('login');
}
?>