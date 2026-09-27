<?php
require_once __DIR__ . '/../../data/OnboardingRepository.php';

class OnboardingController {
    private $onboardingRepo;

    const UPLOAD_DIR = 'uploads/profiles/';
    const MAX_PHOTO_BYTES = 5 * 1024 * 1024;
    const PHOTO_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    const INDUSTRIES = [
        'medicine' => 'Medicine & Healthcare', 'technology' => 'Technology', 'business' => 'Business',
        'engineering' => 'Engineering', 'education' => 'Education', 'research' => 'Research',
        'design' => 'Design', 'finance' => 'Finance', 'other' => 'Other',
    ];

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

    public function getProfile($userId) {
        return $this->onboardingRepo->getUserProfile($userId);
    }

    public function updateAccount($userId, $fullName) {
        if ($fullName === '') {
            return ["success" => false, "message" => "Full Name is required."];
        }
        if (mb_strlen($fullName) > 150) {
            return ["success" => false, "message" => "Full Name must be 150 characters or fewer."];
        }
        if (!$this->onboardingRepo->updateFullName($userId, $fullName)) {
            return ["success" => false, "message" => "Failed to save changes. Please try again."];
        }
        return ["success" => true, "message" => "Account details saved."];
    }

    public function updateProfile($userId, $data, $photoFile = null) {
        $profile = $this->onboardingRepo->getUserProfile($userId);
        if (!$profile) {
            return ["success" => false, "message" => "Profile not found."];
        }
        if (!isset(self::INDUSTRIES[$data['field']]) && $data['field'] !== '') {
            return ["success" => false, "message" => "Please choose a valid field."];
        }
        if (mb_strlen($data['bio']) > 300) {
            return ["success" => false, "message" => "Bio must be 300 characters or fewer."];
        }

        $skills = $this->filterIds($data['skills'], array_column($this->getSkills(), 'skill_id'));
        $interests = $this->filterIds($data['interests'], array_column($this->getInterests(), 'interest_id'));
        $goals = $this->filterIds($data['goals'], array_column($this->getNetworkingGoals(), 'goal_id'));

        $photo = $this->storePhoto($photoFile);
        if (!$photo['success']) {
            return $photo;
        }

        $linkedinUrl = trim($data['linkedin_url'] ?? '');
        $otherSocialUrl = trim($data['other_social_url'] ?? '');

        $saved = $this->onboardingRepo->saveUserProfile($userId, $profile['full_name'], $data['job_title'], $data['organization'], $data['field'], $data['bio'], $linkedinUrl, $otherSocialUrl, $photo['path'])
            && $this->onboardingRepo->saveUserSkills($userId, $skills)
            && $this->onboardingRepo->saveUserInterests($userId, $interests)
            && $this->onboardingRepo->saveUserNetworkingGoals($userId, $goals);

        if (!$saved) {
            $this->deletePhoto($photo['path']);
            return ["success" => false, "message" => "Failed to save profile. Please try again."];
        }
        if ($photo['path']) {
            $this->deletePhoto($profile['profile_photo']);
        }
        return ["success" => true, "message" => "Profile saved."];
    }

    public function removePhoto($userId) {
        $profile = $this->onboardingRepo->getUserProfile($userId);
        if (!$profile || !$profile['profile_photo']) {
            return ["success" => true, "message" => "Profile photo removed."];
        }
        if (!$this->onboardingRepo->clearProfilePhoto($userId)) {
            return ["success" => false, "message" => "Failed to remove photo. Please try again."];
        }
        $this->deletePhoto($profile['profile_photo']);
        return ["success" => true, "message" => "Profile photo removed."];
    }


    private function filterIds($submitted, $knownIds) {
        $known = array_map('intval', $knownIds);
        $ids = array_unique(array_map('intval', (array)$submitted));
        return array_values(array_filter($ids, fn($id) => in_array($id, $known, true)));
    }

    public function getProfileCompletion($profile) {
        $checks = [
            $profile['full_name'], $profile['profile_photo'], $profile['job_title'],
            $profile['organization'], $profile['field'], $profile['bio'],
            $profile['skills'], $profile['interests'], $profile['goals'],
        ];
        $filled = count(array_filter($checks, fn($value) => !empty($value)));
        return (int)round($filled / count($checks) * 100);
    }

    public function processStep1($userId, $fullName, $jobTitle, $organization, $industry, $bio, $linkedinUrl, $otherSocialUrl, $photoFile = null) {
        if (empty($fullName)) {
            return ["success" => false, "message" => "Full Name is required."];
        }

        $photo = $this->storePhoto($photoFile);
        if (!$photo['success']) {
            return $photo;
        }

        $saved = $this->onboardingRepo->saveUserProfile($userId, $fullName, $jobTitle, $organization, $industry, $bio, $linkedinUrl, $otherSocialUrl, $photo['path']);

        if ($saved) {
            return ["success" => true];
        } else {
            $this->deletePhoto($photo['path']);
            return ["success" => false, "message" => "Failed to save profile. Please try again."];
        }
    }

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
