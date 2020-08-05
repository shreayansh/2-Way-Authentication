<?php
include("connection.php");

require_once 'googleLib/GoogleAuthenticator.php';
$gauth = new GoogleAuthenticator();
$secret_key = $gauth->createSecret();

if(!isset($_POST['process_name'])){
	header("Location: " . $APP_URL . "login.php");
	die();
}
$process_name = $_POST['process_name'];



if($process_name == "user_create"){
	$reg_email		= $_POST['reg_email'];
	$reg_password	= md5($_POST['reg_password']);
	$reg_type		= $_POST['user_type'];
    
	$chk_user = mysqli_query($conn,"select * from tbl_users where email='$reg_email'") or die(mysqli_error($conn));
	if(mysqli_num_rows($chk_user) == 0){
    	$query = "insert into tbl_users(email, password, user_type, password_last_change) values('$reg_email', '$reg_password', '$reg_type', now() )";
		$result = mysqli_query($conn,$query) or die(mysqli_error($conn));
		if($result){
			echo "done";
		}else{
			echo "Database error";
		}
    }
    else{
		echo "This Email already exits in system.";
    }
}

if($process_name == "user_registor"){
	$reg_name		= $_POST['reg_name'];
	$reg_email		= $_POST['reg_email'];
	$old_password	= md5($_POST['old_password']);
	$new_password	= md5($_POST['new_password']);
    
	$chk_user = mysqli_query($conn,"select * from tbl_users where email='$reg_email'") or die(mysqli_error($conn));
	if(mysqli_num_rows($chk_user) == 1){
		$user_row = mysqli_fetch_array($chk_user);
		$user_id = $user_row['user_id'];
		if($user_row['password'] == $old_password){
			$query = "UPDATE tbl_users SET password = '$new_password', password_last_change = now(), profile_name = '$reg_name', google_auth_code = '$secret_key', last_login = now() WHERE user_id = '$user_id'";
			$result = mysqli_query($conn,$query) or die(mysqli_error($conn));
			$_SESSION['user_id'] = $user_id;
			$_SESSION['gauth'] = 'new';
			echo "done";
		}else{
			echo "Unauthorised SignUp, Password Invalid";
		}
	}else{
		echo "Email not found";
	}
}

if($process_name == "change-password"){
	$old_password	= md5($_POST['old_password']);
	$new_password	= md5($_POST['new_password']);
    
	$user_id = $_SESSION['user_id'];
	$result = mysqli_query($conn,"select * from tbl_users where user_id='$user_id'") or die(mysqli_error($conn));
	$user_row = mysqli_fetch_array($result);
	if($user_row['password'] == $old_password){
		$query = "UPDATE tbl_users SET password = '$new_password', password_last_change = now() WHERE user_id = '$user_id'";
		$result = mysqli_query($conn,$query) or die(mysqli_error($conn));
		echo "done";
	}else{
		echo "Wrong Current Password !";
	}
}

if($process_name == "user_login"){
	$login_email		= $_POST['login_email'];
	$login_password		= md5($_POST['login_password']);
    
	$user_result = mysqli_query($conn,"select * from tbl_users where email='$login_email' and password='$login_password'") or die(mysqli_error($conn));
	if(mysqli_num_rows($user_result) == 1){
    	$user_row = mysqli_fetch_array($user_result);
		if(!$user_row['google_auth_code']){
			echo "SignUp and MFA Required";
			die();
		}
		$_SESSION['user_id'] = $user_row['user_id'];
		$_SESSION['login_at'] = date('Y-m-d H:i:s',time());
		echo "done";
    }
    else{
		echo "Check your credentials.";
    }
}

if($process_name == "verify_code"){
	$scan_code = $_POST['scan_code'];
	$user_id = $_SESSION['user_id'];
	
	$user_result = mysqli_query($conn,"select * from tbl_users where user_id='$user_id'") or die(mysqli_error($conn));
	$user_row = mysqli_fetch_array($user_result);
	$secret_key	= $user_row['google_auth_code'];
	if(!$secret_key){
		echo "MFA Not Registered";
		die();
	}
	
	$checkResult = $gauth->verifyCode($secret_key, $scan_code, 2);    // 2 = 2*30sec clock tolerance

	if ($checkResult){
		$_SESSION['googleVerifyCode'] = $scan_code;
		if(isset($_SESSION['gauth'])){
			$query = "UPDATE tbl_users SET gauth_qr_last_scan = now() WHERE user_id = '$user_id'";
			$result = mysqli_query($conn,$query) or die(mysqli_error($conn));
			unset($_SESSION['gauth']);
		}
		if(isset($_POST['new_reg'])){
			$_SESSION['gauth'] = 'reg';
			unset($_SESSION['googleVerifyCode']);
		}
		echo "done";
	} 
	else{
		echo 'Note : Code not matched.';
	}
}
?>