<?php

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
?>

<div class="friends-page-container mx-auto py-4" style="max-width: 820px;">

    <div class="card-modern p-4 mb-4 border-0 shadow-sm">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="friends-header-icon-box">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-navy mb-0">Friends</h4>
                    <p class="text-muted small mb-0">Manage your connections in the SMCC community.</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="friends-stat-pill">
                    <span class="fw-bold text-navy fs-5" id="headerFollowersCount"><?= (int)$followersCount ?></span>
                    <span class="text-muted small">Followers</span>
                </div>
                <div class="friends-stat-divider"></div>
                <div class="friends-stat-pill">
                    <span class="fw-bold text-navy fs-5" id="headerFollowingCount"><?= (int)$followingCount ?></span>
                    <span class="text-muted small">Following</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card-modern border-0 shadow-sm p-2 mb-4">
        <ul class="nav nav-pills nav-fill friends-nav-pills gap-2" id="friendsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $activeTab === 'followers' ? 'active' : '' ?> d-flex align-items-center justify-content-center gap-2 py-2-5 rounded-3 fw-semibold"
                   id="followers-tab"
                   href="<?= BASE_URL ?>/index.php?action=friends&tab=followers"
                   role="tab"
                   aria-selected="<?= $activeTab === 'followers' ? 'true' : 'false' ?>">
                    <i class="bi bi-person-check-fill"></i>
                    <span>Followers</span>
                    <span class="badge rounded-pill bg-light text-navy border ms-1" id="tabFollowersBadge"><?= (int)$followersCount ?></span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $activeTab === 'following' ? 'active' : '' ?> d-flex align-items-center justify-content-center gap-2 py-2-5 rounded-3 fw-semibold"
                   id="following-tab"
                   href="<?= BASE_URL ?>/index.php?action=friends&tab=following"
                   role="tab"
                   aria-selected="<?= $activeTab === 'following' ? 'true' : 'false' ?>">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Following</span>
                    <span class="badge rounded-pill bg-light text-navy border ms-1" id="tabFollowingBadge"><?= (int)$followingCount ?></span>
                </a>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="friendsTabContent">

        <?php if ($activeTab === 'followers'): ?>
            <div class="tab-pane fade show active" id="followersPane" role="tabpanel">
                <?php if (!empty($followers)): ?>
                    <div class="row g-3" id="followersList">
                        <?php foreach ($followers as $user): ?>
                            <?php
                            $uId = (int)$user['id'];
                            $uFullName = $user['full_name'] ?? 'User';
                            $uUsername = $user['username'] ?? '';
                            $uBio = $user['bio'] ?? '';
                            $isFollowing = (bool)($user['is_following'] ?? false);

                            $uAvatarPath = null;
                            if (!empty($user['profile_image'])) {
                                $cPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($user['profile_image']);
                                if (file_exists($cPath)) {
                                    $uAvatarPath = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($user['profile_image']));
                                }
                            }

                            $parts = explode(' ', trim($uFullName));
                            $uInitials = count($parts) >= 2
                                ? strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1))
                                : strtoupper(mb_substr($uFullName, 0, 2));
                            ?>
                            <div class="col-md-6" id="friend-card-<?= $uId ?>">
                                <div class="card-modern p-3 h-100 border-0 shadow-sm d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center gap-3">

                                        <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $uId ?>" class="text-decoration-none flex-shrink-0">
                                            <?php if (!empty($uAvatarPath)): ?>
                                                <img src="<?= $uAvatarPath ?>" alt="<?= htmlspecialchars($uFullName); ?>" class="friend-card-avatar" onerror="this.onerror=null; this.outerHTML='<div class=\'friend-card-avatar-initials\'><?= htmlspecialchars($uInitials); ?></div>';">
                                            <?php else: ?>
                                                <div class="friend-card-avatar-initials">
                                                    <?= htmlspecialchars($uInitials); ?>
                                                </div>
                                            <?php endif; ?>
                                        </a>

                                        <div class="min-w-0 flex-grow-1">
                                            <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $uId ?>" class="text-decoration-none">
                                                <h6 class="fw-bold text-navy mb-0 text-truncate friend-card-name"><?= htmlspecialchars($uFullName); ?></h6>
                                            </a>
                                            <div class="text-muted small text-truncate">@<?= htmlspecialchars($uUsername); ?></div>
                                            <?php if (!empty($uBio)): ?>
                                                <p class="micro-text text-secondary mb-0 mt-1 text-truncate" title="<?= htmlspecialchars($uBio); ?>">
                                                    <?= htmlspecialchars($uBio); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-2 border-top d-flex justify-content-end align-items-center">
                                        <form action="<?= BASE_URL ?>/index.php?action=follow_user" method="POST" class="follow-action-form" data-user-id="<?= $uId ?>">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?= $uId ?>">
                                            <input type="hidden" name="redirect" value="friends">
                                            <button type="submit"
                                                    class="btn btn-sm <?= $isFollowing ? 'btn-outline-secondary' : 'btn-navy' ?> rounded-pill px-3 py-1-5 fw-semibold d-inline-flex align-items-center gap-2 friend-toggle-btn shadow-sm"
                                                    data-following="<?= $isFollowing ? '1' : '0' ?>">
                                                <i class="bi <?= $isFollowing ? 'bi-check-lg' : 'bi-person-plus-fill' ?>"></i>
                                                <span class="btn-text"><?= $isFollowing ? 'Following' : 'Follow back' ?></span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>

                    <div class="card-modern p-5 text-center border-0 shadow-sm">
                        <div class="friends-empty-icon mx-auto mb-3">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="fw-bold text-navy mb-2">No followers yet</h5>
                        <p class="text-muted small mx-auto mb-3" style="max-width: 420px;">
                            When fellow SMCC students follow your profile, they will appear here. Share posts and connect with peers to grow your network!
                        </p>
                        <a href="<?= BASE_URL ?>/index.php?action=insights" class="btn btn-navy btn-sm rounded-pill px-4 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-compass me-1"></i> Discover People
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($activeTab === 'following'): ?>
            <div class="tab-pane fade show active" id="followingPane" role="tabpanel">
                <?php if (!empty($following)): ?>
                    <div class="row g-3" id="followingList">
                        <?php foreach ($following as $user): ?>
                            <?php
                            $uId = (int)$user['id'];
                            $uFullName = $user['full_name'] ?? 'User';
                            $uUsername = $user['username'] ?? '';
                            $uBio = $user['bio'] ?? '';

                            $uAvatarPath = null;
                            if (!empty($user['profile_image'])) {
                                $cPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($user['profile_image']);
                                if (file_exists($cPath)) {
                                    $uAvatarPath = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($user['profile_image']));
                                }
                            }

                            $parts = explode(' ', trim($uFullName));
                            $uInitials = count($parts) >= 2
                                ? strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1))
                                : strtoupper(mb_substr($uFullName, 0, 2));
                            ?>
                            <div class="col-md-6" id="friend-card-<?= $uId ?>">
                                <div class="card-modern p-3 h-100 border-0 shadow-sm d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center gap-3">

                                        <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $uId ?>" class="text-decoration-none flex-shrink-0">
                                            <?php if (!empty($uAvatarPath)): ?>
                                                <img src="<?= $uAvatarPath ?>" alt="<?= htmlspecialchars($uFullName); ?>" class="friend-card-avatar" onerror="this.onerror=null; this.outerHTML='<div class=\'friend-card-avatar-initials\'><?= htmlspecialchars($uInitials); ?></div>';">
                                            <?php else: ?>
                                                <div class="friend-card-avatar-initials">
                                                    <?= htmlspecialchars($uInitials); ?>
                                                </div>
                                            <?php endif; ?>
                                        </a>

                                        <div class="min-w-0 flex-grow-1">
                                            <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $uId ?>" class="text-decoration-none">
                                                <h6 class="fw-bold text-navy mb-0 text-truncate friend-card-name"><?= htmlspecialchars($uFullName); ?></h6>
                                            </a>
                                            <div class="text-muted small text-truncate">@<?= htmlspecialchars($uUsername); ?></div>
                                            <?php if (!empty($uBio)): ?>
                                                <p class="micro-text text-secondary mb-0 mt-1 text-truncate" title="<?= htmlspecialchars($uBio); ?>">
                                                    <?= htmlspecialchars($uBio); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-2 border-top d-flex justify-content-end align-items-center">
                                        <form action="<?= BASE_URL ?>/index.php?action=follow_user" method="POST" class="follow-action-form" data-user-id="<?= $uId ?>" data-context="following-tab">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?= $uId ?>">
                                            <input type="hidden" name="redirect" value="friends">
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1-5 fw-semibold d-inline-flex align-items-center gap-2 friend-toggle-btn shadow-sm"
                                                    data-following="1">
                                                <i class="bi bi-check-lg"></i>
                                                <span class="btn-text">Following</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>

                    <div class="card-modern p-5 text-center border-0 shadow-sm">
                        <div class="friends-empty-icon mx-auto mb-3">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <h5 class="fw-bold text-navy mb-2">You aren't following anyone yet</h5>
                        <p class="text-muted small mx-auto mb-3" style="max-width: 420px;">
                            Follow other students, faculty, and campus members to see their updates and activities in your News Feed.
                        </p>
                        <a href="<?= BASE_URL ?>/index.php?action=insights" class="btn btn-navy btn-sm rounded-pill px-4 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-search me-1"></i> Find Students to Follow
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    document.querySelectorAll('.follow-action-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const context = this.getAttribute('data-context');
            const btn = this.querySelector('.friend-toggle-btn');
            const btnIcon = btn.querySelector('i');
            const btnText = btn.querySelector('.btn-text');
            const isFollowingCurrently = btn.getAttribute('data-following') === '1';

            btn.disabled = true;

            fetch(window.BASE_URL + '/index.php?action=follow_user', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    user_id: userId,
                    csrf_token: csrfToken,
                    ajax: '1'
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    const newStatus = data.is_following;
                    btn.setAttribute('data-following', newStatus ? '1' : '0');

                    if (newStatus) {
                        btn.className = 'btn btn-sm btn-outline-secondary rounded-pill px-3 py-1-5 fw-semibold d-inline-flex align-items-center gap-2 friend-toggle-btn shadow-sm';
                        btnIcon.className = 'bi bi-check-lg';
                        btnText.textContent = 'Following';
                    } else {
                        btn.className = 'btn btn-sm btn-navy rounded-pill px-3 py-1-5 fw-semibold d-inline-flex align-items-center gap-2 friend-toggle-btn shadow-sm';
                        btnIcon.className = 'bi bi-person-plus-fill';
                        btnText.textContent = 'Follow';

                        if (context === 'following-tab') {
                            const card = document.getElementById('friend-card-' + userId);
                            if (card) {
                                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                card.style.opacity = '0';
                                card.style.transform = 'scale(0.95)';
                                setTimeout(() => {
                                    card.remove();
                                    const remaining = document.querySelectorAll('#followingList .col-md-6').length;
                                    if (remaining === 0) {
                                        window.location.reload();
                                    }
                                }, 300);
                            }
                        }
                    }

                    if (data.my_following_count !== undefined) {
                        const hFollowing = document.getElementById('headerFollowingCount');
                        const tFollowing = document.getElementById('tabFollowingBadge');
                        if (hFollowing) hFollowing.textContent = data.my_following_count;
                        if (tFollowing) tFollowing.textContent = data.my_following_count;
                    }
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(err => {
                btn.disabled = false;
                console.error('Follow toggle error:', err);
                form.submit();
            });
        });
    });
});
</script>
