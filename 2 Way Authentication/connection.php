<?php
	session_start();
	
	//Database Credentials
	$conn = mysqli_connect("HOST","USERNAME","PASSWORD") or die(mysqli_connect_error());
	//Database Name
	$DB = mysqli_select_db($conn,'DATABASE_NAME') or die(mysqli_error($conn));
	//Time zone can be changed here
	date_default_timezone_set('Asia/Kolkata');
	
	//change the host of your Application here
	$APP_URL = 'http://HOST/'
?>