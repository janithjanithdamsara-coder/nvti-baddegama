<?php
/**
 * Signed Document Preview Token & Online Viewer Helper
 * Enables in-browser preview of Office files (.pptx, .docx, .xlsx) via Microsoft Office
 * Online / Google Docs Viewer without server-side LibreOffice RAM overhead, plus inline
 * code/text preview for .sql and .txt files.
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../../config/config.php';

class PreviewToken {
    private static function getSecret(): string {
        $seed = implode('|', [
            defined('DB_NAME') ? DB_NAME : 'educore_lms',
            defined('DB_PASS') ? DB_PASS : '',
            defined('BASE_PATH') ? BASE_PATH : __DIR__,
            'nvti_preview_hmac_v1'
        ]);
        return hash('sha256', $seed);
    }

    public static function isOfficeExt(string $ext): bool {
        return in_array(strtolower(ltrim($ext, '.')), ['ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx'], true);
    }

    public static function isTextExt(string $ext): bool {
        return in_array(strtolower(ltrim($ext, '.')), ['sql', 'txt'], true);
    }

    public static function isPublicDomain(): bool {
        $host = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost')));
        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return false;
        }
        if (str_ends_with($host, '.local') || str_ends_with($host, '.test')) {
            return false;
        }
        return true;
    }

    public static function makeSignature(string $kind, int $id, int $exp): string {
        return hash_hmac('sha256', "{$kind}|{$id}|{$exp}", self::getSecret());
    }

    public static function verify(string $kind, int $id, int $exp, string $sig): bool {
        if (!in_array($kind, ['material', 'paper'], true) || $id <= 0 || $exp < time() || $sig === '') {
            return false;
        }
        $expected = self::makeSignature($kind, $id, $exp);
        return hash_equals($expected, $sig);
    }

    public static function makePublicFileUrl(string $kind, int $id, int $ttlSeconds = 900): string {
        $exp = time() + max(60, $ttlSeconds);
        $sig = self::makeSignature($kind, $id, $exp);
        return rtrim(BASE_URL, '/') . '/index.php?page=public_preview_file&kind=' . urlencode($kind)
            . '&id=' . $id . '&exp=' . $exp . '&sig=' . urlencode($sig);
    }

    public static function getOfficeEmbedUrl(string $publicFileUrl): string {
        return 'https://view.officeapps.live.com/op/embed.aspx?src=' . rawurlencode($publicFileUrl);
    }

    public static function getGoogleDocsEmbedUrl(string $publicFileUrl): string {
        return 'https://docs.google.com/gview?embedded=true&url=' . rawurlencode($publicFileUrl);
    }

    public static function readTextPreview(string $filePath, int $maxBytes = 524288): string {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return '';
        }
        $size = filesize($filePath);
        $raw = file_get_contents($filePath, false, null, 0, $maxBytes);
        if ($raw === false) {
            return '';
        }
        // Convert non-UTF8 safely
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'ISO-8859-1');
        }
        if ($size > $maxBytes) {
            $raw .= "\n\n--- Preview truncated at 512 KB (Download file for full contents) ---";
        }
        return $raw;
    }

    /**
     * Render a standalone full-screen viewer page for "Open in New Tab" / direct view links
     */
    public static function renderStandaloneViewer(
        string $title,
        string $kind,
        int $id,
        string $filePath,
        string $fileName,
        string $downloadUrl
    ): void {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeName  = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8');
        $safeDown  = htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8');

        if (self::isTextExt($ext)) {
            $content = htmlspecialchars(self::readTextPreview($filePath), ENT_QUOTES, 'UTF-8');
            $langLabel = strtoupper($ext);
            echo "<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>{$safeTitle} | NVTI Baddegama</title>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css' rel='stylesheet'>
</head>
<body class='bg-dark text-light d-flex flex-column' style='height:100vh;'>
  <div class='bg-white text-dark border-bottom px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2'>
    <div class='d-flex align-items-center gap-2 min-w-0'>
      <span class='badge bg-primary'>{$langLabel}</span>
      <strong class='text-truncate'>{$safeTitle}</strong>
      <small class='text-muted d-none d-md-inline'>({$safeName})</small>
    </div>
    <div class='d-flex gap-2'>
      <button type='button' class='btn btn-sm btn-outline-secondary rounded-pill px-3' onclick='navigator.clipboard.writeText(document.getElementById(\"codeBox\").innerText)'>
        <i class='bi bi-clipboard me-1'></i>Copy
      </button>
      <a href='{$safeDown}' class='btn btn-sm btn-danger rounded-pill px-3' style='background:#7A1418;border-color:#7A1418;'>
        <i class='bi bi-download me-1'></i>Download
      </a>
    </div>
  </div>
  <pre id='codeBox' class='flex-grow-1 m-0 p-4 overflow-auto' style='background:#0f172a;color:#e2e8f0;font-size:13.5px;line-height:1.6;'>{$content}</pre>
</body>
</html>";
            exit;
        }

        if (self::isOfficeExt($ext) && self::isPublicDomain()) {
            $pubUrl = self::makePublicFileUrl($kind, $id, 900);
            $officeUrl = htmlspecialchars(self::getOfficeEmbedUrl($pubUrl), ENT_QUOTES, 'UTF-8');
            $googleUrl = htmlspecialchars(self::getGoogleDocsEmbedUrl($pubUrl), ENT_QUOTES, 'UTF-8');
            $extLabel = strtoupper($ext);
            echo "<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>{$safeTitle} | NVTI Baddegama</title>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css' rel='stylesheet'>
</head>
<body class='d-flex flex-column' style='height:100vh;background:#1e293b;'>
  <div class='bg-white border-bottom px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2'>
    <div class='d-flex align-items-center gap-2 min-w-0'>
      <span class='badge bg-danger'>{$extLabel}</span>
      <strong class='text-dark text-truncate'>{$safeTitle}</strong>
    </div>
    <div class='d-flex flex-wrap gap-2'>
      <button type='button' id='btnOffice' class='btn btn-sm btn-primary rounded-pill px-3' onclick='setViewer(\"office\")'>Microsoft Viewer</button>
      <button type='button' id='btnGoogle' class='btn btn-sm btn-outline-secondary rounded-pill px-3' onclick='setViewer(\"google\")'>Google Viewer</button>
      <a href='{$safeDown}' class='btn btn-sm btn-danger rounded-pill px-3' style='background:#7A1418;border-color:#7A1418;'>
        <i class='bi bi-download me-1'></i>Download
      </a>
    </div>
  </div>
  <iframe id='viewerFrame' src='{$officeUrl}' class='w-100 flex-grow-1 border-0 bg-white'></iframe>
  <script>
    const urls = { office: '{$officeUrl}', google: '{$googleUrl}' };
    function setViewer(mode) {
      document.getElementById('viewerFrame').src = urls[mode];
      document.getElementById('btnOffice').className = 'btn btn-sm rounded-pill px-3 ' + (mode === 'office' ? 'btn-primary' : 'btn-outline-secondary');
      document.getElementById('btnGoogle').className = 'btn btn-sm rounded-pill px-3 ' + (mode === 'google' ? 'btn-primary' : 'btn-outline-secondary');
    }
  </script>
</body>
</html>";
            exit;
        }

        // Fallback raw inline stream (PDF / images / localhost)
        Uploader::streamInlineView($filePath, $fileName);
    }
}
