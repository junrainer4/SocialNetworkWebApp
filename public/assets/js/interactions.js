

window.toggleComments = function(postId) {
    if (!postId) return;
    const box = document.getElementById('comments-box-' + postId);
    if (!box) return;

    if (box.classList.contains('d-none')) {
        box.classList.remove('d-none');
        const input = box.querySelector('input[name="content"], textarea[name="content"]');
        if (input) {
            setTimeout(() => input.focus(), 60);
        }
    } else {
        box.classList.add('d-none');
    }
};

document.addEventListener('DOMContentLoaded', function() {

    const baseUrl = (typeof window.BASE_URL !== 'undefined') ? window.BASE_URL : '';

    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !form.action || !form.action.includes('action=like_post')) {
            return;
        }

        e.preventDefault();
        const formData = new FormData(form);
        formData.append('ajax', '1');

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        if (csrfToken && !formData.has('csrf_token')) {
            formData.append('csrf_token', csrfToken);
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const heartIcon = submitBtn ? submitBtn.querySelector('i') : null;
        const countSpan = submitBtn ? submitBtn.querySelector('span') : null;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success) {
                updatePostLikesUI(data.post_id, data.is_liked, data.likes_count);
            }
        })
        .catch(err => console.error('Error toggling like:', err));
    });

    function updatePostLikesUI(postId, isLiked, likesCount) {
        const likeForms = document.querySelectorAll(`form[action*="action=like_post"] input[name="post_id"][value="${postId}"]`);
        likeForms.forEach(input => {
            const form = input.closest('form');
            if (!form) return;
            const btn = form.querySelector('button[type="submit"]');
            if (!btn) return;

            const heartIcon = btn.querySelector('i');
            let countSpan = btn.querySelector('.post-likes-count') || btn.querySelector('span');

            if (btn.classList.contains('post-action-btn')) {
                if (isLiked) {
                    btn.classList.add('liked');
                    if (heartIcon) heartIcon.className = 'bi bi-hand-thumbs-up-fill fs-5';
                } else {
                    btn.classList.remove('liked');
                    if (heartIcon) heartIcon.className = 'bi bi-hand-thumbs-up fs-5';
                }
                const labelSpan = btn.querySelector('span');
                if (labelSpan) labelSpan.textContent = 'Like';
            }

            else {
                if (isLiked) {
                    btn.classList.remove('btn-outline-danger');
                    btn.classList.add('btn-danger');
                    if (heartIcon) heartIcon.className = 'bi bi-heart-fill me-1';
                } else {
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-outline-danger');
                    if (heartIcon) heartIcon.className = 'bi bi-heart me-1';
                }
                let countSpan = btn.querySelector('.post-likes-count') || btn.querySelector('span');
                if (countSpan) countSpan.textContent = likesCount;
            }
        });

        document.querySelectorAll(`.post-likes-count[data-post-id="${postId}"]`).forEach(el => {
            el.textContent = likesCount;
        });
    }

    window.openPostLikersModal = function(postId) {
        if (!postId) return;
        const modalEl = document.getElementById('postLikersModal');
        if (!modalEl) return;

        const modalTitle = document.getElementById('postLikersModalLabel');
        const modalBody = document.getElementById('postLikersModalBody');

        if (modalTitle) {
            modalTitle.textContent = 'People who reacted';
        }

        if (modalBody) {
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border spinner-border-sm text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted small mt-2 mb-0">Loading reactions...</p>
                </div>
            `;
        }

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();

        const fetchUrl = `${baseUrl}/index.php?action=get_post_likers&post_id=${encodeURIComponent(postId)}`;

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (!modalBody) return;

            if (!data.success) {
                modalBody.innerHTML = `
                    <div class="alert alert-danger m-3 text-center small rounded-3">
                        ${escapeHtml(data.error || 'Failed to load reactions.')}
                    </div>
                `;
                return;
            }

            if (modalTitle) {
                modalTitle.textContent = `People who reacted (${data.count})`;
            }

            if (!data.users || data.users.length === 0) {
                modalBody.innerHTML = `
                    <div class="text-center py-5 px-3 text-muted">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-3 mb-2 border shadow-sm" style="width: 56px; height: 56px;">
                            <i class="bi bi-heart text-danger fs-3"></i>
                        </div>
                        <h6 class="fw-semibold text-dark mb-1">No reactions yet</h6>
                        <p class="small text-muted mb-0">Be the first to react to this post!</p>
                    </div>
                `;
                return;
            }

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            let html = '<div class="list-group list-group-flush">';
            data.users.forEach(u => {
                const profileUrl = `${baseUrl}/index.php?action=profile&user_id=${u.id}`;
                let avatarHtml = '';
                if (u.profile_image) {
                    const imgUrl = `${baseUrl}/public/uploads/avatars/${encodeURIComponent(u.profile_image)}`;
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
                                <form action="${baseUrl}/index.php?action=follow_user" method="POST" class="m-0 d-inline">
                                    <input type="hidden" name="csrf_token" value="${escapeHtml(csrfToken)}">
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

            modalBody.innerHTML = html;
        })
        .catch(err => {
            console.error('Error fetching post likers:', err);
            if (modalBody) {
                modalBody.innerHTML = `
                    <div class="alert alert-danger m-3 text-center small rounded-3">
                        Failed to load reactions. Please try again.
                    </div>
                `;
            }
        });
    };

    document.addEventListener('click', function(e) {
        const countTrigger = e.target.closest('.post-likes-count');
        if (!countTrigger) return;

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        const postId = countTrigger.getAttribute('data-post-id');
        if (postId) {
            window.openPostLikersModal(postId);
        }
    }, true);

    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !form.action || (!form.action.includes('action=create_comment') && !form.action.includes('action=add_comment'))) {
            return;
        }

        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const inputField = form.querySelector('input[name="content"]');

        if (!inputField || !inputField.value.trim()) return;

        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('ajax', '1');

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        if (csrfToken && !formData.has('csrf_token')) {
            formData.append('csrf_token', csrfToken);
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data && data.success) {
                inputField.value = '';
                appendCommentToPost(data.post_id, data.comment, data.comments_count);
            } else if (data && data.error) {
                alert(data.error);
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            console.error('Error posting comment:', err);
        });
    });

    function appendCommentToPost(postId, comment, totalCommentsCount) {
        const commentsContainer = document.getElementById(`comments-box-${postId}`);
        if (!commentsContainer) return;

        if (commentsContainer.classList.contains('d-none')) {
            commentsContainer.classList.remove('d-none');
        }

        const commentAuthorUrl = `${baseUrl}/index.php?action=profile&user_id=${comment.user_id}`;
        const avatarHtml = comment.profile_image
            ? `<a href="${commentAuthorUrl}" class="text-decoration-none flex-shrink-0" title="View ${escapeHtml(comment.full_name)}'s profile"><img src="${baseUrl}/public/uploads/avatars/${comment.profile_image}" alt="${escapeHtml(comment.full_name)}" class="comment-avatar" onerror="this.onerror=null; this.outerHTML='<div class=\\\\\\\\\''''comment-avatar\\\\\\\\\''''>${escapeHtml(comment.initials)}</div>';"></a>`
            : `<a href="${commentAuthorUrl}" class="text-decoration-none flex-shrink-0" title="View ${escapeHtml(comment.full_name)}'s profile"><div class="comment-avatar">${escapeHtml(comment.initials)}</div></a>`;

        const commentBubbleHtml = `
            <div class="comment-bubble" id="comment-${comment.id}">
                ${avatarHtml}
                <div class="comment-content-box">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                        <div>
                            <a href="${commentAuthorUrl}" class="comment-author text-decoration-none" title="View ${escapeHtml(comment.full_name)}'s profile">${escapeHtml(comment.full_name)}</a>
                            <a href="${commentAuthorUrl}" class="text-muted small text-decoration-none">@${escapeHtml(comment.username)}</a>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-muted" title="${escapeHtml(comment.created_at)}">
                                ${escapeHtml(comment.time_badge)}
                            </small>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Comment options" style="line-height: 1;">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="min-width: 120px; font-size: 0.85rem;">
                                    <li>
                                        <a class="dropdown-item py-1 text-secondary d-flex align-items-center gap-2" href="${baseUrl}/index.php?action=edit_comment&comment_id=${comment.id}">
                                            <i class="bi bi-pencil-square text-muted"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <form action="${baseUrl}/index.php?action=delete_comment" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this comment? This action cannot be undone.');">
                                            <input type="hidden" name="csrf_token" value="${escapeHtml(document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '')}">
                                            <input type="hidden" name="comment_id" value="${comment.id}">
                                            <button type="submit" class="dropdown-item py-1 text-secondary d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start">
                                                <i class="bi bi-trash text-muted"></i>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-text">
                        ${escapeHtml(comment.content).replace(/\n/g, '<br>')}
                    </div>
                </div>
            </div>
        `;

        const commentForm = commentsContainer.querySelector('form.comment-input-row');
        if (commentForm) {
            commentForm.insertAdjacentHTML('beforebegin', commentBubbleHtml);
        } else {
            commentsContainer.insertAdjacentHTML('beforeend', commentBubbleHtml);
        }

        const postCard = document.getElementById(`post-${postId}`);
        if (postCard) {
            const commentCountSpan = postCard.querySelector('.post-action-btn[onclick*="toggleComments"] span');
            if (commentCountSpan) {
                commentCountSpan.textContent = totalCommentsCount;
            }
        }
    }

    const composerForm = document.querySelector('.composer-card form');
    if (composerForm) {
        composerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const contentInput = composerForm.querySelector('textarea[name="content"], input[name="content"]');
            const fileInput = composerForm.querySelector('input[type="file"]');
            const hasContent = contentInput && contentInput.value.trim().length > 0;
            const hasFiles = fileInput && fileInput.files && fileInput.files.length > 0;

            if (!hasContent && !hasFiles) {
                alert('Please enter some text or select at least one photo to publish a post.');
                if (contentInput) contentInput.focus();
                return;
            }

            const submitBtn = composerForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Post';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Posting...';
            }

            const formData = new FormData(composerForm);
            formData.append('ajax', '1');

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            if (csrfToken && !formData.has('csrf_token')) {
                formData.append('csrf_token', csrfToken);
            }

            fetch(composerForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }

                if (data && data.success) {
                    composerForm.reset();
                    if (typeof window.clearAllSelectedImages === 'function') {
                        window.clearAllSelectedImages();
                    } else if (typeof window.clearSelectedImage === 'function') {
                        window.clearSelectedImage();
                    }
                    prependNewPostToFeed(data.post);
                } else if (data && data.error) {
                    alert(data.error);
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
                console.error('Error publishing post:', err);
            });
        });
    }

    function buildPostImagesHtml(post) {
        let images = [];
        if (Array.isArray(post.images) && post.images.length > 0) {
            images = post.images;
        } else if (post.image) {
            if (typeof post.image === 'string' && post.image.startsWith('[') && post.image.endsWith(']')) {
                try {
                    const parsed = JSON.parse(post.image);
                    if (Array.isArray(parsed)) images = parsed;
                } catch (e) {
                    images = [post.image];
                }
            } else {
                images = [post.image];
            }
        }

        const total = images.length;
        if (total === 0) return '';

        if (total === 1) {
            return `
                <div class="post-image-container mb-3">
                    <a href="${baseUrl}/public/uploads/posts/${escapeHtml(images[0])}" class="post-lightbox-trigger" data-gallery="post-${post.id}" title="View full image">
                        <img src="${baseUrl}/public/uploads/posts/${escapeHtml(images[0])}" alt="Post attachment" loading="lazy" onload="this.naturalHeight > this.naturalWidth ? (this.closest('.post-image-container')?.classList.add('is-portrait'), this.closest('.post-image-container')?.classList.remove('is-landscape')) : (this.closest('.post-image-container')?.classList.add('is-landscape'), this.closest('.post-image-container')?.classList.remove('is-portrait'));">
                    </a>
                </div>
            `;
        }

        const countClass = Math.min(total, 4);
        let galleryItemsHtml = '';

        images.slice(0, 4).forEach((img, idx) => {
            const isLastQuadrant = (idx === 3 && total > 4);
            const remainingCount = total - 4;
            const overlayHtml = isLastQuadrant
                ? `<div class="post-gallery-overlay"><span>+${remainingCount}</span></div>`
                : '';

            galleryItemsHtml += `
                <div class="post-gallery-item">
                    <a href="${baseUrl}/public/uploads/posts/${escapeHtml(img)}" class="post-lightbox-trigger" data-gallery="post-${post.id}" title="View image ${idx + 1}">
                        <img src="${baseUrl}/public/uploads/posts/${escapeHtml(img)}" alt="Post image ${idx + 1}" loading="lazy">
                        ${overlayHtml}
                    </a>
                </div>
            `;
        });

        let hiddenLinksHtml = '';
        if (total > 4) {
            images.slice(4).forEach((extraImg, extraIdx) => {
                hiddenLinksHtml += `
                    <a href="${baseUrl}/public/uploads/posts/${escapeHtml(extraImg)}" class="post-lightbox-trigger d-none" data-gallery="post-${post.id}" title="View image ${5 + extraIdx}"></a>
                `;
            });
        }

        return `
            <div class="post-gallery post-gallery-${countClass} mb-3" data-post-id="${post.id}">
                ${galleryItemsHtml}
                ${hiddenLinksHtml}
            </div>
        `;
    }

    function prependNewPostToFeed(post) {
        const composerCard = document.querySelector('.composer-card');
        const feedHeader = composerCard ? composerCard.nextElementSibling : null;

        let feedParent = composerCard ? composerCard.parentElement : null;
        if (!feedParent) return;

        const emptyStateCard = feedParent.querySelector('.card-modern.p-5.text-center.text-muted');
        if (emptyStateCard) {
            emptyStateCard.remove();
        }

        const postAuthorUrl = `${baseUrl}/index.php?action=profile&user_id=${post.user_id}`;
        const avatarHtml = post.profile_image
            ? `<a href="${postAuthorUrl}" class="text-decoration-none flex-shrink-0" title="View ${escapeHtml(post.full_name)}'s profile"><img src="${baseUrl}/public/uploads/avatars/${post.profile_image}" alt="${escapeHtml(post.full_name)}" class="post-avatar" onerror="this.onerror=null; this.outerHTML='<div class=\\\\\\\\\''''post-avatar\\\\\\\\\''''>${escapeHtml(post.initials)}</div>';"></a>`
            : `<a href="${postAuthorUrl}" class="text-decoration-none flex-shrink-0" title="View ${escapeHtml(post.full_name)}'s profile"><div class="post-avatar">${escapeHtml(post.initials)}</div></a>`;

        const imageHtml = buildPostImagesHtml(post);

        const postCardHtml = `
            <article class="post-card" id="post-${post.id}">
                <div class="post-header">
                    <div class="post-author-box">
                        ${avatarHtml}
                        <div>
                            <a href="${postAuthorUrl}" class="post-author-name" title="View ${escapeHtml(post.full_name)}'s profile">${escapeHtml(post.full_name)}</a>
                            <div class="post-meta">
                                <a href="${postAuthorUrl}" class="text-muted text-decoration-none">@${escapeHtml(post.username)}</a> &bull; ${escapeHtml(post.time_badge)}
                            </div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Post actions">
                            <i class="bi bi-three-dots fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li>
                                <a class="dropdown-item small text-secondary" href="${baseUrl}/index.php?action=edit_post&post_id=${post.id}">
                                    <i class="bi bi-pencil-square me-2 text-muted"></i>Edit post
                                </a>
                            </li>
                            <li>
                                <form action="${baseUrl}/index.php?action=delete_post" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="${escapeHtml(document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '')}">
                                    <input type="hidden" name="post_id" value="${post.id}">
                                    <button type="submit" class="dropdown-item small text-secondary border-0 bg-transparent w-100 text-start">
                                        <i class="bi bi-trash me-2 text-muted"></i>Delete post
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="post-content">
                    ${escapeHtml(post.content).replace(/\n/g, '<br>')}
                </div>

                ${imageHtml}

                <div class="post-footer-actions d-flex align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <form action="${baseUrl}/index.php?action=like_post" method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="${escapeHtml(document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '')}">
                            <input type="hidden" name="post_id" value="${post.id}">
                            <button type="submit" class="post-action-btn" title="Like">
                                <i class="bi bi-heart"></i>
                                <span class="post-likes-count" data-post-id="${post.id}" title="View reactions">0</span>
                            </button>
                        </form>
                        <button type="button" class="post-action-btn" onclick="toggleComments(${post.id})" title="Comments">
                            <i class="bi bi-chat"></i>
                            <span>0</span>
                        </button>
                    </div>
                </div>

                <div class="comments-container d-none" id="comments-box-${post.id}">
                    <form action="${baseUrl}/index.php?action=create_comment" method="POST" class="comment-input-row">
                        <input type="hidden" name="csrf_token" value="${escapeHtml(document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '')}">
                        <input type="hidden" name="post_id" value="${post.id}">
                        <input type="text" name="content" class="form-control" placeholder="Write a comment..." maxlength="1000" required>
                        <button type="submit" class="btn btn-sm btn-accent rounded-pill px-3" title="Post comment">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>
            </article>
        `;

        const firstPost = feedParent.querySelector('.post-card');
        if (firstPost) {
            firstPost.insertAdjacentHTML('beforebegin', postCardHtml);
        } else {
            feedParent.insertAdjacentHTML('beforeend', postCardHtml);
        }
    }

    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !form.action || !form.action.includes('action=delete_post')) {
            return;
        }

        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('ajax', '1');

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        if (csrfToken && !formData.has('csrf_token')) {
            formData.append('csrf_token', csrfToken);
        }

        const postIdInput = form.querySelector('input[name="post_id"]');
        const postId = postIdInput ? postIdInput.value : '';

        const isSinglePostView = window.location.search.includes('action=view_post') || window.location.search.includes('action=post');

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                if (isSinglePostView) {
                    window.location.href = baseUrl + '/index.php';
                    return;
                }

                const postEl = document.getElementById('post-' + postId) || form.closest('.post-card');
                if (postEl) {
                    postEl.style.transition = 'opacity 0.25s ease, transform 0.25s ease, max-height 0.3s ease, margin 0.3s ease, padding 0.3s ease';
                    postEl.style.opacity = '0';
                    postEl.style.transform = 'scale(0.96)';
                    setTimeout(() => {
                        postEl.style.maxHeight = '0px';
                        postEl.style.overflow = 'hidden';
                        postEl.style.marginTop = '0px';
                        postEl.style.marginBottom = '0px';
                        postEl.style.paddingTop = '0px';
                        postEl.style.paddingBottom = '0px';
                        postEl.style.border = 'none';
                        setTimeout(() => {
                            postEl.remove();
                        }, 300);
                    }, 250);
                }
            } else {
                if (submitBtn) submitBtn.disabled = false;
                alert(data && data.error ? data.error : 'Failed to delete post. Please try again.');
            }
        })
        .catch(err => {
            console.error('Delete post error:', err);
            if (submitBtn) submitBtn.disabled = false;
            form.submit();
        });
    });

    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !form.action || !form.action.includes('action=follow_user')) {
            return;
        }

        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('ajax', '1');

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        if (csrfToken && !formData.has('csrf_token')) {
            formData.append('csrf_token', csrfToken);
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data && data.success) {

                if (submitBtn) {
                    const icon = submitBtn.querySelector('i');
                    const textSpan = submitBtn.querySelector('span');
                    const isSm = submitBtn.classList.contains('btn-sm');
                    const isFollowedBy = (typeof data.is_followed_by !== 'undefined')
                        ? Boolean(data.is_followed_by)
                        : (submitBtn.getAttribute('data-is-followed-by') === '1');

                    submitBtn.setAttribute('data-is-followed-by', isFollowedBy ? '1' : '0');
                    const padClass = isSm ? 'px-3' : 'px-4';
                    const smClass = isSm ? 'btn-sm' : '';

                    if (data.is_following) {
                        submitBtn.className = `btn btn-outline-secondary ${smClass} ${padClass} fw-semibold rounded-pill`.replace(/\s+/g, ' ').trim();
                        if (icon) icon.className = 'bi bi-person-check-fill me-1';
                        if (textSpan) textSpan.textContent = 'Following';
                    } else {
                        if (isFollowedBy) {
                            submitBtn.className = `btn btn-primary ${smClass} ${padClass} fw-semibold rounded-pill`.replace(/\s+/g, ' ').trim();
                            if (icon) icon.className = 'bi bi-arrow-repeat me-1';
                            if (textSpan) textSpan.textContent = 'Follow Back';
                        } else {
                            submitBtn.className = `btn btn-navy ${smClass} ${padClass} fw-semibold rounded-pill`.replace(/\s+/g, ' ').trim();
                            if (icon) icon.className = 'bi bi-person-plus-fill me-1';
                            if (textSpan) textSpan.textContent = 'Follow';
                        }
                    }
                }

                const followersCountEl = document.getElementById('profileFollowersCount');
                if (followersCountEl && typeof data.followers_count !== 'undefined') {
                    followersCountEl.textContent = data.followers_count;
                }
            } else if (data && data.error) {
                alert(data.error);
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            console.error('Follow error:', err);
        });
    });

    function highlightPostElement(postEl) {
        if (!postEl) return;
        document.querySelectorAll('.post-card.post-highlight').forEach(el => {
            el.classList.remove('post-highlight');
        });
    }

    function highlightCommentElement(commentEl) {
        if (!commentEl) return;

        document.querySelectorAll('.comment-bubble.comment-highlight').forEach(el => {
            el.classList.remove('comment-highlight');
        });
        document.querySelectorAll('.post-card.post-highlight').forEach(el => {
            el.classList.remove('post-highlight');
        });
        commentEl.classList.add('comment-highlight');
    }

    if (window.location.hash) {
        const hash = window.location.hash;
        if (hash.startsWith('#comment-')) {
            const commentId = hash.substring('#comment-'.length);
            const targetComment = document.getElementById('comment-' + commentId);
            if (targetComment) {

                const parentBox = targetComment.closest('.comments-container');
                if (parentBox && parentBox.classList.contains('d-none')) {
                    parentBox.classList.remove('d-none');
                }
                setTimeout(() => {
                    targetComment.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    highlightCommentElement(targetComment);
                }, 350);
            }

            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, document.title, window.location.pathname + window.location.search);
            }
        } else if (hash.startsWith('#post-')) {
            const targetPost = document.getElementById(hash.substring(1));
            if (targetPost) {
                setTimeout(() => {
                    targetPost.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    const postId = hash.replace('#post-', '');
                    const commentsBox = document.getElementById('comments-box-' + postId);
                    if (commentsBox && commentsBox.classList.contains('d-none')) {
                        commentsBox.classList.remove('d-none');
                    }
                }, 300);
            }

            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, document.title, window.location.pathname + window.location.search);
            }
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    const searchInput = document.getElementById('headerSearchInput');
    const searchDropdown = document.getElementById('liveSearchDropdown');
    const searchResultsBox = document.getElementById('liveSearchResults');
    let searchDebounceTimer = null;

    function highlightMatch(text, query) {
        if (!text) return '';
        if (!query) return escapeHtml(text);
        const escapedText = escapeHtml(text);
        const escapedQuery = escapeHtml(query).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp('(' + escapedQuery + ')', 'gi');
        return escapedText.replace(regex, '<mark class="search-highlight">$1</mark>');
    }

    if (searchInput && searchDropdown && searchResultsBox) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchDebounceTimer);
            const query = this.value.trim();

            if (query.length === 0) {
                searchDropdown.classList.add('d-none');
                searchResultsBox.innerHTML = '';
                return;
            }

            searchDebounceTimer = setTimeout(function() {
                fetch(`${baseUrl}/index.php?action=live_search&q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data || !data.success) {
                            searchDropdown.classList.add('d-none');
                            return;
                        }

                        const users = data.users || [];
                        const posts = data.posts || [];

                        if (users.length === 0 && posts.length === 0) {
                            searchResultsBox.innerHTML = `
                                <div class="p-3 text-center text-muted small">
                                    No matches found for "<strong>${escapeHtml(query)}</strong>"
                                </div>
                            `;
                            searchDropdown.classList.remove('d-none');
                            return;
                        }

                        let html = '';

                        if (users.length > 0) {
                            html += `
                                <div class="search-section-header px-3 py-2 text-uppercase micro-text fw-bold text-muted border-bottom">
                                    People
                                </div>
                                <div class="search-section-list">
                            `;
                            users.forEach(u => {
                                const highlightedName = highlightMatch(u.full_name, query);
                                const highlightedUsername = highlightMatch('@' + u.username, query);
                                const avatarHtml = u.avatar
                                    ? `<img src="${u.avatar}" alt="" class="rounded-circle object-fit-cover flex-shrink-0" style="width: 36px; height: 36px;">`
                                    : `<div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: var(--fb-blue, #1877f2); font-size: 0.8rem;">${escapeHtml(u.initials)}</div>`;

                                html += `
                                    <a href="${baseUrl}/index.php?action=profile&id=${u.id}" class="search-result-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none">
                                        ${avatarHtml}
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-semibold text-dark small text-truncate mb-0">${highlightedName}</div>
                                            <div class="text-muted micro-text text-truncate">${highlightedUsername}</div>
                                        </div>
                                    </a>
                                `;
                            });
                            html += `</div>`;
                        }

                        if (posts.length > 0) {
                            html += `
                                <div class="search-section-header px-3 py-2 text-uppercase micro-text fw-bold text-muted border-bottom mt-1">
                                    Posts
                                </div>
                                <div class="search-section-list">
                            `;
                            posts.forEach(p => {
                                const highlightedSnippet = highlightMatch(p.snippet, query);
                                const highlightedAuthor = highlightMatch(p.author_name, query);

                                html += `
                                    <a href="${baseUrl}/index.php?action=view_post&id=${p.id}" class="search-result-item d-flex align-items-start gap-2 px-3 py-2 text-decoration-none">
                                        <div class="search-post-icon mt-1 flex-shrink-0 text-muted">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                            </svg>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="text-dark small text-truncate mb-0">${highlightedSnippet}</div>
                                            <div class="text-muted micro-text">${highlightedAuthor} &bull; ${escapeHtml(p.created_at)}</div>
                                        </div>
                                    </a>
                                `;
                            });
                            html += `</div>`;
                        }

                        html += `
                            <div class="search-footer-action p-2 border-top text-center">
                                <a href="${baseUrl}/index.php?action=search&q=${encodeURIComponent(query)}" class="small text-primary text-decoration-none fw-semibold d-inline-flex align-items-center gap-1">
                                    <span>See all results for "${escapeHtml(query)}"</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </a>
                            </div>
                        `;

                        searchResultsBox.innerHTML = html;
                        searchDropdown.classList.remove('d-none');
                    })
                    .catch(err => {
                        console.warn('Live search error:', err);
                    });
            }, 200);
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.classList.add('d-none');
            }
        });

        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length > 0 && searchResultsBox.innerHTML.trim().length > 0) {
                searchDropdown.classList.remove('d-none');
            }
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchDropdown.classList.add('d-none');
            }
        });
    }

    const getStoredSet = (key) => {
        try {
            const data = localStorage.getItem(key);
            return data ? new Set(JSON.parse(data)) : new Set();
        } catch (e) {
            return new Set();
        }
    };

    const saveStoredSet = (key, set) => {
        try {
            localStorage.setItem(key, JSON.stringify(Array.from(set)));
        } catch (e) {
            console.warn('LocalStorage error:', e);
        }
    };

    const readMsgs = getStoredSet('unify_read_msgs');
    const readNotifs = getStoredSet('unify_read_notifs');

    const notifBadge = document.getElementById('headerNotifBadge');
    const notifCountText = document.getElementById('notifNewBadgeCount');
    const notifItems = document.querySelectorAll('.user-notif-item');
    const markAllNotifBtn = document.getElementById('btnMarkAllNotifRead');

    const updateNotifUI = () => {
        let unreadCount = 0;
        notifItems.forEach(item => {
            const notifId = item.getAttribute('data-notif-id');
            const unreadDot = item.querySelector('.unread-dot');
            if (readNotifs.has(notifId)) {
                item.classList.remove('unread');
                if (unreadDot) unreadDot.style.display = 'none';
            } else {
                unreadCount++;
            }
        });

        if (notifBadge) {
            if (unreadCount > 0) {
                notifBadge.textContent = unreadCount;
                notifBadge.style.display = 'inline-block';
            } else {
                notifBadge.style.display = 'none';
            }
        }

        if (notifCountText) {
            if (unreadCount > 0) {
                notifCountText.textContent = `${unreadCount} New`;
                notifCountText.style.display = 'inline-block';
            } else {
                notifCountText.style.display = 'none';
            }
        }
    };

    updateNotifUI();

    notifItems.forEach(item => {
        item.addEventListener('click', function() {
            const notifId = this.getAttribute('data-notif-id');
            const target = this.getAttribute('data-target');

            if (notifId && !readNotifs.has(notifId)) {
                readNotifs.add(notifId);
                saveStoredSet('unify_read_notifs', readNotifs);
                updateNotifUI();
            }

            const notifDropdownBtn = document.getElementById('headerNotifBtn');
            if (notifDropdownBtn && window.bootstrap && bootstrap.Dropdown) {
                const bsDrop = bootstrap.Dropdown.getInstance(notifDropdownBtn);
                if (bsDrop) bsDrop.hide();
            }

            if (target === 'profile') {
                window.location.href = `${baseUrl}/index.php?action=profile`;
            } else if (target === 'feed') {
                if (window.location.pathname.includes('index.php') && window.location.search.includes('action=profile')) {
                    window.location.href = `${baseUrl}/`;
                } else {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });
    });

    if (markAllNotifBtn) {
        markAllNotifBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            notifItems.forEach(item => {
                const id = item.getAttribute('data-notif-id');
                if (id) readNotifs.add(id);
            });
            saveStoredSet('unify_read_notifs', readNotifs);
            updateNotifUI();
        });
    }

    const msgBadge = document.getElementById('headerMessagesBadge');
    const msgCountText = document.getElementById('msgNewBadgeCount');
    const msgItems = document.querySelectorAll('.msg-item');
    const markAllMsgBtn = document.getElementById('btnMarkAllMessagesRead');
    const chatSearchInput = document.getElementById('chatSearchInput');

    const chatDock = document.getElementById('fbFloatingChat');
    const chatContactName = document.getElementById('fbChatContactName');
    const chatStatusText = document.getElementById('fbChatStatusText');
    const chatAvatar = document.getElementById('fbChatAvatar');
    const chatMiniAvatar = document.getElementById('fbChatMiniAvatar');
    const chatInitialMsg = document.getElementById('fbChatInitialMsg');
    const chatMessagesBody = document.getElementById('fbChatMessagesBody');
    const chatMinimizeBtn = document.getElementById('fbChatMinimizeBtn');
    const chatMaximizeBtn = document.getElementById('fbChatMaximizeBtn');
    const chatCloseBtn = document.getElementById('fbChatCloseBtn');
    const chatForm = document.getElementById('fbChatForm');
    const chatInput = document.getElementById('fbChatInput');

    let currentChatContact = {
        name: 'Alice Wonderland',
        initials: 'AW',
        color: '#1877f2'
    };

    const updateMsgUI = () => {
        let unreadCount = 0;
        msgItems.forEach(item => {
            const msgId = item.getAttribute('data-msg-id');
            const unreadDot = item.querySelector('.unread-dot');
            if (readMsgs.has(msgId)) {
                item.classList.remove('unread');
                if (unreadDot) unreadDot.style.display = 'none';
            } else {
                unreadCount++;
            }
        });

        if (msgBadge) {
            if (unreadCount > 0) {
                msgBadge.textContent = unreadCount;
                msgBadge.style.display = 'inline-block';
            } else {
                msgBadge.style.display = 'none';
            }
        }

        if (msgCountText) {
            if (unreadCount > 0) {
                msgCountText.textContent = `${unreadCount} New`;
                msgCountText.style.display = 'inline-block';
            } else {
                msgCountText.style.display = 'none';
            }
        }
    };

    updateMsgUI();

    if (markAllMsgBtn) {
        markAllMsgBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            msgItems.forEach(item => {
                const id = item.getAttribute('data-msg-id');
                if (id) readMsgs.add(id);
            });
            saveStoredSet('unify_read_msgs', readMsgs);
            updateMsgUI();
        });
    }

    if (chatSearchInput) {
        chatSearchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            msgItems.forEach(item => {
                const name = (item.getAttribute('data-contact-name') || '').toLowerCase();
                const preview = (item.querySelector('.msg-preview')?.textContent || '').toLowerCase();
                if (!q || name.includes(q) || preview.includes(q)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    const scrollChatToBottom = () => {
        if (chatMessagesBody) {
            chatMessagesBody.scrollTop = chatMessagesBody.scrollHeight;
        }
    };

    msgItems.forEach(item => {
        item.addEventListener('click', function() {
            const msgId = this.getAttribute('data-msg-id');
            const contactName = this.getAttribute('data-contact-name') || 'Contact';
            const contactInitials = this.getAttribute('data-contact-initials') || 'U';
            const contactColor = this.getAttribute('data-contact-color') || '#1877f2';
            const msgPreview = this.querySelector('.msg-preview')?.textContent || 'Hello!';

            currentChatContact = {
                name: contactName,
                initials: contactInitials,
                color: contactColor
            };

            if (msgId && !readMsgs.has(msgId)) {
                readMsgs.add(msgId);
                saveStoredSet('unify_read_msgs', readMsgs);
                updateMsgUI();
            }

            const msgDropdownBtn = document.getElementById('headerMessagesBtn');
            if (msgDropdownBtn && window.bootstrap && bootstrap.Dropdown) {
                const bsDrop = bootstrap.Dropdown.getInstance(msgDropdownBtn);
                if (bsDrop) bsDrop.hide();
            }

            if (chatDock) {
                if (chatContactName) chatContactName.textContent = contactName;
                if (chatStatusText) chatStatusText.textContent = 'Active now';
                if (chatAvatar) {
                    chatAvatar.textContent = contactInitials;
                    chatAvatar.style.background = contactColor;
                }
                if (chatMiniAvatar) {
                    chatMiniAvatar.textContent = contactInitials;
                    chatMiniAvatar.style.background = contactColor;
                }
                if (chatInitialMsg) {
                    chatInitialMsg.textContent = msgPreview;
                }

                chatDock.classList.remove('d-none');
                chatDock.classList.remove('minimized');

                scrollChatToBottom();

                if (chatInput) {
                    setTimeout(() => chatInput.focus(), 150);
                }
            }
        });
    });

    if (chatMinimizeBtn && chatDock) {
        chatMinimizeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            chatDock.classList.toggle('minimized');
        });
    }

    if (chatMaximizeBtn && chatDock) {
        chatMaximizeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            chatDock.classList.remove('minimized');
            chatDock.classList.toggle('expanded');
            const isExp = chatDock.classList.contains('expanded');
            const maxIcon = chatMaximizeBtn.querySelector('.maximize-icon');
            const resIcon = chatMaximizeBtn.querySelector('.restore-icon');
            if (maxIcon && resIcon) {
                if (isExp) {
                    maxIcon.classList.add('d-none');
                    resIcon.classList.remove('d-none');
                    chatMaximizeBtn.setAttribute('title', 'Restore Chat Size');
                } else {
                    maxIcon.classList.remove('d-none');
                    resIcon.classList.add('d-none');
                    chatMaximizeBtn.setAttribute('title', 'Expand / Maximize Chat');
                }
            }
            scrollChatToBottom();
        });
    }

    if (chatCloseBtn && chatDock) {
        chatCloseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            chatDock.classList.add('d-none');
        });
    }

    const chatHeader = chatDock ? chatDock.querySelector('.fb-chat-header') : null;
    if (chatHeader && chatDock) {
        chatHeader.addEventListener('click', function(e) {
            if (e.target.closest('#fbChatMinimizeBtn') || e.target.closest('#fbChatMaximizeBtn') || e.target.closest('#fbChatCloseBtn')) {
                return;
            }
            if (chatDock.classList.contains('minimized')) {
                chatDock.classList.remove('minimized');
                scrollChatToBottom();
            }
        });
    }

    if (chatForm && chatInput && chatMessagesBody) {
        const simulatedReplies = [
            "Got it! Thanks for checking in.",
            "Sounds good! Let me take a look at that right away.",
            "Awesome! Loving the new Unify layout and speed.",
            "Thanks for the update! Catch you later today.",
            "Great! Talk soon."
        ];

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text) return;

            const outRow = document.createElement('div');
            outRow.className = 'd-flex align-items-end justify-content-end mb-2 chat-msg-row outgoing';
            outRow.innerHTML = `
                <div class="chat-bubble chat-bubble-out shadow-sm">
                    ${escapeHtml(text)}
                </div>
            `;
            chatMessagesBody.appendChild(outRow);
            chatInput.value = '';
            scrollChatToBottom();

            setTimeout(() => {
                const typingRow = document.createElement('div');
                typingRow.className = 'd-flex align-items-end gap-2 mb-2 chat-msg-row incoming typing-row';
                typingRow.id = 'chatActiveTyping';
                typingRow.innerHTML = `
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0 fb-chat-avatar-mini" style="width: 26px; height: 26px; background: ${currentChatContact.color}; font-size: 0.7rem;">${escapeHtml(currentChatContact.initials)}</div>
                    <div class="chat-bubble chat-bubble-in shadow-sm py-2 px-3">
                        <div class="typing-dots d-inline-flex align-items-center gap-1">
                            <span class="dot"></span><span class="dot"></span><span class="dot"></span>
                        </div>
                    </div>
                `;
                chatMessagesBody.appendChild(typingRow);
                scrollChatToBottom();

                setTimeout(() => {
                    const typingEl = document.getElementById('chatActiveTyping');
                    if (typingEl) typingEl.remove();

                    const replyText = simulatedReplies[Math.floor(Math.random() * simulatedReplies.length)];
                    const inRow = document.createElement('div');
                    inRow.className = 'd-flex align-items-end gap-2 mb-2 chat-msg-row incoming';
                    inRow.innerHTML = `
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0 fb-chat-avatar-mini" style="width: 26px; height: 26px; background: ${currentChatContact.color}; font-size: 0.7rem;">${escapeHtml(currentChatContact.initials)}</div>
                        <div class="chat-bubble chat-bubble-in shadow-sm">
                            ${escapeHtml(replyText)}
                        </div>
                    `;
                    chatMessagesBody.appendChild(inRow);
                    scrollChatToBottom();
                }, 1400);
            }, 600);
        });
    }
});

