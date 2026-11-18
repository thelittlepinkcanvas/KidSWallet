<?php

echo "THIS IS THE NEW KIDZWALLET REGISTER PAGE";
exit;
session_start();
include 'connection.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone_no = trim($_POST["phone_no"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($username == "" || $email == "" || $phone_no == "" || $password == "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (!preg_match('/^[0-9]+$/', $phone_no)) {
        $message = "Phone number must contain numbers only.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $check_sql = "SELECT parent_id FROM `parents` WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $sql = "INSERT INTO `parents` (name, email, phone, password_hash) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $email, $phone_no, $hashed_password);

            if ($stmt->execute()) {
                header("Location: child-account-create.php");
                exit;
            } else {
                $message = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KidSWallet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sniglet:wght@400;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/register.css">
    <script src="js/register.js"></script>
</head>
<body>
    <header>
        <h3>KidSWallet</h3>
    </header>
    <main class="sign-up">
        <div class="signup-head">
            <h1>Sign Up</h1>
        </div>

        <section class="signup-body" id="signup-body">
            <form method="post" id="signup-form">
                <label for="username">Name:</label><br>
                <input id="username" name="username" type="text" placeholder="Example: John Doe" required>
                <p class="error" id="name-error"></p>
                <label for="email">Email:</label><br>
                <input id="email" name="email" type="email" placeholder="you@example.com" required>
                <p class="error" id="email-error"></p>
                <label for="phno">Phone Number:</label><br>
                <input id="phno" name="phno" type="text" placeholder="Enter phone number without dash" required>
                <p class="error" id="phno-error"></p>
                <label for="password">Password:</label><br>
                <input id="password" name="password" type="password" placeholder="Enter your password" required>
                <p class="error" id="password-error"></p>
                <label for="password2">Confirm Password:</label><br>
                <input id="password2" name="password2" type="password" placeholder="Enter your password" required>
                <p class="error" id="password-error"></p>
                <button type="submit">Create account</button>
            </form>
        </section>

        <div class="sign-in">
            <p>Already have an account? <a href="login.html">Login here!</a></p>
        </div>

    </main>
    <script src="js/register.js"></script>
</body>
</html>