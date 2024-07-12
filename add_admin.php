<?php
// Include your database connection script
require_once "db_connection.php";

// Define admin credentials
$admin_username = "admin";
$admin_password = "admin1234"; // Replace with the desired admin password

// Hash the admin password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Insert the admin user into the database
$sql = "INSERT INTO Users (username, password, role) VALUES (?, ?, 'admin')";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $admin_username, $hashed_password);
$stmt->execute();

// Check if the user was inserted successfully
if ($stmt->affected_rows > 0) {
    echo "Admin user added successfully.";
} else {
    echo "Error adding admin user: " . $stmt->error;
}

// Close statement and connection
$stmt->close();
$mysqli->close();
?>
