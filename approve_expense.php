<?php
session_start();

// Check if the user is logged in and is an admin
if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != 'admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "db_connection.php";

// Check if the expense_id is provided
if(isset($_GET["expense_id"]) && !empty(trim($_GET["expense_id"]))) {
    // Prepare SQL statement to update the expense status
    $sql = "UPDATE Expenses SET status = 'Approved' WHERE expense_id = ?";

    if($stmt = $mysqli->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("i", $param_expense_id);
        $param_expense_id = trim($_GET["expense_id"]);

        // Execute statement
        if($stmt->execute()) {
            // Redirect to dashboard or confirmation page
            header("Location: dashboard.php");
        } else {
            // Error executing statement
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }
} else {
    // If no expense_id is provided, redirect to dashboard
    header("Location: dashboard.php");
}

// Close connection
$mysqli->close();
?>
