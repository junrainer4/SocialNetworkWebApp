<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';

class InsightsController {
    private $userModel;
    private $postModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
        $this->postModel = new PostModel();
    }

    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to access Community Search.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    public function index() {
        $this->requireAuth();

        $currentUserId = (int)$_SESSION['user_id'];
        $errorMessage = '';

        $query = trim($_GET['q'] ?? '');
        $type = strtolower(trim($_GET['type'] ?? 'users'));

        if (!in_array($type, ['users', 'posts'], true)) {
            $type = 'users';
        }

        $rawFilter = strtolower(trim($_GET['filter'] ?? ''));
        if ($type === 'posts') {
            $validPostFilters = ['newest', 'most_liked', 'photos'];
            $filter = in_array($rawFilter, $validPostFilters, true) ? $rawFilter : 'newest';
        } else {
            $validUserFilters = ['all', 'following'];
            $filter = in_array($rawFilter, $validUserFilters, true) ? $rawFilter : 'all';
        }

        $users = [];
        $posts = [];
        $totalCount = 0;
        $hasSearched = ($query !== '');

        if ($hasSearched) {
            try {
                if ($type === 'users') {
                    $followingOnly = ($filter === 'following');
                    $users = $this->userModel->searchUsers($query, $currentUserId, 50, $followingOnly);
                    $totalCount = $this->userModel->countSearchUsers($query, $currentUserId, $followingOnly);
                } else {
                    $mediaOnly = ($filter === 'photos');
                    $sortOrder = ($filter === 'most_liked') ? 'most_liked' : 'newest';
                    $posts = $this->postModel->searchPosts($query, $sortOrder, $currentUserId, null, 50, $mediaOnly);
                    $totalCount = $this->postModel->countSearchPosts($query, $mediaOnly);
                }
            } catch (Exception $e) {
                error_log("Community Search execution error: " . $e->getMessage());
                $errorMessage = "An error occurred while retrieving search results. Please try again.";
            }
        }

        $pageTitle = 'Community Search';

        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/insights/index.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}
