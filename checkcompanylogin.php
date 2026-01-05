<?php
session_start();
require_once("db.php");

if(isset($_POST)) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT id_company, companyname, email, active, password FROM company WHERE email='$email'";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {

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
                    
                    // --- SUCCESS ---
                    session_regenerate_id(true);

                    $otp = rand(100000, 999999);
                    
                    $_SESSION['temp_id_company'] = $row['id_company'];
                    $_SESSION['temp_name'] = $row['companyname'];
                    $_SESSION['temp_role'] = 'company'; 
                    
                    // --- SECURITY FIX: HASH THE OTP ---
                    // This matches the Candidate login method
                    $_SESSION['otp'] = password_hash($otp, PASSWORD_DEFAULT); 

                    // --- SEND EMAIL (For XAMPP Testing) ---
                    // Since we hash the OTP, you can't see it in $_SESSION['otp'] anymore.
                    // You MUST check your email or mail output.
                    
                    $to = $row['email'];
                    $subject = "Job Portal Login OTP";
                    $msg = "Your OTP is: " . $otp;
                    
                    // Send simple email for testing
                    mail($to, $subject, $msg);

                    header("Location: verify.php");
                    exit();

                } else if($row['active'] == '3') {
                    $_SESSION['companyLoginError'] = "Your Account Is Deactivated. Contact Admin For Reactivation.";
                    header("Location: login-company.php");
                    exit();
                }

            } else {
                 $_SESSION['loginError'] = "Invalid Email or Password!";
                 header("Location: login-company.php");
                 exit();
            }
        }
    } else {
        $_SESSION['loginError'] = "Invalid Email or Password!";
        header("Location: login-company.php");
        exit();
    }

    $conn->close();

} else {
    header("Location: login-company.php");
    exit();
}
?>