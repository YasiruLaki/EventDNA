<?php
require_once __DIR__ . '/../../data/RegistrationRepository.php';

class RegistrationController {
    private $registrationRepo;

    public function __construct($conn) {
        $this->registrationRepo = new RegistrationRepository($conn);
    }

    public function getEventDetails($eventId, $userId) {
        $event = $this->registrationRepo->getEventById($eventId);

        if (!$event) {
            return null;
        }

        $registeredCount = $this->registrationRepo->countActiveRegistrations($eventId);
        $registration = $this->registrationRepo->getRegistration($eventId, $userId);

        if ($event['organizer_id'] == $userId) {
            $state = 'OWN';
        } elseif ($registration && $registration['status'] !== 'CANCELLED') {
            $state = $registration['status'];
        } elseif ($event['status'] === 'CANCELLED') {
            $state = 'EVENT_CANCELLED';
        } elseif ($event['db_now'] < $event['registration_open']) {
            $state = 'NOT_OPEN';
        } elseif ($event['db_now'] > $event['registration_close']) {
            $state = 'CLOSED';
        } elseif ($event['visibility'] === 'PUBLIC' && $registeredCount >= $event['capacity']) {
            $state = 'FULL';
        } else {
            $state = 'OPEN';
        }

        return [
            "event" => $event,
            "interests" => $this->registrationRepo->getEventInterests($eventId),
            "registeredCount" => $registeredCount,
            "registration" => $registration,
            "state" => $state
        ];
    }

    public function register($userId, $eventId) {
        $event = $this->registrationRepo->getEventById($eventId);

        if (!$event) {
            return ["success" => false, "code" => "EVENT_NOT_FOUND", "message" => "Event not found."];
        }
        if ($event['organizer_id'] == $userId) {
            return ["success" => false, "code" => "OWN_EVENT", "message" => "You cannot register for your own event."];
        }
        if ($event['status'] === 'CANCELLED') {
            return ["success" => false, "code" => "EVENT_CANCELLED", "message" => "This event has been cancelled."];
        }
        if ($event['db_now'] < $event['registration_open']) {
            return ["success" => false, "code" => "REGISTRATION_NOT_OPEN", "message" => "Registration has not opened yet."];
        }
        if ($event['db_now'] > $event['registration_close']) {
            return ["success" => false, "code" => "REGISTRATION_CLOSED", "message" => "Registration for this event is closed."];
        }

        $status = ($event['visibility'] === 'INVITE_ONLY') ? 'PENDING' : 'REGISTERED';
        $result = $this->registrationRepo->createRegistration($eventId, $userId, $status);

        if ($result === "DUPLICATE") {
            return ["success" => false, "code" => "ALREADY_REGISTERED", "message" => "You have already registered for this event."];
        }
        if ($result === "FULL") {
            return ["success" => false, "code" => "EVENT_FULL", "message" => "This event is currently full."];
        }
        if ($result !== "OK") {
            return ["success" => false, "code" => "SERVER_ERROR", "message" => "Registration failed. Please try again."];
        }

        return ["success" => true, "code" => $status, "message" => ($status === 'PENDING') ? "Your request has been sent to the organizer." : "You are registered for this event."];
    }

    public function getMyRegistrations($userId) {
        $registrations = $this->registrationRepo->getUserRegistrations($userId);
        $upcoming = [];
        $past = [];

        foreach ($registrations as $registration) {
            if ($registration['is_upcoming']) {
                $upcoming[] = $registration;
            } else {
                $past[] = $registration;
            }
        }

        return ["upcoming" => $upcoming, "past" => $past];
    }

    public function cancel($userId, $eventId) {
        $event = $this->registrationRepo->getEventById($eventId);
        $registration = $this->registrationRepo->getRegistration($eventId, $userId);

        if (!$event || !$registration || $registration['status'] === 'CANCELLED') {
            return ["success" => false, "code" => "NOT_REGISTERED", "message" => "You are not registered for this event."];
        }
        if (!in_array($registration['status'], ['REGISTERED', 'APPROVED', 'PENDING'])) {
            return ["success" => false, "code" => "INVALID_STATUS", "message" => "This registration cannot be cancelled."];
        }
        if ($event['db_now'] > $event['event_date'] . ' ' . $event['start_time']) {
            return ["success" => false, "code" => "EVENT_STARTED", "message" => "This event has already started."];
        }

        if (!$this->registrationRepo->updateStatus($registration['registration_id'], 'CANCELLED')) {
            return ["success" => false, "code" => "SERVER_ERROR", "message" => "Could not cancel registration. Please try again."];
        }

        return ["success" => true, "code" => "CANCELLED", "message" => "Your registration has been cancelled."];
    }
}
?>
