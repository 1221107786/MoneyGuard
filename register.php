<?php
require 'C:\xampp\htdocs\PHPMailer-master\PHPMailer-master\src\Exception.php';
require 'C:\xampp\htdocs\PHPMailer-master\PHPMailer-master\src\PHPMailer.php';
require 'C:\xampp\htdocs\PHPMailer-master\PHPMailer-master\src\SMTP.php';

// Database connection settings
$servername = "localhost";
$username = "MONEYGUARD";
$password = "Prasad123";
$dbname = "moneyguard";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        $error_message = "Email already exists. Please use another email.";
    } else {
        // Generate and hash the verification code
        $verification_code = rand(100000, 999999);
        $hashed_verification_code = hash('sha256', $verification_code);

        // Insert user data into the database
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, phone, email, password, verification_code, verified) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("ssssss", $first_name, $last_name, $phone, $email, $password, $hashed_verification_code);

        if ($stmt->execute()) {
            // Set up PHPMailer
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'pv170030@gmail.com'; // Replace with your actual email
                $mail->Password = 'jiim uhis pkxs lwib'; // Replace with your actual password or app-specific password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('pv170030@gmail.com', 'MoneyGuard'); // Replace with your actual sender email and name
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Verify your email address';
                $mail->Body    = "Greetings from MoneyGuard. This is your verification code: <strong>$verification_code</strong>";

                // Send the email
                $mail->send();

                // Redirect to verify.php with the user's email
                header("Location: verify.php?email=" . urlencode($email));
                exit(); // Ensure no further code is executed after redirection
            } catch (Exception $e) {
                $error_message = "Registration failed. Email could not be sent.";
            }
        } else {
            $error_message = "Registration failed. Please try again.";
        }

        $stmt->close();
    }

    $check_email->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Register</title>
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
        .login-link {
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
        <h2>Registration Form</h2>
        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" placeholder="First Name" required>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" placeholder="Last Name" required>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" placeholder="Phone Number" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Password" required>

            <button type="submit" name="submit">Submit</button>

            <button type="button" class="back-button" onclick="window.location.href='dashboard.html';">Back to Dashboard</button>
        </form>
        <a href="login.php" class="login-link">Already have an account? Login here</a>
    </div>
</body>
</html>
