<?php
// app/controllers/NotificationController.php
// Controller handling user notifications, unread counting, and mark-as-read actions for Nexus

require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';

class NotificationController {
    private $notificationModel;
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->notificationModel = new NotificationModel();
        $this->userModel         = new UserModel();
    }

    /**
     * Enforce authentication for notification routes.
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'You must be logged in to view notifications.']);
                exit;
            }
            $_SESSION['error_message'] = 'You must be logged in to view notifications.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || isset($_POST['ajax']) || isset($_GET['ajax'])
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    /**
     * Dedicated Notifications Page.
     */
    public function index() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $notifications = $this->notificationModel->getNotifications($userId, $limit, $offset);
        $unreadCount   = $this->notificationModel->getUnreadCount($userId);

        $pageTitle = 'Notifications';
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/notifications/index.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Return JSON data for header notification dropdown.
     */
    public function getDropdown() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        $notifications = $this->notificationModel->getNotifications($userId, 8, 0);
        $unreadCount   = $this->notificationModel->getUnreadCount($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success'       => true,
            'unread_count'  => $unreadCount,
            'notifications' => array_map(function($n) {
                // Determine destination URL
                $destUrl = BASE_URL . '/index.php';
                if ($n['type'] === 'follow') {
                    $destUrl = BASE_URL . '/index.php?action=profile&user_id=' . (int)$n['actor_id'];
                } elseif ($n['related_post_id']) {
                    $destUrl = BASE_URL . '/index.php?action=view_post&id=' . (int)$n['related_post_id'];
                }

                // Format actor avatar
                $avatarUrl = null;
                if (!empty($n['actor_profile_image'])) {
                    $path = __DIR__ . '/../../../public/uploads/avatars/' . basename($n['actor_profile_image']);
                    if (file_exists($path)) {
                        $avatarUrl = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($n['actor_profile_image']));
                    }
                }

                $parts = explode(' ', trim($n['actor_full_name'] ?? 'U'));
                $initials = count($parts) >= 2
                    ? strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1))
                    : strtoupper(mb_substr($n['actor_full_name'] ?? 'U', 0, 2));

                return [
                    'id'               => (int)$n['id'],
                    'type'             => $n['type'],
                    'message'          => $n['message'],
                    'is_read'          => (bool)$n['is_read'],
                    'created_at'       => $n['created_at'],
                    'time_ago'         => self::formatTimeAgo($n['created_at']),
                    'actor_id'         => (int)$n['actor_id'],
                    'actor_name'       => $n['actor_full_name'],
                    'actor_username'   => $n['actor_username'],
                    'actor_avatar'     => $avatarUrl,
                    'actor_initials'   => $initials,
                    'dest_url'         => $destUrl,
                    'post_exists'      => (bool)$n['post_exists']
                ];
            }, $notifications)
        ]);
        exit;
    }

    /**
     * Open a notification: marks as read and redirects to destination.
     */
    public function open() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];
        $notifId = (int)($_GET['id'] ?? 0);

        if ($notifId > 0) {
            $notif = $this->notificationModel->findById($notifId, $userId);
            if ($notif) {
                $this->notificationModel->markAsRead($notifId, $userId);

                if ($notif['type'] === 'follow') {
                    header('Location: ' . BASE_URL . '/index.php?action=profile&user_id=' . (int)$notif['actor_id']);
                    exit;
                }

                if ($notif['related_post_id']) {
                    header('Location: ' . BASE_URL . '/index.php?action=view_post&id=' . (int)$notif['related_post_id']);
                    exit;
                }
            }
        }

        header('Location: ' . BASE_URL . '/index.php?action=notifications');
        exit;
    }

    /**
     * Mark single notification as read (POST / AJAX).
     */
    public function markRead() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            exit;
        }

        if (!validate_csrf_token()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Security Error: Invalid CSRF token.']);
            exit;
        }

        $notifId = (int)($_POST['id'] ?? 0);
        if ($notifId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid notification ID.']);
            exit;
        }

        $success = $this->notificationModel->markAsRead($notifId, $userId);
        $newUnread = $this->notificationModel->getUnreadCount($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success'      => $success,
            'unread_count' => $newUnread
        ]);
        exit;
    }

    /**
     * Mark all notifications as read (POST / AJAX).
     */
    public function markAllRead() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            exit;
        }

        if (!validate_csrf_token()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Security Error: Invalid CSRF token.']);
            exit;
        }

        $success = $this->notificationModel->markAllAsRead($userId);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'      => $success,
                'unread_count' => 0
            ]);
            exit;
        }

        $_SESSION['success_message'] = 'All notifications marked as read.';
        header('Location: ' . BASE_URL . '/index.php?action=notifications');
        exit;
    }

    /**
     * Helper to format relative time ago.
     */
    public static function formatTimeAgo(string $datetime): string {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $m = (int)($diff / 60);
            return $m . ($m === 1 ? ' min ago' : ' mins ago');
        } elseif ($diff < 86400) {
            $h = (int)($diff / 3600);
            return $h . ($h === 1 ? ' hr ago' : ' hrs ago');
        } elseif ($diff < 604800) {
            $d = (int)($diff / 86400);
            return $d . ($d === 1 ? ' day ago' : ' days ago');
        } else {
            return date('M j, Y', $time);
        }
    }
}
