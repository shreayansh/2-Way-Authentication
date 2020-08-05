<?php
	include("connection.php");
	if(empty($_SESSION['user_id']))
	{
		header('Location: /login.php');
		die();
	}
	$user_id = $_SESSION['user_id'];
	$user_result = mysqli_query($conn,"select * from tbl_users where user_id='$user_id'") or die(mysqli_error($conn));
	$user_row = mysqli_fetch_array($user_result);
	if($user_row['user_type'] != "Admin"){
		header('Location: /dashboard.php');
		die();
	}
	?>
<!DOCTYPE html>
<html>
	<head>
		<title></title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	</head>
	<body>
		<div class="container">
			<h1>Admin - Panel</h1>
			<div class="row">
				<div class="col-md-offset-1 col-md-4">
					<h3>User Creation</h3>
					<form name="signup-form" id="signup-form" method="post">
						<input type="hidden" id="process_name" name="process_name" value="user_create" />
						<div class="errorMsg errorMsgReg"></div>
						<div class="form-group">
							<label for="type">User Type:</label>
							<select name="user_type" class="form-control">
								<option value="Admin">Administrator</option>
								<option value="Standard" selected >Standard User</option>
							</select>
						</div>
						<div class="form-group">
							<label for="email">Email:</label>
							<input type="email" name="reg_email" class="form-control" id="reg_email" required />
						</div>
						<div class="form-group">
							<label for="password">Password:</label>
							<input type="text" name="reg_password" class="form-control" id="reg_password" required />
						</div>
						<button type="submit" class="btn btn-primary btn-reg-submit">Submit</button>
					</form>
				</div>
				<div class="col-md-offset-1 col-md-4">
				</div>
			</div>
		</div>
		<script>
			$(document).ready(function(){
				$(document).on('submit', '#signup-form', function(ev){
					var data = $("#signup-form").serialize();
					$.post('check_user.php', data, function(data,status){
						if( data == "done"){
							alert("User Created Successfully !");
						}
						else{
							alert(data);
						}
						
					});
				});
			});
		</script>
	</body>
</html>