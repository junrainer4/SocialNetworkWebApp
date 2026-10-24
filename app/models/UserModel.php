<?php

require_once __DIR__ . '/../../config/database.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function create($username, $passwordHash, $fullName) {

        $user1Exists = (bool)$this->db->query("SELECT id FROM users WHERE id = 1 LIMIT 1")->fetch();
        if (!$user1Exists) {
            $sql = "INSERT INTO users (id, username, password, full_name) VALUES (1, :username, :password, :full_name)";
            $stmt = $this->db->prepare($sql);
            $res = $stmt->execute([
                ':username'  => $username,
                ':password'  => $passwordHash,
                ':full_name' => $fullName
            ]);

            $this->db->exec("ALTER TABLE users AUTO_INCREMENT = 2");
            return $res;
        }

        $sql = "INSERT INTO users (username, password, full_name) VALUES (:username, :password, :full_name)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username'  => $username,
            ':password'  => $passwordHash,
            ':full_name' => $fullName
        ]);
    }

    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    public function usernameExists($username, $excludeUserId = null) {
        if ($excludeUserId !== null) {
            $sql = "SELECT id FROM users WHERE username = :username AND id != :exclude_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':username'   => $username,
                ':exclude_id' => (int)$excludeUserId
            ]);
            return (bool)$stmt->fetch();
        }

        $sql = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        return (bool)$stmt->fetch();
    }

    public function getPasswordHash($id) {
        $sql = "SELECT password FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetchColumn();
    }

    public function updatePassword($id, $newPasswordHash) {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => $newPasswordHash,
            ':id'       => (int)$id
        ]);
    }

    public function findById($id) {
        $sql = "SELECT id, username, full_name, bio, profile_image, cover_photo, privacy_followers, created_at FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getProfileById($id) {
        return $this->findById($id);
    }

    public function updateProfile($id, $fullName, $bio, $username = null) {
        if ($username !== null) {
            $sql = "UPDATE users SET full_name = :full_name, bio = :bio, username = :username WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':full_name' => $fullName,
                ':bio'       => $bio,
                ':username'  => $username,
                ':id'        => (int)$id
            ]);
        }

        $sql = "UPDATE users SET full_name = :full_name, bio = :bio WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':full_name' => $fullName,
            ':bio'       => $bio,
            ':id'        => (int)$id
        ]);
    }

    public function updateUsername($id, $username) {
        $sql = "UPDATE users SET username = :username WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username' => $username,
            ':id'       => (int)$id
        ]);
    }

    public function updateFullName($id, $fullName) {
        $sql = "UPDATE users SET full_name = :full_name WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':full_name' => $fullName,
            ':id'        => (int)$id
        ]);
    }

    public function updateAccountInfo($id, $fullName, $username) {
        $sql = "UPDATE users SET full_name = :full_name, username = :username WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':full_name' => $fullName,
            ':username'  => $username,
            ':id'        => (int)$id
        ]);
    }

    public function updateProfileImage($id, $profileImage) {
        $sql = "UPDATE users SET profile_image = :profile_image WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':profile_image' => $profileImage,
            ':id'            => $id
        ]);
    }

    public function updateCoverPhoto($id, $coverPhoto) {
        $sql = "UPDATE users SET cover_photo = :cover_photo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':cover_photo' => $coverPhoto,
            ':id'          => $id
        ]);
    }

    public function searchUsers($query, $currentUserId = 0, $limit = 50, $followingOnly = false) {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return [];
        }

        $searchTerm = '%' . $trimmed . '%';
        $currentUserId = (int)$currentUserId;
        $limitInt = max(1, min(100, (int)$limit));

        $extraWhere = '';
        if ($followingOnly) {
            $extraWhere = " AND EXISTS (SELECT 1 FROM follows f WHERE f.follower_id = ? AND f.following_id = u.id) ";
        }

        $sql = "SELECT u.id, u.username, u.full_name, u.bio, u.profile_image, u.created_at,
                       (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id) AS posts_count,
                       (SELECT COUNT(*) FROM follows f WHERE f.following_id = u.id) AS followers_count,
                       (SELECT COUNT(*) FROM follows f WHERE f.follower_id = ? AND f.following_id = u.id) > 0 AS is_following
                FROM users u
                WHERE (u.username LIKE ? OR u.full_name LIKE ?) {$extraWhere}
                ORDER BY
                    CASE
                        WHEN u.username = ? OR u.full_name = ? THEN 1
                        WHEN u.username LIKE ? OR u.full_name LIKE ? THEN 2
                        ELSE 3
                    END,
                    u.full_name ASC
                LIMIT {$limitInt}";

        $stmt = $this->db->prepare($sql);
        $starts = $trimmed . '%';

        $params = [$currentUserId, $searchTerm, $searchTerm];
        if ($followingOnly) {
            $params[] = $currentUserId;
        }
        $params[] = $trimmed;
        $params[] = $trimmed;
        $params[] = $starts;
        $params[] = $starts;

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countSearchUsers($query, $currentUserId = 0, $followingOnly = false) {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return 0;
        }

        $searchTerm = '%' . $trimmed . '%';
        $currentUserId = (int)$currentUserId;

        if ($followingOnly) {
            $sql = "SELECT COUNT(*) FROM users u
                    WHERE (u.username LIKE ? OR u.full_name LIKE ?)
                      AND EXISTS (SELECT 1 FROM follows f WHERE f.follower_id = ? AND f.following_id = u.id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm, $currentUserId]);
        } else {
            $sql = "SELECT COUNT(*) FROM users WHERE username LIKE ? OR full_name LIKE ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm]);
        }
        return (int)$stmt->fetchColumn();
    }

    public function getAllUsers() {
        $sql = "SELECT id, username, full_name FROM users ORDER BY full_name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function updatePrivacyFollowers($id, $privacy) {
        $privacy = ($privacy === 'private') ? 'private' : 'public';
        $sql = "UPDATE users SET privacy_followers = :privacy WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':privacy' => $privacy,
            ':id'      => (int)$id
        ]);
    }
}

