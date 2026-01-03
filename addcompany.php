<?php
session_start();
require_once("db.php");

// Handle form submission
if (isset($_POST)) {

    // 1. Check if email exists (Using Prepared Statement)
    // We use ? as a placeholder instead of the variable directly
    $stmt = $conn->prepare("SELECT email FROM company WHERE email = ?");
    $stmt->bind_param("s", $_POST['email']); // "s" means string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Email is available, proceed with upload and registration
        $stmt->close(); // Close the previous check statement

        $uploadOk = true;
        $folder_dir = "uploads/logo/";

        $base = basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($base, PATHINFO_EXTENSION)); // Converted to lowercase for safety
        $file = uniqid() . "." . $imageFileType;
        $filename = $folder_dir . $file;

        // Validate upload
        if (file_exists($_FILES['image']['tmp_name'])) {

            if ($imageFileType == "jpg" || $imageFileType == "png") {

                if ($_FILES['image']['size'] < 5242880) { 
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

        // 2. Hash Password (Salting is automatic)
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // 3. Insert User (Using Prepared Statement)
        // ? placeholders replace the variables
        $sql = "INSERT INTO company (name, companyname, country, state, city, contactno, website, email, password, aboutme, logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters: "sssssssssss" means we are binding 11 strings
        $stmt->bind_param("sssssssssss", 
            $_POST['name'], 
            $_POST['companyname'], 
            $_POST['country'], 
            $_POST['state'], 
            $_POST['city'], 
            $_POST['contactno'], 
            $_POST['website'], 
            $_POST['email'], 
            $password, 
            $_POST['aboutme'], 
            $file
        );

        if ($stmt->execute()) {
            $_SESSION['registerCompleted'] = true;
            header("Location: login-company.php");
            exit();
        } else {
            // Note: In production, careful about showing specific DB errors to users
            echo "Error: " . $conn->error;
        }

        $stmt->close();

    } else {
        // Email already exists
        $_SESSION['registerError'] = true;
        header("Location: register-company.php");
        exit();
    }

    $conn->close();

} else {
    header("Location: register-company.php");
    exit();
}