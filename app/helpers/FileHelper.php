<?php
// app/helpers/FileHelper.php
// Centralized and secure file management helper for Nexus
// Handles safe deletion, path traversal prevention, reference checks, and orphaned file cleanup.

require_once __DIR__ . '/../../config/database.php';

class FileHelper {

    /**
     * Default and protected assets that must NEVER be deleted from uploads.
     */
    private static $protectedFiles = [
        'posts' => [
            'welcome.png',
            'mvc_diagram.png',
            '.gitkeep',
            '.htaccess',
            'index.html',
            'default.png',
            'default.jpg'
        ],
        'avatars' => [
            'bob.png',
            'default.png',
            'default-avatar.png',
            'default_avatar.png',
            'default-avatar.svg',
            '.gitkeep',
            '.htaccess',
            'index.html'
        ],
        'covers' => [
            'default-cover.png',
            'default-cover.jpg',
            'default_cover.jpg',
            '.gitkeep',
            '.htaccess',
            'index.html'
        ]
    ];

    /**
     * Get the canonical real directory path for an upload category.
     *
     * @param string $category 'posts' | 'avatars' | 'covers'
     * @return string|false Canonical directory path or false if invalid
     */
    public static function getUploadDirectory(string $category) {
        $allowed = ['posts', 'avatars', 'covers'];
        if (!in_array($category, $allowed, true)) {
            return false;
        }

        $dir = __DIR__ . '/../../public/uploads/' . $category;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return realpath($dir);
    }

    /**
     * Check if a filename is marked as protected and must never be deleted.
     *
     * @param string $category
     * @param string $filename
     * @return bool
     */
    public static function isProtected(string $category, string $filename): bool {
        $base = strtolower(basename($filename));
        if ($base === '' || $base === '.' || $base === '..') {
            return true;
        }

        // Check against static protected lists
        $list = self::$protectedFiles[$category] ?? [];
        if (in_array($base, $list, true)) {
            return true;
        }

        // Check if starts with default
        if (strpos($base, 'default') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Safely validate and resolve an upload file path, preventing directory traversal.
     *
     * @param string $category 'posts' | 'avatars' | 'covers'
     * @param string $filename
     * @return string|false Full canonical path if valid and exists within the upload directory, false otherwise
     */
    public static function resolveSafePath(string $category, string $filename) {
        if (empty($filename) || !is_string($filename)) {
            return false;
        }

        // Extract strictly the basename to strip any path traversal characters (../, ..\, etc.)
        $cleanName = basename(trim($filename));
        if ($cleanName === '' || $cleanName === '.' || $cleanName === '..') {
            return false;
        }

        // Validate characters: only allow standard alphanumeric, underscores, hyphens, and single periods
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $cleanName)) {
            return false;
        }

        $baseDir = self::getUploadDirectory($category);
        if (!$baseDir) {
            return false;
        }

        $candidatePath = $baseDir . DIRECTORY_SEPARATOR . $cleanName;

        if (!file_exists($candidatePath) || !is_file($candidatePath)) {
            return false;
        }

        $realPath = realpath($candidatePath);
        if (!$realPath) {
            return false;
        }

        // Ensure real path is strictly within the allowed directory
        if (strpos($realPath, $baseDir) !== 0) {
            return false;
        }

        return $realPath;
    }

