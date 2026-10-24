<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/FollowModel.php';

class SearchController {
    private $userModel;
    private $postModel;
    private $followModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
        $this->postModel = new PostModel();
        $this->followModel = new FollowModel();
    }

    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to search and view social content.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    public function index() {
        $this->requireAuth();

        $currentUserId = (int)$_SESSION['user_id'];
        $rawQuery = $_GET['q'] ?? '';
        $query = trim($rawQuery);
        $type = trim($_GET['type'] ?? 'all');
        $filter = trim($_GET['filter'] ?? 'newest');
        $authorId = isset($_GET['author_id']) && $_GET['author_id'] !== '' ? (int)$_GET['author_id'] : null;

        $validTypes = ['all', 'users', 'posts'];
        if (!in_array($type, $validTypes, true)) {
            $type = 'all';
        }

        $validFilters = ['newest', 'oldest', 'most_liked', 'most_commented'];
        if (!in_array($filter, $validFilters, true)) {
            $filter = 'newest';
        }

        $users = [];
        $posts = [];
        $summaryStats = [
            'total_posts'   => 0,
            'total_authors' => 0,
            'total_comments'=> 0,
            'total_likes'   => 0
        ];
        $allAuthors = $this->userModel->getAllUsers();

        $isEmptySearch = ($query === '' && empty($authorId));

        if (!$isEmptySearch) {

            if ($type === 'all' || $type === 'users') {
                $users = $this->userModel->searchUsers($query);
                if (!empty($users) && $currentUserId) {
                    foreach ($users as &$u) {
                        $uId = (int)$u['id'];
                        if ($uId !== $currentUserId) {
                            $u['is_following'] = $this->followModel->isFollowing($currentUserId, $uId);
                            $u['is_followed_by'] = $this->followModel->isFollowing($uId, $currentUserId);
                        } else {
                            $u['is_following'] = false;
                            $u['is_followed_by'] = false;
                        }
                    }
                    unset($u);
                }
            }

            if ($type === 'all' || $type === 'posts') {
                $posts = $this->postModel->searchPosts($query, $filter, $currentUserId, $authorId);
            }

            $summaryStats = $this->postModel->getSearchSummaryStats($query, $authorId);
        } else {

            $summaryStats = $this->postModel->getSearchSummaryStats('', null);
        }

        $selectedAuthorName = '';
        if ($authorId) {
            $authorObj = $this->userModel->findById($authorId);
            if ($authorObj) {
                $selectedAuthorName = $authorObj['full_name'];
            }
        }

        $pageTitle = $isEmptySearch
            ? 'Search & Reports'
            : 'Search: ' . ($query !== '' ? $query : ($selectedAuthorName ? 'Author: ' . $selectedAuthorName : 'Filtered Results'));

        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/search/results.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    public function liveSearch() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'users' => [], 'posts' => []]);
            exit;
        }

        $query = trim($_GET['q'] ?? '');
        if ($query === '' || strlen($query) < 1) {
            echo json_encode(['success' => true, 'query' => '', 'users' => [], 'posts' => []]);
            exit;
        }

        $currentUserId = (int)$_SESSION['user_id'];

        $rawUsers = $this->userModel->searchUsers($query);
        $users = [];
        $topUsers = array_slice($rawUsers, 0, 5);
        foreach ($topUsers as $u) {
            $avatarUrl = null;
            if (!empty($u['profile_image'])) {
                $avatarUrl = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($u['profile_image']));
            }
            $parts = explode(' ', trim($u['full_name']));
            $initials = (count($parts) >= 2)
                ? strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1))
                : strtoupper(substr($u['full_name'], 0, 2));

            $users[] = [
                'id'        => (int)$u['id'],
                'username'  => $u['username'],
                'full_name' => $u['full_name'],
                'avatar'    => $avatarUrl,
                'initials'  => $initials
            ];
        }

        $rawPosts = $this->postModel->searchPosts($query, 'newest', $currentUserId);
        $posts = [];
        $topPosts = array_slice($rawPosts, 0, 5);
        foreach ($topPosts as $p) {
            $cleanContent = strip_tags($p['content']);
            $snippet = substr($cleanContent, 0, 85);
            if (strlen($cleanContent) > 85) {
                $snippet .= '...';
            }
            $posts[] = [
                'id'              => (int)$p['id'],
                'snippet'         => $snippet,
                'author_name'     => $p['full_name'] ?? 'User',
                'author_username' => $p['username'] ?? '',
                'created_at'      => !empty($p['created_at']) ? date('M j', strtotime($p['created_at'])) : ''
            ];
        }

        echo json_encode([
            'success' => true,
            'query'   => $query,
            'users'   => $users,
            'posts'   => $posts
        ]);
        exit;
    }
}
