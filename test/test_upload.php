<?php
require_once "data/database.php";
require_once "application/controllers/EventController.php";

$eventController = new EventController($conn);

$input = [
    'name' => 'Test Event ' . time(),
    'description' => 'Test',
    'event_date' => '2026-10-10',
    'start_time' => '10:00',
    'end_time' => '12:00',
    'location' => 'Test Location',
    'address' => '',
    'capacity' => '100',
    'visibility' => 'PUBLIC',
    'registration_open' => '2026-10-01',
    'registration_close' => '2026-10-09',
    'interests' => []
];


file_put_contents('dummy.jpg', 'fake image data');
$cover = [
    'name' => 'dummy.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => __DIR__ . '/dummy.jpg',
    'error' => UPLOAD_ERR_OK,
    'size' => 15
];

$result = $eventController->createEvent(1, $input, $cover);
print_r($result);

if ($result['success']) {

    $res = $conn->query("SELECT cover_photo FROM events WHERE event_id = " . $result['event_id']);
    $row = $res->fetch_assoc();
    echo "\nDB cover_photo: " . var_export($row['cover_photo'], true) . "\n";
}
