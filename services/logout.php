<?php
session_start();
// Destroy all data registered to the current session, effectively logging the user out
session_destroy();
echo json_encode(["success" => true]);