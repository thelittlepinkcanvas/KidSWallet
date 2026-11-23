<?php
session_start();
include 'connection.php';

header("Content-Type: application/json");

if (!isset($_SESSION["pending_approval_request"])) {
    echo json_encode(["status" => "ERROR"]);
    exit;
}

if (($_SESSION["role"] ?? "") === "CHILD"
    && isset($_SESSION["child_id"])) {
    echo json_encode(["status" => "APPROVED"]);
    unset($_SESSION["pending_approval_request"]);
    exit;
}

$request_id = $_SESSION["pending_approval_request"];

$sql = "SELECT ar.status, ar.expires_at,
               ar.child_id, ar.parent_id,
               c.username, c.child_name
        FROM approval_requests ar
        JOIN children c ON ar.child_id = c.child_id
        WHERE ar.request_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

/*output encoding*/
if (!$request) {
    echo json_encode(["status" => "ERROR"]);
    exit;
}

if ($request["status"] == "APPROVED") {
    session_regenerate_id(true);
    $_SESSION["child_id"] = $request["child_id"];
    $_SESSION["parent_id"] = $request["parent_id"];
    $_SESSION["username"] = $request["username"];
    $_SESSION["child_name"] = $request["child_name"];
    $_SESSION["role"] = "CHILD";
    unset($_SESSION["pending_approval_request"]);
    echo json_encode(["status" => "APPROVED"]);
} elseif ($request["status"] == "REJECTED") {
    unset($_SESSION["pending_approval_request"]);
    echo json_encode(["status" => "REJECTED"]);
} elseif (strtotime($request["expires_at"]) <= time()) {
    unset($_SESSION["pending_approval_request"]);
    echo json_encode(["status" => "EXPIRED"]);
} else {
    echo json_encode(["status" => "PENDING"]);
}

$stmt->close();
session_write_close();
?>
