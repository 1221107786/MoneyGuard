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
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a numeric verification code
        $verification_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); // 6-digit code
        $hashed_verification_code = hash('sha256', $verification_code);
        $expires = date("U") + 3600; // Code expires in 1 hour

        // Store the code and expiration in the database
        $stmt = $conn->prepare("INSERT INTO password_resets (email, verification_code, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE verification_code = VALUES(verification_code), expires = VALUES(expires)");
        $stmt->bind_param("sss", $email, $hashed_verification_code, $expires);
        $stmt->execute();

        // Send reset email
        require 'C:\xampp\htdocs\PHPMailer-master\PHPMailer-master\src\Exception.php';
        require 'C:\xampp\htdocs\PHPMailer-master\PHPMailer-master\src\PHPMailer.php';
        require 'C:\xampp\htdocs\PHPMailer-master\PHPMailer-master\src\SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer();
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pv170030@gmail.com';
            $mail->Password = 'jiim uhis pkxs lwib';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('pv170030@gmail.com', 'MoneyGuard');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Please use the following code to reset your password:<br><br>
                <b>$verification_code</b><br><br>
                This code will expire in 1 hour.";

            $mail->send();
            $success_message = "A verification code has been sent to your email.";
            
            // Redirect to reset password page with email
            header("Location: reset_password.php?email=" . urlencode($email));
            exit();
        } catch (Exception $e) {
            $error_message = "Email could not be sent. Please try again.";
        }
    } else {
        $error_message = "No account found with that email.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Forgot Password</title>
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
            color: #000;
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
        .success-message {
            color: green;
            margin-bottom: 20px;
        }

        button.back-button {
            background-color: #444;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid_code') : ?>
            <div class="error-message">Invalid or expired verification code. Please try again.</div>
        <?php endif; ?>
        <?php if (isset($_GET['reset']) && $_GET['reset'] == 'success') : ?>
            <div class="success-message">Password reset successfully. You can now log in.</div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Verification Code</button>
            <button type="button" class="back-button" onclick="window.location.href='dashboard.html';">Back to Dashboard</button>
        </form>
    </div>
</body>
</html>
