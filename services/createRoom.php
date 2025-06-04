<?php
session_start();
// Set the response content type to JSON
header('Content-Type: application/json');
// Check if the user is logged in by verifying the 'username'
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in.']);
    exit;
}
// Retrieve and decode the JSON payload
$data = json_decode(file_get_contents("php://input"), true);
// Extract and trim the chatroom name and key from the decoded data, 
$chatroomName = trim($data['chatroomName'] ?? '');
$chatroomKey = trim($data['chatroomKey'] ?? '');
// Get the username of the current logged-in user from the session
$username = $_SESSION['username'];
// Validate that the chatroom name is provided
if ($chatroomName === '') {
    echo json_encode(['success' => false, 'error' => 'Chatroom name is required.']);
    exit;
}
// Include database connection file
require_once 'db.php';

try {
    // Check if chatroom name already exists
    $stmt = $pdo->prepare("SELECT 1 FROM list_of_chatrooms WHERE chatroomName = ?");
    $stmt->execute([$chatroomName]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Chatroom name already taken.']);
        exit;
    }

    // Insert new chatroom
    $stmt = $pdo->prepare("INSERT INTO list_of_chatrooms (chatroomName, chatroomKey, creatorUsername) VALUES (?, ?, ?)");
    $stmt->execute([$chatroomName, $chatroomKey, $username]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}