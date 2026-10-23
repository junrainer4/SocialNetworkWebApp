-- Migration: Create notifications table for Nexus
-- Date: 2026-10-23

CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `recipient_id` INT NOT NULL COMMENT 'User who receives the notification',
    `actor_id` INT NOT NULL COMMENT 'User who performed the action',
    `type` VARCHAR(40) NOT NULL COMMENT 'follow | like | comment',
    `message` VARCHAR(255) DEFAULT NULL COMMENT 'Human-readable action description',
    `related_post_id` INT DEFAULT NULL,
    `related_comment_id` INT DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_notif_recipient` (`recipient_id`),
    KEY `idx_notif_unread` (`recipient_id`, `is_read`),
    KEY `idx_notif_created` (`created_at`),
    KEY `idx_notif_actor` (`actor_id`),
    KEY `idx_notif_post` (`related_post_id`),
    KEY `idx_notif_comment` (`related_comment_id`),
    CONSTRAINT `fk_notif_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notif_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notif_post` FOREIGN KEY (`related_post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_notif_comment` FOREIGN KEY (`related_comment_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
