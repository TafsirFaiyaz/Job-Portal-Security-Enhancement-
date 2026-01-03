<?php

//To Handle Session Variables on This Page
session_start();

//Including Database Connection From db.php file to avoid rewriting in all files
require_once("db.php");

//If user Actually clicked login button 
if(isset($_POST)) {

	//Escape Special Characters in String
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	$password = mysqli_real_escape_string($conn, $_POST['password']);

	// --- CHANGE: Removed old encryption ---
	// $password = base64_encode(strrev(md5($password)));

	// --- CHANGE: Fetch password hash from DB ---
	$sql = "SELECT id_company, companyname, email, active, password FROM company WHERE email='$email'";
	$result = $conn->query($sql);

	//if company table has this this login details
	if($result->num_rows > 0) {
		//output data
		while($row = $result->fetch_assoc()) {

            // --- CHANGE: Verify Password ---
            if(password_verify($password, $row['password'])) {

                if($row['active'] == '2') {
                    $_SESSION['companyLoginError'] = "Your Account Is Still Pending Approval.";
                    header("Location: login-company.php");
                    exit();
                } else if($row['active'] == '0') {
                    $_SESSION['companyLoginError'] = "Your Account Is Rejected. Please Contact For More Info.";
                    header("Location: login-company.php");
                    exit();
                } else if($row['active'] == '1') {
                    
                    // --- CHANGE: Secure Session ---
                    session_regenerate_id(true);

                    // --- CHANGE: 2FA Logic ---
                    $otp = rand(100000, 999999);
                    
                    $_SESSION['temp_id_company'] = $row['id_company'];
                    $_SESSION['temp_name'] = $row['companyname'];
                    $_SESSION['temp_role'] = 'company'; // RBAC Role
                    $_SESSION['otp'] = $otp;

                    // Send Email Here...
                    // mail($email, "Job Portal OTP", "Your Code: $otp");

                    header("Location: verify.php");
                    exit();

                } else if($row['active'] == '3') {
                    $_SESSION['companyLoginError'] = "Your Account Is Deactivated. Contact Admin For Reactivation.";
                    header("Location: login-company.php");
                    exit();
                }

            } else {
                 // Password incorrect
                 $_SESSION['loginError'] = "Invalid Email or Password!";
                 header("Location: login-company.php");
                 exit();
            }
		}
 	} else {
 		//if no matching record found
 		$_SESSION['loginError'] = "Invalid Email or Password!";
 		header("Location: login-company.php");
		exit();
 	}

 	//Close database connection. Not compulsory but good practice.
 	$conn->close();

} else {
	//redirect them back to login page if they didn't click login button
	header("Location: login-company.php");
	exit();
}