<?php
require_once "../data/database.php";
$stmt = $conn->query("SELECT name, cover_photo FROM events ORDER BY event_id DESC LIMIT 3");
while ($row = $stmt->fetch_assoc()) {
    var_dump($row);
}
