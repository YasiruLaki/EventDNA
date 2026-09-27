<?php
require_once __DIR__ . '/../../data/SettingsRepository.php';

class SettingsController {
    private $settingsRepo;

    const VISIBILITY_OPTIONS = ['MEMBERS', 'CONNECTIONS'];

    public function __construct($conn) {
        $this->settingsRepo = new SettingsRepository($conn);
    }

    public function getSettings($userId) {
        return $this->settingsRepo->getSettings($userId);
    }

    // $enabled: the submitted notification column names that are switched on
    public function updateNotifications($userId, $enabled) {
        $enabled = (array)$enabled;
        $flags = [];
        foreach (SettingsRepository::NOTIFICATION_COLUMNS as $column) {
            $flags[$column] = in_array($column, $enabled, true) ? 1 : 0;
        }
        if (!$this->settingsRepo->saveNotifications($userId, $flags)) {
            return ["success" => false, "message" => "Failed to save notification settings. Please try again."];
        }
        return ["success" => true, "message" => "Notification settings saved."];
    }

    public function updatePrivacy($userId, $visibility) {
        if (!in_array($visibility, self::VISIBILITY_OPTIONS, true)) {
            return ["success" => false, "message" => "Please choose a valid visibility option."];
        }
        if (!$this->settingsRepo->saveProfileVisibility($userId, $visibility)) {
            return ["success" => false, "message" => "Failed to save privacy settings. Please try again."];
        }
        return ["success" => true, "message" => "Privacy settings saved."];
    }
}
?>
