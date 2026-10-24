<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $base = preg_replace('#/public.*$#', '', $scriptDir);
    define('BASE_URL', rtrim($base, '/'));
}

$currentAction = $_GET['action'] ?? 'home';
$isLoggedIn = isset($_SESSION['user_id']);

if (empty($_SESSION['csrf_token'])) {
    if (function_exists('csrf_token')) {
        csrf_token();
    } else {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

$userInitials = 'U';
$userAvatarPath = null;
if ($isLoggedIn) {

    if (!isset($_SESSION['profile_image_synced']) || $_SESSION['profile_image_synced'] < (time() - 60)) {
        require_once __DIR__ . '/../../models/UserModel.php';
        $headerUserModel = new UserModel();
        $currentUserData = $headerUserModel->findById((int)$_SESSION['user_id']);
        if ($currentUserData) {
            $_SESSION['profile_image'] = $currentUserData['profile_image'] ?? null;
            $_SESSION['full_name'] = $currentUserData['full_name'] ?? $_SESSION['full_name'];
            $_SESSION['profile_image_synced'] = time();
        }
    }

    $name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        $userInitials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
    } else {
        $userInitials = strtoupper(substr($name, 0, 2));
    }

    if (!empty($_SESSION['profile_image'])) {
        $uploadedPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($_SESSION['profile_image']);
        if (file_exists($uploadedPath)) {
            $userAvatarPath = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($_SESSION['profile_image']));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<<<<<<< HEAD
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Unify' : 'Unify'; ?></title>
=======
    <title><?= isset($pageTitle) ? (strpos($pageTitle, 'Nexus') !== false ? htmlspecialchars($pageTitle) : htmlspecialchars($pageTitle) . ' - Nexus') : 'Nexus - The SMCC Community Hub'; ?></title>
>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script>window.BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/public/assets/js/theme.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?>">
</head>
<body class="<?= $isLoggedIn ? 'app-logged-in' : 'app-logged-out' ?>">

<div class="app-layout">

    <div class="app-main">

        <header class="app-header">
            <div class="header-container d-flex align-items-center justify-content-between w-100">
                <?php if ($isLoggedIn): ?>
<<<<<<< HEAD

                    <div class="header-left d-flex align-items-center flex-shrink-0">
                        <a href="<?= BASE_URL ?>/" class="brand-link d-flex align-items-center gap-2" id="headerLogoBtn" title="Unify Home">
                            <div class="brand-logo-icon flex-shrink-0">
                                <svg width="42" height="42" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="unify-logo-svg">
                                    <defs>
                                        <linearGradient id="unifyGradPrimary" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#0062ff"/>
                                            <stop offset="50%" stop-color="#3b82f6"/>
                                            <stop offset="100%" stop-color="#00d2ff"/>
                                        </linearGradient>
                                        <linearGradient id="unifyGradAccent" x1="100%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#00f2fe"/>
                                            <stop offset="100%" stop-color="#7c3aed"/>
                                        </linearGradient>
                                        <filter id="unifyGlow" x="-15%" y="-15%" width="130%" height="130%">
                                            <feDropShadow dx="0" dy="4" stdDeviation="5" flood-color="#0062ff" flood-opacity="0.32"/>
                                        </filter>
                                    </defs>

                                    <rect width="48" height="48" rx="14" fill="url(#unifyGradPrimary)" filter="url(#unifyGlow)"/>

                                    <path d="M15 13V26C15 30.9706 19.0294 35 24 35C28.9706 35 33 30.9706 33 26V13" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M21 14V25C21 26.6569 22.3431 28 24 28C25.6569 28 27 26.6569 27 25V14" stroke="url(#unifyGradAccent)" stroke-width="2.5" stroke-linecap="round"/>
                                    <circle cx="24" cy="20" r="2.2" fill="#FFFFFF"/>
                                </svg>
                            </div>
                            <span class="brand-title fw-bold fs-4 d-none d-sm-inline">Unify</span>
=======

                    <div class="header-brand-group d-flex align-items-center me-3">
                        <a href="<?= BASE_URL ?>/" class="brand-link align-items-center gap-2" id="headerLogoBtn" title="Nexus Home">
                            <div class="brand-icon-box flex-shrink-0">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <span class="fs-5 brand-title fw-bold d-none d-sm-inline">Nexus</span>
>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534
                        </a>
                    </div>

                    <div class="header-center flex-grow-1 d-flex justify-content-center px-2 px-md-4">
                        <div class="header-search-wrapper position-relative w-100" style="max-width: 580px;">
                            <form action="<?= BASE_URL ?>/index.php" method="GET" class="header-search" id="headerSearchForm" role="search">
                                <input type="hidden" name="action" value="search">
                                <span class="search-icon d-flex align-items-center justify-content-center text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="q" id="headerSearchInput" value="<?= htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Search Unify..." aria-label="Search" autocomplete="off">
                                <?php if (!empty($_GET['q'])): ?>
                                    <a href="<?= BASE_URL ?>/index.php?action=search" class="search-clear-btn" title="Clear Search">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                <?php endif; ?>
                            </form>

<<<<<<< HEAD

                            <div id="liveSearchDropdown" class="live-search-dropdown shadow-lg rounded-3 d-none">
                                <div id="liveSearchResults" class="live-search-results">

                                </div>
                            </div>
                        </div>
                    </div>
=======

                    <div class="header-actions">

                        <?php if ($isLoggedIn): ?>
                        <div class="position-relative">
                            <a href="<?= BASE_URL ?>/index.php?action=notifications" class="header-icon-btn notif-bell-btn" title="Notifications" aria-label="Notifications" id="headerNotifBtn">
                                <i class="bi bi-bell-fill"></i>
                                <?php
                                require_once __DIR__ . '/../../models/NotificationModel.php';
                                $headerNotifModel = new NotificationModel();
                                $headerUnreadCount = $headerNotifModel->getUnreadCount((int)$_SESSION['user_id']);
                                if ($headerUnreadCount > 0): ?>
                                    <span class="notif-badge-count" id="headerNotifBadge"><?= $headerUnreadCount > 99 ? '99+' : $headerUnreadCount ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <?php endif; ?>

                        <button class="header-icon-btn theme-toggle-btn" type="button" onclick="toggleTheme()" title="Switch to Dark Mode" aria-label="Switch to Dark Mode">
                            <i class="theme-toggle-icon bi bi-moon-fill"></i>
                        </button>
>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534

                    <div class="header-right d-flex align-items-center gap-2 flex-shrink-0">

                        <div class="dropdown">
                            <button class="fb-round-btn position-relative" type="button" id="headerMessagesBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Chats & Messages" aria-label="Chats & Messages">
                                <i class="bi bi-chat-dots fs-5"></i>
                                <span class="fb-badge" id="headerMessagesBadge">2</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end fb-dropdown-menu fb-notif-menu fb-messages-menu shadow-lg border-0 p-0" aria-labelledby="headerMessagesBtn" id="headerMessagesDropdownMenu">
                                <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="fw-bold mb-0 text-dark">Chats</h5>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill small" id="msgNewBadgeCount">2 New</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none p-0 fw-semibold mark-all-read-btn" id="btnMarkAllMessagesRead" title="Mark all chats as read">
                                        Mark all as read
                                    </button>
                                </div>
                                <div class="px-3 pt-2 pb-1">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0 text-muted ps-2 pe-1">
                                            <i class="bi bi-search small"></i>
                                        </span>
                                        <input type="text" class="form-control bg-light border-start-0 py-1 ps-1" placeholder="Search Messenger..." id="chatSearchInput" style="font-size: 0.85rem;">
                                    </div>
                                </div>
                                <div class="notif-list py-1" id="messagesListContainer">
                                    <div class="dropdown-item notif-item msg-item d-flex align-items-center gap-3 p-3 text-wrap unread" data-msg-id="msg-alice" data-contact-name="Alice Wonderland" data-contact-initials="AW" data-contact-color="#1877f2" role="button" tabindex="0">
                                        <div class="position-relative flex-shrink-0">
                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px; background: #1877f2;">AW</div>
                                            <span class="online-status-dot"></span>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="fw-bold text-dark small text-truncate contact-name">Alice Wonderland</span>
                                                <span class="text-primary micro-text fw-semibold msg-time">10m ago</span>
                                            </div>
                                            <div class="text-muted micro-text text-truncate msg-preview">Hey, loved your latest post! Are you free later?</div>
                                        </div>
                                        <div class="unread-dot flex-shrink-0"></div>
                                    </div>
                                    <div class="dropdown-item notif-item msg-item d-flex align-items-center gap-3 p-3 text-wrap unread" data-msg-id="msg-bob" data-contact-name="Bob Builder" data-contact-initials="BB" data-contact-color="#10b981" role="button" tabindex="0">
                                        <div class="position-relative flex-shrink-0">
                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px; background: #10b981;">BB</div>
                                            <span class="online-status-dot"></span>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="fw-bold text-dark small text-truncate contact-name">Bob Builder</span>
                                                <span class="text-muted micro-text msg-time">1h ago</span>
                                            </div>
                                            <div class="text-muted micro-text text-truncate msg-preview">Check out the new layout update for Unify!</div>
                                        </div>
                                        <div class="unread-dot flex-shrink-0"></div>
                                    </div>
                                </div>
                                <div class="p-2 border-top text-center bg-light-subtle">
                                    <span class="small text-muted fw-semibold">Click any message to open instant chat</span>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="fb-round-btn position-relative" type="button" id="headerNotifBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notifications" aria-label="Notifications">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="fb-badge" id="headerNotifBadge">3</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end fb-dropdown-menu fb-notif-menu shadow-lg border-0 p-0" aria-labelledby="headerNotifBtn" id="headerNotifDropdownMenu">
                                <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="fw-bold mb-0 text-dark">Notifications</h5>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill small" id="notifNewBadgeCount">3 New</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none p-0 fw-semibold mark-all-read-btn" id="btnMarkAllNotifRead" title="Mark all notifications as read">
                                        Mark all as read
                                    </button>
                                </div>
                                <div class="notif-list py-1" id="notificationsListContainer">
                                    <div class="dropdown-item notif-item user-notif-item d-flex align-items-center gap-3 p-3 text-wrap unread" data-notif-id="notif-like-1" data-target="feed" role="button" tabindex="0">
                                        <div class="position-relative flex-shrink-0">
                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px; background: #e11d48;">
                                                <i class="bi bi-hand-thumbs-up-fill fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="small text-dark notif-text"><strong class="text-primary">Alice Wonderland</strong> liked your post</div>
                                            <div class="text-primary micro-text mt-1 fw-semibold notif-time">5m ago</div>
                                        </div>
                                        <div class="unread-dot flex-shrink-0"></div>
                                    </div>
                                    <div class="dropdown-item notif-item user-notif-item d-flex align-items-center gap-3 p-3 text-wrap unread" data-notif-id="notif-comment-2" data-target="feed" role="button" tabindex="0">
                                        <div class="position-relative flex-shrink-0">
                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px; background: #0284c7;">
                                                <i class="bi bi-chat-left-text-fill fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="small text-dark notif-text"><strong class="text-primary">Bob Builder</strong> commented on your update</div>
                                            <div class="text-muted micro-text mt-1 notif-time">25m ago</div>
                                        </div>
                                        <div class="unread-dot flex-shrink-0"></div>
                                    </div>
                                    <div class="dropdown-item notif-item user-notif-item d-flex align-items-center gap-3 p-3 text-wrap unread" data-notif-id="notif-follow-3" data-target="profile" role="button" tabindex="0">
                                        <div class="position-relative flex-shrink-0">
                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px; background: #8b5cf6;">
                                                <i class="bi bi-person-plus-fill fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="small text-dark notif-text"><strong class="text-primary">Sarah Connor</strong> started following your profile</div>
                                            <div class="text-muted micro-text mt-1 notif-time">2h ago</div>
                                        </div>
                                        <div class="unread-dot flex-shrink-0"></div>
                                    </div>
                                </div>
                                <div class="p-2 border-top text-center bg-light-subtle">
                                    <span class="small text-muted fw-semibold">Click any notification to mark as read</span>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown fb-profile-dropdown">
                            <button class="fb-profile-trigger d-flex align-items-center"
                                    type="button"
                                    id="headerUserDropdown"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="true"
                                    aria-expanded="false"
                                    title="Account Menu"
                                    aria-label="Account Menu">
                                <?php if (!empty($userAvatarPath)): ?>
                                    <img src="<?= $userAvatarPath ?>" alt="<?= htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>" class="fb-avatar-trigger" onerror="this.onerror=null; this.outerHTML='<div class=\'fb-avatar-trigger\'><?= htmlspecialchars($userInitials) ?></div>';">
                                <?php else: ?>
                                    <div class="fb-avatar-trigger">
                                        <?= htmlspecialchars($userInitials); ?>
                                    </div>
                                <?php endif; ?>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end fb-dropdown-menu shadow border-0" aria-labelledby="headerUserDropdown">

                                <a href="<?= BASE_URL ?>/index.php?action=profile" class="fb-user-card text-decoration-none">
                                    <?php if (!empty($userAvatarPath)): ?>
                                        <img src="<?= $userAvatarPath ?>" alt="<?= htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>" class="fb-avatar-card" onerror="this.onerror=null; this.outerHTML='<div class=\'fb-avatar-card\'><?= htmlspecialchars($userInitials) ?></div>';">
                                    <?php else: ?>
                                        <div class="fb-avatar-card">
                                            <?= htmlspecialchars($userInitials); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex flex-column min-w-0 flex-grow-1">
                                        <span class="fb-user-fullname text-truncate"><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?></span>
                                        <span class="fb-user-subtext text-truncate">@<?= htmlspecialchars($_SESSION['username'] ?? 'user'); ?></span>
                                    </div>
                                </a>

                                <div class="fb-menu-divider"></div>

                                <a class="dropdown-item fb-menu-item" href="<?= BASE_URL ?>/index.php?action=profile">
                                    <div class="fb-menu-item-left">
                                        <div class="fb-icon-circle">
                                            <i class="bi bi-person fs-5"></i>
                                        </div>
                                        <span class="fb-item-label">View Profile</span>
                                    </div>
                                    <i class="bi bi-chevron-right fb-arrow-right"></i>
                                </a>

                                <a class="dropdown-item fb-menu-item" href="<?= BASE_URL ?>/index.php?action=friends">
                                    <div class="fb-menu-item-left">
                                        <div class="fb-icon-circle">
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                        <span class="fb-item-label">Friends</span>
                                    </div>
                                    <i class="bi bi-chevron-right fb-arrow-right"></i>
                                </a>

                                <a class="dropdown-item fb-menu-item" href="<?= BASE_URL ?>/index.php?action=settings" title="Account Settings">
                                    <div class="fb-menu-item-left">
                                        <div class="fb-icon-circle">
                                            <i class="bi bi-gear fs-5"></i>
                                        </div>
                                        <span class="fb-item-label">Account Settings</span>
                                    </div>
                                    <i class="bi bi-chevron-right fb-arrow-right"></i>
                                </a>

                                <button type="button" class="dropdown-item fb-menu-item fb-menu-theme-item w-100 border-0 bg-transparent" onclick="toggleTheme(); event.stopPropagation();">
                                    <div class="fb-menu-item-left">
                                        <div class="fb-icon-circle theme-icon-circle">
                                            <i class="bi bi-moon fs-5 theme-icon-moon"></i>
                                            <i class="bi bi-sun fs-5 theme-icon-sun d-none"></i>
                                        </div>
                                        <span class="fb-item-label theme-label-text">Dark Mode</span>
                                    </div>
                                    <div class="form-check form-switch m-0 pe-none">
                                        <input class="form-check-input theme-toggle-switch" type="checkbox" role="switch" id="themeSwitchCheckbox">
                                    </div>
                                </button>

                                <div class="fb-menu-divider"></div>

                                <a class="dropdown-item fb-menu-item fb-menu-signout" href="<?= BASE_URL ?>/index.php?action=logout">
                                    <div class="fb-menu-item-left">
                                        <div class="fb-icon-circle fb-icon-signout">
                                            <i class="bi bi-box-arrow-right fs-5 text-danger"></i>
                                        </div>
                                        <span class="fb-item-label text-danger">Sign Out</span>
                                    </div>
                                    <i class="bi bi-chevron-right fb-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>

                    <div class="d-flex align-items-center">
<<<<<<< HEAD
                        <a href="<?= BASE_URL ?>/" class="brand-link d-flex align-items-center gap-2" title="Unify">
                            <div class="brand-logo-icon flex-shrink-0">
                                <svg width="42" height="42" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="unify-logo-svg">
                                    <defs>
                                        <linearGradient id="unifyGradPublic" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#0062ff"/>
                                            <stop offset="50%" stop-color="#3b82f6"/>
                                            <stop offset="100%" stop-color="#00d2ff"/>
                                        </linearGradient>
                                        <linearGradient id="unifyGradAccentPub" x1="100%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#00f2fe"/>
                                            <stop offset="100%" stop-color="#7c3aed"/>
                                        </linearGradient>
                                        <filter id="unifyGlowPub" x="-15%" y="-15%" width="130%" height="130%">
                                            <feDropShadow dx="0" dy="4" stdDeviation="5" flood-color="#0062ff" flood-opacity="0.32"/>
                                        </filter>
                                    </defs>
                                    <rect width="48" height="48" rx="14" fill="url(#unifyGradPublic)" filter="url(#unifyGlowPub)"/>
                                    <path d="M15 13V26C15 30.9706 19.0294 35 24 35C28.9706 35 33 30.9706 33 26V13" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M21 14V25C21 26.6569 22.3431 28 24 28C25.6569 28 27 26.6569 27 25V14" stroke="url(#unifyGradAccentPub)" stroke-width="2.5" stroke-linecap="round"/>
                                    <circle cx="24" cy="20" r="2.2" fill="#FFFFFF"/>
                                </svg>
                            </div>
                            <span class="brand-title fs-4 fw-bold">Unify</span>
=======
                        <a href="<?= BASE_URL ?>/" class="brand-link text-dark" title="Nexus">
                            <div class="brand-icon-box">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <span class="fs-5 font-navy ms-2">Nexus</span>
>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534
                        </a>
                    </div>

                    <div class="header-actions">

                        <button class="fb-round-btn theme-toggle-btn" type="button" onclick="toggleTheme()" title="Switch Theme" aria-label="Switch Theme">
                            <i class="bi bi-moon fs-5 theme-icon-moon"></i>
                            <i class="bi bi-sun fs-5 theme-icon-sun d-none"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <main class="<?= $isLoggedIn ? 'p-3 p-md-4 flex-grow-1' : 'auth-main flex-grow-1 d-flex align-items-center justify-content-center p-3 p-md-4' ?>">

            <?php if (!empty($_SESSION['error_message']) && $currentAction !== 'home' && $currentAction !== 'insights' && $currentAction !== 'welcome' && $currentAction !== 'login' && $currentAction !== 'register'): ?>
                <div class="alert alert-danger fade show border-0 shadow-sm mb-4 mx-auto" style="max-width: 900px;" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['error_message']); ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success_message']) && $currentAction !== 'home' && $currentAction !== 'insights' && $currentAction !== 'welcome' && $currentAction !== 'login' && $currentAction !== 'register'): ?>
                <div class="alert alert-success fade show border-0 shadow-sm mb-4 mx-auto" style="max-width: 900px;" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['success_message']); ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
