<?php
// app/views/insights/index.php
// Redesigned Community Search View for Nexus — The SMCC Community Hub

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$type = $type ?? 'users';
$query = $query ?? '';
$filter = $filter ?? ($type === 'posts' ? 'newest' : 'all');
$hasSearched = !empty($hasSearched);
$totalCount = (int)($totalCount ?? 0);
?>

<div class="insights-container py-4">
    <div class="search-reports-container mx-auto" style="max-width: 780px;">

        <!-- Back to Newsfeed Link -->
        <div class="mb-3">
            <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1-5 fw-medium shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Newsfeed</span>
            </a>
        </div>

        <!-- Flash Notifications / Error Alerts -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- MAIN SEARCH CARD -->
        <div class="search-reports-card mb-4 border-0 shadow-sm">
            <!-- Header -->
            <div class="mb-4">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="insights-header-icon-box">
                        <i class="bi bi-search fs-5"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold insights-title mb-0" style="letter-spacing: -0.5px;">Community Search</h3>
                        <p class="text-secondary small mb-0">Discover SMCC community members and find posts that matter to you.</p>
                    </div>
                </div>
            </div>

            <!-- Search Form -->
            <form action="<?= BASE_URL ?>/index.php" method="GET" id="communitySearchForm">
                <input type="hidden" name="action" value="search">
                <input type="hidden" name="type" id="searchTypeInput" value="<?= htmlspecialchars($type); ?>">
                <input type="hidden" name="filter" id="searchFilterInput" value="<?= htmlspecialchars($filter); ?>">

                <!-- Segmented Users / Posts Selector -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div class="insights-segmented-wrap" role="tablist" aria-label="Search mode">
                        <button type="button" 
                                class="insights-segmented-btn <?= $type === 'users' ? 'active' : ''; ?>" 
                                data-type="users"
                                id="btnModeUsers"
                                role="tab"
                                aria-selected="<?= $type === 'users' ? 'true' : 'false'; ?>">
                            <i class="bi bi-people-fill me-1"></i> Users
                        </button>
                        <button type="button" 
                                class="insights-segmented-btn <?= $type === 'posts' ? 'active' : ''; ?>" 
                                data-type="posts"
                                id="btnModePosts"
                                role="tab"
                                aria-selected="<?= $type === 'posts' ? 'true' : 'false'; ?>">
                            <i class="bi bi-chat-square-text-fill me-1"></i> Posts
                        </button>
                    </div>

                    <?php if ($hasSearched): ?>
                        <a href="<?= BASE_URL ?>/index.php?action=search&type=<?= urlencode($type); ?>" class="btn btn-sm btn-link text-muted text-decoration-none py-0">
                            <i class="bi bi-x-circle me-1"></i> Clear search
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Prominent Search Input + Button Group -->
                <div class="input-group search-input-group shadow-sm rounded-4 overflow-hidden border">
                    <span class="input-group-text bg-transparent border-0 ps-3 pe-2">
                        <i class="bi bi-search text-muted fs-6"></i>
                    </span>
                    <input 
                        type="text" 
                        name="q" 
                        id="communitySearchInput"
                        class="form-control border-0 py-2-5 ps-1" 
                        style="height: 50px; font-size: 0.95rem; box-shadow: none;"
                        placeholder="<?= $type === 'users' ? 'Search by name or username...' : 'Search posts by keywords...'; ?>" 
                        value="<?= htmlspecialchars($query); ?>"
                        autocomplete="off"
                        required
                    >
                    <?php if (!empty($query)): ?>
                        <button type="button" class="btn bg-transparent border-0 text-muted px-2" id="clearInputBtn" title="Clear input">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-navy px-4 fw-semibold d-inline-flex align-items-center gap-2" id="searchSubmitBtn">
                        <span>Search</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

                <!-- Modern Quick Filter Pills -->
                <div class="d-flex align-items-center flex-wrap gap-2 mt-3 pt-3 border-top insights-border" id="filterPillsRow">
                    <span class="micro-text fw-bold text-uppercase text-muted me-1">
                        <i class="bi bi-funnel me-1"></i> Filter:
                    </span>

                    <!-- Filters for Users mode -->
                    <div class="filter-group-users <?= $type === 'users' ? 'd-inline-flex' : 'd-none'; ?> align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-sm filter-pill <?= ($filter === 'all' || empty($filter)) ? 'active' : ''; ?>" data-filter="all">
                            <i class="bi bi-people me-1"></i> All Members
                        </button>
                        <button type="button" class="btn btn-sm filter-pill <?= $filter === 'following' ? 'active' : ''; ?>" data-filter="following">
                            <i class="bi bi-person-check-fill me-1"></i> Following Only
                        </button>
                    </div>

                    <!-- Filters for Posts mode -->
                    <div class="filter-group-posts <?= $type === 'posts' ? 'd-inline-flex' : 'd-none'; ?> align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-sm filter-pill <?= ($filter === 'newest' || empty($filter)) ? 'active' : ''; ?>" data-filter="newest">
                            <i class="bi bi-clock-history me-1"></i> Latest
                        </button>
                        <button type="button" class="btn btn-sm filter-pill <?= $filter === 'most_liked' ? 'active' : ''; ?>" data-filter="most_liked">
                            <i class="bi bi-heart-fill text-danger me-1"></i> Top Liked
                        </button>
                        <button type="button" class="btn btn-sm filter-pill <?= $filter === 'photos' ? 'active' : ''; ?>" data-filter="photos">
                            <i class="bi bi-images text-primary me-1"></i> With Photos
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- SEARCH RESULTS SECTION -->
        <div id="searchResultsSection">

            <?php if (!$hasSearched): ?>
                <!-- 1. Initial State: Explore SMCC Community -->
                <div class="search-reports-card text-center py-5 border-0 shadow-sm">
                    <div class="insights-header-icon-box mx-auto mb-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-compass fs-4"></i>
                    </div>
                    <h5 class="fw-bold insights-title mb-2">Explore the SMCC Community</h5>
                    <p class="text-secondary small mb-3 mx-auto" style="max-width: 460px; line-height: 1.6;">
                        Type a name or keyword above and press Search to discover students, faculty, and updates from across the campus.
                    </p>
                    <div class="d-flex justify-content-center flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1-5 quick-search-tag" data-query="SMCC" data-type="posts" data-filter="newest">
                            <i class="bi bi-tag me-1"></i> #SMCC
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1-5 quick-search-tag" data-query="sample" data-type="posts" data-filter="photos">
                            <i class="bi bi-images me-1"></i> Photos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1-5 quick-search-tag" data-query="Elle" data-type="users" data-filter="all">
                            <i class="bi bi-person me-1"></i> Elle
                        </button>
                    </div>
                </div>

            <?php elseif ($totalCount === 0): ?>
                <!-- 2. Empty State: No matches found -->
                <div class="search-reports-card text-center py-5 border-0 shadow-sm">
                    <div class="notif-empty-icon mx-auto mb-3">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5 class="fw-bold insights-title mb-2">No matching results found</h5>
                    <p class="text-secondary small mb-3 mx-auto" style="max-width: 440px;">
                        We couldn't find any <?= $type === 'users' ? 'members' : 'posts' ?> matching "<strong><?= htmlspecialchars($query); ?></strong>"
                        <?php if ($filter === 'following'): ?>
                            among people you follow.
                        <?php elseif ($filter === 'photos'): ?>
                            with attached photos.
                        <?php else: ?>
                            . Try checking your spelling or selecting a different filter.
                        <?php endif; ?>
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-navy rounded-pill px-4 py-2 fw-semibold" onclick="document.getElementById('communitySearchInput').focus(); document.getElementById('communitySearchInput').select();">
                            <i class="bi bi-arrow-repeat me-1"></i> Try Another Search
                        </button>
                    </div>
                </div>

            <?php else: ?>
                <!-- 3. Results Header & Count -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 px-1">
                    <div class="d-flex align-items-center gap-2">
                        <span class="micro-text fw-bold text-uppercase text-secondary">
                            <i class="bi <?= $type === 'users' ? 'bi-people' : 'bi-chat-square-text'; ?> me-1"></i>
                            Matching <?= $type === 'users' ? 'Members' : 'Posts'; ?>
                        </span>
                        <?php if ($type === 'users' && $filter === 'following'): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle micro-text">
                                <i class="bi bi-person-check-fill me-1"></i>Following only
                            </span>
                        <?php elseif ($type === 'posts' && $filter === 'photos'): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle micro-text">
                                <i class="bi bi-images me-1"></i>Photos only
                            </span>
                        <?php elseif ($type === 'posts' && $filter === 'most_liked'): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle micro-text">
                                <i class="bi bi-heart-fill me-1"></i>Top liked
                            </span>
                        <?php endif; ?>
                    </div>
                    <span class="badge rounded-pill bg-light text-navy border px-3 py-1-5 fw-semibold">
                        <?= $totalCount; ?> <?= $type === 'users' ? ($totalCount === 1 ? 'member found' : 'members found') : ($totalCount === 1 ? 'post found' : 'posts found'); ?>
                    </span>
                </div>

                <!-- 4. User Results Grid -->
                <?php if ($type === 'users' && !empty($users)): ?>
                    <div class="row g-3">
                        <?php foreach ($users as $u): ?>
                            <?php
                            $uId = (int)$u['id'];
                            $uFullName = $u['full_name'] ?? 'User';
                            $uUsername = $u['username'] ?? '';
                            $uBio = $u['bio'] ?? '';
                            $uPostsCount = (int)($u['posts_count'] ?? 0);
                            $uFollowersCount = (int)($u['followers_count'] ?? 0);
                            $isFollowing = (bool)($u['is_following'] ?? false);
                            $isSelf = ($currentUserId === $uId);

                            $uAvatarPath = null;
                            if (!empty($u['profile_image'])) {
                                $cPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($u['profile_image']);
                                if (file_exists($cPath)) {
                                    $uAvatarPath = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($u['profile_image']));
                                }
                            }

                            $parts = explode(' ', trim($uFullName));
                            $uInitials = count($parts) >= 2
                                ? strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1))
                                : strtoupper(mb_substr($uFullName, 0, 2));
                            ?>
                            <div class="col-12 col-md-6">
                                <div class="card-modern p-3 h-100 border-0 shadow-sm d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <!-- Avatar Link -->
                                            <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $uId ?>" class="text-decoration-none flex-shrink-0">
                                                <?php if (!empty($uAvatarPath)): ?>
                                                    <img src="<?= $uAvatarPath ?>" alt="<?= htmlspecialchars($uFullName); ?>" class="friend-card-avatar" onerror="this.onerror=null; this.outerHTML='<div class=\'friend-card-avatar-initials\'><?= htmlspecialchars($uInitials); ?></div>';">
                                                <?php else: ?>
                                                    <div class="friend-card-avatar-initials">
                                                        <?= htmlspecialchars($uInitials); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </a>

                                            <!-- User Details -->
                                            <div class="min-w-0 flex-grow-1">
                                                <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $uId ?>" class="text-decoration-none">
                                                    <h6 class="fw-bold insights-title mb-0 text-truncate"><?= htmlspecialchars($uFullName); ?></h6>
                                                </a>
                                                <div class="text-muted small text-truncate">@<?= htmlspecialchars($uUsername); ?></div>
                                            </div>
                                        </div>

                                        <?php if (!empty($uBio)): ?>
                                            <p class="micro-text text-secondary mb-2 text-truncate-2" title="<?= htmlspecialchars($uBio); ?>">
                                                <?= htmlspecialchars($uBio); ?>
                                            </p>
                                        <?php endif; ?>

                                        <!-- Aggregate Stats (Posts & Followers) -->
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-light text-secondary border micro-text">
                                                <i class="bi bi-file-earmark-post me-1"></i><?= $uPostsCount; ?> <?= $uPostsCount === 1 ? 'post' : 'posts'; ?>
                                            </span>
                                            <span class="badge bg-light text-secondary border micro-text">
                                                <i class="bi bi-people me-1"></i><?= $uFollowersCount; ?> <?= $uFollowersCount === 1 ? 'follower' : 'followers'; ?>
                                            </span>
                                            <?php if ($isFollowing): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle micro-text">
                                                    <i class="bi bi-check-lg me-1"></i>Following
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Action Link -->
                                    <div class="pt-2 border-top insights-border d-flex justify-content-between align-items-center">
                                        <?php if ($isSelf): ?>
                                            <span class="badge bg-secondary-subtle text-secondary micro-text">Your Profile</span>
                                            <a href="<?= BASE_URL ?>/index.php?action=profile" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 micro-text fw-semibold">
                                                View Profile
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $uId ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 micro-text fw-semibold">
                                                View Profile
                                            </a>
                                            <form action="<?= BASE_URL ?>/index.php?action=follow_user" method="POST" class="d-inline">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="user_id" value="<?= $uId ?>">
                                                <input type="hidden" name="redirect" value="friends">
                                                <button type="submit" class="btn btn-sm <?= $isFollowing ? 'btn-outline-secondary' : 'btn-navy'; ?> rounded-pill px-3 py-1 micro-text fw-semibold">
                                                    <i class="bi <?= $isFollowing ? 'bi-check-lg' : 'bi-person-plus-fill'; ?> me-1"></i>
                                                    <?= $isFollowing ? 'Following' : 'Follow'; ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 5. Post Results List -->
                <?php if ($type === 'posts' && !empty($posts)): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($posts as $post): ?>
                            <?php
                            $authorId = (int)$post['user_id'];
                            $authorName = $post['full_name'] ?? 'User';
                            $authorUsername = $post['username'] ?? '';
                            $postTime = date('M j, Y \a\t g:i a', strtotime($post['created_at']));
                            $likesCount = (int)($post['likes_count'] ?? 0);
                            $commentsCount = (int)($post['comments_count'] ?? 0);
                            $isLiked = !empty($post['is_liked']);

                            $authorAvatarPath = null;
                            if (!empty($post['profile_image'])) {
                                $cPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($post['profile_image']);
                                if (file_exists($cPath)) {
                                    $authorAvatarPath = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($post['profile_image']));
                                }
                            }

                            $authorParts = explode(' ', trim($authorName));
                            $authorInitials = count($authorParts) >= 2
                                ? strtoupper(mb_substr($authorParts[0], 0, 1) . mb_substr($authorParts[count($authorParts)-1], 0, 1))
                                : strtoupper(mb_substr($authorName, 0, 2));
                            ?>
                            <article class="card-modern p-3 p-md-4 border-0 shadow-sm" id="post-<?= (int)$post['id'] ?>">
                                <!-- Author Header -->
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $authorId ?>" class="text-decoration-none">
                                            <?php if (!empty($authorAvatarPath)): ?>
                                                <img src="<?= $authorAvatarPath ?>" alt="<?= htmlspecialchars($authorName); ?>" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.onerror=null; this.outerHTML='<div class=\'user-avatar-sm\' style=\'width: 40px; height: 40px; font-size: 0.85rem;\'><?= htmlspecialchars($authorInitials) ?></div>';">
                                            <?php else: ?>
                                                <div class="user-avatar-sm" style="width: 40px; height: 40px; font-size: 0.85rem;">
                                                    <?= htmlspecialchars($authorInitials) ?>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        <div>
                                            <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $authorId ?>" class="fw-bold insights-title text-decoration-none small d-block">
                                                <?= htmlspecialchars($authorName); ?>
                                            </a>
                                            <div class="text-muted micro-text">
                                                @<?= htmlspecialchars($authorUsername); ?> &bull; <?= htmlspecialchars($postTime); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Functional Feed Link -->
                                    <a href="<?= BASE_URL ?>/index.php?action=home#post-<?= (int)$post['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 micro-text" title="View in Newsfeed">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Feed
                                    </a>
                                </div>

                                <!-- Post Content -->
                                <div class="insights-title mb-3" style="font-size: 0.95rem; line-height: 1.6; word-break: break-word;">
                                    <?= nl2br(htmlspecialchars($post['content'])); ?>
                                </div>

                                <!-- Image Attachment(s) -->
                                <?php
                                $postImages = !empty($post['images']) ? $post['images'] : PostModel::parseImages($post['image'] ?? null);
                                $totalPostImages = count($postImages);
                                ?>
                                <?php if ($totalPostImages === 1): ?>
                                    <?php
                                    $singleImg = $postImages[0];
                                    $postImgFile = __DIR__ . '/../../../public/uploads/posts/' . basename($singleImg);
                                    $hasRealImage = file_exists($postImgFile);
                                    ?>
                                    <?php if ($hasRealImage): ?>
                                        <div class="post-image-container mb-3" style="max-height: 400px; overflow: hidden; border-radius: 12px;">
                                            <a href="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($singleImg)) ?>" class="post-lightbox-trigger" data-gallery="post-<?= (int)$post['id'] ?>" title="View full photo">
                                                <img src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($singleImg)) ?>" alt="Post photo" class="img-fluid rounded-3" style="width: 100%; max-height: 400px; object-fit: cover;" loading="lazy">
                                            </a>
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
                                                    <img src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($imgFile)) ?>" alt="Post photo <?= $idx + 1 ?>" loading="lazy">
                                                    <?php if ($isLastQuadrant): ?>
                                                        <div class="post-gallery-overlay">
                                                            <span>+<?= $remainingCount ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Engagement Stats & Actions -->
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top insights-border">
                                    <div class="d-flex align-items-center gap-2">
                                        <form action="<?= BASE_URL ?>/index.php?action=like_post" method="POST" class="d-inline">
                                            <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                            <?= csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm <?= $isLiked ? 'btn-danger' : 'btn-outline-danger'; ?> rounded-pill px-3 py-1 micro-text fw-medium" title="Like">
                                                <i class="bi <?= $isLiked ? 'bi-heart-fill' : 'bi-heart'; ?> me-1"></i>
                                                <span class="post-likes-count" data-post-id="<?= (int)$post['id']; ?>"><?= $likesCount; ?></span>
                                            </button>
                                        </form>

                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 micro-text fw-medium" onclick="toggleSearchPostComments(<?= (int)$post['id'] ?>)">
                                            <i class="bi bi-chat-dots me-1"></i> <?= $commentsCount; ?> <?= $commentsCount === 1 ? 'Comment' : 'Comments'; ?>
                                        </button>
                                    </div>

                                    <?php if ($currentUserId === $authorId): ?>
                                        <span class="badge bg-light text-secondary border micro-text insights-badge-subtle">Your Post</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Comments Expansion Box -->
                                <div class="comments-container d-none mt-3 pt-2 border-top insights-border" id="search-comments-box-<?= (int)$post['id'] ?>">
                                    <?php if (!empty($post['comments'])): ?>
                                        <div class="d-flex flex-column gap-2 mb-2">
                                            <?php foreach ($post['comments'] as $comment): ?>
                                                <div class="p-2 rounded-3 border bg-body small">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$comment['user_id']; ?>" class="fw-bold insights-title text-decoration-none micro-text">
                                                            @<?= htmlspecialchars($comment['username']); ?>
                                                        </a>
                                                        <span class="text-muted micro-text"><?= date('M j, g:i a', strtotime($comment['created_at'])); ?></span>
                                                    </div>
                                                    <div class="micro-text"><?= nl2br(htmlspecialchars($comment['content'])); ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted micro-text mb-2">No comments on this post yet.</p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modeUsersBtn = document.getElementById('btnModeUsers');
    const modePostsBtn = document.getElementById('btnModePosts');
    const typeInput = document.getElementById('searchTypeInput');
    const filterInput = document.getElementById('searchFilterInput');
    const searchInput = document.getElementById('communitySearchInput');
    const searchForm = document.getElementById('communitySearchForm');
    const clearBtn = document.getElementById('clearInputBtn');

    const usersGroup = document.querySelector('.filter-group-users');
    const postsGroup = document.querySelector('.filter-group-posts');

    // Placeholders mapping
    const placeholders = {
        users: 'Search by name or username...',
        posts: 'Search posts by keywords...'
    };

    function setMode(mode, submitOnChange = false) {
        if (!typeInput || !searchInput) return;

        typeInput.value = mode;
        searchInput.placeholder = placeholders[mode] || placeholders.users;

        if (mode === 'users') {
            modeUsersBtn?.classList.add('active');
            modeUsersBtn?.setAttribute('aria-selected', 'true');
            modePostsBtn?.classList.remove('active');
            modePostsBtn?.setAttribute('aria-selected', 'false');

            usersGroup?.classList.remove('d-none');
            usersGroup?.classList.add('d-inline-flex');
            postsGroup?.classList.remove('d-inline-flex');
            postsGroup?.classList.add('d-none');

            // Default filter for users
            if (filterInput && !['all', 'following'].includes(filterInput.value)) {
                filterInput.value = 'all';
            }
        } else {
            modePostsBtn?.classList.add('active');
            modePostsBtn?.setAttribute('aria-selected', 'true');
            modeUsersBtn?.classList.remove('active');
            modeUsersBtn?.setAttribute('aria-selected', 'false');

            postsGroup?.classList.remove('d-none');
            postsGroup?.classList.add('d-inline-flex');
            usersGroup?.classList.remove('d-inline-flex');
            usersGroup?.classList.add('d-none');

            // Default filter for posts
            if (filterInput && !['newest', 'most_liked', 'photos'].includes(filterInput.value)) {
                filterInput.value = 'newest';
            }
        }

        // If user already entered text and clicked the other mode, submit to switch view
        if (submitOnChange && searchInput.value.trim().length > 0) {
            searchForm?.submit();
        } else {
            searchInput.focus();
        }
    }

    modeUsersBtn?.addEventListener('click', function() {
        if (typeInput?.value !== 'users') {
            setMode('users', true);
        }
    });

    modePostsBtn?.addEventListener('click', function() {
        if (typeInput?.value !== 'posts') {
            setMode('posts', true);
        }
    });

    // Handle filter pill clicks
    document.querySelectorAll('.filter-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            const fVal = this.getAttribute('data-filter');
            if (filterInput) {
                filterInput.value = fVal;
            }

            const parent = this.closest('.filter-group-users, .filter-group-posts');
            if (parent) {
                parent.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
            }
            this.classList.add('active');

            if (searchInput && searchInput.value.trim().length > 0) {
                searchForm?.submit();
            }
        });
    });

    // Clear input button handler
    clearBtn?.addEventListener('click', function() {
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
            clearBtn.style.display = 'none';
        }
    });

    // Quick tag pills in initial state
    document.querySelectorAll('.quick-search-tag').forEach(function(pill) {
        pill.addEventListener('click', function() {
            const q = this.getAttribute('data-query');
            const targetType = this.getAttribute('data-type') || 'posts';
            const targetFilter = this.getAttribute('data-filter') || (targetType === 'posts' ? 'newest' : 'all');
            if (searchInput && typeInput && searchForm) {
                searchInput.value = q;
                typeInput.value = targetType;
                if (filterInput) filterInput.value = targetFilter;
                searchForm.submit();
            }
        });
    });
});

function toggleSearchPostComments(postId) {
    const box = document.getElementById('search-comments-box-' + postId);
    if (!box) return;
    box.classList.toggle('d-none');
}
</script>
