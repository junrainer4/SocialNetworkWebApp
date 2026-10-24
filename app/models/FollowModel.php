<?php

require_once __DIR__ . '/../../config/database.php';

class FollowModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function follow($followerId, $followingId) {
        $followerId = (int)$followerId;
        $followingId = (int)$followingId;

        if ($followerId <= 0 || $followingId <= 0 || $followerId === $followingId) {
            return false;
        }

        $sql = "INSERT IGNORE INTO follows (follower_id, following_id, created_at) VALUES (:follower_id, :following_id, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':follower_id'  => $followerId,
            ':following_id' => $followingId
        ]);
    }

    public function unfollow($followerId, $followingId) {
        $followerId = (int)$followerId;
        $followingId = (int)$followingId;

        if ($followerId <= 0 || $followingId <= 0 || $followerId === $followingId) {
            return false;
        }

        $sql = "DELETE FROM follows WHERE follower_id = :follower_id AND following_id = :following_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':follower_id'  => $followerId,
            ':following_id' => $followingId
        ]);
    }

    public function isFollowing($followerId, $followingId) {
        $followerId = (int)$followerId;
        $followingId = (int)$followingId;

        if ($followerId <= 0 || $followingId <= 0) {
            return false;
        }

        $sql = "SELECT id FROM follows WHERE follower_id = :follower_id AND following_id = :following_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':follower_id'  => $followerId,
            ':following_id' => $followingId
        ]);
        return (bool)$stmt->fetch();
    }

    public function getFollowersCount($userId) {
        $sql = "SELECT COUNT(*) FROM follows WHERE following_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => (int)$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getFollowingCount($userId) {
        $sql = "SELECT COUNT(*) FROM follows WHERE follower_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => (int)$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getFollowers($userId) {
        $sql = "SELECT u.id, u.username, u.full_name, u.profile_image, u.bio, f.created_at AS followed_at
                FROM follows f
                JOIN users u ON f.follower_id = u.id
                WHERE f.following_id = :user_id
                ORDER BY f.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => (int)$userId]);
        return $stmt->fetchAll();
    }

    public function getFollowing($userId) {
        $sql = "SELECT u.id, u.username, u.full_name, u.profile_image, u.bio, f.created_at AS followed_at
                FROM follows f
                JOIN users u ON f.following_id = u.id
                WHERE f.follower_id = :user_id
                ORDER BY f.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => (int)$userId]);
        return $stmt->fetchAll();
    }
}
