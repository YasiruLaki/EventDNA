<?php
require_once __DIR__ . "/includes/guard.php";
require_once "../../data/database.php";
require_once "../../application/controllers/RegistrationController.php";

$eventId = (int)($_GET['id'] ?? 0);
$registrationController = new RegistrationController($conn);
$attendees = $registrationController->getEventAttendees($organizerId, $eventId, (string)($_GET['q'] ?? ''), (string)($_GET['filter'] ?? 'all'));
if ($attendees === null) {
    http_response_code(404);
    die("Event not found.");
}

// Spreadsheet apps run cells starting with these characters as formulas, so prefix them with a quote
function csv_safe($value) {
    $value = (string)$value;
    return ($value !== '' && strpbrk($value[0], "=+-@\t\r") !== false) ? "'" . $value : $value;
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="event-' . $eventId . '-attendees-' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads names as UTF-8
fputcsv($out, ['Name', 'Email', 'Registration Status', 'Registered At', 'Approved At', 'Attendance', 'Checked In At']);
foreach ($attendees['rows'] as $row) {
    fputcsv($out, [
        csv_safe($row['full_name']),
        csv_safe($row['email']),
        $row['status'],
        $row['registered_at'],
        $row['approved_at'] ?? '',
        (int)$row['checked_in'] === 1 ? 'Checked-in' : 'Not checked in',
        $row['checked_in_at'] ?? '',
    ]);
}
fclose($out);
