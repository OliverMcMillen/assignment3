<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$chatroomName = trim($data['chatroomName'] ?? '');
$chatroomKey = trim($data['chatroomKey'] ?? '');
$username = $_SESSION['username'];

if ($chatroomName === '') {
    echo json_encode(['success' => false, 'error' => 'Chatroom name is required.']);
    exit;
}

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