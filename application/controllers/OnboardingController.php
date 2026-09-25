<?php
require_once __DIR__ . '/../../data/OnboardingRepository.php';

class OnboardingController {
    private $onboardingRepo;

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

    public function processStep1($userId, $fullName, $jobTitle, $organization, $industry, $bio) {
        if (empty($fullName)) {
            return ["success" => false, "message" => "Full Name is required."];
        }

        $saved = $this->onboardingRepo->saveUserProfile($userId, $fullName, $jobTitle, $organization, $industry, $bio);

        if ($saved) {
            return ["success" => true];
        } else {
            return ["success" => false, "message" => "Failed to save profile. Please try again."];
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
