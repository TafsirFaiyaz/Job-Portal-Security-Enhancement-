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

	// --- CHANGE: Removed the old MD5 encryption line ---
	// $password = base64_encode(strrev(md5($password))); 

	// --- CHANGE: Select password hash from DB using only Email ---
	$sql = "SELECT id_user, firstname, lastname, email, active, password FROM users WHERE email='$email'";
	$result = $conn->query($sql);

	//if user table has this this login details
	if($result->num_rows > 0) {
		//output data
		while($row = $result->fetch_assoc()) {

            // --- CHANGE: Verify the hashed password ---
            if(password_verify($password, $row['password'])) {

                // Password matches, now check if account is active
                if($row['active'] == '0') {
                    $_SESSION['loginActiveError'] = "Your Account Is Not Active. Check Your Email.";
                    header("Location: login-candidates.php");
                    exit();
                } else if($row['active'] == '1') { 

                    // --- CHANGE: Secure Session Fixation Prevention ---
                    session_regenerate_id(true);

                    // --- CHANGE: 2FA / Two-Step Verification Logic ---
                    // Instead of logging in directly, we generate a code and redirect to verify.php
                    
                    // 1. Generate 6-digit OTP
                    $otp = rand(100000, 999999);
                    
                    // 2. Store user info and OTP in TEMPORARY session variables
                    $_SESSION['temp_id_user'] = $row['id_user'];
                    $_SESSION['temp_name'] = $row['firstname'] . " " . $row['lastname'];
                    $_SESSION['temp_role'] = 'candidate'; // RBAC Role
                    $_SESSION['otp'] = $otp;
                    
                    // 3. Send OTP to email (Simulated for now, un-comment mail code to use)
                    // mail($email, "Your OTP", "Your One Time Password is: $otp");
                    
                    // NOTE: For testing, we will just redirect. You must check your DB or echo the OTP on verify.php to see it.
                    header("Location: verify.php"); 
                    exit();

                    /* NOTE: The old direct login code is removed because we now force 2FA.
                       The redirection to 'user/index.php' will happen inside verify.php
                    */

                } else if($row['active'] == '2') { 

                    $_SESSION['loginActiveError'] = "Your Account Is Deactivated. Contact Admin To Reactivate.";
                    header("Location: login-candidates.php");
                    exit();
                }

            } else {
                // Password incorrect
                $_SESSION['loginError'] = "Invalid Email or Password!";
                header("Location: login-candidates.php");
                exit();
            }
		}
 	} else {
 		//if no matching record found in user table
 		$_SESSION['loginError'] = "Invalid Email or Password!";
 		header("Location: login-candidates.php");
		exit();
 	}

 	//Close database connection. Not compulsory but good practice.
 	$conn->close();

} else {
	//redirect them back to login page if they didn't click login button
	header("Location: login-candidates.php");
	exit();
}