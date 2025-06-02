<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT chatroomName, chatroomKey FROM list_of_chatrooms ORDER BY chatroomName");
    $rooms = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rooms[] = [
            'name' => $row['chatroomName'],
            'locked' => !empty($row['chatroomKey']),
        ];
    }

    echo json_encode(['success' => true, 'rooms' => $rooms]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}