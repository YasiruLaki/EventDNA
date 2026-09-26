<?php
require_once __DIR__ . '/../../data/EventRepository.php';

class EventController {
    private $eventRepo;

    const UPLOAD_DIR = 'uploads/events/';           // relative to project root
    const MAX_COVER_BYTES = 5 * 1024 * 1024;        // 5 MB
    const COVER_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    const MAX_INTERESTS = 10;

    public function __construct($conn) {
        $this->eventRepo = new EventRepository($conn);
    }

    public function getInterests() {
        return $this->eventRepo->getInterests();
    }

    public function createEvent($organizerId, $input, $coverFile) {
        $interests = $this->getValidInterestIds($input['interests'] ?? []);
        $errors = $this->validate($input, null);
        if ($interests === false) {
            $errors[] = "Please choose up to " . self::MAX_INTERESTS . " valid interest tags.";
        }
        if ($errors) {
            return ["success" => false, "errors" => $errors];
        }

        $event = $this->normalize($input);

        $upload = $this->storeCover($coverFile);
        if (!$upload["success"]) {
            return ["success" => false, "errors" => [$upload["message"]]];
        }
        $event['cover_photo'] = $upload["path"];

        try {
            $this->eventRepo->beginTransaction();
            $eventId = $this->eventRepo->createEvent($organizerId, $event);
            if (!$eventId || !$this->eventRepo->setEventInterests($eventId, $interests)) {
                throw new Exception("Insert failed");
            }
            $this->eventRepo->commit();
        } catch (Exception $e) {
            $this->eventRepo->rollback();
            $this->deleteCover($event['cover_photo']);
            return ["success" => false, "errors" => ["Failed to create event. Please try again."]];
        }

        return ["success" => true, "event_id" => $eventId];
    }

    public function updateEvent($organizerId, $eventId, $input, $coverFile) {
        $existing = $this->getOwnedEvent($organizerId, $eventId);
        if (!$existing) {
            return ["success" => false, "errors" => ["Event not found."]];
        }
        if (!$this->isEditable($existing)) {
            return ["success" => false, "errors" => ["Cancelled or completed events cannot be edited."]];
        }

        $interests = $this->getValidInterestIds($input['interests'] ?? []);
        $errors = $this->validate($input, $existing);
        if ($interests === false) {
            $errors[] = "Please choose up to " . self::MAX_INTERESTS . " valid interest tags.";
        }
        if ($errors) {
            return ["success" => false, "errors" => $errors];
        }

        $event = $this->normalize($input);

        $upload = $this->storeCover($coverFile);
        if (!$upload["success"]) {
            return ["success" => false, "errors" => [$upload["message"]]];
        }
        $event['cover_photo'] = $upload["path"] ?? $existing['cover_photo'];

        try {
            $this->eventRepo->beginTransaction();
            if (!$this->eventRepo->updateEvent($eventId, $event) ||
                !$this->eventRepo->setEventInterests($eventId, $interests)) {
                throw new Exception("Update failed");
            }
            $this->eventRepo->commit();
        } catch (Exception $e) {
            $this->eventRepo->rollback();
            if ($upload["path"]) {
                $this->deleteCover($upload["path"]);
            }
            return ["success" => false, "errors" => ["Failed to update event. Please try again."]];
        }

        // Replace the old cover only after the new one is saved
        if ($upload["path"] && $existing['cover_photo']) {
            $this->deleteCover($existing['cover_photo']);
        }

        return ["success" => true, "event_id" => $eventId];
    }

    public function cancelEvent($organizerId, $eventId) {
        $event = $this->getOwnedEvent($organizerId, $eventId);
        if (!$event) {
            return ["success" => false, "message" => "Event not found."];
        }
        if ($event['status'] === 'CANCELLED') {
            return ["success" => false, "message" => "This event is already cancelled."];
        }
        if ($this->getDisplayStatus($event) === 'Completed') {
            return ["success" => false, "message" => "Completed events cannot be cancelled."];
        }

        if ($this->eventRepo->cancelEvent($eventId)) {
            return ["success" => true];
        }
        return ["success" => false, "message" => "Failed to cancel event. Please try again."];
    }

