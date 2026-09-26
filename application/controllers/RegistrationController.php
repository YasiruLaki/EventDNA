<?php
require_once __DIR__ . '/../../data/RegistrationRepository.php';

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class RegistrationController {
    private $registrationRepo;

    const SEAT_STATUSES = ['PENDING', 'APPROVED', 'REGISTERED'];
    const BLOCKED_STATUSES = ['REJECTED', 'REMOVED'];

    public function __construct($conn) {
        $this->registrationRepo = new RegistrationRepository($conn);
    }

    // Registers the user for an event. Public events are confirmed straight away; invite-only ones wait for approval.
    public function register($userId, $eventId) {
        $userId = (int)$userId;
        $eventId = (int)$eventId;
        if ($userId <= 0) {
            return ["success" => false, "message" => "Please log in to register."];
        }

        try {
            $this->registrationRepo->beginTransaction();

            // Holding the event row lock means two people cannot both take the last seat
            $event = $this->registrationRepo->lockEvent($eventId);
            if (!$event) {
                return $this->fail("Event not found.");
            }
            if ($event['status'] !== 'ACTIVE') {
                return $this->fail("This event is no longer accepting registrations.");
            }

            $now = date('Y-m-d H:i:s');
            if ($now < $event['registration_open']) {
                return $this->fail("Registration has not opened yet.");
            }
            if ($now > $event['registration_close']) {
                return $this->fail("Registration for this event has closed.");
            }

            if ($this->registrationRepo->countSeatsTaken($eventId) >= (int)$event['capacity']) {
                return $this->fail("Sorry, this event is fully booked.");
            }

            $existing = $this->registrationRepo->getRegistration($eventId, $userId);
            if ($existing && in_array($existing['status'], self::SEAT_STATUSES, true)) {
                return $this->fail("You are already registered for this event.");
            }
            if ($existing && in_array($existing['status'], self::BLOCKED_STATUSES, true)) {
                return $this->fail("You cannot register for this event.");
            }

            $status = $event['visibility'] === 'INVITE_ONLY' ? 'PENDING' : 'REGISTERED';
            $saved = $existing
                ? $this->registrationRepo->reactivateRegistration($existing['registration_id'], $status)
                : $this->registrationRepo->createRegistration($eventId, $userId, $status);
            if (!$saved) {
                throw new Exception("Insert failed");
            }

            $this->registrationRepo->commit();
        } catch (Exception $e) {
            $this->registrationRepo->rollback();
            return ["success" => false, "message" => "Registration failed. Please try again."];
        }

        $this->sendConfirmation($userId, $event, $status);

        return ["success" => true, "status" => $status];
    }

    // Gives up the user's seat. Allowed until the event starts, as long as they have not checked in.
    public function cancelRegistration($userId, $eventId) {
        $userId = (int)$userId;
        $eventId = (int)$eventId;

        try {
            $this->registrationRepo->beginTransaction();

            $event = $this->registrationRepo->lockEvent($eventId);
            $registration = $event ? $this->registrationRepo->getRegistration($eventId, $userId) : null;
            if (!$registration || !in_array($registration['status'], self::SEAT_STATUSES, true)) {
                return $this->fail("You are not registered for this event.");
            }
            if ($event['status'] !== 'ACTIVE') {
                return $this->fail("This event has been cancelled.");
            }
            if (date('Y-m-d H:i:s') >= $event['event_date'] . ' ' . $event['start_time']) {
                return $this->fail("Registrations cannot be cancelled after the event has started.");
            }
            if ($this->registrationRepo->isCheckedIn($eventId, $userId)) {
                return $this->fail("You have already checked in to this event.");
            }

            if (!$this->registrationRepo->cancelRegistration($registration['registration_id'])) {
                throw new Exception("Update failed");
            }

            $this->registrationRepo->commit();
        } catch (Exception $e) {
            $this->registrationRepo->rollback();
            return ["success" => false, "message" => "Could not cancel your registration. Please try again."];
        }

        $this->notify($userId, 'REGISTRATION_CANCELLED', 'Registration cancelled',
            "You have cancelled your registration for " . $event['name'] . ".", $eventId);

        return ["success" => true];
    }

    private function fail($message) {
        $this->registrationRepo->rollback();
        return ["success" => false, "message" => $message];
    }

    // Sends an in-app notification and an email. The registration is already saved, so failures here are ignored.
    private function sendConfirmation($userId, $event, $status) {
        $when = date('M j, Y', strtotime($event['event_date'])) . ' at ' . date('g:i A', strtotime($event['start_time']));

        if ($status === 'PENDING') {
            $type = 'REGISTRATION_PENDING';
            $title = 'Registration request sent';
            $message = "Your request to join " . $event['name'] . " on $when is waiting for the organizer's approval.";
        } else {
            $type = 'REGISTRATION_CONFIRMED';
            $title = "You're registered";
            $message = "You're registered for " . $event['name'] . " on $when at " . $event['location'] . ".";
        }
        $this->notify($userId, $type, $title, $message, $event['event_id']);

        $user = $this->registrationRepo->getUserContact($userId);
        if ($user) {
            $name = htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8');
            $body = "Hi $name,<br><br>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
                . ($status === 'PENDING' ? "<br><br>We'll let you know once the organizer responds." : "<br><br>Scan the event QR code at the entrance to check in.")
                . "<br><br>— EventDNA";
            $this->sendEmail($user['email'], $user['full_name'], $title . ' - ' . $event['name'], $body);
        }
    }

    private function notify($userId, $type, $title, $message, $eventId) {
        try {
            $this->registrationRepo->createNotification($userId, $type, $title, $message, 'EVENT', $eventId);
        } catch (Exception $e) {
            // A missing notification should not undo the registration
        }
    }

    private function sendEmail($toEmail, $toName, $subject, $body) {
        $env = parse_ini_file(__DIR__ . '/../../.env');

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $env['MAIL_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $env['MAIL_USERNAME'] ?? '';
            $mail->Password = $env['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $env['MAIL_PORT'] ?? 587;

            $fromEmail = $env['MAIL_FROM_ADDRESS'] ?? $env['MAIL_USERNAME'];
            $fromName = $env['MAIL_FROM_NAME'] ?? 'EventDNA';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (MailException $e) {
            return false;
        }
    }
}
?>
