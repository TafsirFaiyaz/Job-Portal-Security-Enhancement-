<?php
session_start();
require_once("db.php");

if (isset($_POST)) {

    // Check if company exists using Prepared Statement
    $stmt = $conn->prepare("SELECT id_company, companyname, email, active, password FROM company WHERE email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify Password
        if (password_verify($_POST['password'], $row['password'])) {

            // Check Account Status
            if ($row['active'] == '2') {
                $_SESSION['companyLoginError'] = "Account pending approval.";
                header("Location: login-company.php");
                exit();

            } else if ($row['active'] == '0') {
                $_SESSION['companyLoginError'] = "Account rejected. Contact Admin.";
                header("Location: login-company.php");
                exit();

            } else if ($row['active'] == '1') {
                
                // Account Active - Generate OTP
                session_regenerate_id(true);
                $otp = rand(100000, 999999);
                
                // Store temporary session data
                $_SESSION['temp_id_company'] = $row['id_company'];
                $_SESSION['temp_name'] = $row['companyname'];
                $_SESSION['temp_role'] = 'company'; 
                $_SESSION['otp'] = password_hash($otp, PASSWORD_DEFAULT); 
                $_SESSION['otp_expires'] = time() + 300; // 5 minutes

                // Prepare Email
                $to = $row['email'];
                $subject = "Job Portal - Company Login Verification";
                
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
                            <p style='margin: 5px 0 0 0;'>Company Login</p>
                        </div>
                        <div class='content'>
                            <p>Hello <strong>" . htmlspecialchars($row['companyname']) . "</strong>,</p>
                            <p>You are attempting to log in. Please use the following OTP:</p>
                            <div class='otp-box'>
                                <div class='otp-code'>$otp</div>
                            </div>
                            <p><strong>Important:</strong> Valid for <span class='warning'>5 minutes</span> only.</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; " . date('Y') . " Job Portal.</p>
                        </div>
                    </div>
                </body>
                </html>";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
                $headers .= "From: Job Portal <noreply@jobportal.com>" . "\r\n"; 

                // Send Email
                if (mail($to, $subject, $message, $headers)) {
                    header("Location: verify.php");
                    exit();
                } else {
                    $_SESSION['loginError'] = "Could not send verification code.";
                    header("Location: login-company.php");
                    exit();
                }

            } else if ($row['active'] == '3') {
                $_SESSION['companyLoginError'] = "Account deactivated. Contact Admin.";
                header("Location: login-company.php");
                exit();
            }

        } else {
            $_SESSION['loginError'] = "Invalid Email or Password!";
            header("Location: login-company.php");
            exit();
        }
    } else {
        $_SESSION['loginError'] = "Invalid Email or Password!";
        header("Location: login-company.php");
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: login-company.php");
    exit();
}
?>