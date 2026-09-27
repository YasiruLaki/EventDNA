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

    // Account + profile details for the settings page. Returns null if the user doesn't exist.
    public function getUserProfile($userId) {
        $stmt = $this->conn->prepare("
            SELECT u.full_name, u.email, r.role_name,
                   p.job_title, p.organization, p.field, p.bio, p.linkedin_url, p.other_social_url, p.profile_photo
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.user_id
            LEFT JOIN roles r ON r.role_id = ur.role_id
            LEFT JOIN profiles p ON p.user_id = u.user_id
            WHERE u.user_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_assoc();
        if (!$profile) {
            return null;
        }

        // Each list is keyed by ID: [id => name]
        $profile['skills'] = $this->getSelections("SELECT s.skill_id, s.skill_name FROM user_skills us JOIN skills s ON s.skill_id = us.skill_id WHERE us.user_id = ? ORDER BY s.skill_name", $userId);
        $profile['interests'] = $this->getSelections("SELECT i.interest_id, i.interest_name FROM user_interests ui JOIN interests i ON i.interest_id = ui.interest_id WHERE ui.user_id = ? ORDER BY i.interest_name", $userId);
        $profile['goals'] = $this->getSelections("SELECT g.goal_id, g.goal_name FROM user_networking_goals ug JOIN networking_goals g ON g.goal_id = ug.goal_id WHERE ug.user_id = ? ORDER BY g.goal_name", $userId);

        return $profile;
    }

    private function getSelections($sql, $userId) {
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_NUM);
        return array_column($rows, 1, 0);
    }

    public function updateFullName($userId, $fullName) {
        $stmt = $this->conn->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
        $stmt->bind_param("si", $fullName, $userId);
        return $stmt->execute();
    }

    public function clearProfilePhoto($userId) {
        $stmt = $this->conn->prepare("UPDATE profiles SET profile_photo = NULL WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    public function saveUserProfile($userId, $fullName, $jobTitle, $organization, $industry, $bio, $linkedinUrl, $otherSocialUrl, $photoPath = null) {
        $this->conn->begin_transaction();
        try {
            // Update full name in users table
            $stmt1 = $this->conn->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
            $stmt1->bind_param("si", $fullName, $userId);
            $stmt1->execute();

            // Insert or update profiles table
            $stmt2 = $this->conn->prepare("INSERT INTO profiles (user_id, job_title, organization, field, bio, linkedin_url, other_social_url, profile_photo, profile_completed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE job_title = VALUES(job_title), organization = VALUES(organization), field = VALUES(field), bio = VALUES(bio), linkedin_url = VALUES(linkedin_url), other_social_url = VALUES(other_social_url), profile_photo = COALESCE(VALUES(profile_photo), profile_photo), profile_completed = 1");
            
            $stmt2->bind_param("isssssss", $userId, $jobTitle, $organization, $industry, $bio, $linkedinUrl, $otherSocialUrl, $photoPath);
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
