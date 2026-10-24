<?php

require_once __DIR__ . '/../layouts/header.php';

$avatarUrl = null;
if (!empty($user['profile_image'])) {
    $uploadedPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($user['profile_image']);
    if (file_exists($uploadedPath)) {
        $avatarUrl = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($user['profile_image']));
    } else {
        $avatarUrl = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars($user['profile_image']);
    }
}

$coverUrl = null;
if (!empty($user['cover_photo'])) {
    $uploadedCoverPath = __DIR__ . '/../../../public/uploads/covers/' . basename($user['cover_photo']);
    if (file_exists($uploadedCoverPath)) {
        $coverUrl = BASE_URL . '/public/uploads/covers/' . htmlspecialchars(basename($user['cover_photo']));
    } else {
        $coverUrl = BASE_URL . '/public/uploads/covers/' . htmlspecialchars($user['cover_photo']);
    }
}

$formattedDate = !empty($user['created_at'])
    ? date('F j, Y', strtotime($user['created_at']))
    : 'N/A';

$userInitials = 'U';
if (!empty($user['full_name'])) {
    $parts = explode(' ', trim($user['full_name']));
    if (count($parts) >= 2) {
        $userInitials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
    } else {
        $userInitials = strtoupper(substr($user['full_name'], 0, 2));
    }
}
?>

