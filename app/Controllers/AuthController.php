<?php
/**
 * Auth Controller
 * EduCore LMS
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
                Flash::warning('Your student account is pending approval by an administrator.');
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
        $db = Database::getConnection();
        $course = $db->query("SELECT * FROM courses WHERE status = 'active' ORDER BY id ASC LIMIT 1")->fetch();
        $class  = $db->query("SELECT * FROM classes WHERE status = 'active' ORDER BY id ASC LIMIT 1")->fetch();

        $pageTitle = "Student Registration";
        require_once __DIR__ . '/../Views/auth/register.php';
    }

    public function handleRegister(): void {
        Csrf::checkOrAbort();

        $fullName       = trim($_POST['name'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $whatsappNumber = trim($_POST['whatsapp_number'] ?? '');
        $nic            = strtoupper(trim($_POST['nic'] ?? ''));
        $address        = trim($_POST['address'] ?? '');
        $courseId       = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : 1;
        $classId        = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : 1;
        $password       = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

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

        if (strlen($password) < 6) {
            Flash::error('Password must be at least 6 characters long.');
            header('Location: index.php?page=register');
            exit;
        }

        if ($password !== $confirmPassword) {
            Flash::error('Password confirmation does not match.');
            header('Location: index.php?page=register');
            exit;
        }

        $db = Database::getConnection();

        // Check if email already registered
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Flash::error('This email is already registered. Please sign in or use a different email.');
            header('Location: index.php?page=register');
            exit;
        }

        // Check if NIC already registered
        if (!empty($nic)) {
            $nicStmt = $db->prepare("SELECT id FROM student_profiles WHERE nic = ?");
            $nicStmt->execute([$nic]);
            if ($nicStmt->fetch()) {
                Flash::error("A student account with NIC '$nic' already exists. Please sign in or use Account Activation.");
                header('Location: index.php?page=register');
                exit;
            }
        }

        try {
            $db->beginTransaction();

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert User (status = 'active')
            $userStmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'student', 'active')");
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

            // Auto enroll in selected class
            if ($classId) {
                $enrollStmt = $db->prepare("INSERT INTO enrollments (student_id, class_id, status) VALUES (?, ?, 'active')");
                $enrollStmt->execute([$userId, $classId]);
            }

            Auth::logAudit($userId, 'STUDENT_REGISTER', "Student self-registered with Code: $studentCode, MIS: $misNumber");
            $db->commit();

            // Auto login the newly registered student
            $newUser = [
                'id'    => $userId,
                'name'  => $fullName,
                'email' => $email,
                'role'  => 'student',
                'image' => null
            ];
            Auth::login($newUser);

            Flash::success("Welcome to NVTI Baddegama Diploma Portal, $fullName! Your student profile has been created successfully (Student Code: $studentCode).");
            header('Location: index.php?page=student_dashboard');
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

        if (strlen($newPass) < 6) {
            Flash::error('New password must be at least 6 characters long.');
            header('Location: index.php?page=change_password');
            exit;
        }

        if ($newPass !== $confirmPass) {
            Flash::error('New passwords do not match.');
            header('Location: index.php?page=change_password');
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
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
