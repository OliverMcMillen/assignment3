<?php
session_start();
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$screenName = trim($data['screenName'] ?? '');

if (!$username || !$password || !$screenName) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

$stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ? OR screen_name = ?");
$stmt->execute([$username, $screenName]);

if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["error" => "Username or screen name taken"]);
    exit;
}

$hashPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, password_hash, screen_name) VALUES (?, ?, ?)");
$stmt->execute([$username, $hashPassword, $screenName]);

// Start session with user if signup is successful
$_SESSION['username'] = $username;
$_SESSION['screenName'] = $screenName;

echo json_encode(["success" => true]);