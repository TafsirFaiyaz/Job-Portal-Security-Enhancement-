<?php
session_start();
require_once("../db.php");

if(isset($_POST)) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // --- CHANGE 1: Select by username only (don't check password in SQL) ---
    $sql = "SELECT * FROM admin WHERE username='$username'";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            
            // --- CHANGE 2: Verify the BCRYPT hash ---
            if(password_verify($password, $row['password'])) {

                // --- CHANGE 3: Secure Session & 2FA Setup ---
                session_regenerate_id(true);

                // Generate OTP
                $otp = rand(100000, 999999);
                
                // Store Temporary Session Vars
                $_SESSION['temp_id_admin'] = $row['id_admin'];
                $_SESSION['temp_role'] = 'admin'; // RBAC Role
                $_SESSION['otp'] = $otp;
                
                // --- CHANGE 4: Redirect to the verify page in the ROOT folder ---
                header("Location: ../verify.php"); 
                exit();

            } else {
                // Password Wrong
                $_SESSION['loginError'] = true;
                header("Location: index.php");
                exit();
            }
        }
    } else {
        // Username Wrong
        $_SESSION['loginError'] = true;
        header("Location: index.php");
        exit();
    }

    $conn->close();

} else {
    header("Location: index.php");
    exit();
}
?>