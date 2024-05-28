<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "db_connection.php";

// Fetch all expenses including pending, approved, and rejected
$sql = "SELECT E.expense_id, E.description, E.amount, E.status, U.username 
        FROM Expenses E 
        JOIN Users U ON E.user_id = U.user_id 
        ORDER BY E.expense_id DESC"; // Order by expense_id to maintain correct sequence
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$expenses = $stmt->get_result();

// Close statement and connection
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Approval System</title>
    
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="style/dashboard.css" rel="stylesheet">
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
                <li class="nav-item">
                    <a class="nav-link" href="submit_expense.php">Submit Expense</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container mt-4">
        <h2>Welcome to the Expense Approval System</h2>
        
        <!-- All Expenses Table -->
        <div class="mt-4">
            <h2>All Expenses</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Expense ID</th>
                        <th>Amount (₦)</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>User</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1; // Initialize counter for IDs
                    while ($row = $expenses->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td> <!-- Use the counter instead of expense_id -->
                        <td>₦<?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td class="<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <form action="delete_expense.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense?');" style="display:inline;">
                                    <input type="hidden" name="expense_id" value="<?php echo $row['expense_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
