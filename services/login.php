<?php
session_start();
// Include the database connection script
require 'db.php';

// Read JSON-encoded data from the HTTP request 
$data = json_decode(file_get_contents("php://input"), true);
// Extract and sanitize the username and password
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

// Check if username or password is missing
if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}
// Prepare a SQL statement to fetch user details for the given username from the database
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
// Fetch the user's data as an associative array
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If user is empty or passwords do not match then return error
if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid username or password"]);
    exit;
}
// Authentication successful:
$_SESSION['username'] = $username;
$_SESSION['screenName'] = $user['screen_name'];

echo json_encode(["success" => true]);