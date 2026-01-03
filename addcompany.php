<?php
session_start();
require_once("db.php");

// Handle form submission
if (isset($_POST)) {

	// Sanitize inputs
	$companyname = mysqli_real_escape_string($conn, $_POST['companyname']);
	$contactno   = mysqli_real_escape_string($conn, $_POST['contactno']);
	$website     = mysqli_real_escape_string($conn, $_POST['website']);
	$email       = mysqli_real_escape_string($conn, $_POST['email']);
	$password    = password_hash($_POST['password'], PASSWORD_BCRYPT);
	$country     = mysqli_real_escape_string($conn, $_POST['country']);
	$state       = mysqli_real_escape_string($conn, $_POST['state']);
	$city        = mysqli_real_escape_string($conn, $_POST['city']);
	$aboutme     = mysqli_real_escape_string($conn, $_POST['aboutme']);
	$name        = mysqli_real_escape_string($conn, $_POST['name']);

	// Check if email exists
	$sql = "SELECT email FROM company WHERE email='$email'";
	$result = $conn->query($sql);

	if ($result->num_rows == 0) {

		$uploadOk = true;
		$folder_dir = "uploads/logo/";

		$base = basename($_FILES['image']['name']);
		$imageFileType = pathinfo($base, PATHINFO_EXTENSION);
		$file = uniqid() . "." . $imageFileType;
		$filename = $folder_dir . $file;

		// Validate upload
		if (file_exists($_FILES['image']['tmp_name'])) {

			if ($imageFileType == "jpg" || $imageFileType == "png") {

				if ($_FILES['image']['size'] < 500000) {
					move_uploaded_file($_FILES["image"]["tmp_name"], $filename);
				} else {
					$_SESSION['uploadError'] = "File too large (max 5MB)";
					$uploadOk = false;
				}

			} else {
				$_SESSION['uploadError'] = "Only JPG and PNG allowed";
				$uploadOk = false;
			}

		} else {
			$_SESSION['uploadError'] = "Upload failed";
			$uploadOk = false;
		}

		if ($uploadOk == false) {
			header("Location: register-company.php");
			exit();
		}

		// Insert company data
		$sql = "INSERT INTO company 
			(name, companyname, country, state, city, contactno, website, email, password, aboutme, logo)
			VALUES 
			('$name', '$companyname', '$country', '$state', '$city', '$contactno', '$website', '$email', '$password', '$aboutme', '$file')";

		if ($conn->query($sql) === TRUE) {
			$_SESSION['registerCompleted'] = true;
			header("Location: login-company.php");
			exit();
		} else {
			echo "Error: " . $conn->error;
		}

	} else {
		$_SESSION['registerError'] = true;
		header("Location: register-company.php");
		exit();
	}

	$conn->close();

} else {
	header("Location: register-company.php");
	exit();
}
