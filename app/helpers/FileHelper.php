<?php

require_once __DIR__ . '/../../config/database.php';

class FileHelper {

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

    public static function isProtected(string $category, string $filename): bool {
        $base = strtolower(basename($filename));
        if ($base === '' || $base === '.' || $base === '..') {
            return true;
        }

        $list = self::$protectedFiles[$category] ?? [];
        if (in_array($base, $list, true)) {
            return true;
        }

        if (strpos($base, 'default') === 0) {
            return true;
        }

        return false;
    }

    public static function resolveSafePath(string $category, string $filename) {
        if (empty($filename) || !is_string($filename)) {
            return false;
        }

        $cleanName = basename(trim($filename));
        if ($cleanName === '' || $cleanName === '.' || $cleanName === '..') {
            return false;
        }

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

        if (strpos($realPath, $baseDir) !== 0) {
            return false;
        }

        return $realPath;
    }

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
            return true;
        }
    }

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

            return true;
        }

        return @unlink($fullPath);
    }

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

    public static function findOrphanedImages(): array {
        $orphaned = [
            'posts'   => [],
            'avatars' => [],
            'covers'  => []
        ];

        try {

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
