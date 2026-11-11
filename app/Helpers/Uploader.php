<?php
/**
 * Secure File Uploader & Streamer Helper
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../../config/config.php';

class Uploader {
    private static array $mimeMap = [
        'pdf'  => ['application/pdf', 'application/x-pdf'],
        'doc'  => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'ppt'  => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'xls'  => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'zip'  => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'],
        'rar'  => ['application/x-rar-compressed', 'application/octet-stream'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
        'txt'  => ['text/plain'],
        'sql'  => ['text/plain', 'application/sql', 'text/x-sql', 'application/x-sql', 'text/sql']
    ];

    /**
     * Upload a single file safely
     */
    public static function upload(array $file, string $targetFolder, ?array $allowedExts = null): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'error' => 'Invalid upload request.'];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'error' => 'No file was uploaded.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'error' => 'File size exceeds system upload limits.'];
            default:
                return ['success' => false, 'error' => 'Unknown upload error occurred.'];
        }

        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return ['success' => false, 'error' => 'File size exceeds maximum limit of 25MB.'];
        }

        $originalName = basename($file['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowed = $allowedExts ?? ALLOWED_FILE_EXTENSIONS;
        if (!in_array($ext, $allowed, true)) {
            return ['success' => false, 'error' => "Disallowed file extension (.$ext). Permitted: " . implode(', ', $allowed)];
        }

        // Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        if (isset(self::$mimeMap[$ext])) {
            $expectedMimes = self::$mimeMap[$ext];
            if (!in_array($realMime, $expectedMimes, true) && $realMime !== 'application/octet-stream') {
                return ['success' => false, 'error' => "File content does not match extension (MIME type: $realMime)."];
            }
        }

        // Ensure directory exists
        if (!is_dir($targetFolder)) {
            mkdir($targetFolder, 0755, true);
        }

        // Generate safe randomized filename
        $safeFileName = bin2hex(random_bytes(16)) . '_' . time() . '.' . $ext;
        $destination = $targetFolder . DIRECTORY_SEPARATOR . $safeFileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Failed to save uploaded file to storage.'];
        }

        return [
            'success'          => true,
            'filename'         => $safeFileName,
            'original_filename'=> $originalName,
            'path'             => $destination,
            'size'             => $file['size'],
            'mime'             => $realMime,
            'ext'              => $ext
        ];
    }

    /**
     * Upload an image (avatar, teacher photo, etc.) safely into the public assets directory
     * 
     * @param array $file The $_FILES['xyz'] array
     * @param string $subfolder Subfolder under assets/images/ (defaults to 'teachers')
     * @return array Result containing 'success', 'path' (relative web path), 'error', etc.
     */
    public static function uploadImage(array $file, string $subfolder = 'teachers'): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'error' => 'Invalid image upload.'];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'error' => 'No file was uploaded.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'error' => 'Image file size exceeds upload limit.'];
            default:
                return ['success' => false, 'error' => 'Image upload failed with error code: ' . $file['error']];
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Image size exceeds maximum limit of 5MB.'];
        }

        $originalName = basename($file['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed, true)) {
            return ['success' => false, 'error' => "Disallowed image format (.$ext). Permitted: jpg, jpeg, png, webp, gif."];
        }

        // Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);
        if (!str_starts_with($realMime, 'image/')) {
            return ['success' => false, 'error' => 'The uploaded file is not a valid image.'];
        }

        // Target directory: BASE_PATH/assets/images/$subfolder
        $targetDir = BASE_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $subfolder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $safeFileName = bin2hex(random_bytes(10)) . '_' . time() . '.' . $ext;
        $destination = $targetDir . DIRECTORY_SEPARATOR . $safeFileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Failed to save uploaded image.'];
        }

        $webPath = 'assets/images/' . $subfolder . '/' . $safeFileName;

        return [
            'success'   => true,
            'filename'  => $safeFileName,
            'path'      => $webPath,
            'full_path' => $destination,
            'size'      => $file['size'],
            'mime'      => $realMime
        ];
    }

    /**
     * Safe file download streamer (prevents path traversal)
     */
    public static function streamDownload(string $filePath, string $downloadName): void {
        // Prevent path traversal
        $realPath = realpath($filePath);
        $allowedBasePath = realpath(STORAGE_PATH);

        if (!$realPath || !str_starts_with($realPath, $allowedBasePath) || !file_exists($realPath)) {
            http_response_code(404);
            die("File not found or access denied.");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($realPath) ?: 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($realPath));
        readfile($realPath);
        exit;
    }

    /**
     * Safe in-browser inline viewer streamer (for PDF reader, images, documents)
     */
    public static function streamInlineView(string $filePath, string $displayName): void {
        $realPath = realpath($filePath);
        $allowedBasePath = realpath(STORAGE_PATH);

        if (!$realPath || !str_starts_with($realPath, $allowedBasePath) || !file_exists($realPath)) {
            http_response_code(404);
            die("File not found or access denied.");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($realPath) ?: 'application/pdf';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . addslashes($displayName) . '"');
        header('Expires: 0');
        header('Cache-Control: public, max-age=3600');
        header('Pragma: public');
        header('Content-Length: ' . filesize($realPath));
        readfile($realPath);
        exit;
    }
}