<div class="profile-layout-container mx-auto" style="max-width: 1040px; width: 100%;">

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success fade show mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['success_message']); ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="card-modern overflow-hidden mb-4 border-0 shadow-sm">

        <div style="<?= $coverUrl ? "background: url('" . $coverUrl . "') center center / cover no-repeat;" : "background: linear-gradient(135deg, #07193d 0%, #1e3a8a 50%, #0062ff 100%);" ?> height: 260px;" class="position-relative">
            <div class="position-absolute top-0 end-0 m-3 d-flex align-items-center gap-2">
                <span class="badge" style="background-color: var(--accent-yellow, #f1bd27); color: #07193d; font-weight: 700; padding: 0.45rem 0.85rem; border-radius: 50px;">
                    <i class="bi bi-patch-check-fill me-1"></i> Verified Profile
                </span>
                <?php if (!empty($isOwnProfile)): ?>
                    <a href="<?= BASE_URL ?>/index.php?action=edit_profile" class="btn btn-sm btn-cover-action shadow fw-semibold d-inline-flex align-items-center gap-1" title="Edit Cover Photo">
                        <i class="bi bi-camera-fill me-1"></i>
                        <span>Edit Cover</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

<<<<<<< HEAD

        <div class="px-4 py-3 border-bottom">
            <div class="d-flex align-items-md-end justify-content-between flex-column flex-md-row gap-3">

                <div class="d-flex align-items-center align-items-md-end gap-3 flex-wrap">
                    <div class="position-relative flex-shrink-0" style="margin-top: -75px;">
                        <?php if ($avatarUrl): ?>
                            <img
                                src="<?= $avatarUrl ?>"
                                alt="<?= htmlspecialchars($user['full_name']); ?>"
                                class="rounded-circle border border-4 border-white shadow bg-white"
                                style="width: 136px; height: 136px; object-fit: cover;"
                                onerror="this.onerror=null; this.outerHTML='<div class=\'rounded-circle border border-4 border-white shadow text-white d-inline-flex align-items-center justify-content-center fs-1 fw-bold\' style=\'width: 136px; height: 136px; background-color: #07193d;\'><?= htmlspecialchars($userInitials) ?></div>';"
                            >
                        <?php else: ?>
                            <div
                                class="rounded-circle border border-4 border-white shadow text-white d-inline-flex align-items-center justify-content-center fs-1 fw-bold"
                                style="width: 136px; height: 136px; background-color: #07193d;"
                            >
                                <?= htmlspecialchars($userInitials); ?>
                            </div>
                        <?php endif; ?>
=======

        <div class="card-modern overflow-hidden mb-4">

            <div style="<?= $coverUrl ? "background: url('" . $coverUrl . "') center center / cover no-repeat;" : "background: linear-gradient(135deg, #07193d 0%, #1e3a8a 100%);" ?> height: 200px;" class="position-relative">
            </div>
>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534

                        <?php if (!empty($isOwnProfile)): ?>
                            <button type="button" class="avatar-camera-btn shadow d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#modalEditAvatar" title="Update Profile Picture" aria-label="Update Profile Picture" style="bottom: 6px; right: 6px; width: 36px; height: 36px;">
                                <i class="bi bi-camera-fill" style="font-size: 1rem;"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="pb-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h2 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;"><?= htmlspecialchars($user['full_name']); ?></h2>
                            <?php if (!empty($isFollowedBy)): ?>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 0.72rem;">Follows you</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted fw-semibold mb-2">@<?= htmlspecialchars($user['username']); ?></div>

                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <button type="button" class="btn btn-link p-0 text-decoration-none text-muted fw-semibold follow-stat-btn" onclick="openFollowListModal('followers', <?= (int)$user['id'] ?>)">
                                <strong class="text-dark fs-6" id="profileFollowersCount"><?= (int)($followersCount ?? 0) ?></strong> Followers
                            </button>
                            <span>&bull;</span>
                            <button type="button" class="btn btn-link p-0 text-decoration-none text-muted fw-semibold follow-stat-btn" onclick="openFollowListModal('following', <?= (int)$user['id'] ?>)">
                                <strong class="text-dark fs-6" id="profileFollowingCount"><?= (int)($followingCount ?? 0) ?></strong> Following
                            </button>
                            <span>&bull;</span>
                            <span><strong class="text-dark fs-6"><?= !empty($posts) ? count($posts) : 0; ?></strong> Posts</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 pb-2 flex-wrap">
                    <?php if (!empty($isOwnProfile)): ?>
                        <a href="<?= BASE_URL ?>/index.php?action=edit_profile" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
                            <i class="bi bi-pencil-square"></i>
                            <span>Edit Profile</span>
                        </a>
                        <a href="<?= BASE_URL ?>/index.php?action=settings" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
                        </a>
                    <?php else: ?>

                        <form action="<?= BASE_URL ?>/index.php?action=follow_user" method="POST" class="d-inline" id="profileFollowForm">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="user_id" value="<?= (int)$user['id']; ?>">
                            <button type="submit" id="btnProfileFollow" class="btn <?= !empty($isFollowing) ? 'btn-outline-secondary' : (!empty($isFollowedBy) ? 'btn-primary' : 'btn-navy') ?> px-4 fw-semibold rounded-pill" data-is-followed-by="<?= !empty($isFollowedBy) ? '1' : '0' ?>">
                                <i class="bi <?= !empty($isFollowing) ? 'bi-person-check-fill' : (!empty($isFollowedBy) ? 'bi-arrow-repeat' : 'bi-person-plus-fill') ?> me-1"></i>
                                <span><?= !empty($isFollowing) ? 'Following' : (!empty($isFollowedBy) ? 'Follow Back' : 'Follow') ?></span>
                            </button>
                        </form>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-medium d-inline-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i>
                        <span>Feed</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="px-4 d-flex align-items-center gap-3">
            <span class="py-3 px-2 text-primary fw-bold border-bottom border-3 border-primary" style="font-size: 0.95rem;">Timeline Posts (<?= !empty($posts) ? count($posts) : 0; ?>)</span>
            <a href="#aboutDetailsCard" class="py-3 px-2 text-muted fw-semibold text-decoration-none" style="font-size: 0.95rem;">About & Details</a>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-5 col-xl-4" id="aboutDetailsCard">

            <div class="card-modern p-4 mb-4 border-0 shadow-sm">
                <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary"></i>
                    <span>Intro</span>
                </h5>

                <?php if (!empty($user['bio'])): ?>
                    <p class="text-dark small mb-3 lh-base fst-italic">“<?= nl2br(htmlspecialchars($user['bio'])); ?>”</p>
                <?php else: ?>
                    <p class="text-muted small mb-3 fst-italic">No bio added yet. Tell fellow campus members about yourself!</p>
                <?php endif; ?>

                <?php if (!empty($isOwnProfile)): ?>
                    <a href="<?= BASE_URL ?>/index.php?action=edit_profile" class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-semibold mb-3">
                        Edit Bio
                    </a>
                <?php endif; ?>

                <div class="border-top pt-3">
                    <div class="d-flex align-items-center gap-3 mb-3 text-muted small">
                        <i class="bi bi-person text-primary fs-5 flex-shrink-0"></i>
                        <div>Username: <strong class="text-dark">@<?= htmlspecialchars($user['username']); ?></strong></div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3 text-muted small">
                        <i class="bi bi-calendar3 text-primary fs-5 flex-shrink-0"></i>
                        <div>Joined: <strong class="text-dark"><?= $formattedDate; ?></strong></div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3 text-muted small">
                        <i class="bi bi-people text-primary fs-5 flex-shrink-0"></i>
                        <div>Followers: <strong class="text-dark"><?= (int)($followersCount ?? 0); ?></strong></div>
                    </div>
                    <div class="d-flex align-items-center gap-3 text-muted small">
                        <i class="bi bi-file-earmark-text text-primary fs-5 flex-shrink-0"></i>
                        <div>Total Posts: <strong class="text-dark"><?= !empty($posts) ? count($posts) : 0; ?></strong></div>
                    </div>
                </div>
            </div>

            <div class="card-modern p-4 mb-4 border-0 shadow-sm">
                <h6 class="fw-bold mb-2 text-dark">Unify Network</h6>
                <p class="text-muted small mb-0">Connected member of the Unify campus social platform.</p>
            </div>
        </div>

        <div class="col-lg-7 col-xl-8">
            <div class="user-posts-section mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-post text-primary"></i>
                    <span><?= !empty($isOwnProfile) ? 'My Posts' : 'Posts'; ?></span>
                    <span class="badge rounded-pill bg-light text-secondary border fs-6 fw-semibold">
                        <?= !empty($posts) ? count($posts) : 0; ?>
                    </span>
                </h4>
            </div>

            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <?php

                    $authorParts = explode(' ', trim($post['full_name']));
                    $authorInitials = count($authorParts) >= 2
                        ? strtoupper(substr($authorParts[0], 0, 1) . substr($authorParts[count($authorParts)-1], 0, 1))
                        : strtoupper(substr($post['full_name'], 0, 2));

                    $postTime = date('M j, Y \a\t g:i a', strtotime($post['created_at']));
                    $timeBadge = format_time_ago($post['created_at']);
                    ?>
                    <article class="post-card" id="post-<?= $post['id'] ?>">

                        <div class="post-header">
                            <div class="post-author-box">
                                <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$post['user_id']; ?>" class="text-decoration-none flex-shrink-0" title="View <?= htmlspecialchars($post['full_name']); ?>'s profile">
                                    <?php if (!empty($post['profile_image'])): ?>
                                        <img
                                            src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars(basename($post['profile_image'])) ?>"
                                            alt="<?= htmlspecialchars($post['full_name']); ?>"
                                            class="post-avatar"
                                            onerror="this.onerror=null; this.outerHTML='<div class=\'post-avatar\'><?= htmlspecialchars($authorInitials) ?></div>';"
                                        >
                                    <?php else: ?>
                                        <div class="post-avatar">
                                            <?= htmlspecialchars($authorInitials) ?>
                                        </div>
                                    <?php endif; ?>
                                </a>

                                <div>
                                    <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$post['user_id']; ?>" class="post-author-name" title="View <?= htmlspecialchars($post['full_name']); ?>'s profile"><?= htmlspecialchars($post['full_name']); ?></a>
                                    <div class="post-meta">
                                        <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$post['user_id']; ?>" class="text-muted text-decoration-none">@<?= htmlspecialchars($post['username']); ?></a> &bull; <?= htmlspecialchars($timeBadge); ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$post['user_id']): ?>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Post actions">
                                    <i class="bi bi-three-dots fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li>
                                        <a class="dropdown-item small text-secondary" href="<?= BASE_URL ?>/index.php?action=edit_post&post_id=<?= $post['id'] ?>">
                                            <i class="bi bi-pencil-square me-2 text-muted"></i>Edit post
                                        </a>
                                    </li>
                                    <li>
                                        <form action="<?= BASE_URL ?>/index.php?action=delete_post" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                            <button type="submit" class="dropdown-item small text-secondary border-0 bg-transparent w-100 text-start">
                                                <i class="bi bi-trash me-2 text-muted"></i>Delete post
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="post-content">
                            <?= nl2br(htmlspecialchars($post['content'])); ?>
                        </div>

                        <?php
                        $postImages = !empty($post['images']) ? $post['images'] : PostModel::parseImages($post['image'] ?? null);
                        $totalPostImages = count($postImages);
                        ?>
                        <?php if ($totalPostImages === 1): ?>
                            <?php
                            $singleImg = $postImages[0];
                            $postImgFile = __DIR__ . '/../../../public/uploads/posts/' . basename($singleImg);
                            $hasRealImage = file_exists($postImgFile);
                            $imgSize = $hasRealImage ? @getimagesize($postImgFile) : false;
                            $imgOrientationClass = ($imgSize && $imgSize[1] > $imgSize[0]) ? 'is-portrait' : 'is-landscape';
                            ?>
                            <?php if ($hasRealImage): ?>
                                <div class="post-image-container <?= $imgOrientationClass ?> mb-3">
                                    <a href="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($singleImg)) ?>" class="post-lightbox-trigger" data-gallery="post-<?= (int)$post['id'] ?>" title="View full image">
                                        <img src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($singleImg)) ?>" alt="Post attachment" loading="lazy" onload="this.naturalHeight > this.naturalWidth ? (this.closest('.post-image-container')?.classList.add('is-portrait'), this.closest('.post-image-container')?.classList.remove('is-landscape')) : (this.closest('.post-image-container')?.classList.add('is-landscape'), this.closest('.post-image-container')?.classList.remove('is-portrait'));">
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="post-image-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($totalPostImages > 1): ?>
                            <div class="post-gallery post-gallery-<?= min($totalPostImages, 4) ?> mb-3" data-post-id="<?= (int)$post['id'] ?>">
                                <?php foreach (array_slice($postImages, 0, 4) as $idx => $imgFile): ?>
                                    <?php
                                    $isLastQuadrant = ($idx === 3 && $totalPostImages > 4);
                                    $remainingCount = $totalPostImages - 4;
                                    ?>
                                    <div class="post-gallery-item">
                                        <a href="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($imgFile)) ?>" class="post-lightbox-trigger" data-gallery="post-<?= (int)$post['id'] ?>" title="View image <?= $idx + 1 ?>">
                                            <img src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($imgFile)) ?>" alt="Post image <?= $idx + 1 ?>" loading="lazy">
                                            <?php if ($isLastQuadrant): ?>
                                                <div class="post-gallery-overlay">
                                                    <span>+<?= $remainingCount ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($totalPostImages > 4): ?>
                                    <?php foreach (array_slice($postImages, 4) as $extraIdx => $extraImg): ?>
                                        <a href="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($extraImg)) ?>" class="post-lightbox-trigger d-none" data-gallery="post-<?= (int)$post['id'] ?>" title="View image <?= 5 + $extraIdx ?>"></a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="post-footer-actions d-flex align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <form action="<?= BASE_URL ?>/index.php?action=like_post" method="POST" class="d-inline">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="post-action-btn <?= !empty($post['is_liked']) ? 'liked' : '' ?>" title="Like">
                                        <i class="bi <?= !empty($post['is_liked']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                        <span class="post-likes-count" data-post-id="<?= (int)$post['id']; ?>" title="View reactions"><?= (int)$post['likes_count']; ?></span>
                                    </button>
                                </form>

                                <button type="button" class="post-action-btn" onclick="toggleComments(<?= $post['id'] ?>)" title="Comments">
                                    <i class="bi bi-chat"></i>
                                    <span><?= (int)$post['comments_count']; ?></span>
                                </button>
                            </div>
                        </div>

                        <div class="comments-container d-none" id="comments-box-<?= $post['id'] ?>">
                            <?php if (!empty($post['comments'])): ?>
                                <?php foreach ($post['comments'] as $comment): ?>
                                    <?php
                                    $cParts = explode(' ', trim($comment['full_name'] ?? 'User'));
                                    $cInitials = count($cParts) >= 2
                                        ? strtoupper(substr($cParts[0], 0, 1) . substr($cParts[count($cParts)-1], 0, 1))
                                        : strtoupper(substr($comment['full_name'] ?? 'U', 0, 2));

                                    $cTimeBadge = format_time_ago($comment['created_at']);
                                    $cFullTime = date('M j, Y \a\t g:i a', strtotime($comment['created_at']));
                                    ?>
                                    <div class="comment-bubble" id="comment-<?= $comment['id'] ?>">
                                        <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$comment['user_id']; ?>" class="text-decoration-none flex-shrink-0" title="View <?= htmlspecialchars($comment['full_name']); ?>'s profile">
                                            <?php if (!empty($comment['profile_image'])): ?>
                                                <img
                                                    src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars(basename($comment['profile_image'])) ?>"
                                                    alt="<?= htmlspecialchars($comment['full_name']); ?>"
                                                    class="comment-avatar"
                                                    onerror="this.onerror=null; this.outerHTML='<div class=\'comment-avatar\'><?= htmlspecialchars($cInitials) ?></div>';"
                                                >
                                            <?php else: ?>
                                                <div class="comment-avatar">
                                                    <?= htmlspecialchars($cInitials) ?>
                                                </div>
                                            <?php endif; ?>
                                        </a>

                                        <div class="comment-content-box">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                                <div>
                                                    <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$comment['user_id']; ?>" class="comment-author text-decoration-none" title="View <?= htmlspecialchars($comment['full_name']); ?>'s profile"><?= htmlspecialchars($comment['full_name']); ?></a>
                                                    <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$comment['user_id']; ?>" class="text-muted small text-decoration-none">@<?= htmlspecialchars($comment['username']); ?></a>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <small class="text-muted" title="<?= htmlspecialchars($cFullTime); ?>">
                                                        <?= htmlspecialchars($cTimeBadge); ?>
                                                    </small>

                                                    <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$comment['user_id']): ?>
                                                        <a href="<?= BASE_URL ?>/index.php?action=edit_comment&comment_id=<?= $comment['id'] ?>" class="comment-btn-action text-primary" title="Edit comment">
                                                            <i class="bi bi-pencil-square"></i> Edit
                                                        </a>
                                                        <form action="<?= BASE_URL ?>/index.php?action=delete_comment" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this comment? This action cannot be undone.');">
                                                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                            <button type="submit" class="comment-btn-action text-danger" title="Delete comment">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="comment-text">
                                                <?= nl2br(htmlspecialchars($comment['content'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($_SESSION['user_id'])): ?>
                                <form action="<?= BASE_URL ?>/index.php?action=create_comment" method="POST" class="comment-input-row">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <input
                                        type="text"
                                        name="content"
                                        class="form-control"
                                        placeholder="Write a comment..."
                                        maxlength="1000"
                                        required
                                    >
                                    <button type="submit" class="btn btn-sm btn-accent rounded-pill px-3" title="Post comment">
                                        <i class="bi bi-send-fill"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="text-center py-2">
                                    <a href="<?= BASE_URL ?>/index.php?action=login" class="text-muted small text-decoration-none">
                                        Sign in to comment
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>

                <div class="card-modern p-5 text-center text-muted">
                    <i class="bi bi-chat-square-text fs-1 mb-3 text-secondary"></i>
                    <h5 class="fw-semibold text-dark">No posts yet.</h5>
                    <p class="small mb-0 text-muted">
                        <?= !empty($isOwnProfile) ? "You haven't published any posts yet. Head over to the feed to share what's happening!" : "This user hasn't shared any posts yet."; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        </div>
    </div>
</div>

<style>
.follow-stat-btn {
    transition: transform 0.15s ease, opacity 0.15s ease;
}
.follow-stat-btn:hover {
    transform: translateY(-2px);
    opacity: 0.85;
}
.follow-stat-btn:hover .follow-stat-num {
    text-decoration: underline;
    color: var(--accent-yellow-hover, #0d6efd) !important;
}
</style>

<div class="modal fade" id="followListModal" tabindex="-1" aria-labelledby="followListModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow border-0 rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2 mb-0" id="followListModalTitle">
                    <i class="bi bi-people-fill text-primary" id="followListModalIcon"></i>
                    <span id="followListModalHeading">Followers</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="followListModalBody" style="min-height: 200px; max-height: 420px; overflow-y: auto;">

            </div>
        </div>
    </div>
</div>

<script>
function toggleComments(postId) {
    const box = document.getElementById('comments-box-' + postId);
    if (!box) return;
    if (box.classList.contains('d-none')) {
        box.classList.remove('d-none');
        const input = box.querySelector('input[name="content"], textarea[name="content"]');
        if (input) setTimeout(() => input.focus(), 60);
    } else {
        box.classList.add('d-none');
    }
}

let followListModalInstance = null;

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function openFollowListModal(type, targetUserId) {
    const modalEl = document.getElementById('followListModal');
    if (!modalEl) return;

    if (!followListModalInstance) {
        followListModalInstance = new bootstrap.Modal(modalEl);
    }

    const titleEl = document.getElementById('followListModalHeading');
    const iconEl = document.getElementById('followListModalIcon');
    const bodyEl = document.getElementById('followListModalBody');

    const isFollowers = (type === 'followers');
    if (titleEl) {
        titleEl.textContent = isFollowers ? 'Followers' : 'Following';
    }
    if (iconEl) {
        iconEl.className = isFollowers ? 'bi bi-people-fill text-primary' : 'bi bi-person-check-fill text-primary';
    }

    bodyEl.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted small mt-2 mb-0">Loading ${isFollowers ? 'followers' : 'following'}...</p>
        </div>
    `;

    followListModalInstance.show();

    const fetchUrl = `<?= BASE_URL ?>/index.php?action=get_follow_list&user_id=${targetUserId}&type=${type}`;

    fetch(fetchUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.is_private) {
            bodyEl.innerHTML = `
                <div class="text-center py-5 px-4">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-3 mb-3 border shadow-sm" style="width: 64px; height: 64px;">
                        <i class="bi bi-lock-fill text-secondary fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Follow List is Private</h6>
                    <p class="text-muted small mb-0 px-3">
                        ${escapeHtml(data.message || 'This account has made their follower and following list private.')}
                    </p>
                </div>
            `;
            return;
        }

        if (!data.success) {
            bodyEl.innerHTML = `
                <div class="alert alert-danger m-3 text-center small rounded-3">
                    ${escapeHtml(data.error || 'Unable to load list.')}
                </div>
            `;
            return;
        }

        if (titleEl) {
            titleEl.textContent = `${isFollowers ? 'Followers' : 'Following'} (${data.count})`;
        }

        if (!data.users || data.users.length === 0) {
            bodyEl.innerHTML = `
                <div class="text-center py-5 text-muted px-3">
                    <i class="bi bi-people fs-1 text-secondary mb-2 d-block opacity-50"></i>
                    <h6 class="fw-semibold text-dark mb-1">${isFollowers ? 'No followers yet' : 'Not following anyone yet'}</h6>
                    <p class="small text-muted mb-0">When people ${isFollowers ? 'follow this profile' : 'are followed by this profile'}, they will appear here.</p>
                </div>
            `;
            return;
        }

        let html = '<div class="list-group list-group-flush">';
        data.users.forEach(u => {
            const profileUrl = `<?= BASE_URL ?>/index.php?action=profile&user_id=${u.id}`;
            let avatarHtml = '';
            if (u.profile_image) {
                const imgUrl = `<?= BASE_URL ?>/public/uploads/avatars/${encodeURIComponent(u.profile_image)}`;
                avatarHtml = `<img src="${imgUrl}" alt="${escapeHtml(u.full_name)}" class="rounded-circle object-fit-cover shadow-sm flex-shrink-0" style="width: 44px; height: 44px;" onerror="this.onerror=null; this.outerHTML='<div class=\\\\\\\\\''''rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-sm flex-shrink-0\\\\\\\\\'''' style=\\\\\\\\\''''width: 44px; height: 44px; background-color: #07193d; font-size: 0.85rem;\\\\\\\\\''''>${escapeHtml(u.initials)}</div>';">`;
            } else {
                avatarHtml = `<div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 44px; height: 44px; background-color: #07193d; font-size: 0.85rem;">${escapeHtml(u.initials)}</div>`;
            }

            html += `
                <div class="list-group-item d-flex align-items-center justify-content-between px-3 py-3 border-0 border-bottom">
                    <div class="d-flex align-items-center gap-3 text-truncate">
                        <a href="${profileUrl}" class="text-decoration-none">
                            ${avatarHtml}
                        </a>
                        <div class="text-truncate">
                            <a href="${profileUrl}" class="text-dark fw-bold text-decoration-none d-block small text-truncate">
                                ${escapeHtml(u.full_name)}
                                ${u.is_self ? '<span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size: 0.65rem;">You</span>' : ''}
                                ${(!u.is_self && u.is_followed_by) ? '<span class="badge bg-secondary-subtle text-secondary border ms-1" style="font-size: 0.65rem;">Follows you</span>' : ''}
                            </a>
                            <a href="${profileUrl}" class="text-muted text-decoration-none d-block small text-truncate">@${escapeHtml(u.username)}</a>
                        </div>
                    </div>
                    <div class="ms-2 flex-shrink-0 d-flex align-items-center gap-2">
                        ${!u.is_self ? `
                            <form action="<?= BASE_URL ?>/index.php?action=follow_user" method="POST" class="m-0 d-inline">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="user_id" value="${u.id}">
                                <button type="submit" class="btn btn-sm ${u.is_following ? 'btn-outline-secondary' : (u.is_followed_by ? 'btn-primary' : 'btn-navy')} rounded-pill px-3 fw-semibold" style="font-size: 0.78rem;" data-is-followed-by="${u.is_followed_by ? '1' : '0'}">
                                    <i class="bi ${u.is_following ? 'bi-person-check-fill' : (u.is_followed_by ? 'bi-arrow-repeat' : 'bi-person-plus-fill')} me-1"></i>
                                    <span>${u.is_following ? 'Following' : (u.is_followed_by ? 'Follow Back' : 'Follow')}</span>
                                </button>
                            </form>
                        ` : ''}
                        <a href="${profileUrl}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold" style="font-size: 0.78rem;">
                            View
                        </a>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        bodyEl.innerHTML = html;
    })
    .catch(err => {
        bodyEl.innerHTML = `
            <div class="alert alert-danger m-3 text-center small rounded-3">
                Failed to load follow list. Please try again.
            </div>
        `;
    });
}
</script>

<?php if (!empty($isOwnProfile)): ?>

<div class="modal fade" id="modalEditAvatar" tabindex="-1" aria-labelledby="editAvatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="editAvatarModalLabel">
                    <i class="bi bi-camera-fill text-primary fs-5"></i>
                    <span>Update Profile Picture</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/index.php?action=edit_profile" method="POST" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body p-4 text-center">
                    <div class="mb-4">
                        <?php if ($avatarUrl): ?>
                            <img src="<?= $avatarUrl ?>" alt="Current Avatar" id="modalAvatarPreview" class="rounded-circle border border-4 border-white shadow" style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div id="modalAvatarPlaceholder" class="rounded-circle border border-4 border-white shadow text-white d-inline-flex align-items-center justify-content-center fs-2 fw-bold" style="width: 120px; height: 120px; background-color: var(--fb-blue, #1877f2);">
                                <?= htmlspecialchars($userInitials); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="modalProfileImageInput" class="form-label small fw-semibold text-secondary">Select New Profile Picture</label>
                        <input type="file" name="profile_image" id="modalProfileImageInput" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" required>
                        <div class="form-text small text-muted">Supports JPG, PNG, WEBP, or GIF (max 5MB).</div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold">Save Profile Picture</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('modalProfileImageInput');
    const avatarPreview = document.getElementById('modalAvatarPreview');
    if (avatarInput) {
        avatarInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (avatarPreview) {
                        avatarPreview.src = e.target.result;
                    } else {
                        const placeholder = document.getElementById('modalAvatarPlaceholder');
                        if (placeholder) {
                            placeholder.outerHTML = '<img src="' + e.target.result + '" id="modalAvatarPreview" class="rounded-circle border border-4 border-white shadow" style="width: 120px; height: 120px; object-fit: cover;">';
                        }
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});
</script>
<?php endif; ?>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
