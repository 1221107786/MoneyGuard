<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection settings
$servername = "localhost";
$username = "MONEYGUARD";
$password = "Prasad123";
$dbname = "moneyguard";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_password'])) {
    $input_password = $_POST['verify_password'];
    $user_id = $_SESSION['user_id'];

    // Fetch the stored password hash
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($stored_password_hash);
    $stmt->fetch();
    $stmt->close();

    // Verify the password
    if (password_verify($input_password, $stored_password_hash)) {
        $_SESSION['password_verified'] = true;
        header("Location: user_management.php"); // Redirect to user management page
        exit();
    } else {
        $error_message = "Incorrect password. Please try again.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verify Password</title>
    <style>
        body {
            font-family: "Open Sans", Helvetica, Arial, sans-serif;
            background-color: #111317;
            color: #FFF;
            line-height: 1.5;
            text-align: center;
            padding: 50px;
        }

        .container {
            max-width: 400px;
            width: 90%;
            margin: auto;
            padding: 20px;
            border: 2px solid #c1cdcd;
            border-radius: 8px;
            background-color: #222;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }

        input {
            width: calc(100% - 22px);
            border: 2px solid #c1cdcd;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 4px;
            background: #333;
            color: #FFF;
        }

        button {
            cursor: pointer;
            width: 100%;
            border: none;
            background: rgb(208, 147, 62);
            color: #000;
            padding: 10px;
            font-size: 15px;
            border-radius: 4px;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Verify Password</h2>

        <!-- Error Messages -->
        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Password Verification Form -->
        <form action="verify_password.php" method="post">
            <label for="verify_password">Current Password:</label>
            <input type="password" id="verify_password" name="verify_password" required>
            <button type="submit">Verify Password</button>
        </form>
    </div>
</body>

</html>
