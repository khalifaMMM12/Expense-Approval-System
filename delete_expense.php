<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "db_connection.php";

// Check if expense_id is set in POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['expense_id'])) {
    $expense_id = $_POST['expense_id'];

    // Prepare delete statement
    $sql = "DELETE FROM Expenses WHERE expense_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $param_expense_id);
        $param_expense_id = $expense_id;

        // Attempt to execute the statement
        if ($stmt->execute()) {
            // Expense deleted successfully
            header("Location: index.php");
            exit;
        } else {
            echo "Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }
}

// Close connection
$mysqli->close();
?>
