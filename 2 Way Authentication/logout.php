<?php
include("connection.php");
if(isset($_SESSION['login_at']) && isset($_SESSION['user_id'])){
	$uid =  $_SESSION['user_id'];
	$ts = $_SESSION['login_at'];
	$query = "UPDATE tbl_users SET last_login = '$ts' WHERE user_id = '$uid'";
	$result = mysqli_query($conn,$query) or die(mysqli_error($conn));
}
session_start();
session_unset();
session_destroy();
header("Location: " . $APP_URL . "login.php");
?>