<?php

require_once __DIR__ . '/../models/FollowModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class FollowController {
    private $followModel;
    private $userModel;
    private $notificationModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->followModel       = new FollowModel();
        $this->userModel         = new UserModel();
        $this->notificationModel = new NotificationModel();
    }

    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            if (!empty($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'You must be logged in to access connections.']);
                exit;
            }
            $_SESSION['error_message'] = 'You must be logged in to view friends and connections.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    public function friends() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        $activeTab = ($_GET['tab'] ?? 'followers') === 'following' ? 'following' : 'followers';

        $rawFollowers = $this->followModel->getFollowers($userId);
        $rawFollowing = $this->followModel->getFollowing($userId);

        $followersCount = count($rawFollowers);
        $followingCount = count($rawFollowing);

        $followers = [];
        foreach ($rawFollowers as $f) {
            $f['is_following'] = $this->followModel->isFollowing($userId, (int)$f['id']);
            $followers[] = $f;
        }

        $following = [];
        foreach ($rawFollowing as $f) {
            $f['is_following'] = true;
            $following[] = $f;
        }

        $pageTitle = 'Friends';
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/friends/index.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    public function toggle() {
        $this->requireAuth();

        $isAjax = !empty($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
                exit;
            }
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        if (!validate_csrf_token()) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Security error: Invalid CSRF token.']);
                exit;
            }
            $_SESSION['error_message'] = 'Security error: Invalid CSRF token.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        $followerId = (int)$_SESSION['user_id'];
        $followingId = (int)($_POST['user_id'] ?? 0);

        if ($followingId <= 0 || $followerId === $followingId) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Invalid user or cannot follow yourself.']);
                exit;
            }
            $_SESSION['error_message'] = 'Invalid operation.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        $targetUser = $this->userModel->findById($followingId);
        if (!$targetUser) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'User not found.']);
                exit;
            }
            $_SESSION['error_message'] = 'User not found.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        $isCurrentlyFollowing = $this->followModel->isFollowing($followerId, $followingId);

        if ($isCurrentlyFollowing) {
            $this->followModel->unfollow($followerId, $followingId);
            $newStatus = false;
        } else {
            $this->followModel->follow($followerId, $followingId);
            $newStatus = true;

            $this->notificationModel->create(
                $followingId,
                $followerId,
                'follow',
                'started following you.'
            );
        }

        $followersCount = $this->followModel->getFollowersCount($followingId);
        $followingCount = $this->followModel->getFollowingCount($followingId);
        $isFollowedBy   = $this->followModel->isFollowing($followingId, $followerId);

        $myFollowingCount = $this->followModel->getFollowingCount($followerId);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'            => true,
                'is_following'       => $newStatus,
                'is_followed_by'     => $isFollowedBy,
                'followers_count'    => $followersCount,
                'following_count'    => $followingCount,
                'my_following_count' => $myFollowingCount,
                'user_id'            => $followingId
            ]);
            exit;
        }

        $redirect = $_POST['redirect'] ?? '';
        if ($redirect === 'friends') {
            header('Location: ' . BASE_URL . '/index.php?action=friends');
            exit;
        }

        header('Location: ' . BASE_URL . '/index.php?action=profile&user_id=' . $followingId);
        exit;
    }
}
