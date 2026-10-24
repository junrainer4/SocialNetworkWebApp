<?php

require_once __DIR__ . '/../../config/database.php';

class NotificationModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function create(int $recipientId, int $actorId, string $type, string $message, ?int $relatedPostId = null, ?int $relatedCommentId = null) {
        $recipientId = (int)$recipientId;
        $actorId = (int)$actorId;

        if ($recipientId <= 0 || $actorId <= 0 || $recipientId === $actorId) {
            return false;
        }

        if ($type === 'follow') {
            $checkSql = "SELECT id FROM notifications
                         WHERE recipient_id = :recipient_id
                           AND actor_id = :actor_id
                           AND type = 'follow'
                           AND is_read = 0
                         LIMIT 1";
            $stmt = $this->db->prepare($checkSql);
            $stmt->execute([
                ':recipient_id' => $recipientId,
                ':actor_id'     => $actorId
            ]);
            if ($stmt->fetch()) {
                return false;
            }
        } elseif ($type === 'like' && $relatedPostId) {
            $checkSql = "SELECT id FROM notifications
                         WHERE recipient_id = :recipient_id
                           AND actor_id = :actor_id
                           AND type = 'like'
                           AND related_post_id = :post_id
                           AND is_read = 0
                         LIMIT 1";
            $stmt = $this->db->prepare($checkSql);
            $stmt->execute([
                ':recipient_id' => $recipientId,
                ':actor_id'     => $actorId,
                ':post_id'      => $relatedPostId
            ]);
            if ($stmt->fetch()) {
                return false;
            }
        }

        $sql = "INSERT INTO notifications (recipient_id, actor_id, type, message, related_post_id, related_comment_id, is_read, created_at)
                VALUES (:recipient_id, :actor_id, :type, :message, :related_post_id, :related_comment_id, 0, NOW())";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            ':recipient_id'        => $recipientId,
            ':actor_id'            => $actorId,
            ':type'                => $type,
            ':message'             => $message,
            ':related_post_id'     => $relatedPostId ? (int)$relatedPostId : null,
            ':related_comment_id'  => $relatedCommentId ? (int)$relatedCommentId : null
        ]);

        return $res ? (int)$this->db->lastInsertId() : false;
    }

    public function getUnreadCount(int $userId): int {
        $sql = "SELECT COUNT(*) FROM notifications WHERE recipient_id = :user_id AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => (int)$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getNotifications(int $userId, int $limit = 30, int $offset = 0): array {
        $sql = "SELECT n.id, n.recipient_id, n.actor_id, n.type, n.message, n.related_post_id, n.related_comment_id, n.is_read, n.created_at,
                       u.username AS actor_username, u.full_name AS actor_full_name, u.profile_image AS actor_profile_image,
                       CASE WHEN p.id IS NOT NULL THEN 1 ELSE 0 END AS post_exists,
                       CASE WHEN c.id IS NOT NULL THEN 1 ELSE 0 END AS comment_exists
                FROM notifications n
                JOIN users u ON n.actor_id = u.id
                LEFT JOIN posts p ON n.related_post_id = p.id
                LEFT JOIN comments c ON n.related_comment_id = c.id
                WHERE n.recipient_id = :user_id
                ORDER BY n.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead(int $notificationId, int $userId): bool {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id AND recipient_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'      => (int)$notificationId,
            ':user_id' => (int)$userId
        ]);
    }

    public function markAllAsRead(int $userId): bool {
        $sql = "UPDATE notifications SET is_read = 1 WHERE recipient_id = :user_id AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => (int)$userId]);
    }

    public function findById(int $notificationId, int $userId) {
        $sql = "SELECT * FROM notifications WHERE id = :id AND recipient_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id'      => (int)$notificationId,
            ':user_id' => (int)$userId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
