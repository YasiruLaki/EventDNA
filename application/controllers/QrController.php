<?php
require_once __DIR__ . '/../../data/QrTokenRepository.php';

require_once __DIR__ . '/../../vendor/autoload.php';

use tekintian\QRcode;

class QrController {
    private $qrRepo;

    // Shared with check-in (Member 3): the QR encodes CHECKIN_PATH?token=<64 hex chars>
    const CHECKIN_PATH = 'presentation/events/checkin/index.php';
    const QR_ECLEVEL_M = 1;     // PHP QR Code error-correction level M (~15%)
    const QR_QUIET_ZONE = 4;    // blank modules around the code, as the QR spec requires

    public function __construct($conn) {
        $this->qrRepo = new QrTokenRepository($conn);
    }

    // Creates a new check-in QR for the event and revokes any previous one. Returns the raw token once.
    public function generateEventQr($organizerId, $eventId) {
        try {
            $this->qrRepo->beginTransaction();

            $event = $this->qrRepo->lockEvent((int)$eventId);
            if (!$event || (int)$event['organizer_id'] !== (int)$organizerId) {
                return $this->fail("Event not found.");
            }
            if ($event['status'] !== 'ACTIVE') {
                return $this->fail("Cancelled events cannot have a check-in QR.");
            }
            $endsAt = $event['event_date'] . ' ' . $event['end_time'];
            if (date('Y-m-d H:i:s') >= $endsAt) {
                return $this->fail("This event has ended.");
            }

            $token = bin2hex(random_bytes(32));
            if (!$this->qrRepo->revokeActiveEventTokens($event['event_id']) ||
                !$this->qrRepo->createEventToken($event['event_id'], hash('sha256', $token), $endsAt)) {
                throw new Exception("QR insert failed");
            }

            $this->qrRepo->commit();
        } catch (Exception $e) {
            $this->qrRepo->rollback();
            return ["success" => false, "message" => "Could not generate the QR code. Please try again."];
        }

        return ["success" => true, "token" => $token];
    }

    // State of the event's QR for the organizer page. Only the hash is stored, so the image can be shown
    // only while the caller still holds the raw token (e.g. in the organizer's session).
    public function getEventQr($eventId, $rawToken = null) {
        $active = $this->qrRepo->getActiveEventToken((int)$eventId);
        if (!$active) {
            return ["active" => false, "created_at" => null, "expires_at" => null, "link" => null, "svg" => null];
        }

        $qr = [
            "active" => true,
            "created_at" => $active['created_at'],
            "expires_at" => $active['expires_at'],
            "link" => null,
            "svg" => null,
        ];
        if (is_string($rawToken) && hash_equals($active['token_hash'], hash('sha256', $rawToken))) {
            $qr["link"] = $this->checkinUrl($rawToken);
            $qr["svg"] = $this->renderSvg($qr["link"]);
        }
        return $qr;
    }

    public function checkinUrl($rawToken) {
        return $this->baseUrl() . '/' . self::CHECKIN_PATH . '?token=' . rawurlencode($rawToken);
    }

    // APP_URL in .env (e.g. http://192.168.1.10/EventDNA) lets phones on the LAN reach the link;
    // otherwise the link uses the host the organizer is browsing on.
    private function baseUrl() {
        $env = parse_ini_file(__DIR__ . '/../../.env');
        if (!empty($env['APP_URL'])) {
            return rtrim($env['APP_URL'], '/');
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $docRoot = str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/../..'));
        $path = ($docRoot !== '' && stripos($projectRoot, $docRoot) === 0) ? substr($projectRoot, strlen($docRoot)) : '/EventDNA';

        return $scheme . '://' . $host . '/' . trim($path, '/');
    }

    // PHP QR Code builds the module matrix; drawing it as SVG avoids needing the GD extension
    private function renderSvg($text) {
        $rows = QRcode::text($text, false, self::QR_ECLEVEL_M, 1, 0);
        $size = count($rows) + 2 * self::QR_QUIET_ZONE;

        $path = '';
        foreach ($rows as $y => $row) {
            // One rectangle per horizontal run of dark modules keeps the SVG small
            preg_match_all('/1+/', $row, $runs, PREG_OFFSET_CAPTURE);
            foreach ($runs[0] as [$run, $x]) {
                $path .= 'M' . ($x + self::QR_QUIET_ZONE) . ',' . ($y + self::QR_QUIET_ZONE) . 'h' . strlen($run) . 'v1h-' . strlen($run) . 'z';
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $size . ' ' . $size . '" shape-rendering="crispEdges" role="img" aria-label="Event check-in QR code">'
            . '<rect width="100%" height="100%" fill="#fff"/><path fill="#000" d="' . $path . '"/></svg>';
    }

    private function fail($message) {
        $this->qrRepo->rollback();
        return ["success" => false, "message" => $message];
    }
}
?>
