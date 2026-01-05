<?php
session_start();
require_once("db.php");

if(isset($_POST)) {

    // 1. Check for duplicate email
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 0) {
        
        $stmt->close(); // Close check statement

        // 2. Handle File Upload
        $uploadOk = true;
        $folder_dir = "uploads/resume/";

        $base = basename($_FILES['resume']['name']); 
        $resumeFileType = strtolower(pathinfo($base, PATHINFO_EXTENSION)); 
        $file = uniqid() . "." . $resumeFileType;   
        $filename = $folder_dir . $file;  

        if(file_exists($_FILES['resume']['tmp_name'])) { 

            if($resumeFileType == "pdf")  {

                // Fixed: 5MB is 5,242,880 bytes
                if($_FILES['resume']['size'] < 5242880) { 
                    move_uploaded_file($_FILES["resume"]["tmp_name"], $filename);
                } else {
                    $_SESSION['uploadError'] = "Wrong Size. Max Size Allowed : 5MB";
                    $uploadOk = false;
                }
            } else {
                $_SESSION['uploadError'] = "Wrong Format. Only PDF Allowed";
                $uploadOk = false;
            }
        } else {
            $_SESSION['uploadError'] = "Something Went Wrong. File Not Uploaded.";
            $uploadOk = false;
        }

        if($uploadOk == false) {
            header("Location: register-candidates.php");
            exit();
        }

        // 3. Prepare Data for Insertion
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $hash = md5(uniqid()); // Email verification token

// 4. Insert User (Prepared Statement)
        // MODIFICATION: Added 'active' to the columns and '1' to the values
        $sql = "INSERT INTO users(firstname, lastname, email, password, address, city, state, contactno, qualification, stream, passingyear, dob, age, designation, resume, hash, aboutme, skills, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1')";
        
        $stmt = $conn->prepare($sql);

        // Bind parameters (No changes needed here)
        $stmt->bind_param("ssssssssssssssssss", 
            $_POST['fname'], 
            $_POST['lname'], 
            $_POST['email'], 
            $password, 
            $_POST['address'], 
            $_POST['city'], 
            $_POST['state'], 
            $_POST['contactno'], 
            $_POST['qualification'], 
            $_POST['stream'], 
            $_POST['passingyear'], 
            $_POST['dob'], 
            $_POST['age'], 
            $_POST['designation'], 
            $file, 
            $hash, 
            $_POST['aboutme'], 
            $_POST['skills']
        );

        if($stmt->execute()) {
            $_SESSION['registerCompleted'] = true;
            header("Location: login-candidates.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }

        $stmt->close();

    } else {
        // Email already exists
        $_SESSION['registerError'] = true;
        header("Location: register-candidates.php");
        exit();
    }

    $conn->close();

} else {
    header("Location: register-candidates.php");
    exit();
}