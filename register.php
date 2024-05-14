<?php
// Include database connection
require_once "db_connection.php";

// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

// Process form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    // Validate username
    if(empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // Prepare SQL statement to check if username already exists
        $sql = "SELECT user_id FROM Users WHERE username = ?";

        if($stmt = $mysqli->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);

            // Execute statement
            if($stmt->execute()) {
                // Store result
                $stmt->store_result();

                if($stmt->num_rows == 1) {
                    // Username already exists
                    $username_err = "This username is already taken.";
                } else {
                    // Username is available
                    $username = trim($_POST["username"]);
                }
            } else {
                // Error executing statement
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Validate password
    if(empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif(strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting into database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
        // Prepare SQL statement to insert user into database
        $sql = "INSERT INTO Users (username, password) VALUES (?, ?)";

        if($stmt = $mysqli->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("ss", $param_username, $param_password);
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Hash password

            // Execute statement
            if($stmt->execute()) {
                // Redirect to login page
                header("location: login.php");
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
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Register</h2>
        <?php 
        if(!empty($username_err)){
            echo '<div class="alert alert-danger">' . $username_err . '</div>';
        }        
        if(!empty($password_err)){
            echo '<div class="alert alert-danger">' . $password_err . '</div>';
        }        
        if(!empty($confirm_password_err)){
            echo '<div class="alert alert-danger">' . $confirm_password_err . '</div>';
        }        
        ?>
        <form action="register.php" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" required>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>" required>
            </div>
            <div class="form-group">
                <input type="submit" name="register" class="btn btn-primary" value="Register">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>    
</body>
</html>
