<?php
require_once __DIR__ . '/../../data/RegistrationRepository.php';
require_once __DIR__ . '/../../data/EventRepository.php';
require_once __DIR__ . '/EventController.php';

class RegistrationController {
    private $registrationRepo;
    private $eventRepo;
    private $eventController;

    const SEAT_STATUSES = ['PENDING', 'APPROVED', 'REGISTERED'];

    public function __construct($conn) {
        $this->registrationRepo = new RegistrationRepository($conn);
        $this->eventRepo = new EventRepository($conn);
        $this->eventController = new EventController($conn);
    }

    public function register($userId, $eventId) {
        // Uses the same visibility and status rules as the event page
        $event = $this->eventController->getEventForAttendee($userId, $eventId);

        if (!$event) {
            return ["success" => false, "code" => "EVENT_NOT_FOUND", "message" => "Event not found."];
        }
        if ($event['display_status'] === 'Cancelled') {
            return ["success" => false, "code" => "EVENT_CANCELLED", "message" => "This event has been cancelled."];
        }
        if ($event['display_status'] === 'Completed') {
            return ["success" => false, "code" => "EVENT_ENDED", "message" => "This event has already ended."];
        }
        if (in_array($event['my_registration'], self::SEAT_STATUSES)) {
            return ["success" => false, "code" => "ALREADY_REGISTERED", "message" => "You have already registered for this event."];
        }
        if (in_array($event['my_registration'], ['REJECTED', 'REMOVED'])) {
            return ["success" => false, "code" => "NOT_ALLOWED", "message" => "Registration for this event is unavailable."];
        }
        if ($event['registration_state'] === 'Not yet open') {
            return ["success" => false, "code" => "REGISTRATION_NOT_OPEN", "message" => "Registration has not opened yet."];
        }
        if ($event['registration_state'] === 'Closed') {
            return ["success" => false, "code" => "REGISTRATION_CLOSED", "message" => "Registration for this event is closed."];
        }

        $status = ($event['visibility'] === 'INVITE_ONLY') ? 'PENDING' : 'REGISTERED';
        $result = $this->registrationRepo->createRegistration($event['event_id'], $userId, $status);

        if ($result === "DUPLICATE") {
            return ["success" => false, "code" => "ALREADY_REGISTERED", "message" => "You have already registered for this event."];
        }
        if ($result === "BLOCKED") {
            return ["success" => false, "code" => "NOT_ALLOWED", "message" => "Registration for this event is unavailable."];
        }
        if ($result === "FULL") {
            return ["success" => false, "code" => "EVENT_FULL", "message" => "This event is currently full."];
        }
        if ($result !== "OK") {
            return ["success" => false, "code" => "SERVER_ERROR", "message" => "Registration failed. Please try again."];
        }

        return ["success" => true, "code" => $status, "message" => ($status === 'PENDING') ? "Your request has been sent to the organizer." : "You are registered for this event."];
    }

    public function cancel($userId, $eventId) {
        $event = $this->eventRepo->getEventById($eventId);
        $registration = $this->registrationRepo->getRegistration($eventId, $userId);

        if (!$event || !$registration || !in_array($registration['status'], self::SEAT_STATUSES)) {
            return ["success" => false, "code" => "NOT_REGISTERED", "message" => "You are not registered for this event."];
        }
        if ($event['status'] === 'CANCELLED') {
            return ["success" => false, "code" => "EVENT_CANCELLED", "message" => "This event has been cancelled."];
        }
        if (date('Y-m-d H:i:s') >= $event['event_date'] . ' ' . $event['start_time']) {
            return ["success" => false, "code" => "EVENT_STARTED", "message" => "This event has already started."];
        }

        if (!$this->registrationRepo->updateStatus($registration['registration_id'], self::SEAT_STATUSES, 'CANCELLED')) {
            return ["success" => false, "code" => "SERVER_ERROR", "message" => "Could not cancel registration. Please try again."];
        }

        return ["success" => true, "code" => "CANCELLED", "message" => "Your registration has been cancelled."];
    }

    // Attendee list and status counts for an event the organizer owns
    public function getEventAttendees($organizerId, $eventId) {
        $event = $this->eventRepo->getEventById($eventId);

        if (!$event || (int) $event['organizer_id'] !== (int) $organizerId) {
            return null;
        }

        return [
            "attendees" => $this->registrationRepo->getEventAttendees($eventId),
            "counts" => $this->registrationRepo->countRegistrationsByStatus($eventId)
        ];
    }

    public function updateAttendeeStatus($organizerId, $eventId, $registrationId, $action) {
        $registration = $this->registrationRepo->getRegistrationById($registrationId);
        $event = $this->eventRepo->getEventById($eventId);

        if (!$registration || !$event || (int) $registration['event_id'] !== (int) $event['event_id']) {
            return ["success" => false, "code" => "NOT_FOUND", "message" => "Registration not found."];
        }
        if ((int) $event['organizer_id'] !== (int) $organizerId) {
            return ["success" => false, "code" => "FORBIDDEN", "message" => "You can only manage your own events."];
        }
        if (in_array($this->eventController->getDisplayStatus($event), ['Cancelled', 'Completed'])) {
            return ["success" => false, "code" => "EVENT_INACTIVE", "message" => "Attendees can only be managed for upcoming or live events."];
        }

        // Pending requests already hold a seat, so approving never goes over capacity
        $transitions = [
            'approve' => [['PENDING'], 'APPROVED', "Request approved."],
            'reject' => [['PENDING'], 'REJECTED', "Request rejected."],
            'remove' => [['REGISTERED', 'APPROVED'], 'REMOVED', "Attendee removed."]
        ];

        if (!isset($transitions[$action]) || !in_array($registration['status'], $transitions[$action][0])) {
            return ["success" => false, "code" => "INVALID_TRANSITION", "message" => "This action is not allowed for the current status."];
        }

        [$fromStatuses, $toStatus, $message] = $transitions[$action];

        if (!$this->registrationRepo->updateStatus($registrationId, $fromStatuses, $toStatus)) {
            return ["success" => false, "code" => "SERVER_ERROR", "message" => "Could not update the registration. Please try again."];
        }

        return ["success" => true, "code" => $toStatus, "message" => $message];
    }
}
?>
