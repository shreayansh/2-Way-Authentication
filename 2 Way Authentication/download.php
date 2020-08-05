<?php
	include("connection.php");
	if(empty($_SESSION['user_id']) || empty($_SESSION['googleVerifyCode'])){
		header("Location: " . $APP_URL . 'dashboard');
		die();
	}

	require 'vendor/autoload.php';
	
	use Aws\S3\S3Client;
	use Aws\S3\Exception\S3Exception;

	include('aws-config.php');
	
	if(!isset($_POST['keyName'])){
		header("Location: " . $APP_URL . 'dashboard');
		die();
	}
	$keyName = $_POST['keyName'];
	
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

	// Add it to S3
	try {
		$command = $s3->getCommand('GetObject', array(
			'Bucket' => $bucketName,
			'Key' => $keyName,
			'ResponseContentType' => 'application/octet-stream',
			'ResponseContentDisposition' => 'attachment'
		));

		// Create a signed URL from the command object that will last for
		// 2 minutes from the current time
		$response = $s3->createPresignedRequest($command, '+10 minutes');
		$presignedUrl = (string)$response->getUri();
		header('Location: ' . $presignedUrl);
		
	} catch (S3Exception $e) {
		die('Error:' . $e->getMessage());
	} catch (Exception $e) {
		die('Error:' . $e->getMessage());
	}
?> 