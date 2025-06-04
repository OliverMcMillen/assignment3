<?php
session_start();
// Include the database connection script
require 'db.php';
// Read the raw POST data (JSON)
$data = json_decode(file_get_contents("php://input"), true);
// Extract and sanitize user inputs from the decoded data
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$screenName = trim($data['screenName'] ?? '');
// Check if any of the required fields are missing or empty
if (!$username || !$password || !$screenName) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}
// Prepare a SQL statement to check if the username or screen name is already taken
$stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ? OR screen_name = ?");
// Execute the query with the provided username and screen name as parameters (to prevent SQL injection)
$stmt->execute([$username, $screenName]);

/ If a record is found, it means the username or screen name is already in use
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["error" => "Username or screen name taken"]);
    exit;
}
// Hash the password securely
$hashPassword = password_hash($password, PASSWORD_DEFAULT);
// Prepare a SQL statement to insert the new user into the 'users' table
$stmt = $pdo->prepare("INSERT INTO users (username, password_hash, screen_name) VALUES (?, ?, ?)");
// Execute the insert query with the sanitized username, hashed password, and screen name
$stmt->execute([$username, $hashPassword, $screenName]);

// Start session with user if signup is successful
$_SESSION['username'] = $username;
$_SESSION['screenName'] = $screenName;

echo json_encode(["success" => true]);