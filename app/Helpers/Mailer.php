<?php
/**
 * Mailer Helper
 * Supports Brevo SMTP (SSL Port 465 / TLS Port 587) and Brevo REST API
 * EduCore LMS - NVTI Baddegama Diploma
 */

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
     * @return array ['success' => bool, 'message' => string]
     */
    public static function send(string $toEmail, string $subject, string $htmlBody, string $toName = '', string $plainText = ''): array {
        self::$lastError = null;

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            self::$lastError = "Invalid recipient email address: $toEmail";
            return ['success' => false, 'message' => self::$lastError];
        }

        // Check if Brevo API driver is requested
        if (defined('MAIL_DRIVER') && MAIL_DRIVER === 'brevo_api') {
            return self::sendViaBrevoApi($toEmail, $subject, $htmlBody, $toName);
        }

        // Default: Brevo / standard SMTP
        $result = self::sendViaSmtp($toEmail, $subject, $htmlBody, $toName, $plainText);
        
        // If SMTP fails and an API key is set, try Brevo API as fallback
        if (!$result['success'] && defined('BREVO_API_KEY') && !empty(BREVO_API_KEY)) {
            return self::sendViaBrevoApi($toEmail, $subject, $htmlBody, $toName);
        }

        return $result;
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
        $clientHost = gethostname() ?: 'localhost';
        if (!$sendCommand("EHLO {$clientHost}", '250')) {
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
            if (!$sendCommand("EHLO {$clientHost}", '250')) {
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

        // 10. Construct Headers & Body
        $recipientHeader = !empty($toName) ? "=?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>" : "<{$toEmail}>";
        $senderHeader    = "=?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>";
        $subjectHeader   = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        $dateHeader      = date('r');
        $msgId           = '<' . md5(uniqid(microtime(), true)) . '@' . ($clientHost ?: 'nvtibaddegama.site') . '>';

        $headers = [
            "Date: {$dateHeader}",
            "From: {$senderHeader}",
            "To: {$recipientHeader}",
            "Subject: {$subjectHeader}",
            "Message-ID: {$msgId}",
            "X-Mailer: EduCore-LMS-Brevo",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "Content-Transfer-Encoding: base64"
        ];

        $encodedBody = chunk_split(base64_encode($htmlBody));
        $fullPayload = implode("\r\n", $headers) . "\r\n\r\n" . $encodedBody . "\r\n.";

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
            'to' => [
                [
                    'email' => $toEmail,
                    'name'  => !empty($toName) ? $toName : $toEmail
                ]
            ],
            'subject'     => $subject,
            'htmlContent' => $htmlBody
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
        $resetUrl = rtrim(BASE_URL, '/') . '/index.php?page=forgot_password&token=' . urlencode($resetToken);
        $appName  = defined('APP_NAME') ? APP_NAME : 'EduCore LMS';
        $subTitle = defined('APP_SUBTITLE') ? APP_SUBTITLE : 'NVTI Baddegama';

        $html = "
        <div style='background-color: #f1f5f9; padding: 35px 15px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;'>
                <div style='background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); padding: 28px 24px; text-align: center; color: #ffffff;'>
                    <h1 style='margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.5px;'>{$appName}</h1>
                    <p style='margin: 6px 0 0; font-size: 13px; opacity: 0.9;'>{$subTitle}</p>
                </div>
                <div style='padding: 32px 28px; color: #334155; line-height: 1.6;'>
                    <h2 style='margin-top: 0; font-size: 18px; color: #0f172a;'>Password Reset Request</h2>
                    <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
                    <p>We received a request to reset your password for your EduCore account. Click the button below to proceed:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetUrl}' style='display: inline-block; background: #4f46e5; color: #ffffff; text-decoration: none; padding: 12px 28px; font-weight: 600; font-size: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);'>Reset My Password</a>
                    </div>
                    <p style='font-size: 13px; color: #64748b; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px dashed #cbd5e1;'>
                        Or copy and paste your reset token manually:<br>
                        <strong style='font-family: monospace; font-size: 15px; color: #4f46e5;'>{$resetToken}</strong>
                    </p>
                    <p style='font-size: 13px; color: #94a3b8; margin-top: 24px;'>This link and token will expire in 1 hour. If you did not request this reset, you can safely ignore this email.</p>
                </div>
                <div style='background: #f8fafc; padding: 16px 24px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                    &copy; " . date('Y') . " NVTI Baddegama. ICT NVQ Level 5 Diploma Portal.
                </div>
            </div>
        </div>";

        return self::send($toEmail, "Password Reset Request - {$appName}", $html, $name);
    }

    /**
     * Send Welcome / Account Created Email
     */
    public static function sendWelcomeEmail(string $toEmail, string $name, string $username, string $password, string $role = 'student'): array {
        $loginUrl = rtrim(BASE_URL, '/') . '/index.php?page=login';
        $appName  = defined('APP_NAME') ? APP_NAME : 'EduCore LMS';

        $html = "
        <div style='background-color: #f1f5f9; padding: 35px 15px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;'>
                <div style='background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 28px 24px; text-align: center; color: #ffffff;'>
                    <h1 style='margin: 0; font-size: 22px; font-weight: 700;'>Welcome to {$appName}!</h1>
                    <p style='margin: 6px 0 0; font-size: 13px; opacity: 0.9;'>NVTI Baddegama - ICT NVQ Level 5</p>
                </div>
                <div style='padding: 32px 28px; color: #334155; line-height: 1.6;'>
                    <p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
                    <p>Your account has been successfully created as a <strong>" . ucfirst($role) . "</strong> on the EduCore Diploma Portal.</p>
                    <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin: 20px 0;'>
                        <p style='margin: 0 0 8px; font-size: 14px;'><strong>Login Email / Username:</strong> <span style='color: #4f46e5;'>" . htmlspecialchars($username) . "</span></p>
                        <p style='margin: 0; font-size: 14px;'><strong>Password:</strong> <span style='font-family: monospace; background: #e2e8f0; padding: 2px 6px; border-radius: 4px;'>" . htmlspecialchars($password) . "</span></p>
                    </div>
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='{$loginUrl}' style='display: inline-block; background: #10b981; color: #ffffff; text-decoration: none; padding: 12px 28px; font-weight: 600; font-size: 15px; border-radius: 8px;'>Login to Portal</a>
                    </div>
                    <p style='font-size: 13px; color: #e11d48;'>* For security reasons, please change your password immediately after your first sign-in.</p>
                </div>
                <div style='background: #f8fafc; padding: 16px 24px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                    &copy; " . date('Y') . " NVTI Baddegama. All rights reserved.
                </div>
            </div>
        </div>";

        return self::send($toEmail, "Welcome to {$appName} - Your Account Credentials", $html, $name);
    }
}
