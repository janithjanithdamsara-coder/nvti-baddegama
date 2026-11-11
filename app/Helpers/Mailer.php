<?php
/**
 * Mailer Helper
 * Supports Brevo SMTP (SSL Port 465 / TLS Port 587) and Brevo REST API
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/EmailSettings.php';

class Mailer {
    private static ?string $lastError = null;

    public static function getLastError(): ?string {
        return self::$lastError;
    }

    /**
     * Send an email using configured driver (SMTP or Brevo API)
     *
     * @param string $toEmail Recipient email address
     * @param string $subject Email subject line
     * @param string $htmlBody HTML content
     * @param string $toName Optional recipient name
     * @param string $plainText Optional plain text alternative
     * @param string $emailType Category tag for toggle & log history
     * @param int|null $sentBy Optional user ID of triggerer
     * @param bool $bypassSettings Set true for explicit manual admin test/custom/resend actions
     * @return array ['success' => bool, 'message' => string, 'skipped' => bool]
     */
    public static function send(
        string $toEmail,
        string $subject,
        string $htmlBody,
        string $toName = '',
        string $plainText = '',
        string $emailType = 'general',
        ?int $sentBy = null,
        bool $bypassSettings = false
    ): array {
        self::$lastError = null;
        $resolvedSenderId = $sentBy ?? (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null);

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            self::$lastError = "Invalid recipient email address: $toEmail";
            EmailSettings::logEmail($toEmail, $toName, $subject, $htmlBody, $emailType, 'failed', self::$lastError, $resolvedSenderId);
            return ['success' => false, 'skipped' => false, 'message' => self::$lastError];
        }

        if (!$bypassSettings) {
            if (!EmailSettings::isMasterEnabled()) {
                $msg = "Skipped: Master Email Switch is turned OFF in Email Settings.";
                EmailSettings::logEmail($toEmail, $toName, $subject, $htmlBody, $emailType, 'skipped', $msg, $resolvedSenderId);
                return ['success' => false, 'skipped' => true, 'message' => $msg];
            }

            if (!EmailSettings::isCategoryEnabled($emailType)) {
                $msg = "Skipped: '{$emailType}' email notifications are turned OFF in Email Settings.";
                EmailSettings::logEmail($toEmail, $toName, $subject, $htmlBody, $emailType, 'skipped', $msg, $resolvedSenderId);
                return ['success' => false, 'skipped' => true, 'message' => $msg];
            }
        }

        // Check if Brevo API driver is requested
        if (defined('MAIL_DRIVER') && MAIL_DRIVER === 'brevo_api') {
            $result = self::sendViaBrevoApi($toEmail, $subject, $htmlBody, $toName);
        } else {
            // Default: Brevo / standard SMTP
            $result = self::sendViaSmtp($toEmail, $subject, $htmlBody, $toName, $plainText);

            // If SMTP fails and an API key is set, try Brevo API as fallback
            if (!$result['success'] && defined('BREVO_API_KEY') && !empty(BREVO_API_KEY)) {
                $result = self::sendViaBrevoApi($toEmail, $subject, $htmlBody, $toName);
            }
        }

        $status = !empty($result['success']) ? 'sent' : 'failed';
        $errMsg = !empty($result['success']) ? null : ($result['message'] ?? self::$lastError ?? 'Delivery failed');
        EmailSettings::logEmail($toEmail, $toName, $subject, $htmlBody, $emailType, $status, $errMsg, $resolvedSenderId);

        $result['skipped'] = false;
        return $result;
    }

    /**
     * Wrap a custom plain/rich message in the portal's branded responsive email template
     */
    public static function wrapBrandedTemplate(string $subjectTitle, string $messageBody, string $badgeLabel = 'NVTI BADDEGAMA &bull; OFFICIAL NOTICE'): string {
        $appName  = defined('APP_NAME') ? APP_NAME : 'NVTI Baddegama National Diploma';
        $loginUrl = rtrim(BASE_URL, '/') . '/index.php?page=login';
        $safeTitle = htmlspecialchars($subjectTitle, ENT_QUOTES, 'UTF-8');
        $formattedBody = nl2br(htmlspecialchars($messageBody, ENT_QUOTES, 'UTF-8'));

        return "
        <div style='background-color: #f1f5f9; padding: 30px 15px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0;'>
                <div style='background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%); padding: 24px 28px; color: #ffffff;'>
                    <div style='font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #fecaca; font-weight: 700; margin-bottom: 6px;'>{$badgeLabel}</div>
                    <h2 style='margin: 0; font-size: 20px; font-weight: 700; color: #ffffff;'>{$safeTitle}</h2>
                </div>
                <div style='padding: 28px; color: #334155; font-size: 14px; line-height: 1.7;'>
                    <div style='margin-bottom: 22px;'>{$formattedBody}</div>
                    <div style='text-align: center; margin: 24px 0 8px;'>
                        <a href='{$loginUrl}' style='display: inline-block; background: #7A1418; color: #ffffff; text-decoration: none; padding: 11px 26px; font-weight: 600; font-size: 14px; border-radius: 999px;'>Open Student Portal &rarr;</a>
                    </div>
                </div>
                <div style='background: #f8fafc; padding: 14px 24px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                    Sent by {$appName} Administration &bull; NVTI Baddegama
                </div>
            </div>
        </div>";
    }

    /**
     * Send via native PHP Socket SMTP (Port 465 SSL or Port 587 TLS)
     */
    public static function sendViaSmtp(string $toEmail, string $subject, string $htmlBody, string $toName = '', string $plainText = ''): array {
        $host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp-relay.brevo.com';
        $port       = defined('SMTP_PORT') ? (int)SMTP_PORT : 465;
        $encryption = defined('SMTP_ENCRYPTION') ? strtolower(SMTP_ENCRYPTION) : 'ssl';
        $user       = defined('SMTP_USER') ? SMTP_USER : '';
        $pass       = defined('SMTP_PASS') ? SMTP_PASS : '';
        $fromEmail  = defined('MAIL_FROM_EMAIL') ? MAIL_FROM_EMAIL : 'noreply@nvtibaddegama.site';
        $fromName   = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'NVTI Baddegama - ICT Diploma';

        if (empty($user) || empty($pass)) {
            self::$lastError = "SMTP username or password (Brevo key) is not configured in config.php.";
            return ['success' => false, 'message' => self::$lastError];
        }

        $timeout = 10;
        $socketTarget = ($encryption === 'ssl') ? "ssl://{$host}:{$port}" : "tcp://{$host}:{$port}";

        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client($socketTarget, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            self::$lastError = "Connection failed to {$socketTarget}: ($errno) $errstr";
            return ['success' => false, 'message' => self::$lastError];
        }

        stream_set_timeout($socket, $timeout);

        $readResponse = function() use ($socket): string {
            $response = '';
            while ($line = fgets($socket, 515)) {
                $response .= $line;
                // SMTP multiline response check: 4th character is space when finished
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return trim($response);
        };

        $sendCommand = function(string $cmd, string $expectedCode) use ($socket, $readResponse): bool {
            fputs($socket, $cmd . "\r\n");
            $res = $readResponse();
            if (substr($res, 0, 3) !== $expectedCode) {
                self::$lastError = "SMTP Error for '{$cmd}': {$res}";
                return false;
            }
            return true;
        };

        // 1. Initial banner
        $banner = $readResponse();
        if (substr($banner, 0, 3) !== '220') {
            fclose($socket);
            self::$lastError = "Unexpected SMTP server greeting: $banner";
            return ['success' => false, 'message' => self::$lastError];
        }

        // 2. EHLO
        $senderDomain = substr(strrchr($fromEmail, '@') ?: '', 1) ?: 'nvtibaddegama.site';
        $httpHost = preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? ''));
        $ehloHost = (!empty($httpHost) && strpos($httpHost, '.') !== false && $httpHost !== '127.0.0.1')
            ? $httpHost
            : $senderDomain;
        if (!$sendCommand("EHLO {$ehloHost}", '250')) {
            fclose($socket);
            return ['success' => false, 'message' => self::$lastError];
        }

        // 3. STARTTLS if TLS mode
        if ($encryption === 'tls') {
            if (!$sendCommand("STARTTLS", '220')) {
                fclose($socket);
                return ['success' => false, 'message' => self::$lastError];
            }
            $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
            if (!$crypto) {
                fclose($socket);
                self::$lastError = "Failed to establish TLS encryption with SMTP server.";
                return ['success' => false, 'message' => self::$lastError];
            }
            if (!$sendCommand("EHLO {$ehloHost}", '250')) {
                fclose($socket);
                return ['success' => false, 'message' => self::$lastError];
            }
        }

        // 4. AUTH LOGIN
        if (!$sendCommand("AUTH LOGIN", '334')) {
            fclose($socket);
            return ['success' => false, 'message' => self::$lastError];
        }

        // 5. Send Username (Base64)
        if (!$sendCommand(base64_encode($user), '334')) {
            fclose($socket);
            return ['success' => false, 'message' => "SMTP Username rejected: " . self::$lastError];
        }

        // 6. Send Password / API Key (Base64)
        if (!$sendCommand(base64_encode($pass), '235')) {
            fclose($socket);
            return ['success' => false, 'message' => "SMTP Password/Key rejected: " . self::$lastError];
        }

        // 7. MAIL FROM
        if (!$sendCommand("MAIL FROM: <{$fromEmail}>", '250')) {
            fclose($socket);
            return ['success' => false, 'message' => self::$lastError];
        }

        // 8. RCPT TO
        if (!$sendCommand("RCPT TO: <{$toEmail}>", '250')) {
            fclose($socket);
            return ['success' => false, 'message' => self::$lastError];
        }

        // 9. DATA
        if (!$sendCommand("DATA", '354')) {
            fclose($socket);
            return ['success' => false, 'message' => self::$lastError];
        }

        // 10. Construct Headers & Multipart Body (Plain Text + HTML reduces spam score)
        $recipientHeader = !empty($toName) ? "=?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>" : "<{$toEmail}>";
        $senderHeader    = "=?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>";
        $subjectHeader   = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        $dateHeader      = date('r');
        $msgId           = '<' . bin2hex(random_bytes(16)) . '@' . $senderDomain . '>';
        $boundary        = 'b1_' . bin2hex(random_bytes(12));

        $plainFallback = trim($plainText) !== '' ? $plainText : self::htmlToPlainText($htmlBody);

        $headers = [
            "Date: {$dateHeader}",
            "From: {$senderHeader}",
            "Reply-To: {$senderHeader}",
            "To: {$recipientHeader}",
            "Subject: {$subjectHeader}",
            "Message-ID: {$msgId}",
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\""
        ];

        $parts = [
            "--{$boundary}",
            "Content-Type: text/plain; charset=UTF-8",
            "Content-Transfer-Encoding: base64",
            "",
            rtrim(chunk_split(base64_encode($plainFallback), 76, "\r\n")),
            "--{$boundary}",
            "Content-Type: text/html; charset=UTF-8",
            "Content-Transfer-Encoding: base64",
            "",
            rtrim(chunk_split(base64_encode($htmlBody), 76, "\r\n")),
            "--{$boundary}--"
        ];

        $fullPayload = implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $parts) . "\r\n.";

        fputs($socket, $fullPayload . "\r\n");
        $dataRes = $readResponse();
        if (substr($dataRes, 0, 3) !== '250') {
            fclose($socket);
            self::$lastError = "SMTP Data submission failed: {$dataRes}";
            return ['success' => false, 'message' => self::$lastError];
        }

        // 11. QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return ['success' => true, 'message' => 'Email sent successfully via SMTP!'];
    }

    /**
     * Send via Brevo HTTP REST API (Port 443 - zero firewall/ISP port issues)
     */
    public static function sendViaBrevoApi(string $toEmail, string $subject, string $htmlBody, string $toName = ''): array {
        $apiKey    = defined('BREVO_API_KEY') && !empty(BREVO_API_KEY) ? BREVO_API_KEY : (defined('SMTP_PASS') ? SMTP_PASS : '');
        $fromEmail = defined('MAIL_FROM_EMAIL') ? MAIL_FROM_EMAIL : 'noreply@nvtibaddegama.site';
        $fromName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'NVTI Baddegama - ICT Diploma';

        if (empty($apiKey)) {
            self::$lastError = "Brevo API Key is missing.";
            return ['success' => false, 'message' => self::$lastError];
        }

        $payload = [
            'sender' => [
                'name'  => $fromName,
                'email' => $fromEmail
            ],
            'replyTo' => [
                'name'  => $fromName,
                'email' => $fromEmail
            ],
            'to' => [
                [
                    'email' => $toEmail,
                    'name'  => !empty($toName) ? $toName : $toEmail
                ]
            ],
            'subject'     => $subject,
            'htmlContent' => $htmlBody,
            'textContent' => self::htmlToPlainText($htmlBody)
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if (!empty($curlErr)) {
            self::$lastError = "Brevo API cURL error: {$curlErr}";
            return ['success' => false, 'message' => self::$lastError];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'message' => 'Email sent successfully via Brevo API!'];
        }

        $decoded = json_decode($response, true);
        $errorMsg = $decoded['message'] ?? "HTTP {$httpCode}: {$response}";
        self::$lastError = "Brevo API Error: {$errorMsg}";
        return ['success' => false, 'message' => self::$lastError];
    }

    /**
     * Send Password Reset Email Template
     */
    public static function sendPasswordReset(string $toEmail, string $name, string $resetToken): array {
        $resetUrl = rtrim(BASE_URL, '/') . '/index.php?page=reset_password&token=' . urlencode($resetToken);
        $appName  = defined('APP_NAME') ? APP_NAME : 'NVTI Baddegama National Diploma';
        $subTitle = defined('APP_SUBTITLE') ? APP_SUBTITLE : 'ICT NVQ Level 5';

        $html = "
        <div style='background-color: #f1f5f9; padding: 35px 15px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;'>
                <div style='background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%); padding: 28px 24px; text-align: center; color: #ffffff;'>
                    <h1 style='margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.5px;'>{$appName}</h1>
                    <p style='margin: 6px 0 0; font-size: 13px; opacity: 0.9;'>{$subTitle}</p>
                </div>
                <div style='padding: 32px 28px; color: #334155; line-height: 1.6;'>
                    <h2 style='margin-top: 0; font-size: 18px; color: #0f172a;'>Password Reset Request</h2>
                    <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
                    <p>We received a request to reset your password for your portal account. Click the button below to proceed:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetUrl}' style='display: inline-block; background: #7A1418; color: #ffffff; text-decoration: none; padding: 12px 28px; font-weight: 600; font-size: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(122, 20, 24, 0.3);'>Reset My Password</a>
                    </div>
                    <p style='font-size: 13px; color: #64748b; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px dashed #cbd5e1;'>
                        Or copy and paste your reset token manually:<br>
                        <strong style='font-family: monospace; font-size: 15px; color: #7A1418;'>{$resetToken}</strong>
                    </p>
                    <p style='font-size: 13px; color: #94a3b8; margin-top: 24px;'>This link and token will expire in 1 hour. If you did not request this reset, you can safely ignore this email.</p>
                </div>
                <div style='background: #f8fafc; padding: 16px 24px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                    &copy; " . date('Y') . " NVTI Baddegama. ICT NVQ Level 5 Diploma Portal.
                </div>
            </div>
        </div>";

        return self::send($toEmail, "Password Reset Request - {$appName}", $html, $name, '', 'password_reset');
    }

    /**
     * Send Welcome / Account Created Email
     */
    public static function sendWelcomeEmail(string $toEmail, string $name, string $username, string $password, string $role = 'student'): array {
        $loginUrl = rtrim(BASE_URL, '/') . '/index.php?page=login';
        $appName  = defined('APP_NAME') ? APP_NAME : 'NVTI Baddegama National Diploma';

        $html = "
        <div style='background-color: #f1f5f9; padding: 35px 15px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;'>
                <div style='background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%); padding: 28px 24px; text-align: center; color: #ffffff;'>
                    <h1 style='margin: 0; font-size: 22px; font-weight: 700;'>Welcome to {$appName}!</h1>
                    <p style='margin: 6px 0 0; font-size: 13px; opacity: 0.9;'>NVTI Baddegama - ICT NVQ Level 5</p>
                </div>
                <div style='padding: 32px 28px; color: #334155; line-height: 1.6;'>
                    <p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
                    <p>Your account has been successfully created as a <strong>" . ucfirst($role) . "</strong> on the National Diploma Portal.</p>
                    <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin: 20px 0;'>
                        <p style='margin: 0 0 8px; font-size: 14px;'><strong>Login Email / Username:</strong> <span style='color: #7A1418; font-weight: 600;'>" . htmlspecialchars($username) . "</span></p>
                        <p style='margin: 0; font-size: 14px;'><strong>Password:</strong> <span style='font-family: monospace; background: #e2e8f0; padding: 2px 6px; border-radius: 4px;'>" . htmlspecialchars($password) . "</span></p>
                    </div>
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='{$loginUrl}' style='display: inline-block; background: #7A1418; color: #ffffff; text-decoration: none; padding: 12px 28px; font-weight: 600; font-size: 15px; border-radius: 8px;'>Login to Portal</a>
                    </div>
                    <p style='font-size: 13px; color: #e11d48;'>* For security reasons, please change your password immediately after your first sign-in.</p>
                </div>
                <div style='background: #f8fafc; padding: 16px 24px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                    &copy; " . date('Y') . " NVTI Baddegama. All rights reserved.
                </div>
            </div>
        </div>";

        return self::send($toEmail, "Welcome to {$appName} - Your Account Credentials", $html, $name, '', 'welcome');
    }

    /**
     * Google Classroom Style: Notify All Registered Class Students of New Study Material
     */
    public static function notifyNewMaterial(int $classId, string $materialTitle, string $subjectName, string $teacherName, string $description = '', string $type = 'file'): int {
        if (!EmailSettings::isCategoryEnabled('material')) {
            $reason = !EmailSettings::isMasterEnabled()
                ? 'Skipped: Master Email Switch is turned OFF in Email Settings.'
                : 'Skipped: New Study Material email notifications are turned OFF in Email Settings.';
            EmailSettings::logEmail(
                "Class #{$classId} (Enrolled Students)",
                'Batch Students',
                "New Material: {$materialTitle} ({$subjectName})",
                '',
                'material',
                'skipped',
                $reason,
                isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null
            );
            return 0;
        }

        require_once __DIR__ . '/../../config/database.php';
        $db = Database::getConnection();

        $sql = "SELECT u.name, u.email 
                FROM users u 
                JOIN student_profiles sp ON u.id = sp.user_id 
                WHERE u.role = 'student' AND u.status = 'active'";
        $params = [];

        if ($classId > 0) {
            $sql .= " AND (sp.class_id = ? OR sp.class_id IS NULL)";
            $params[] = $classId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($students)) {
            return 0;
        }

        $portalUrl = rtrim(BASE_URL, '/') . '/index.php?page=student_materials';
        $appName   = defined('APP_NAME') ? APP_NAME : 'NVTI Baddegama National Diploma';
        $typeLabel = ucfirst($type);
        $notifiedCount = 0;

        foreach ($students as $student) {
            $firstName = explode(' ', trim($student['name']))[0] ?? $student['name'];
            
            $html = "
            <div style='background-color: #f1f5f9; padding: 30px 15px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                <div style='max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0;'>
                    <!-- Header -->
                    <div style='background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 22px 24px; color: #ffffff;'>
                        <div style='font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; font-weight: 600;'>NVTI Baddegama &bull; ICT NVQ Level 5</div>
                        <h2 style='margin: 4px 0 0; font-size: 19px; font-weight: 700; color: #ffffff;'>{$appName}</h2>
                    </div>

                    <!-- Material Card (Google Classroom Style) -->
                    <div style='padding: 28px 24px;'>
                        <div style='display: flex; align-items: center; margin-bottom: 16px;'>
                            <span style='background: #e0e7ff; color: #4338ca; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;'>
                                &#x1F4D8; New {$typeLabel} Material
                            </span>
                            <span style='margin-left: 10px; color: #64748b; font-size: 12px;'>" . date('F j, Y') . "</span>
                        </div>

                        <h3 style='margin: 0 0 12px; font-size: 20px; color: #0f172a; line-height: 1.4;'>" . htmlspecialchars($materialTitle) . "</h3>
                        
                        <div style='background: #f8fafc; border-left: 4px solid #4338ca; padding: 14px 16px; border-radius: 6px; margin: 16px 0;'>
                            <table style='width: 100%; border-collapse: collapse; font-size: 13px; color: #334155;'>
                                <tr>
                                    <td style='padding: 3px 0; font-weight: 600; width: 85px;'>Subject:</td>
                                    <td style='padding: 3px 0;'>" . htmlspecialchars($subjectName) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 3px 0; font-weight: 600;'>Lecturer:</td>
                                    <td style='padding: 3px 0;'>" . htmlspecialchars($teacherName) . "</td>
                                </tr>
                            </table>
                        </div>";

            if (!empty($description)) {
                $html .= "<div style='color: #475569; font-size: 14px; line-height: 1.6; margin: 16px 0; padding: 12px; background: #fafafa; border-radius: 6px;'>
                            " . nl2br(htmlspecialchars($description)) . "
                          </div>";
            }

            $html .= "
                        <p style='color: #64748b; font-size: 13px; margin: 20px 0 24px;'>
                            Hi <strong>" . htmlspecialchars($firstName) . "</strong>, a new learning resource has been uploaded for your diploma module. Click below to view and download it from the student portal:
                        </p>

                        <div style='text-align: center; margin: 26px 0;'>
                            <a href='{$portalUrl}' style='display: inline-block; background: #7A1418; color: #ffffff; text-decoration: none; padding: 12px 28px; font-weight: 600; font-size: 14px; border-radius: 8px; box-shadow: 0 2px 4px rgba(122,20,24,0.25);'>
                                View Material in Portal &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style='background: #f8fafc; padding: 14px 24px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                        NVTI Baddegama ICT Department &bull; Official Student Portal Notification
                    </div>
                </div>
            </div>";

            $res = self::send($student['email'], "New Material: " . $materialTitle . " (" . $subjectName . ")", $html, $student['name'], '', 'material');
            if ($res['success']) {
                $notifiedCount++;
            }
            usleep(200000);
        }

        return $notifiedCount;
    }

    /**
     * Google Classroom Style: Notify All Registered Class Students of New Assignment
     */
    public static function notifyNewAssignment(int $classId, string $assignmentTitle, string $subjectName, string $teacherName, string $dueDate = '', int $maxMarks = 100, string $instructions = ''): int {
        if (!EmailSettings::isCategoryEnabled('assignment')) {
            $reason = !EmailSettings::isMasterEnabled()
                ? 'Skipped: Master Email Switch is turned OFF in Email Settings.'
                : 'Skipped: New Assignment email notifications are turned OFF in Email Settings.';
            EmailSettings::logEmail(
                "Class #{$classId} (Enrolled Students)",
                'Batch Students',
                "New Assignment: {$assignmentTitle} ({$subjectName})",
                '',
                'assignment',
                'skipped',
                $reason,
                isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null
            );
            return 0;
        }

        require_once __DIR__ . '/../../config/database.php';
        $db = Database::getConnection();

        $sql = "SELECT u.name, u.email 
                FROM users u 
                JOIN student_profiles sp ON u.id = sp.user_id 
                WHERE u.role = 'student' AND u.status = 'active'";
        $params = [];

        if ($classId > 0) {
            $sql .= " AND (sp.class_id = ? OR sp.class_id IS NULL)";
            $params[] = $classId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($students)) {
            return 0;
        }

        $portalUrl = rtrim(BASE_URL, '/') . '/index.php?page=student_assignments';
        $appName   = defined('APP_NAME') ? APP_NAME : 'NVTI Baddegama National Diploma';
        $formattedDue = !empty($dueDate) ? date('l, F j, Y - h:i A', strtotime($dueDate)) : 'Announced in Class';
        $notifiedCount = 0;

        foreach ($students as $student) {
            $firstName = explode(' ', trim($student['name']))[0] ?? $student['name'];
            
            $html = "
            <div style='background-color: #f1f5f9; padding: 30px 15px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                <div style='max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0;'>
                    <!-- Header -->
                    <div style='background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%); padding: 22px 24px; color: #ffffff;'>
                        <div style='font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #fecaca; font-weight: 600;'>NVTI Baddegama &bull; ICT NVQ Level 5</div>
                        <h2 style='margin: 4px 0 0; font-size: 19px; font-weight: 700; color: #ffffff;'>{$appName}</h2>
                    </div>

                    <!-- Assignment Card (Google Classroom Style) -->
                    <div style='padding: 28px 24px;'>
                        <div style='display: flex; align-items: center; margin-bottom: 16px;'>
                            <span style='background: #fee2e2; color: #991b1b; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;'>
                                &#x1F4CB; New Assignment Posted
                            </span>
                            <span style='margin-left: 10px; color: #64748b; font-size: 12px;'>Max Marks: " . $maxMarks . "</span>
                        </div>

                        <h3 style='margin: 0 0 12px; font-size: 20px; color: #0f172a; line-height: 1.4;'>" . htmlspecialchars($assignmentTitle) . "</h3>
                        
                        <div style='background: #fff1f2; border-left: 4px solid #e11d48; padding: 14px 16px; border-radius: 6px; margin: 16px 0;'>
                            <table style='width: 100%; border-collapse: collapse; font-size: 13px; color: #334155;'>
                                <tr>
                                    <td style='padding: 3px 0; font-weight: 600; width: 90px;'>Subject:</td>
                                    <td style='padding: 3px 0;'>" . htmlspecialchars($subjectName) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 3px 0; font-weight: 600;'>Lecturer:</td>
                                    <td style='padding: 3px 0;'>" . htmlspecialchars($teacherName) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 3px 0; font-weight: 600; color: #e11d48;'>Deadline:</td>
                                    <td style='padding: 3px 0; font-weight: 600; color: #e11d48;'>" . htmlspecialchars($formattedDue) . "</td>
                                </tr>
                            </table>
                        </div>";

            if (!empty($instructions)) {
                $html .= "<div style='color: #475569; font-size: 14px; line-height: 1.6; margin: 16px 0; padding: 12px; background: #fafafa; border-radius: 6px;'>
                            <strong>Instructions:</strong><br>" . nl2br(htmlspecialchars($instructions)) . "
                          </div>";
            }

            $html .= "
                        <p style='color: #64748b; font-size: 13px; margin: 20px 0 24px;'>
                            Hi <strong>" . htmlspecialchars($firstName) . "</strong>, please check the requirements and ensure your submission is uploaded to the student portal prior to the deadline.
                        </p>

                        <div style='text-align: center; margin: 26px 0;'>
                            <a href='{$portalUrl}' style='display: inline-block; background: #7A1418; color: #ffffff; text-decoration: none; padding: 12px 28px; font-weight: 600; font-size: 14px; border-radius: 8px; box-shadow: 0 2px 4px rgba(122,20,24,0.25);'>
                                Open Assignment in Portal &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style='background: #f8fafc; padding: 14px 24px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                        NVTI Baddegama ICT Department &bull; Official Student Portal Notification
                    </div>
                </div>
            </div>";

            $res = self::send($student['email'], "New Assignment: " . $assignmentTitle . " (Due: " . $formattedDue . ")", $html, $student['name'], '', 'assignment');
            if ($res['success']) {
                $notifiedCount++;
            }
            usleep(200000);
        }

        return $notifiedCount;
    }

    /**
     * Convert HTML email body into a clean plain-text alternative
     */
    public static function htmlToPlainText(string $html): string {
        $text = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
        $text = preg_replace('/<\/\s*(p|div|tr|li|h1|h2|h3|h4|h5|h6)\s*>/i', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+\n/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }
}