    // Returns the event with its interest tags, or null if it does not belong to this organizer
    public function getOwnedEvent($organizerId, $eventId) {
        $event = $this->eventRepo->getEventById((int)$eventId);
        if (!$event || (int)$event['organizer_id'] !== (int)$organizerId) {
            return null;
        }
        $event['interest_ids'] = $this->eventRepo->getEventInterestIds($event['event_id']);
        $event['interest_names'] = $this->eventRepo->getEventInterestNames($event['event_id']);
        $event['display_status'] = $this->getDisplayStatus($event);
        $event['registration_state'] = $this->getRegistrationState($event);
        $event['editable'] = $this->isEditable($event);
        return $event;
    }

    public function getOrganizerDashboard($organizerId) {
        $events = $this->eventRepo->getEventsByOrganizer($organizerId);

        $upcoming = 0;
        $totalRegistrations = 0;
        $active = null;

        foreach ($events as &$event) {
            $event['display_status'] = $this->getDisplayStatus($event);
            $totalRegistrations += (int)$event['registered_count'];

            if ($event['display_status'] === 'Upcoming' || $event['display_status'] === 'Live') {
                $upcoming++;
            }
            // Feature a live event, otherwise the soonest upcoming one
            if ($event['display_status'] === 'Live') {
                $active = $event;
            } elseif ($event['display_status'] === 'Upcoming' &&
                      ($active === null || ($active['display_status'] !== 'Live' && $event['event_date'] < $active['event_date']))) {
                $active = $event;
            }
        }
        unset($event);

        return [
            "events" => $events,
            "active" => $active,
            "stats" => [
                "total" => count($events),
                "upcoming" => $upcoming,
                "registrations" => $totalRegistrations,
            ],
        ];
    }

    public function getDisplayStatus($event) {
        if ($event['status'] === 'CANCELLED') {
            return 'Cancelled';
        }
        $today = date('Y-m-d');
        if ($event['event_date'] < $today) {
            return 'Completed';
        }
        if ($event['event_date'] === $today) {
            return date('H:i:s') > $event['end_time'] ? 'Completed' : 'Live';
        }
        return 'Upcoming';
    }

    public function getRegistrationState($event) {
        if ($event['status'] === 'CANCELLED') {
            return 'Closed';
        }
        $now = date('Y-m-d H:i:s');
        if ($now < $event['registration_open']) {
            return 'Not yet open';
        }
        if ($now > $event['registration_close']) {
            return 'Closed';
        }
        if ((int)$event['registered_count'] >= (int)$event['capacity']) {
            return 'Full';
        }
        return 'Open';
    }

    private function isEditable($event) {
        $status = $this->getDisplayStatus($event);
        return $status === 'Upcoming' || $status === 'Live';
    }

