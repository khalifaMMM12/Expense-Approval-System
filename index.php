<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "db_connection.php";

// Fetch pending expenses for admin view
if ($_SESSION["role"] == 'admin') {
    $sql = "SELECT E.expense_id, E.description, E.amount, E.status, U.username 
            FROM Expenses E 
            JOIN Users U ON E.user_id = U.user_id 
            WHERE E.status = 'Pending' 
            ORDER BY E.created_at DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $pending_expenses = $stmt->get_result();
} else {
    // Fetch expenses only for the logged-in user
    $sql = "SELECT expense_id, description, amount, status 
            FROM Expenses 
            WHERE user_id = ? AND status = 'Pending' 
            ORDER BY created_at DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $pending_expenses = $stmt->get_result();
}


// Define variables and initialize with empty values
$description = $amount = "";
$description_err = $amount_err = "";

// Process form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["index"])) {
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
        $amount_err = "Please enter a valid amount.";
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

    // Close statement and connection
    $stmt->close();
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Approval System</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="style/index.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">Expense Approval System</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" href="index.php">Submit Expense</a>
                </li> -->
                
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container mt-6">
        <h2>Welcome to the Expense Approval System</h2>
        
        <!-- Submit Expense Form -->
        <div class="mt-4">
            <h2>Submit Expense</h2>
            <?php 
        if(!empty($description_err)){
            echo '<div class="alert alert-danger">' . $description_err . '</div>';
        }        
        if(!empty($amount_err)){
            echo '<div class="alert alert-danger">' . $amount_err . '</div>';
        }        
        ?>
        <form action="index.php" method="post">
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" class="form-control" value="<?php echo $description; ?>" required>
            </div>    
            <div class="form-group">
                <label>Amount</label>
                <input type="text" name="amount" class="form-control" value="<?php echo $amount; ?>" required>
            </div>
            <div class="form-group">
                <input type="submit" name="index" class="btn btn-primary" value="Submit">
            </div>
        </form>
        </div>

        <!-- Pending Expenses Table -->
        <div class="mt-4">
            <h2>Pending Expenses</h2>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Expense ID</th>
                        <th>Amount (₦)</th>
                        <th>Description</th>
                        <th>Status</th>
                        <?php if ($_SESSION["role"] == 'admin'): ?>
                        <th>User</th>
                        <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $pending_expenses->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['expense_id']; ?></td>
                        <td><?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <?php if ($_SESSION["role"] == 'admin'): ?>
                        <td><?php echo $row['username']; ?></td>
                        <td>
                            <a href="approve_expense.php?expense_id=<?php echo $row['expense_id']; ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="reject_expense.php?expense_id=<?php echo $row['expense_id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS (optional, for certain Bootstrap components that require JavaScript) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
