<?php
session_start();
include 'connection.php';

$message = "";
$name_error = "";
$email_error = "";
$phno_error = "";
$password_error = "";
$password2_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phno = trim($_POST["phno"] ?? "");
    $password = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";

    if ($username == "" || $email == "" || $phno == "" || $password == "" || $password2 ==""){
        $message = "Please fill in all fields.";
    }elseif ($username == "") {
        $name_error = "Please enter your name.";
    } elseif ($email == "") {
        $email_error = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Please enter a valid email address.";
    } elseif ($phno == "") {
        $phno_error = "Please enter your phone number.";
    } elseif (!preg_match('/^[0-9]+$/', $phno)) {
        $phno_error = "Phone number must contain numbers only.";
    } elseif ($password == "") {
        $password_error = "Please enter a password.";
    } elseif (mb_strlen($password, "UTF-8") < 15) {
        $password_error = "Password must be at least 15 characters long.";
    } elseif ($password2 == "") {
        $password2_error = "Please confirm your password.";
    } elseif ($password !== $password2) {
        $password2_error = "Passwords do not match.";
    }else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $check_sql = "SELECT parent_id FROM `parents` WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $email_error = "This email is already registered.";
        } else {
            $sql = "INSERT INTO `parents` (name, email, phone, password_hash) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $email, $phno, $hashed_password);

            if ($stmt->execute()) {
                $parent_id = $conn->insert_id;
                $_SESSION["parent_id"] = $parent_id;
                $_SESSION["parent_name"] = $username;
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
                <input id="username" name="username" type="text" placeholder="Example: John Doe" value="<?= htmlspecialchars($name ?? '') ?>" required>
                <p class="error"><?= htmlspecialchars($name_error) ?></p>
                <label for="email">Email:</label><br>
                <input id="email" name="email" type="email" placeholder="you@example.com" value="<?= htmlspecialchars($email ?? '') ?>" required>
                <p class="error"><?= htmlspecialchars($email_error) ?></p>
                <label for="phno">Phone Number:</label><br>
                <input id="phno" name="phno" type="text" placeholder="Enter phone number without dash" value="<?= htmlspecialchars($phno ?? '') ?>" required>
                <p class="error"><?= htmlspecialchars($phno_error) ?></p>
                <label for="password">Password:</label><br>
                <input id="password" name="password" type="password" minlength="15" placeholder="Enter at least 15 characters" required>
                <p class="error"><?= htmlspecialchars($password_error) ?></p>
                <label for="password2">Confirm Password:</label><br>
                <input id="password2" name="password2" type="password" minlength="15" placeholder="Re-enter your password" required>
                <p class="error"><?= htmlspecialchars($password2_error) ?></p>
                <?php if ($message != ""): ?><p class="error"><?= htmlspecialchars($message) ?></p><?php endif; ?>
                <button type="submit">Create account</button>
            </form>
        </section>

        <div class="sign-in">
            <p>Already have an account? <a href="login.php">Login here!</a></p>
        </div>

    </main>
    <script src="js/register.js"></script>
</body>
</html>