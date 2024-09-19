<?php
// Database connection settings
$servername = "localhost";
$username = "MONEYGUARD";
$password = "Prasad123";
$dbname = "moneyguard";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
$email = ""; // Initialize email variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $verification_code = $_POST['verification_code'];
    $hashed_verification_code = hash('sha256', $verification_code);

    // Verify the hashed code
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND verification_code = ? AND verified = 0");
    $stmt->bind_param("ss", $email, $hashed_verification_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update the user as verified
        $update_stmt = $conn->prepare("UPDATE users SET verified = 1 WHERE email = ?");
        $update_stmt->bind_param("s", $email);
        if ($update_stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Verification failed. Please try again.";
        }
        $update_stmt->close();
    } else {
        $error_message = "Invalid verification code .";
    }

    $stmt->close();
} else if (isset($_GET['email'])) {
    $email = $_GET['email']; // Get email from URL if available
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify Your Email</title>
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
        .forgot-password-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: rgb(208, 147, 62);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify Your Email</h2>
        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>

            <label for="verification_code">Verification Code:</label>
            <input type="text" id="verification_code" name="verification_code" placeholder="Enter the code" required>

            <button type="submit">Verify</button>
        </form>

    </div>
</body>
</html>
