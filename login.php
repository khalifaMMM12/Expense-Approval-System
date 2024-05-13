<?php
session_start();

// Check if the user is already logged in
if(isset($_SESSION["user_id"])) {
    // Redirect to dashboard or home page
    header("Location: dashboard.php");
    exit;
}

// Check if form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    // Include database connection
    require_once "db_connection.php";

    // Retrieve username and password from form
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Prepare SQL statement to fetch user from database
    $sql = "SELECT * FROM Users WHERE username = ?";

    if($stmt = $mysqli->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("s", $param_username);
        $param_username = $username;

        // Execute statement
        if($stmt->execute()) {
            // Store result
            $stmt->store_result();

            // Check if username exists
            if($stmt->num_rows == 1) {
                // Bind result variables
                $stmt->bind_result($user_id, $username, $hashed_password);
                if($stmt->fetch()) {
                    // Verify password
                    if(password_verify($password, $hashed_password)) {
                        // Password is correct, start session
                        session_start();

                        // Store data in session variables
                        $_SESSION["user_id"] = $user_id;
                        $_SESSION["username"] = $username;

                        // Redirect to dashboard or home page
                        header("Location: dashboard.php");
                    } else {
                        // Password is incorrect
                        $login_err = "Invalid username or password.";
                    }
                }
            } else {
                // Username does not exist
                $login_err = "Invalid username or password.";
            }
        } else {
            // Error executing statement
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }

    // Close connection
    $mysqli->close();
}
?>
