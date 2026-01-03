<?php
// Change 'admin123' to whatever password you want your admin to have
$password = "admin123"; 
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Copy this hash into your database: <br><br><b>" . $hash . "</b>";
?>