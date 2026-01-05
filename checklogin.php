<?php
session_start();
require_once("db.php");

if(isset($_POST)) {

    // Escape inputs for security
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if user exists
    $sql = "SELECT id_user, firstname, lastname, email, active, password FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {

            // 1. Verify Password
            if(password_verify($password, $row['password'])) {

                // 2. Check Account Status
                if($row['active'] == '0') {
                    $_SESSION['loginActiveError'] = "Your Account Is Not Active. Check Your Email.";
                    header("Location: login-candidates.php");
                    exit();
                    
                } else if($row['active'] == '1') { 

                    // 3. Account is Active - Generate OTP
                    session_regenerate_id(true);
                    
                    $otp = rand(100000, 999999);
                    
                    // Store temporary session data
                    $_SESSION['temp_id_user'] = $row['id_user'];
                    $_SESSION['temp_name'] = $row['firstname'] . " " . $row['lastname'];
                    $_SESSION['temp_role'] = 'candidate';
                    
                    // IMPORTANT: We are HASHING the OTP for security.
                    // In your verify.php, you MUST compare using: if(password_verify($user_input, $_SESSION['otp']))
                    $_SESSION['otp'] = password_hash($otp, PASSWORD_DEFAULT); 
                    $_SESSION['otp_expires'] = time() + 300; // 5 minutes expiry
                    
                    // ==========================================
                    // SEND OTP EMAIL
                    // ==========================================
                    
                    $to = $row['email'];
                    $subject = "Job Portal - Login Verification Code";
                    
                    // HTML Email Body
                    $message = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                            .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
                            .otp-box { background: white; padding: 20px; text-align: center; border: 2px dashed #4CAF50; margin: 20px 0; border-radius: 5px; }
                            .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #4CAF50; }
                            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; border-top: 1px solid #ddd; padding-top: 20px; }
                            .warning { color: #d32f2f; font-weight: bold; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1 style='margin: 0;'>Job Portal</h1>
                                <p style='margin: 5px 0 0 0;'>Login Verification</p>
                            </div>
                            <div class='content'>
                                <p>Hello <strong>" . htmlspecialchars($row['firstname']) . " " . htmlspecialchars($row['lastname']) . "</strong>,</p>
                                <p>You are attempting to log in to your Job Portal account. Please use the following One-Time Password (OTP) to complete your login:</p>
                                
                                <div class='otp-box'>
                                    <div class='otp-code'>$otp</div>
                                </div>
                                
                                <p><strong>Important:</strong></p>
                                <ul>
                                    <li>This OTP is valid for <span class='warning'>5 minutes only</span>.</li>
                                    <li>Do not share this code with anyone.</li>
                                </ul>
                            </div>
                            <div class='footer'>
                                <p>&copy; " . date('Y') . " Job Portal. All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>";
                    
                    // Email Headers
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
                    // Make sure this email matches your XAMPP sender config if possible, or use a generic one
                    $headers .= "From: Job Portal <noreply@jobportal.com>" . "\r\n"; 
                    
                    // Send Email
                    if(mail($to, $subject, $message, $headers)) {
                        header("Location: verify.php"); 
                        exit();
                    } else {
                        $_SESSION['loginError'] = "Could not send verification code. Please try again.";
                        header("Location: login-candidates.php");
                        exit();
                    }

                } else if($row['active'] == '2') { 
                    $_SESSION['loginActiveError'] = "Your Account Is Deactivated. Contact Admin To Reactivate.";
                    header("Location: login-candidates.php");
                    exit();
                }

            } else {
                // Password Wrong
                $_SESSION['loginError'] = "Invalid Email or Password!";
                header("Location: login-candidates.php");
                exit();
            }
        }
    } else {
        // Email Wrong
        $_SESSION['loginError'] = "Invalid Email or Password!";
        header("Location: login-candidates.php");
        exit();
    }

    $conn->close();

} else {
    header("Location: login-candidates.php");
    exit();
}
?>