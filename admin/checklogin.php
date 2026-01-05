<?php
session_start();
require_once("../db.php");

if (isset($_POST)) {

    // Check Admin Credentials using Prepared Statement
    $stmt = $conn->prepare("SELECT id_admin, username, password, email FROM admin WHERE username = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify Password
        if (password_verify($_POST['password'], $row['password'])) {

            // Secure Session & 2FA Setup
            session_regenerate_id(true);

            $otp = rand(100000, 999999);
            
            // Store Temporary Session Vars
            $_SESSION['temp_id_admin'] = $row['id_admin'];
            $_SESSION['temp_role'] = 'admin'; 
            $_SESSION['otp'] = password_hash($otp, PASSWORD_DEFAULT); 
            $_SESSION['otp_expires'] = time() + 300; // 5 minutes
            
            // Determine Email (Fallback if email column is empty in older DBs)
            $to = !empty($row['email']) ? $row['email'] : 'admin@jobportal.com'; 
            $subject = "Job Portal - Admin Login Verification";
            
            // Admin-specific HTML Template (Red Theme)
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #d9534f; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
                    .otp-box { background: white; padding: 20px; text-align: center; border: 2px dashed #d9534f; margin: 20px 0; border-radius: 5px; }
                    .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #d9534f; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; border-top: 1px solid #ddd; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1 style='margin: 0;'>Admin Portal</h1>
                        <p style='margin: 5px 0 0 0;'>Security Verification</p>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>Admin</strong>,</p>
                        <p>A login attempt was detected. Please use the following OTP:</p>
                        <div class='otp-box'>
                            <div class='otp-code'>$otp</div>
                        </div>
                        <p><strong>Warning:</strong> Do not share this code.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " Job Portal.</p>
                    </div>
                </div>
            </body>
            </html>";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
            $headers .= "From: Job Portal Admin <noreply@jobportal.com>" . "\r\n"; 
            
            // Send Email
            if (mail($to, $subject, $message, $headers)) {
                header("Location: ../verify.php"); 
                exit();
            } else {
                // In local environments (XAMPP), mail might fail but still output to file.
                // We redirect anyway to allow entry of the OTP found in mailoutput.
                header("Location: ../verify.php"); 
                exit();
            }

        } else {
            $_SESSION['loginError'] = true;
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['loginError'] = true;
        header("Location: index.php");
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: index.php");
    exit();
}
?>