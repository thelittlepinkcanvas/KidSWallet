<?php
session_start();
include 'connection.php';

/*RBAC*/
if (!isset($_SESSION["parent_id"]) || ($_SESSION["role"] ?? "") !== "PARENT") {
    header("Location: login.php");
    exit;
}

$parent_id = $_SESSION["parent_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST["request_id"] ?? "";
    $action = $_POST["action"] ?? "";
    if ($action == "approve" || $action == "reject") {
        $status = ($action == "approve") ? "APPROVED" : "REJECTED";
        $sql = "UPDATE approval_requests
                SET status = ?, responded_at = NOW()
                WHERE request_id = ? /*tampering protection */
                AND parent_id = ?
                AND status = 'PENDING'
                AND expires_at > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $status, $request_id, $parent_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $message = ($status == "APPROVED")
                ? "Login request approved!"
                : "Login request rejected!";
        } else {
            $message = "This request is no longer available.";
        }
        $stmt->close();
    }
}

$sql = "SELECT ar.request_id, ar.requested_at, c.child_name, c.username
        FROM approval_requests ar
        JOIN children c ON ar.child_id = c.child_id
        WHERE ar.parent_id = ?
        AND ar.status = 'PENDING'
        AND ar.expires_at > NOW()
        ORDER BY ar.requested_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$requests = $stmt->get_result();

$sql = "SELECT c.child_id, c.child_name, w.balance FROM children c LEFT JOIN wallets w ON c.child_id = w.child_id WHERE c.parent_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$children = $stmt->get_result();

$sql = "SELECT t.transaction_id, t.transaction_type,t.amount, t.description, t.created_at,c.child_name FROM transactions t JOIN children c ON t.child_id = c.child_id WHERE c.parent_id = ? ORDER BY t.created_at DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$transactions = $stmt->get_result();
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
    <link rel="stylesheet" href="css/parent-dashboard.css">
</head>
<body>
    <header>
        <h3>KidSWallet</h3>
        <nav>
            <ul class="nav-links">
                <li>Home</li>
                <li>Transactions</li>
                <li>Manage children</li>
                <li>Your account</li>
                <li><a href="logout.php" onclick="return confirm('Are you sure you want to log out?')" id="logout">Logout</a></li>
            </ul>
        </nav>
    </header>
    <h1>Welcome, <?= htmlspecialchars($_SESSION["parent_name"]) ?>!</h1>
    <?php if ($message != ""): ?>
        <p class="success-message">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <?php if ($requests->num_rows > 0): ?>
        <section class="approval-requests">
            <?php while ($request = $requests->fetch_assoc()): ?>
                <div class="approval-card">
                    <h3>Login request from <?= htmlspecialchars($request["child_name"]) ?></h3>
                    <p>Username: <?= htmlspecialchars($request["username"]) ?></p>
                    <p>This child is trying to log in to KidzWallet. Do you approve this request?</p>
                    <form method="post" class="approval">
                        <input type="hidden" name="request_id" value="<?= $request["request_id"] ?>">
                        <button type="submit" name="action" value="approve" class="approve">Approve</button>
                        <button type="submit" name="action" value="reject" class="reject">Reject</button>
                    </form>
                </div>
            <?php endwhile; ?>

        </section>
    <?php endif; ?>
    <main class="dashboard">
        <div class="cont1">
            <div class="cont1-head">
                <h3>Your children</h3>
                <a href="child-account-create.php" class="addChild">+ Add child account</a>
            </div>
            <hr>
            <?php if ($children->num_rows > 0): ?>
                <?php while ($child = $children->fetch_assoc()): ?>
                    <div class="child">
                        <p class="childName"><?= htmlspecialchars($child["child_name"]) ?></p>
                        <p>Wallet balance: RM<?= number_format((float)($child["balance"] ?? 0), 2) ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You have no child accounts yet.</p>
            <?php endif; ?>
        </div>
        <div class="cont2">
            <div class="cont2-head">
                <h3>Transaction history</h3>
            </div>
            <hr>
            <?php if ($transactions->num_rows > 0): ?>
                <?php while ($transaction = $transactions->fetch_assoc()): ?>
                    <div class="history">
                        <p class="dates"><?= date("d/m/Y", strtotime($transaction["created_at"])) ?></p>
                        <p>
                            <?php if ($transaction["transaction_type"] == "TOP_UP"): ?>
                                Added RM<?= number_format($transaction["amount"], 2) ?>
                                to <?= htmlspecialchars($transaction["child_name"]) ?>
                            <?php elseif ($transaction["transaction_type"] == "PAYMENT"): ?>
                                <?= htmlspecialchars($transaction["child_name"]) ?>
                                spent RM<?= number_format($transaction["amount"], 2) ?>
                                - <?= htmlspecialchars($transaction["description"]) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
    <p>No transactions yet.</p>
<?php endif; ?>
            <a href="#" class="viewall">View All</a>
        </div>
    </main>
    
</body>
</html>