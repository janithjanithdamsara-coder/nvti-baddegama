<?php
/**
 * Auth Controller
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Csrf.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Helpers/Sanitizer.php';

class AuthController {

    public function showLogin(): void {
        if (Auth::check()) {
            $this->redirectByRole(Auth::role());
        }
        $pageTitle = "Sign In";
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function handleLogin(): void {
        Csrf::checkOrAbort();

        $loginInput = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($loginInput) || empty($password)) {
            Flash::error('Please enter your Email, MIS Number, or NIC along with your password.');
            header('Location: index.php?page=login');
            exit;
        }

        // Brute-force & Rate Limiting: Lock out after 5 failed attempts within 15 minutes
        if ($this->isRateLimited($loginInput)) {
            Flash::error('Too many failed login attempts detected. For security purposes, access is temporarily locked. Please wait 15 minutes before trying again.');
            header('Location: index.php?page=login');
            exit;
        }

        $db = Database::getConnection();
        // Allow login by Email, or Student MIS Number, or NIC, or Student Code!
        $stmt = $db->prepare("
            SELECT u.*, sp.mis_number, sp.nic, sp.student_code 
            FROM users u 
            LEFT JOIN student_profiles sp ON u.id = sp.user_id 
            WHERE u.email = ? OR sp.mis_number = ? OR sp.nic = ? OR sp.student_code = ?
            LIMIT 1
        ");
        $stmt->execute([$loginInput, $loginInput, $loginInput, $loginInput]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'pending') {
                Auth::logLoginAttempt($loginInput, false);
                Flash::warning('Your student registration is pending verification by NVTI Baddegama Administration. You will receive an approval email once your course enrollment is confirmed.');
                header('Location: index.php?page=login');
                exit;
            }
            if ($user['status'] === 'suspended') {
                Auth::logLoginAttempt($loginInput, false);
                Flash::error('Your account has been suspended. Please contact the NVTI administration.');
                header('Location: index.php?page=login');
                exit;
            }

            // Success
            Auth::logLoginAttempt($loginInput, true);
            Auth::login($user);
            Flash::success("Welcome back, " . $user['name'] . "!");
            $this->redirectByRole($user['role']);
        } else {
            Auth::logLoginAttempt($loginInput, false);
            Flash::error('Invalid credentials or password. You can login using your Email, MIS Number, or NIC.');
            header('Location: index.php?page=login');
            exit;
        }
    }

    public function showActivate(): void {
        if (Auth::check()) {
            $this->redirectByRole(Auth::role());
        }
        $pageTitle = "Activate Student Account & Set Password";
        require_once __DIR__ . '/../Views/auth/activate.php';
    }

    public function handleActivate(): void {
        Csrf::checkOrAbort();

        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($identifier) || empty($password)) {
            Flash::error('Please enter your MIS Number / NIC and your chosen password.');
            header('Location: index.php?page=activate');
            exit;
        }

        if (strlen($password) < 8) {
            Flash::error('Password must be at least 8 characters long.');
            header('Location: index.php?page=activate');
            exit;
        }

        if ($password !== $confirmPassword) {
            Flash::error('Password confirmation does not match.');
            header('Location: index.php?page=activate');
            exit;
        }

        $db = Database::getConnection();
        // Look up student by MIS number, NIC, or Email
        $stmt = $db->prepare("
            SELECT u.*, sp.mis_number, sp.nic, sp.student_code 
            FROM users u 
            JOIN student_profiles sp ON u.id = sp.user_id 
            WHERE sp.mis_number = ? OR sp.nic = ? OR u.email = ?
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier, $identifier]);
        $user = $stmt->fetch();

        if (!$user) {
            Flash::error("No enrolled student found matching '$identifier'. Please verify your MIS Number / NIC or contact the NVTI IT Department.");
            header('Location: index.php?page=activate');
            exit;
        }

        // Set password and ensure status is active
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $up = $db->prepare("UPDATE users SET password = ?, status = 'active' WHERE id = ?");
        $up->execute([$hash, $user['id']]);

        // Auto login
        Auth::logAudit($user['id'], 'ACCOUNT_ACTIVATED', "Student activated their account and set their password.");
        Auth::login($user);
        Flash::success("Account activated successfully! Welcome to your NVTI Baddegama Diploma Portal, " . $user['name'] . ".");
        header('Location: index.php?page=student_dashboard');
        exit;
    }

    public function showRegister(): void {
        if (Auth::check()) {
            $this->redirectByRole(Auth::role());
        }
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $db = Database::getConnection();
        $course = $db->query("SELECT * FROM courses WHERE status = 'active' ORDER BY id ASC LIMIT 1")->fetch();
        $activeBatches = $db->query("SELECT * FROM classes WHERE status = 'active' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($activeBatches)) {
            $activeBatches = [AttendanceManager::ensureActiveBatch($db, (int)($course['id'] ?? 1))];
        }
        $class = $activeBatches[0];

        // Retrieve preserved old input if a previous submission had a server-side error
        $oldReg = $_SESSION['old_register'] ?? [];
        unset($_SESSION['old_register']);

        $pageTitle = "Student Registration";
        require_once __DIR__ . '/../Views/auth/register.php';
    }

    /**
     * Check whether email, NIC, or mobile phone is already registered.
     */
    private function findExistingRegisterField(PDO $db, string $field, string $value): bool {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return false;
        }

        if ($field === 'email') {
            $stmt = $db->prepare("SELECT id FROM users WHERE LOWER(TRIM(email)) = LOWER(?) LIMIT 1");
            $stmt->execute([$trimmed]);
            return (bool)$stmt->fetchColumn();
        }

        if ($field === 'nic') {
            $normalized = strtoupper(str_replace(' ', '', $trimmed));
            $stmt = $db->prepare("SELECT id FROM student_profiles WHERE UPPER(REPLACE(TRIM(nic), ' ', '')) = ? LIMIT 1");
            $stmt->execute([$normalized]);
            return (bool)$stmt->fetchColumn();
        }

        if ($field === 'phone') {
            $digits = preg_replace('/[^0-9]/', '', $trimmed);
            if (strlen($digits) < 9) {
                return false;
            }
            $lastNine = substr($digits, -9);
            $stmt = $db->prepare("
                SELECT id FROM student_profiles
                WHERE phone IS NOT NULL AND phone != ''
                  AND RIGHT(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), 9) = ?
                LIMIT 1
            ");
            $stmt->execute([$lastNine]);
            return (bool)$stmt->fetchColumn();
        }

        return false;
    }

    /**
     * AJAX endpoint: Instant duplicate field check while student types in the registration form.
     */
    public function checkRegisterDuplicate(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $field = trim($_GET['field'] ?? $_POST['field'] ?? '');
        $value = trim($_GET['value'] ?? $_POST['value'] ?? '');

        $messages = [
            'email' => 'This email address is already registered! You cannot register with it again (මෙම Email එක දැනටමත් register කර ඇත).',
            'nic'   => 'This NIC number is already registered! You cannot add it again (මෙම NIC අංකය දැනටමත් register කර ඇත).',
            'phone' => 'This mobile number is already linked to an existing student account (මෙම දුරකථන අංකය දැනටමත් ඇතුළත් කර ඇත).',
        ];

        if (!isset($messages[$field]) || $value === '') {
            echo json_encode(['exists' => false, 'message' => '']);
            exit;
        }

        try {
            $db = Database::getConnection();
            $exists = $this->findExistingRegisterField($db, $field, $value);
            echo json_encode([
                'exists'  => $exists,
                'message' => $exists ? $messages[$field] : '',
            ]);
        } catch (Exception $e) {
            error_log('Register duplicate check error: ' . $e->getMessage());
            echo json_encode(['exists' => false, 'message' => '']);
        }
        exit;
    }

    public function handleRegister(): void {
        Csrf::checkOrAbort();
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';

        $fullName       = trim($_POST['name'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $whatsappNumber = trim($_POST['whatsapp_number'] ?? '');
        $nic            = strtoupper(trim($_POST['nic'] ?? ''));
        $address        = trim($_POST['address'] ?? '');
        $courseId       = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : 1;
        $requestedClassId = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
        $password       = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Save old inputs so user never loses typed data on validation error
        $_SESSION['old_register'] = [
            'name'            => $fullName,
            'email'           => $email,
            'phone'           => $phone,
            'whatsapp_number' => $whatsappNumber,
            'nic'             => $nic,
            'address'         => $address,
            'class_id'        => $requestedClassId,
        ];

        // Basic validations
        if (empty($fullName) || empty($email) || empty($password)) {
            Flash::error('Full name, email address, and password are required.');
            header('Location: index.php?page=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Please enter a valid email address.');
            header('Location: index.php?page=register');
            exit;
        }

        $passCheck = Sanitizer::validatePassword($password);
        if (!$passCheck['valid']) {
            Flash::error($passCheck['message']);
            header('Location: index.php?page=register');
            exit;
        }

        if ($password !== $confirmPassword) {
            Flash::error('Password confirmation does not match.');
            header('Location: index.php?page=register');
            exit;
        }

        $db = Database::getConnection();

        if (!Csrf::claimSubmission('auth_register', [strtolower($email), $nic], 60)) {
            Flash::info("Your registration was already submitted! Please wait for administration approval.");
            header('Location: index.php?page=login');
            exit;
        }

        // Strictly resolve an ACTIVE batch (never enroll new students into an Old / Archived batch!)
        $classId = 0;
        if ($requestedClassId > 0) {
            $clsChk = $db->prepare("SELECT id FROM classes WHERE id = ? AND status = 'active' LIMIT 1");
            $clsChk->execute([$requestedClassId]);
            $classId = (int)$clsChk->fetchColumn();
        }
        if ($classId <= 0) {
            $activeBatch = AttendanceManager::ensureActiveBatch($db, $courseId);
            $classId = (int)$activeBatch['id'];
        }

        // Check if email already registered
        if ($this->findExistingRegisterField($db, 'email', $email)) {
            Flash::error('This email is already registered. Please sign in or use a different email.');
            header('Location: index.php?page=register');
            exit;
        }

        // Check if NIC already registered
        if (!empty($nic) && $this->findExistingRegisterField($db, 'nic', $nic)) {
            Flash::error("A student account with NIC '$nic' already exists. Please sign in or contact administration.");
            header('Location: index.php?page=register');
            exit;
        }

        // Check if Mobile Phone already registered
        if (!empty($phone) && $this->findExistingRegisterField($db, 'phone', $phone)) {
            Flash::error("This mobile number ($phone) is already registered to another student account.");
            header('Location: index.php?page=register');
            exit;
        }

        try {
            $db->beginTransaction();

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert User (status = 'pending' until Admin confirms course enrollment)
            $userStmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'student', 'pending')");
            $userStmt->execute([$fullName, $email, $passwordHash]);
            $userId = (int)$db->lastInsertId();

            // Auto-generate official student code & MIS number
            $studentCode = 'NVTI-ST-' . date('Y') . '-' . str_pad($userId, 3, '0', STR_PAD_LEFT);
            $misNumber   = 'MIS/ICT/' . date('Y') . '/' . str_pad($userId, 3, '0', STR_PAD_LEFT);
            $finalWhatsapp = !empty($whatsappNumber) ? $whatsappNumber : $phone;

            // Insert Student Profile
            $profStmt = $db->prepare("
                INSERT INTO student_profiles (user_id, student_code, mis_number, phone, whatsapp_number, nic, address, course_id, class_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $profStmt->execute([$userId, $studentCode, $misNumber, $phone, $finalWhatsapp, $nic, $address, $courseId, $classId]);

            // Auto enroll in active class (enrollment starts pending/active once user is verified)
            if ($classId) {
                $enrollStmt = $db->prepare("INSERT INTO enrollments (student_id, class_id, status) VALUES (?, ?, 'active')");
                $enrollStmt->execute([$userId, $classId]);
            }

            Auth::logAudit($userId, 'STUDENT_REGISTER_PENDING', "Student registered ($studentCode) - awaiting Admin verification.");
            $db->commit();
            unset($_SESSION['old_register']);

            Flash::info("Registration submitted successfully, $fullName! Your profile is now pending confirmation by the NVTI Baddegama Administration. Once confirmed as a diploma student, you will receive an approval email at <strong>$email</strong> and can sign in.");
            header('Location: index.php?page=login');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Student registration error: " . $e->getMessage());
            Flash::error('Registration failed due to a server error. Please try again.');
            header('Location: index.php?page=register');
            exit;
        }
    }

    public function showChangePassword(): void {
        Auth::requireLogin();
        $pageTitle = "Change Password";
        require_once __DIR__ . '/../Views/auth/change_password.php';
    }

    public function handleChangePassword(): void {
        Auth::requireLogin();
        Csrf::checkOrAbort();

        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($currentPass) || empty($newPass)) {
            Flash::error('Please fill all required password fields.');
            header('Location: index.php?page=change_password');
            exit;
        }

        $passCheck = Sanitizer::validatePassword($newPass);
        if (!$passCheck['valid']) {
            Flash::error($passCheck['message']);
            header('Location: index.php?page=change_password');
            exit;
        }

        if ($newPass !== $confirmPass) {
            Flash::error('New passwords do not match.');
            header('Location: index.php?page=change_password');
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT email, name, password FROM users WHERE id = ?");
        $stmt->execute([Auth::id()]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPass, $user['password'])) {
            Flash::error('Current password is incorrect.');
            header('Location: index.php?page=change_password');
            exit;
        }

        $newHash = password_hash($newPass, PASSWORD_BCRYPT);
        $upStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upStmt->execute([$newHash, Auth::id()]);

        Auth::logAudit(Auth::id(), 'PASSWORD_CHANGE', 'User changed their account password.');

        // Dispatch security notification email to user
        if (!empty($user['email'])) {
            $alertHtml = "
            <div style='background-color: #f8fafc; padding: 25px; font-family: sans-serif;'>
                <div style='max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 10px; padding: 24px; border: 1px solid #e2e8f0;'>
                    <h3 style='color: #7A1418; margin-top: 0;'>&#x1F512; Security Notice: Password Changed</h3>
                    <p>Dear <strong>" . htmlspecialchars($user['name']) . "</strong>,</p>
                    <p>Your portal account password was successfully updated on <strong>" . date('Y-m-d H:i:s') . "</strong>.</p>
                    <p style='color: #e11d48; font-size: 13px;'>* If you did not perform this change, please inform the NVTI administration immediately to secure your account.</p>
                </div>
            </div>";
            Mailer::send($user['email'], "Security Alert: Password Changed - NVTI Baddegama", $alertHtml, $user['name']);
        }

        Flash::success('Password has been successfully changed!');
        header('Location: index.php?page=change_password');
        exit;
    }

    public function showForgotPassword(): void {
        $pageTitle = "Forgot Password";
        require_once __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function handleForgotPassword(): void {
        Csrf::checkOrAbort();
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            Flash::error('Please enter your registered email address.');
            header('Location: index.php?page=forgot_password');
            exit;
        }

        if (!Csrf::claimSubmission('auth_forgot_password', [strtolower($email)], 60)) {
            Flash::info("A password reset request for <strong>" . htmlspecialchars($email) . "</strong> was already processed. Please check your inbox.");
            header('Location: index.php?page=forgot_password');
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(24));
            $tokenHash = hash('sha256', $token);
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $ins = $db->prepare("INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)");
            $ins->execute([$email, $tokenHash, $expires]);

            Auth::logAudit($user['id'], 'PASSWORD_RESET_REQUEST', "Requested password reset token");
            
            // Send Password Reset Email via Brevo / Mailer
            if (defined('SMTP_PASS') && SMTP_PASS !== 'YOUR_BREVO_MASTER_SMTP_KEY') {
                $mailResult = Mailer::sendPasswordReset($email, $user['name'], $token);
                if ($mailResult['success']) {
                    Flash::success("A password reset link has been dispatched to your email address (<strong>" . htmlspecialchars($email) . "</strong>). Please check your inbox.");
                } else {
                    Flash::warning("Password reset token generated: <code>$token</code>. (Email delivery error: " . htmlspecialchars($mailResult['message']) . ")");
                }
            } else {
                // Helpful notice when SMTP credentials have not been configured yet
                Flash::info("Password reset token generated: <code>$token</code>. (Configure Brevo SMTP in <code>config/config.php</code> to dispatch real emails).");
            }
        } else {
            Flash::info("If an account exists with this email, reset instructions have been generated.");
        }

        header('Location: index.php?page=forgot_password');
        exit;
    }

    public function showResetPassword(): void {
        $token = trim($_GET['token'] ?? '');
        if (empty($token)) {
            Flash::error('Invalid or missing password reset token.');
            header('Location: index.php?page=forgot_password');
            exit;
        }

        $db = Database::getConnection();
        $tokenHash = hash('sha256', $token);
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token_hash = ? AND used = 0 AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$tokenHash]);
        $reset = $stmt->fetch();

        if (!$reset) {
            Flash::error('This password reset link is invalid, has expired, or has already been used.');
            header('Location: index.php?page=forgot_password');
            exit;
        }

        $pageTitle = "Set New Password";
        require_once __DIR__ . '/../Views/auth/reset_password.php';
    }

    public function handleResetPassword(): void {
        Csrf::checkOrAbort();
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($token)) {
            Flash::error('Invalid password reset session.');
            header('Location: index.php?page=forgot_password');
            exit;
        }

        $passVal = Sanitizer::validatePassword($password);
        if (!$passVal['valid']) {
            Flash::error($passVal['message']);
            header("Location: index.php?page=reset_password&token=" . urlencode($token));
            exit;
        }

        if ($password !== $confirmPassword) {
            Flash::error('Passwords do not match.');
            header("Location: index.php?page=reset_password&token=" . urlencode($token));
            exit;
        }

        $db = Database::getConnection();
        $tokenHash = hash('sha256', $token);
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token_hash = ? AND used = 0 AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$tokenHash]);
        $reset = $stmt->fetch();

        if (!$reset) {
            Flash::error('This reset request is invalid, has expired, or was already used.');
            header('Location: index.php?page=forgot_password');
            exit;
        }

        // Update user password
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $up = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $up->execute([$newHash, $reset['email']]);

        // Invalidate token (Single-use token protection)
        $db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$reset['id']]);

        // Security confirmation email
        $alertHtml = "
        <div style='background-color: #f8fafc; padding: 25px; font-family: sans-serif;'>
            <div style='max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 10px; padding: 24px; border: 1px solid #e2e8f0;'>
                <h3 style='color: #10b981; margin-top: 0;'>&#x2705; Password Reset Completed</h3>
                <p>Your password for the <strong>NVTI Baddegama National Diploma Portal</strong> has been reset successfully.</p>
                <p>You can now sign in securely with your new password.</p>
            </div>
        </div>";
        Mailer::send($reset['email'], "Password Reset Confirmation - NVTI Baddegama", $alertHtml);

        Flash::success('Your password has been successfully reset! You can now log in.');
        header('Location: index.php?page=login');
        exit;
    }

    private function isRateLimited(string $loginInput): bool {
        try {
            $db = Database::getConnection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM login_logs 
                WHERE (ip_address = ? OR email = ?) 
                  AND status = 'failed' 
                  AND created_at >= (NOW() - INTERVAL 15 MINUTE)
            ");
            $stmt->execute([$ip, $loginInput]);
            return (int)$stmt->fetchColumn() >= 5;
        } catch (Exception $e) {
            return false;
        }
    }

    public function logout(): void {
        Auth::logout();
        Flash::info('You have been securely signed out.');
        header('Location: index.php?page=login');
        exit;
    }

    private function redirectByRole(string $role): void {
        switch ($role) {
            case 'admin':
                header('Location: index.php?page=admin_dashboard');
                break;
            case 'teacher':
                header('Location: index.php?page=teacher_dashboard');
                break;
            case 'student':
            default:
                header('Location: index.php?page=student_dashboard');
                break;
        }
        exit;
    }
}
