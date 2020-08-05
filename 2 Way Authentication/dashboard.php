<?php
	include("connection.php");
	if(empty($_SESSION['user_id']) || empty($_SESSION['googleVerifyCode']))
	{
		header("Location: " . $APP_URL . 'login.php');
		die();
	}
	
	$user_id = $_SESSION['user_id'];
	$user_result = mysqli_query($conn,"select * from tbl_users where user_id='$user_id'") or die(mysqli_error($conn));
	$user_row = mysqli_fetch_array($user_result);
	
	require 'vendor/autoload.php';
	
	use Aws\S3\S3Client;
	use Aws\S3\Exception\S3Exception;
	
	include('aws-config.php');
	
	// Connect to AWS
	try {
		$s3 = S3Client::factory(
			array(
				'credentials' => array(
					'key' => $IAM_KEY,
					'secret' => $IAM_SECRET
				),
				'version' => 'latest',
				'region'  => $REGION
			)
		);
	} catch (Exception $e) {
		die("Error: " . $e->getMessage());
	}
	
	if(isset($_POST['new_folder_click'])) { 
		$keyName = $_POST['directory'] . $_POST['newFolder'] . '/';
		try{
			$s3->putObject(
				array(
					'Bucket'=>$bucketName,
					'Key' =>  $keyName,
					'Body'=> ""
				)
			);
			echo '<script>alert("Folder ' . $keyName .' Created !")</script>';
		} catch (S3Exception $e) {
			die('Error:' . $e->getMessage());
		} catch (Exception $e) {
			die('Error:' . $e->getMessage());
		}
	}
	
	if(!isset($_GET['f']) || $_GET['f'] == '/')
		$folder = '';
	else
		$folder = $_GET['f'];
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>EVOLVES - Dashboard</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="css/dashboard-style.css" type="text/css" media="all" />
		<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
		<style>
			<?php
				if(!isset($_GET['result'])){
					echo '#ok,#error{ display: none; }';
				}else{
					if($_GET['result']=='success')
						echo '#error{ display: none; }';
					elseif($_GET['result']=='failed')
						echo '#ok{ display: none; }';
				}
				?>
		</style>
	</head>
	<body>
		<!-- Header -->
		<div id="header">
			<div class="shell">
				<!-- Logo + Top Nav -->
				<div id="top">
					<h1><a href="dashboard.php">-- EVOLVES --</a></h1>
					<div id="top-navigation"> Welcome <a href="profile"><strong><?php echo ucfirst($user_row['profile_name']); ?></strong></a> <span>|</span> <a href="#">Help</a> <span>|</span> <a href="profile.php">Profile Settings</a> <span>|</span> <a href="logout.php">Log out</a> </div>
				</div>
				<!-- End Logo + Top Nav -->
				<!-- Main Nav -->
				<div id="navigation">
					<ul>
						<li><a href="#dashboard" class="active"><span>Dashboard</span></a></li>
						<li><a href="#upload"><span>Upload</span></a></li>
						<li><a href="profile.php"><span>Profile Settings</span></a></li>
					</ul>
				</div>
				<!-- End Main Nav -->
			</div>
		</div>
		<!-- End Header -->
		<!-- Container -->
		<div id="container">
			<div class="shell" id="dashboard">
				<!-- Small Nav -->
				<div class="small-nav"> <i class="material-icons" style="position: relative; top:6px;" onclick="window.location = '/dashboard'">home</i> <span>&gt;</span> <input size="115" type = "text" placeholder="/" name = "directory" value="<?php echo $folder;?>" disabled class="directory"> </div>
				<!-- End Small Nav -->
				<!-- Message OK -->
				<div class="msg msg-ok" id="ok">
					<p><strong>Request completed successfully !</strong></p>
					<a class="close" onclick="close()">close</a> 
				</div>
				<!-- End Message OK -->
				<!-- Message Error -->
				<div class="msg msg-error" id="error">
					<p><strong>Error in completing your Request !</strong></p>
					<a class="close" onclick="close()">close</a> 
				</div>
				<!-- End Message Error -->
				<br />
				<!-- Main -->
				<div id="main">
					<div class="cl">&nbsp;</div>
					<!-- Content -->
					<div id="content">
						<!-- Box -->
						<div class="box">
							<!-- Box Head -->
							<div class="box-head">
								<h2 class="left">Current Objects</h2>
								<!-- <div class="right"> -->
									<!-- <label>search objects</label> -->
									<!-- <input type="text" class="field small-field" /> -->
									<!-- <input type="submit" class="button" value="search" /> -->
								<!-- </div> -->
							</div>
							<!-- End Box Head -->
							<!-- Table -->
							<div class="table">
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<th width="13"><input type="checkbox" class="checkbox" /></th>
										<th>Title</th>
										<th>Date</th>
										<th>Added by</th>
										<th width="110" class="ac">Content Control</th>
									</tr>
									<?php
										try {
											$results = $s3->getPaginator('ListObjects', [
												'Bucket' => $bucketName
											]);
											foreach ($results as $result) {
												if(sizeof($result['Contents']) == 0)
													throw new Exception("Bucket Empty!");
												foreach ($result['Contents'] as $object) {
													if ($folder == '' || strpos($object['Key'], $folder) === 0) {
														if($object['Key'] != $folder){
															$ltrimmed = ltrim($object['Key'],$folder);
															if(strlen(substr($ltrimmed,strpos($ltrimmed,'/')+1))>0 && strpos($ltrimmed,'/'))
																continue;
															if(substr($object['Key'], -1) == '/'){
																echo '<tr><td><input type="checkbox" disabled class="checkbox" /></td>
																	<td><h3><a href="/dashboard.php?f=' . $object['Key'] . '">';
																if (substr($object['Key'], 0, strlen($folder)) == $folder) {
																	$str = substr($object['Key'], strlen($folder));
																}
																echo $str . '</a></h3></td>
																	<td>xx.xx.xx</td>
																	<td>Administrator</td>
																	<td><a href="#" class="ico del">Delete</a></td>
																</tr>	
																';
															}
															else{
																echo '<tr><td><input type="checkbox" class="checkbox" /></td>
																	<td><h3><a href="#">';
																if (substr($object['Key'], 0, strlen($folder)) == $folder) {
																	$str = substr($object['Key'], strlen($folder));
																}
																echo $str .'</a></h3></td>
																	<td>xx.xx.xx</td>
																	<td>Administrator</td>
																	<td><a onclick="del(\'' . $object["Key"] . '\')" class="ico del">Delete</a><a onclick="dload(\'' . $object["Key"] . '\')" class="ico edit">Download</a></td>
																</tr>
																';
															}
														}else{
															echo '<tr><td><input type="checkbox" disabled class="checkbox" /></td>
																<td><h3><a href="/dashboard.php?f=' . substr($folder,0,strrpos(rtrim($folder,'/'),'/')) . '/"> <-- Back </a></h3></td>
																<td>xx.xx.xx</td>
																<td>Administrator</td>
																<td></td>
															</tr>	
															';
														}
													}
												}
											}
										} catch (S3Exception $e) {
											echo $e->getMessage() . PHP_EOL;
										} catch (Exception $e) {
											echo $e->getMessage() . PHP_EOL;
										}
										?>
								</table>
								<!-- Pagging -->
								<div class="pagging">
									<!-- <div class="left">Showing 1-12 of 44</div> -->
									<!-- <div class="right"> <a href="#">Previous</a> <a href="#">1</a> <a href="#">2</a> <a href="#">3</a> <a href="#">4</a> <a href="#">245</a> <span>...</span> <a href="#">Next</a> <a href="#">View all</a> </div> -->
								</div>
								<!-- End Pagging -->
							</div>
							<!-- Table -->
						</div>
						<!-- End Box -->
						<!-- Box -->
						<div class="box" id="upload">
							<!-- Box Head -->
							<div class="box-head">
								<h2>Upload File</h2>
							</div>
							<!-- End Box Head -->
							<!-- Form -->
							<div class="form">
								<p>
									<label>Current Directory <span>(Location where file will get uploaded)</span></label>
									<input size="115" type = "text" placeholder="/" name = "directory" value="<?php echo '# /' . $folder;?>" disabled class="directory size1">
								</p>
								</br>
								</br>
								</br>
								<form action="upload.php" method="post" enctype="multipart/form-data">
									<p> <span class="req">max 10 MB</span>
										<label>Select File to upload <span></span></label>
										<input type = "hidden" value="<?php echo $folder; ?>" name = "directory" class="directory">
									<div class="buttons">
										<input required type="file" name="fileToUpload" class="field">
										<input type="submit" class="button" value="Upload File">
									</div>
									</p>
								</form>
								</br>
								<center>
									<p><label>- - OR - -</label></p>
								</center>
								</br>
								<form action="upload-link.php" method="post">
									<p> <span class="req">max 10 MB</span>
										<label>Paste File file URL to upload: <span></span></label>
										<input type = "hidden" value="<?php echo $folder; ?>" name = "directory" class="directory">
									<div class="buttons">
										<input required placeholder="http://paste-url-here" type="text" name="url" class="field" size="60">
										<input type="submit" value="Upload File" class="button">
									</div>
									</p>
								</form>
							</div>
							<!-- End Form -->
						</div>
						<!-- End Box -->
					</div>
					<!-- End Content -->
					<!-- Sidebar -->
					<div id="sidebar">
						<!-- Box -->
						<div class="box">
							<!-- Box Head -->
							<div class="box-head">
								<h2>Directory</h2>
							</div>
							<!-- End Box Head-->
							<div class="box-content">
								<form method = "post">
									<input autocomplete="off" required type = "text" placeholder="Create New Folder" name = "newFolder">
									<input type="submit" class="add-button" value="Create Folder " name="new_folder_click">
									<input type = "hidden" value="<?php echo $folder ?>" name = "directory" class="directory">
								</form>
								<div class="cl">&nbsp;</div>
								<div class="sort">
									<pre><a href="/dashboard.php"><i class="material-icons" style="position: relative; top:6px;">home</i> Root</a></pre>
									<br>
									<?php
										try {
											$results = $s3->getPaginator('ListObjects', [
												'Bucket' => $bucketName
											]);
											$dir_arr = array();
											foreach ($results as $result) {
												if(sizeof($result['Contents']) == 0)
													throw new Exception("Bucket Empty!");
												foreach ($result['Contents'] as $object) 
													if(substr($object['Key'], -1) == '/')
														array_push($dir_arr,$object['Key']);
											}
											$dir_arr_prev = array($dir_arr[0]);
											echo '<pre><li><a href="/dashboard.php?f=' . $dir_arr[0] . '">' . $dir_arr[0] . '</a></li></pre>';
											$j = 0;
											for($i=1;$i<sizeof($dir_arr);$i++){
												if(strpos($dir_arr[$i],$dir_arr_prev[$j]) !== false){
													$dir_arr_prev[++$j] = $dir_arr[$i];
													echo '<pre><li>';
													for($tab=0;$tab<$j;$tab++){
														if($tab == $j-1)
															echo '|...';
														else
															echo '|   ';
													}
													echo '<a href="/dashboard.php?f=' . $dir_arr[$i] . '">' . str_replace($dir_arr_prev[$j-1],'',$dir_arr[$i]) . '</a></li></pre>';
												}else{
													if($j != 0){
														$j--;
														$i--;
													}else{
														$dir_arr_prev[$j] = $dir_arr[$i];
														echo '<pre><li>';
														for($tab=0;$tab<$j;$tab++)
															echo '    ';
														echo '<a href="/dashboard.php?f=' . $dir_arr[$i] . '">' . $dir_arr[$i] . '</a></li></pre>';
													}
												}
											}
										} catch (S3Exception $e) {
											echo $e->getMessage() . PHP_EOL;
										} catch (Exception $e) {
											echo $e->getMessage() . PHP_EOL;
										}
									?>
								</div>
							</div>
						</div>
						<!-- End Box -->
					</div>
					<!-- End Sidebar -->
					<div class="cl">&nbsp;</div>
				</div>
				<!-- Main -->
			</div>
		</div>
		<!-- End Container -->
		<!-- Footer -->
		<div id="footer">
			<div class="shell"> <span class="left">&copy; 2020 - EVOLVES</span> <span class="right"> Template by <a href="http://chocotemplates.com">Chocotemplates.com</a> </span> </div>
		</div>
		<!-- End Footer -->
		<script>
			function del(key){
				if(confirm("Are you sure to delete: " + key)){
					var form = document.createElement("form");
					document.body.appendChild(form);
					form.method = "POST";
					form.action = "delete.php";
					var element1 = document.createElement("INPUT");         
					element1.name="keyName";
					element1.value = key;
					element1.type = 'hidden';
					form.appendChild(element1);
					var element2 = document.createElement("INPUT");         
					element2.name="directory";
					element2.value = <?php echo '"' . $folder . '"' ?>;
					element2.type = 'hidden';
					form.appendChild(element2);
					form.submit();
				}
			}

			function dload(key){
				var form = document.createElement("form");
				document.body.appendChild(form);
				form.method = "POST";
				form.action = "download.php";
				form.target = "_blank";
				var element1 = document.createElement("INPUT");         
				element1.name="keyName";
				element1.value = key;
				element1.type = 'hidden';
				form.appendChild(element1);
				form.submit();
			}
		</script>
	</body>
</html>