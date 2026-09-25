<?php

class OnboardingRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getSkills() {
        $result = $this->conn->query("SELECT skill_id, skill_name FROM skills ORDER BY skill_name ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getInterests() {
        $result = $this->conn->query("SELECT interest_id, interest_name FROM interests ORDER BY interest_name ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getNetworkingGoals() {
        $result = $this->conn->query("SELECT goal_id, goal_name FROM networking_goals ORDER BY goal_name ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function saveUserProfile($userId, $fullName, $jobTitle, $organization, $industry, $bio, $photoPath = null) {
        $this->conn->begin_transaction();
        try {
            // Update full name in users table
            $stmt1 = $this->conn->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
            $stmt1->bind_param("si", $fullName, $userId);
            $stmt1->execute();

            // Insert or update profiles table
            $stmt2 = $this->conn->prepare("INSERT INTO profiles (user_id, job_title, organization, field, bio, profile_photo, profile_completed) VALUES (?, ?, ?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE job_title = VALUES(job_title), organization = VALUES(organization), field = VALUES(field), bio = VALUES(bio), profile_photo = COALESCE(VALUES(profile_photo), profile_photo), profile_completed = 1");
            
            $stmt2->bind_param("isssss", $userId, $jobTitle, $organization, $industry, $bio, $photoPath);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function saveUserSkills($userId, $skillIds) {
        $this->conn->begin_transaction();
        try {
            $delStmt = $this->conn->prepare("DELETE FROM user_skills WHERE user_id = ?");
            $delStmt->bind_param("i", $userId);
            $delStmt->execute();

            if (!empty($skillIds)) {
                $insStmt = $this->conn->prepare("INSERT INTO user_skills (user_id, skill_id) VALUES (?, ?)");
                foreach ($skillIds as $skillId) {
                    $insStmt->bind_param("ii", $userId, $skillId);
                    $insStmt->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function saveUserInterests($userId, $interestIds) {
        $this->conn->begin_transaction();
        try {
            $delStmt = $this->conn->prepare("DELETE FROM user_interests WHERE user_id = ?");
            $delStmt->bind_param("i", $userId);
            $delStmt->execute();

            if (!empty($interestIds)) {
                $insStmt = $this->conn->prepare("INSERT INTO user_interests (user_id, interest_id) VALUES (?, ?)");
                foreach ($interestIds as $interestId) {
                    $insStmt->bind_param("ii", $userId, $interestId);
                    $insStmt->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function saveUserNetworkingGoals($userId, $goalIds) {
        $this->conn->begin_transaction();
        try {
            $delStmt = $this->conn->prepare("DELETE FROM user_networking_goals WHERE user_id = ?");
            $delStmt->bind_param("i", $userId);
            $delStmt->execute();

            if (!empty($goalIds)) {
                $insStmt = $this->conn->prepare("INSERT INTO user_networking_goals (user_id, goal_id) VALUES (?, ?)");
                foreach ($goalIds as $goalId) {
                    $insStmt->bind_param("ii", $userId, $goalId);
                    $insStmt->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
?>
