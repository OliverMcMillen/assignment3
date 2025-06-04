<?php
session_start();
// Set the response content type to JSON
header('Content-Type: application/json');

// Check if the user is logged
if (!isset($_SESSION['username']) || !isset($_SESSION['screenName'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}
// Read JSON input data from the HTTP request
$data = json_decode(file_get_contents("php://input"), true);
// Extract and sanitize the chatroom name and key from the input
$chatroomName = trim($data['chatroomName']);
$chatroomKey = trim($data['chatroomKey']);

// Validate that a chatroom name was provided, otherwise return an error response
if (!$chatroomName) {
    echo json_encode(['success' => false, 'error' => 'Missing chatroom name']);
    exit;
}
// Include the database connection script
require_once 'db.php';
// Query the database to retrieve the stored key for the requested chatroom
$stmt = $pdo->prepare("SELECT chatroomKey FROM list_of_chatrooms WHERE chatroomName = ?");
$stmt->execute([$chatroomName]);
$room = $stmt->fetch();
// If the chatroom does not exist, return an error
if (!$room) {
    echo json_encode(['success' => false, 'error' => 'Chatroom not found']);
    exit;
}
// Check if the chatroom requires a key and if the provided key matches
if (!empty($room['chatroomKey']) && $room['chatroomKey'] !== $chatroomKey) {
    echo json_encode(['success' => false, 'error' => 'Incorrect key']);
    exit;
}

// Add user to current occupants
$stmt = $pdo->prepare("
    REPLACE INTO current_chatroom_occupants (chatroomName, screenName, socketId)
    VALUES (?, ?, '')
");
$stmt->execute([$chatroomName, $_SESSION['screenName']]);

echo json_encode(['success' => true]);