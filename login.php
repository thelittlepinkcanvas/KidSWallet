<?php
session_start();
include 'connection.php';

$message = "";
$activeStep = "identify";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $step = $_POST["step"] ?? "";
    if ($step == "identify") {
        $identifier = trim($_POST["identifier"] ?? "");
        if ($identifier == "") {
            $message = "Please enter your email or username.";
        } else {
            $sql = "SELECT * FROM parents WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $_SESSION["pending_login"] = [
                    "role" => "PARENT",
                    "parent_id" => $row["parent_id"],
                    "email" => $row["email"],
                    "name" => $row["name"],
                    "phone" => $row["phone"]
                ];
                $activeStep = "parent";
            } else{
                $sql = "SELECT * FROM children WHERE username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $identifier);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    $_SESSION["pending_login"] = [
                        "role" => "CHILD",
                        "child_id" => $row["child_id"],
                        "parent_id" => $row["parent_id"],
                        "childName" => $row["child_name"],
                        "username" => $row["username"]
                    ];
                    $activeStep = "child";
                } else {
                    $message = "Account not found.";
                }
            }
            $stmt->close();
        }
    }
    elseif ($step == "parent_login") {
        $password = $_POST["password"] ?? "";
        $pending = $_SESSION["pending_login"] ?? null;
        if (!$pending || $pending["role"] !== "PARENT") {
            $message = "Please enter your account details again.";
            $activeStep = "identify";
        } else {
            $sql = "SELECT password_hash FROM parents WHERE parent_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pending["parent_id"]);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($row && password_verify($password, $row["password_hash"])) {
                $activeStep = "mfa";
            } else {
                $message = "Incorrect password.";
                $activeStep = "parent";
            }
            $stmt->close();
        }
    }
    elseif ($step == "mfa_verify") { //MFA simulation that accepts any 6 digit value
        $code = $_POST["mfa-code"] ?? "";
        $pending = $_SESSION["pending_login"] ?? null;

        if (!$pending || $pending["role"] !== "PARENT") {
            $message = "Please enter your account details again.";
            $activeStep = "identify";

        } elseif (preg_match('/^[0-9]{6}$/', $code)) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $pending["parent_id"];
            $_SESSION["parent_id"] = $pending["parent_id"];
            $_SESSION["role"] = "PARENT";
            $_SESSION["parent_name"] = $pending["name"];
            $_SESSION["email"] = $pending["email"];
            $_SESSION["phone"] = $pending["phone"];
            unset($_SESSION["pending_login"]);
            header("Location: parent-dashboard.php");
            exit;

        } else {
            $message = "Please enter a valid 6-digit code.";
            $activeStep = "mfa";
        }
    }
    elseif ($step == "child_login") {
        $pin = $_POST["pin"] ?? "";
        $pending = $_SESSION["pending_login"] ?? null;
        if (!$pending || $pending["role"] !== "CHILD") {
            $message = "Please enter your account details again.";
            $activeStep = "identify";
        } else {
            $sql = "SELECT pin_hash FROM children WHERE child_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pending["child_id"]);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($row && password_verify($pin, $row["pin_hash"])) {
                $sql = "INSERT INTO approval_requests
                        (child_id, parent_id, expires_at)
                        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 60 MINUTE))";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "ii",
                    $pending["child_id"],
                    $pending["parent_id"]
                );
                $stmt->execute();
                $_SESSION["pending_approval_request"] = $stmt->insert_id;
                unset($_SESSION["pending_login"]);
                $activeStep = "approval";
            } else {
                $message = "Incorrect PIN.";
                $activeStep = "child";
            }
            $stmt->close();
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
    <link rel="stylesheet" href="css/login.css">
    <script src="js/login.js"></script>
</head>
<body>
    <header>
        <h3>KidSWallet</h3>
    </header>
    <main class="login">
        <div class="login-head">
            <h1>Login</h1>
            <div class="progress">
                <span class="progress-step active"></span>
                <span class="progress-step"></span>
                <span class="progress-step"></span>
            </div>
        </div>

        <section class="step <?= $activeStep == 'identify' ? 'active' : '' ?>" id="email-step" <?= $activeStep != 'identify' ? 'hidden' : '' ?>>
            <h3>Welcome back</h3>
            <form method="post" id="email-form">
                <label for="email">Email:</label><br>
                <input type="hidden" name="step" value="identify">
                <input id="email" name="identifier" type="text" placeholder="Enter email or username" required>
                <p class="error"><?= htmlspecialchars($message) ?></p>
                <button type="submit">Continue</button>
            </form>
            <div class="sign-up">
                <p>No account? <a href="register.php">Sign up here!</a></p>
            </div>
        </section>
        
        <section class="step <?= $activeStep == 'parent' ? 'active' : '' ?>" id="pwsd-step" <?= $activeStep != 'parent' ? 'hidden' : '' ?>>
            <a href="#" class="back-button" data-back="email">← Back</a>
            <h3>Welcome back, <?= htmlspecialchars($_SESSION["pending_login"]["name"] ?? "Parent") ?>!</h3>
            <form method="post" id="pswd-form">
                <input type="hidden" name="step" value="parent_login">
                <label for="password">Password:</label><br>
                <input id="password" name="password" type="password" placeholder="Enter your password" required>
                <p class="error"><?= htmlspecialchars($message) ?></p>
                <button type="submit">Sign in</button>
            </form>
            <p class="forgot"><a href="#">Forgot password?</a></p>
        </section>

        <section class="step <?= $activeStep == 'child' ? 'active' : '' ?>" id="pin-step" <?= $activeStep != 'child' ? 'hidden' : '' ?>>
            <a href="#" class="back-button" data-back="email">← Back</a>
            <h3>Welcome back,  <?= htmlspecialchars($_SESSION["pending_login"]["childName"] ?? "Child") ?>!</h3>
            <form method="post" id="pin-form">
                 <input type="hidden" name="step" value="child_login">
                <label for="pin">Pin:</label><br>
                <input id="pin" name="pin" type="password" placeholder="Enter your pin" required>
                <p class="error"><?= htmlspecialchars($message) ?></p>
                <button type="submit">Sign in</button>
            </form>
            <p class="forgot">Forgot pin? Contact your parents for the pin.</p>
        </section>
        
       <section class="step <?= $activeStep == 'mfa' ? 'active' : '' ?>" id="mfa-step" <?= $activeStep != 'mfa' ? 'hidden' : '' ?>>
            <a href="#" class="back-button" data-back="email">← Back</a>
            <p>Parent Acocunt</p>
            <h3>Verify it's you.</h3>
            <p class="subheading">Enter the verification code sent to your email.</p>
            <form method="post" id="mfa-form">
                <input type="hidden" name="step" value="mfa_verify">
                <label for="mfa-code">Verification code:</label><br>
                <input id="mfa-code" name="mfa-code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="0 0 0 0 0 0" required>
                <p class="error"><?= htmlspecialchars($message) ?></p>
                <button type="submit">Verify</button>
            </form>
             <p class="no-code">Didn't receive a code? <a href="#" id="resend-code">Resend code</a></p>
        </section>

        <section class="step <?= $activeStep == 'approval' ? 'active' : '' ?>" id="approval-step" <?= $activeStep != 'approval' ? 'hidden' : '' ?>>
            <a href="#" class="back-button" data-back="email">← Back</a>
            <p>Child Acocunt</p>
            <h2>Waiting for parent approval...</h2>
        </section>

    </main>
</body>
</html>