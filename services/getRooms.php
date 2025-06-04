<?php
session_start();
// Set the HTTP response header to indicate JSON content
header('Content-Type: application/json');
// Check if the user is logged in by verifying the 'username' 
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}
// Include the database connection script
require_once 'db.php';

try {
    // Query the database to get all chatrooms with their names and keys, ordered alphabetically by name
    $stmt = $pdo->query("SELECT chatroomName, chatroomKey FROM list_of_chatrooms ORDER BY chatroomName");
    $rooms = [];
    // Loop through each returned row (chatroom) from the query
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rooms[] = [
            'name' => $row['chatroomName'],
            'locked' => !empty($row['chatroomKey']),
        ];
    }
    // Output a JSON response with success=true and the list of chatrooms
    echo json_encode(['success' => true, 'rooms' => $rooms]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}