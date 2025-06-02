<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username']) || !isset($_SESSION['screenName'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$chatroomName = trim($data['chatroomName']);
$chatroomKey = trim($data['chatroomKey']);

if (!$chatroomName) {
    echo json_encode(['success' => false, 'error' => 'Missing chatroom name']);
    exit;
}

require_once 'db.php';

$stmt = $pdo->prepare("SELECT chatroomKey FROM list_of_chatrooms WHERE chatroomName = ?");
$stmt->execute([$chatroomName]);
$room = $stmt->fetch();

if (!$room) {
    echo json_encode(['success' => false, 'error' => 'Chatroom not found']);
    exit;
}

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