    /**
     * Check if a post image is referenced by any other post in the database.
     *
     * @param string $filename
     * @param int|null $excludePostId
     * @return bool
     */
    public static function isPostImageReferencedElsewhere(string $filename, ?int $excludePostId = null): bool {
        $cleanName = basename(trim($filename));
        if ($cleanName === '') {
            return false;
        }

        try {
            $pdo = Database::connect();
            $params = [
                ':fn'       => $cleanName,
                ':json_fn'  => '%"' . $cleanName . '"%',
                ':comma_fn' => '%' . $cleanName . '%'
            ];

            if ($excludePostId !== null && (int)$excludePostId > 0) {
                $sql = "SELECT COUNT(*) FROM posts 
                        WHERE id != :exclude_id
                          AND (image = :fn 
                               OR image LIKE :json_fn 
                               OR image LIKE :comma_fn)";
                $params[':exclude_id'] = (int)$excludePostId;
            } else {
                $sql = "SELECT COUNT(*) FROM posts 
                        WHERE (image = :fn 
                               OR image LIKE :json_fn 
                               OR image LIKE :comma_fn)";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            error_log("FileHelper::isPostImageReferencedElsewhere error: " . $e->getMessage());
            return true; // Fail safe: assume referenced if query fails
        }
    }

    /**
     * Check if an avatar image is referenced by any other user in the database.
     *
     * @param string $filename
     * @param int|null $excludeUserId
     * @return bool
     */
    public static function isAvatarReferencedElsewhere(string $filename, ?int $excludeUserId = null): bool {
        $cleanName = basename(trim($filename));
        if ($cleanName === '') {
            return false;
        }

        try {
            $pdo = Database::connect();
            $params = [':fn' => $cleanName];

            if ($excludeUserId !== null && (int)$excludeUserId > 0) {
                $sql = "SELECT COUNT(*) FROM users 
                        WHERE id != :exclude_id
                          AND profile_image = :fn";
                $params[':exclude_id'] = (int)$excludeUserId;
            } else {
                $sql = "SELECT COUNT(*) FROM users 
                        WHERE profile_image = :fn";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            error_log("FileHelper::isAvatarReferencedElsewhere error: " . $e->getMessage());
            return true;
        }
    }

    /**
     * Check if a cover photo is referenced by any other user in the database.
     *
     * @param string $filename
     * @param int|null $excludeUserId
     * @return bool
     */
    public static function isCoverReferencedElsewhere(string $filename, ?int $excludeUserId = null): bool {
        $cleanName = basename(trim($filename));
        if ($cleanName === '') {
            return false;
        }

        try {
            $pdo = Database::connect();
            $params = [':fn' => $cleanName];

            if ($excludeUserId !== null && (int)$excludeUserId > 0) {
                $sql = "SELECT COUNT(*) FROM users 
                        WHERE id != :exclude_id
                          AND cover_photo = :fn";
                $params[':exclude_id'] = (int)$excludeUserId;
            } else {
                $sql = "SELECT COUNT(*) FROM users 
                        WHERE cover_photo = :fn";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            error_log("FileHelper::isCoverReferencedElsewhere error: " . $e->getMessage());
            return true;
        }
    }

    /**
     * Safely delete a post image file if not protected and not referenced elsewhere.
     *
     * @param string $filename
     * @param int|null $excludePostId
     * @return bool True if deleted or did not exist, false if blocked or deletion failed
     */
    public static function deletePostImage(string $filename, ?int $excludePostId = null): bool {
        $cleanName = basename(trim($filename));
        if ($cleanName === '') {
            return true;
        }

        if (self::isProtected('posts', $cleanName)) {
            return false;
        }

        if (self::isPostImageReferencedElsewhere($cleanName, $excludePostId)) {
            return false;
        }

        $fullPath = self::resolveSafePath('posts', $cleanName);
        if (!$fullPath) {
            // File does not exist on disk, nothing to delete
            return true;
        }

        return @unlink($fullPath);
    }

    /**
     * Safely delete multiple post images.
     *
     * @param mixed $images Filename, array of filenames, or JSON string
     * @param int|null $excludePostId
     * @return int Number of files successfully unlinked
     */
    public static function deletePostImages($images, ?int $excludePostId = null): int {
        require_once __DIR__ . '/../models/PostModel.php';
        $parsed = PostModel::parseImages($images);
        $count = 0;

        foreach ($parsed as $img) {
            if (self::deletePostImage($img, $excludePostId)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Safely delete an avatar image file if not protected and not referenced elsewhere.
     *
     * @param string $filename
     * @param int|null $excludeUserId
     * @return bool
     */
    public static function deleteAvatar(string $filename, ?int $excludeUserId = null): bool {
        $cleanName = basename(trim($filename));
        if ($cleanName === '') {
            return true;
        }

        if (self::isProtected('avatars', $cleanName)) {
            return false;
        }

        if (self::isAvatarReferencedElsewhere($cleanName, $excludeUserId)) {
            return false;
        }

        $fullPath = self::resolveSafePath('avatars', $cleanName);
        if (!$fullPath) {
            return true;
        }

        return @unlink($fullPath);
    }

    /**
     * Safely delete a cover photo file if not protected and not referenced elsewhere.
     *
     * @param string $filename
     * @param int|null $excludeUserId
     * @return bool
     */
    public static function deleteCover(string $filename, ?int $excludeUserId = null): bool {
        $cleanName = basename(trim($filename));
        if ($cleanName === '') {
            return true;
        }

        if (self::isProtected('covers', $cleanName)) {
            return false;
        }

        if (self::isCoverReferencedElsewhere($cleanName, $excludeUserId)) {
            return false;
        }

        $fullPath = self::resolveSafePath('covers', $cleanName);
        if (!$fullPath) {
            return true;
        }

        return @unlink($fullPath);
    }

    /**
     * Audit all upload directories and find orphaned image files that are not referenced in the database.
     *
     * @return array ['posts' => [...], 'avatars' => [...], 'covers' => [...]]
     */
    public static function findOrphanedImages(): array {
        $orphaned = [
            'posts'   => [],
            'avatars' => [],
            'covers'  => []
        ];

        try {
            // 1. Audit posts
            $postsDir = self::getUploadDirectory('posts');
            if ($postsDir && is_dir($postsDir)) {
                $files = scandir($postsDir);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || self::isProtected('posts', $file)) {
                        continue;
                    }
                    if (is_file($postsDir . DIRECTORY_SEPARATOR . $file)) {
                        if (!self::isPostImageReferencedElsewhere($file, null)) {
                            $orphaned['posts'][] = [
                                'file' => $file,
                                'path' => $postsDir . DIRECTORY_SEPARATOR . $file,
                                'size' => filesize($postsDir . DIRECTORY_SEPARATOR . $file)
                            ];
                        }
                    }
                }
            }

            // 2. Audit avatars
            $avatarsDir = self::getUploadDirectory('avatars');
            if ($avatarsDir && is_dir($avatarsDir)) {
                $files = scandir($avatarsDir);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || self::isProtected('avatars', $file)) {
                        continue;
                    }
                    if (is_file($avatarsDir . DIRECTORY_SEPARATOR . $file)) {
                        if (!self::isAvatarReferencedElsewhere($file, null)) {
                            $orphaned['avatars'][] = [
                                'file' => $file,
                                'path' => $avatarsDir . DIRECTORY_SEPARATOR . $file,
                                'size' => filesize($avatarsDir . DIRECTORY_SEPARATOR . $file)
                            ];
                        }
                    }
                }
            }

            // 3. Audit covers
            $coversDir = self::getUploadDirectory('covers');
            if ($coversDir && is_dir($coversDir)) {
                $files = scandir($coversDir);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || self::isProtected('covers', $file)) {
                        continue;
                    }
                    if (is_file($coversDir . DIRECTORY_SEPARATOR . $file)) {
                        if (!self::isCoverReferencedElsewhere($file, null)) {
                            $orphaned['covers'][] = [
                                'file' => $file,
                                'path' => $coversDir . DIRECTORY_SEPARATOR . $file,
                                'size' => filesize($coversDir . DIRECTORY_SEPARATOR . $file)
                            ];
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            error_log("FileHelper::findOrphanedImages error: " . $e->getMessage());
        }

        return $orphaned;
    }

    /**
     * Clean up orphaned image files after verification against active database records.
     *
     * @param bool $dryRun If true, returns report without deleting files
     * @return array
     */
    public static function cleanOrphanedImages(bool $dryRun = true): array {
        $orphans = self::findOrphanedImages();
        $report = [
            'dry_run'         => $dryRun,
            'posts_cleaned'   => 0,
            'avatars_cleaned' => 0,
            'covers_cleaned'  => 0,
            'bytes_freed'     => 0,
            'deleted_files'   => []
        ];

        foreach (['posts', 'avatars', 'covers'] as $category) {
            foreach ($orphans[$category] as $item) {
                if (!$dryRun) {
                    if (@unlink($item['path'])) {
                        $report[$category . '_cleaned']++;
                        $report['bytes_freed'] += $item['size'];
                        $report['deleted_files'][] = $category . '/' . $item['file'];
                    }
                } else {
                    $report['deleted_files'][] = '[DRY-RUN] ' . $category . '/' . $item['file'] . ' (' . $item['size'] . ' bytes)';
                }
            }
        }

        return $report;
    }
}
