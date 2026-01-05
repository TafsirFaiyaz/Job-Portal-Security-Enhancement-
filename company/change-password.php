<?php
session_start();

// Verify company login
if (empty($_SESSION['id_company']) || $_SESSION['role'] != 'company') {
    header("Location: ../index.php");
    exit();
}

require_once("../db.php");

if (isset($_POST)) {

    // Hash new password
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Update password using Prepared Statement
    $stmt = $conn->prepare("UPDATE company SET password=? WHERE id_company=?");
    $stmt->bind_param("si", $password, $_SESSION['id_company']);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: settings.php");
    exit();
}
?>