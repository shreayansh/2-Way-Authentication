<?php
	include("connection.php");
	if(empty($_SESSION['user_id']) || empty($_SESSION['googleVerifyCode'])){
		header("Location: " . $APP_URL . 'dashboard.php');
		die();
	}

	require 'vendor/autoload.php';
	
	use Aws\S3\S3Client;
	use Aws\S3\Exception\S3Exception;

	include('aws-config.php');
	
	if(!isset($_POST['directory'])){
		header("Location: " . $APP_URL . 'dashboard.php');
		die();
	}
	$folder = $_POST['directory'];
		
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
		header('Location: ' . $RedirectURL . '?result=failed&f=' . $folder);
		die("Error: " . $e->getMessage());
	}

	$fileURL = $_POST['url']; // Change this

	$keyName = $folder . basename($fileURL);
	$pathInS3 = 'https://s3.' . $REGION . '.amazonaws.com/' . $bucketName . '/' . $keyName;

	// Add it to S3
	try {
		if (!file_exists('/tmp')) {
			mkdir('/tmp');
		}
		$tempFilePath = '/tmp/' . basename($fileURL);
		$tempFile = fopen($tempFilePath, "w") or die("Error: Unable to open file.");
		$fileContents = file_get_contents($fileURL);
		$tempFile = file_put_contents($tempFilePath, $fileContents);

		$s3->putObject(
			array(
				'Bucket'=>$bucketName,
				'Key' =>  $keyName,
				'SourceFile' => $tempFilePath,
				'StorageClass' => 'REDUCED_REDUNDANCY'
			)
		);
		
		// Deleting temporary file from server
		// Delete Here

	} catch (S3Exception $e) {
		header('Location: ' . $RedirectURL . '?result=failed&f=' . $folder);
		die('Error:' . $e->getMessage());
	} catch (Exception $e) {
		header('Location: ' . $RedirectURL . '?result=failed&f=' . $folder);
		die('Error:' . $e->getMessage());
	}
	header('Location: ' . $RedirectURL . '?result=success&f=' . $folder);

?>