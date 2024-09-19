<?php
// Database connection
$servername = "localhost";
$username = "MONEYGUARD";
$password = "Prasad123";
$dbname = "moneyguard";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email exists and is verified
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ? AND verified = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            session_start();
            $_SESSION['user_id'] = $user_id;
            header("Location: profile.php");
            exit();
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "Email not found or not verified.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <style>
        body {
            font-family: "Open Sans", Helvetica, Arial, sans-serif;
            line-height: 1.5;
            background-color: #111317;
            color: #FFF;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border: 2px solid #c1cdcd;
            border-radius: 8px;
            background-color: #1b1f23;
        }
        form {
            width: 100%;
        }
        label {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
            color: #c1cdcd;
        }
        input {
            width: calc(100% - 20px);
            padding: 10px;
            border: 2px solid #c1cdcd;
            background-color: #FFF;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: rgb(208, 147, 62);
            border: none;
            color: #000;
            font-weight: bold;
            border-radius: 4px;
        }
        .error-message {
            color: red;
            margin-bottom: 20px;
        }
        .register-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: rgb(208, 147, 62);
            text-decoration: none;
        }

        button.back-button {
            background-color: #444;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Login</button>
            <button type="button" class="back-button" onclick="window.location.href='dashboard.html';">Back to Dashboard</button>
        </form>
        <a href="register.php" class="register-link">Don't have an account? Register here</a>
        <a href="forgot_password.php" class="register-link">Forgot Password?</a>
    </div>
</body>
</html>
