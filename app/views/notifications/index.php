<?php

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
?>

<div class="notifications-page-container mx-auto py-4" style="max-width: 780px;">

    <div class="card-modern p-4 mb-4 border-0 shadow-sm">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="notif-header-icon-box">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-navy mb-0">Notifications</h4>
                    <p class="text-muted small mb-0">Stay updated on who followed you, liked, or commented on your posts.</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if ($unreadCount > 0): ?>
                    <form action="<?= BASE_URL ?>/index.php?action=mark_all_notifications_read" method="POST" class="d-inline" id="markAllReadForm">
                        <?= csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-navy fw-semibold rounded-pill px-3 py-1-5 d-flex align-items-center gap-2 shadow-sm" id="markAllReadBtn">
                            <i class="bi bi-check2-all"></i>
                            <span>Mark all as read</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="notifications-list-wrapper">
        <?php if (!empty($notifications)): ?>
            <div class="card-modern border-0 shadow-sm overflow-hidden p-0" id="notificationsListCard">
                <div class="list-group list-group-flush" id="notificationsList">
                    <?php foreach ($notifications as $n): ?>
                        <?php
                        $isUnread = !(bool)$n['is_read'];
                        $actorName = $n['actor_full_name'] ?? 'User';
                        $actorUsername = $n['actor_username'] ?? '';
                        $actorAvatar = $n['actor_profile_image'] ?? null;

                        $avatarPath = null;
                        if (!empty($actorAvatar)) {
                            $checkPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($actorAvatar);
                            if (file_exists($checkPath)) {
                                $avatarPath = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($actorAvatar));
                            }
                        }

                        $parts = explode(' ', trim($actorName));
                        $initials = count($parts) >= 2
                            ? strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1))
                            : strtoupper(mb_substr($actorName, 0, 2));

                        $destUrl = BASE_URL . '/index.php?action=open_notification&id=' . (int)$n['id'];

                        $typeIcon = 'bi-bell-fill';
                        $typeBadgeClass = 'bg-primary text-white';
                        if ($n['type'] === 'follow') {
                            $typeIcon = 'bi-person-plus-fill';
                            $typeBadgeClass = 'bg-primary text-white';
                        } elseif ($n['type'] === 'like') {
                            $typeIcon = 'bi-heart-fill';
                            $typeBadgeClass = 'bg-danger text-white';
                        } elseif ($n['type'] === 'comment') {
                            $typeIcon = 'bi-chat-dots-fill';
                            $typeBadgeClass = 'bg-success text-white';
                        }
                        ?>
                        <div class="list-group-item list-group-item-action p-3 notification-item <?= $isUnread ? 'notification-unread' : '' ?>" id="notif-item-<?= (int)$n['id'] ?>">
                            <div class="d-flex align-items-center justify-content-between gap-3">

                                <a href="<?= $destUrl ?>" class="d-flex align-items-center gap-3 text-decoration-none text-reset flex-grow-1 min-w-0">

                                    <div class="position-relative flex-shrink-0">
                                        <?php if (!empty($avatarPath)): ?>
                                            <img src="<?= $avatarPath ?>" alt="<?= htmlspecialchars($actorName); ?>" class="notif-avatar" onerror="this.onerror=null; this.outerHTML='<div class=\'notif-avatar-initials\'><?= htmlspecialchars($initials); ?></div>';">
                                        <?php else: ?>
                                            <div class="notif-avatar-initials">
                                                <?= htmlspecialchars($initials); ?>
                                            </div>
                                        <?php endif; ?>
                                        <span class="position-absolute bottom-0 end-0 rounded-circle d-inline-flex align-items-center justify-content-center notif-type-bubble <?= $typeBadgeClass ?>">
                                            <i class="bi <?= $typeIcon ?>" style="font-size: 0.65rem;"></i>
                                        </span>
                                    </div>

                                    <div class="flex-grow-1 min-w-0">
                                        <div class="notif-text mb-1 text-truncate-2">
                                            <strong class="text-navy"><?= htmlspecialchars($actorName); ?></strong>
                                            <span class="text-secondary"><?= htmlspecialchars($n['message']); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 micro-text text-muted">
                                            <span><?= NotificationController::formatTimeAgo($n['created_at']); ?></span>
                                            <?php if ($n['type'] !== 'follow' && empty($n['post_exists'])): ?>
                                                <span>&bull;</span>
                                                <span class="text-warning"><i class="bi bi-info-circle me-1"></i>Post removed</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>

                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <?php if ($isUnread): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-icon-subtle mark-read-single-btn"
                                                data-id="<?= (int)$n['id'] ?>"
                                                title="Mark as read"
                                                aria-label="Mark as read">
                                            <span class="notif-unread-dot"></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>

            <div class="card-modern p-5 text-center border-0 shadow-sm">
                <div class="notif-empty-icon mx-auto mb-3">
                    <i class="bi bi-bell-slash"></i>
                </div>
                <h5 class="fw-bold text-navy mb-2">You're all caught up!</h5>
                <p class="text-muted small mx-auto mb-0" style="max-width: 420px;">
                    When someone follows you, likes, or comments on your posts, you'll receive notifications here.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    document.querySelectorAll('.mark-read-single-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const notifId = this.getAttribute('data-id');
            const item = document.getElementById('notif-item-' + notifId);
            const buttonEl = this;

            fetch(window.BASE_URL + '/index.php?action=mark_notification_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    id: notifId,
                    csrf_token: csrfToken
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (item) {
                        item.classList.remove('notification-unread');
                    }
                    buttonEl.remove();
                    if (window.updateHeaderNotificationBadge) {
                        window.updateHeaderNotificationBadge(data.unread_count);
                    }
                }
            })
            .catch(err => console.error('Error marking notification as read:', err));
        });
    });

    const markAllForm = document.getElementById('markAllReadForm');
    if (markAllForm) {
        markAllForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetch(window.BASE_URL + '/index.php?action=mark_all_notifications_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    csrf_token: csrfToken,
                    ajax: '1'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item').forEach(el => el.classList.remove('notification-unread'));
                    document.querySelectorAll('.mark-read-single-btn').forEach(el => el.remove());
                    markAllForm.remove();
                    if (window.updateHeaderNotificationBadge) {
                        window.updateHeaderNotificationBadge(0);
                    }
                }
            })
            .catch(err => {

                markAllForm.submit();
            });
        });
    }
});
</script>
