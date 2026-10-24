<?php
// app/controllers/SearchController.php
// Controller handling search for users and posts, with filtering

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

    /**
     * Enforce authentication for search.
     * Prevents guest users from accessing protected social search results.
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to search and view social content.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Main search, filter, and reporting handler.
     */
    public function index() {
        $this->requireAuth();

        $currentUserId = (int)$_SESSION['user_id'];
        $rawQuery = $_GET['q'] ?? '';
        $query = trim($rawQuery);
        $type = trim($_GET['type'] ?? 'all'); // 'all', 'users', 'posts'
        $filter = trim($_GET['filter'] ?? 'newest'); // 'newest', 'oldest', 'most_liked', 'most_commented'
        $authorId = isset($_GET['author_id']) && $_GET['author_id'] !== '' ? (int)$_GET['author_id'] : null;

        // Valid types
        $validTypes = ['all', 'users', 'posts'];
        if (!in_array($type, $validTypes, true)) {
            $type = 'all';
        }

        // Valid post sorting filters (Step 11 & Step 12 requirements)
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
            // Execute search queries inside models using prepared statements
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
            // Always compute summary statistics based on current search query and author filter
            $summaryStats = $this->postModel->getSearchSummaryStats($query, $authorId);
        } else {
            // Default summary stats for overall database dataset when landing on search page
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

        // Render search view wrapped in header and footer
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/search/results.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Live search API endpoint for header dropdown real-time suggestions.
     * Returns JSON list of matching users and posts.
     */
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

        // Search users
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

        // Search posts
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
