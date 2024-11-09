<?php
session_start();
require_once '../tutee.php';
$user = new TUTEE();
session_start();
 if(isset($_SESSION["userSession"])){
	  session_unset();
// Destroying session
session_destroy();
	header( "Location:login");
 }
if($user->is_logged_in()!="")
{
	$_SESSION[ 'userSession' ];
	session_unset();
	$user->logout();	
	$user->redirect('login');
}
?>