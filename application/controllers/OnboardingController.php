<?php
require_once __DIR__ . '/../../data/OnboardingRepository.php';

class OnboardingController {
    private $onboardingRepo;

    const UPLOAD_DIR = 'uploads/profiles/';
    const MAX_PHOTO_BYTES = 5 * 1024 * 1024;
    const PHOTO_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function __construct($conn) {
        $this->onboardingRepo = new OnboardingRepository($conn);
    }

    public function getSkills() {
        return $this->onboardingRepo->getSkills();
    }

    public function getInterests() {
        return $this->onboardingRepo->getInterests();
    }

    public function getNetworkingGoals() {
        return $this->onboardingRepo->getNetworkingGoals();
    }

    public function processStep1($userId, $fullName, $jobTitle, $organization, $industry, $bio, $photoFile = null) {
        if (empty($fullName)) {
            return ["success" => false, "message" => "Full Name is required."];
        }

        $photo = $this->storePhoto($photoFile);
        if (!$photo['success']) {
            return $photo;
        }

        $saved = $this->onboardingRepo->saveUserProfile($userId, $fullName, $jobTitle, $organization, $industry, $bio, $photo['path']);

        if ($saved) {
            return ["success" => true];
        } else {
            $this->deletePhoto($photo['path']);
            return ["success" => false, "message" => "Failed to save profile. Please try again."];
        }
    }

    // Saves an optional profile photo. "path" is null when no file was uploaded.
    private function storePhoto($file) {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ["success" => true, "path" => null];
        }
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ["success" => false, "message" => "Profile photo must be 5 MB or smaller."];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ["success" => false, "message" => "Profile photo upload failed (error code " . $file['error'] . ")."];
        }
        if ($file['size'] > self::MAX_PHOTO_BYTES) {
            return ["success" => false, "message" => "Profile photo must be 5 MB or smaller."];
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::PHOTO_TYPES[$mime])) {
            return ["success" => false, "message" => "Profile photo must be a JPG, PNG or WebP image."];
        }

        $dir = __DIR__ . '/../../' . self::UPLOAD_DIR;
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return ["success" => false, "message" => "Could not save the profile photo."];
        }

        $filename = bin2hex(random_bytes(16)) . '.' . self::PHOTO_TYPES[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            return ["success" => false, "message" => "Could not save the profile photo."];
        }

        return ["success" => true, "path" => self::UPLOAD_DIR . $filename];
    }

    private function deletePhoto($path) {
        if ($path && strpos($path, self::UPLOAD_DIR) === 0) {
            $full = __DIR__ . '/../../' . $path;
            if (is_file($full)) {
                unlink($full);
            }
        }
    }

    public function processStep2($userId, $skills, $interests) {
        // skills and interests should be arrays of IDs
        $skillsSaved = $this->onboardingRepo->saveUserSkills($userId, $skills);
        $interestsSaved = $this->onboardingRepo->saveUserInterests($userId, $interests);

        if ($skillsSaved && $interestsSaved) {
            return ["success" => true];
        } else {
            return ["success" => false, "message" => "Failed to save skills and interests. Please try again."];
        }
    }

    public function processStep3($userId, $goals) {
        // goals should be an array of IDs
        $goalsSaved = $this->onboardingRepo->saveUserNetworkingGoals($userId, $goals);

        if ($goalsSaved) {
            return ["success" => true];
        } else {
            return ["success" => false, "message" => "Failed to save networking goals. Please try again."];
        }
    }
}
?>