    private function validate($in, $existing) {
        $errors = [];

        $name = trim($in['name'] ?? '');
        if ($name === '' || mb_strlen($name) > 200) {
            $errors[] = "Event name is required (max 200 characters).";
        }
        $location = trim($in['location'] ?? '');
        if ($location === '' || mb_strlen($location) > 200) {
            $errors[] = "Venue / location is required (max 200 characters).";
        }
        if (mb_strlen(trim($in['address'] ?? '')) > 300) {
            $errors[] = "Address must be 300 characters or fewer.";
        }

        $eventDate = $this->parseDate($in['event_date'] ?? '');
        $today = date('Y-m-d');
        if (!$eventDate) {
            $errors[] = "Please enter a valid event date.";
        } elseif ($eventDate < $today && !($existing && $eventDate === $existing['event_date'])) {
            $errors[] = "Event date cannot be in the past.";
        }

        $start = $this->parseTime($in['start_time'] ?? '');
        $end = $this->parseTime($in['end_time'] ?? '');
        if (!$start || !$end) {
            $errors[] = "Please enter valid start and end times.";
        } elseif ($end <= $start) {
            $errors[] = "End time must be after the start time.";
        }

        $capacity = filter_var($in['capacity'] ?? '', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 100000]]);
        if ($capacity === false) {
            $errors[] = "Capacity must be a whole number between 1 and 100000.";
        } elseif ($existing && $capacity < (int)$existing['registered_count']) {
            $errors[] = "Capacity cannot be lower than the " . $existing['registered_count'] . " people already registered.";
        }

        if (!in_array($in['visibility'] ?? '', ['PUBLIC', 'INVITE_ONLY'], true)) {
            $errors[] = "Please choose a valid visibility.";
        }

        $regOpen = $this->parseDate($in['registration_open'] ?? '');
        $regClose = $this->parseDate($in['registration_close'] ?? '');
        if (!$regOpen || !$regClose) {
            $errors[] = "Please enter valid registration open and close dates.";
        } else {
            if ($regClose < $regOpen) {
                $errors[] = "Registration must close on or after the day it opens.";
            }
            if ($eventDate && $regClose > $eventDate) {
                $errors[] = "Registration must close on or before the event date.";
            }
        }

        return $errors;
    }

    // Converts validated form input into the shape the repository expects
    private function normalize($in) {
        $address = trim($in['address'] ?? '');
        $description = trim($in['description'] ?? '');
        return [
            'name' => trim($in['name']),
            'description' => $description === '' ? null : $description,
            'event_date' => $this->parseDate($in['event_date']),
            'start_time' => $this->parseTime($in['start_time']),
            'end_time' => $this->parseTime($in['end_time']),
            'location' => trim($in['location']),
            'address' => $address === '' ? null : $address,
            'capacity' => (int)$in['capacity'],
            'visibility' => $in['visibility'],
            'registration_open' => $this->parseDate($in['registration_open']) . ' 00:00:00',
            'registration_close' => $this->parseDate($in['registration_close']) . ' 23:59:59',
        ];
    }

    // Returns the list of valid interest IDs, or false if any are invalid
    private function getValidInterestIds($ids) {
        if (!is_array($ids)) {
            return false;
        }
        $ids = array_unique(array_map('intval', $ids));
        if (count($ids) > self::MAX_INTERESTS) {
            return false;
        }
        $known = array_map('intval', array_column($this->eventRepo->getInterests(), 'interest_id'));
        foreach ($ids as $id) {
            if (!in_array($id, $known, true)) {
                return false;
            }
        }
        return array_values($ids);
    }

    // Saves an optional cover photo. "path" is null when no file was uploaded.
    private function storeCover($file) {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ["success" => true, "path" => null];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ["success" => false, "message" => "Cover photo upload failed. Please try again."];
        }
        if ($file['size'] > self::MAX_COVER_BYTES) {
            return ["success" => false, "message" => "Cover photo must be 5 MB or smaller."];
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::COVER_TYPES[$mime])) {
            return ["success" => false, "message" => "Cover photo must be a JPG, PNG or WebP image."];
        }

        $dir = __DIR__ . '/../../' . self::UPLOAD_DIR;
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return ["success" => false, "message" => "Could not save the cover photo."];
        }

        $filename = bin2hex(random_bytes(16)) . '.' . self::COVER_TYPES[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            return ["success" => false, "message" => "Could not save the cover photo."];
        }

        return ["success" => true, "path" => self::UPLOAD_DIR . $filename];
    }

    private function deleteCover($path) {
        if ($path && strpos($path, self::UPLOAD_DIR) === 0) {
            $full = __DIR__ . '/../../' . $path;
            if (is_file($full)) {
                unlink($full);
            }
        }
    }

    private function parseDate($value) {
        $d = DateTime::createFromFormat('!Y-m-d', $value);
        return ($d && $d->format('Y-m-d') === $value) ? $value : null;
    }

    private function parseTime($value) {
        foreach (['H:i', 'H:i:s'] as $format) {
            $t = DateTime::createFromFormat('!' . $format, $value);
            if ($t && $t->format($format) === $value) {
                return $t->format('H:i:s');
            }
        }
        return null;
    }
}
?>
