<?php
session_start();

// Check if the user is logged in; if not, redirect to login page
if(!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "db_connection.php";

// Define variables and initialize with empty values
$description = $amount = "";
$description_err = $amount_err = "";

// Process form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_expense"])) {
    // Validate description
    if(empty(trim($_POST["description"]))) {
        $description_err = "Please enter a description.";
    } else {
        $description = trim($_POST["description"]);
    }

    // Validate amount
    if(empty(trim($_POST["amount"]))) {
        $amount_err = "Please enter an amount.";
    } elseif(!is_numeric(trim($_POST["amount"]))) {
        $amount_err = "Please enter a valid number.";
    } else {
        $amount = trim($_POST["amount"]);
    }

    // Check input errors before inserting into database
    if(empty($description_err) && empty($amount_err)) {
        // Prepare SQL statement to insert expense into database
        $sql = "INSERT INTO Expenses (user_id, description, amount, status) VALUES (?, ?, ?, 'Pending')";

        if($stmt = $mysqli->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("isd", $param_user_id, $param_description, $param_amount);
            $param_user_id = $_SESSION["user_id"];
            $param_description = $description;
            $param_amount = $amount;

            // Execute statement
            if($stmt->execute()) {
                // Redirect to a confirmation page or the dashboard
                header("Location: dashboard.php");
            } else {
                // Error executing statement
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Expense</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Submit Expense</h2>
        <?php 
        if(!empty($description_err)){
            echo '<div class="alert alert-danger">' . $description_err . '</div>';
        }        
        if(!empty($amount_err)){
            echo '<div class="alert alert-danger">' . $amount_err . '</div>';
        }        
        ?>
        <form action="submit_expense.php" method="post">
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" class="form-control" value="<?php echo $description; ?>" required>
            </div>    
            <div class="form-group">
                <label>Amount</label>
                <input type="text" name="amount" class="form-control" value="<?php echo $amount; ?>" required>
            </div>
            <div class="form-group">
                <input type="submit" name="submit_expense" class="btn btn-primary" value="Submit">
            </div>
        </form>
    </div>    
</body>
</html>
