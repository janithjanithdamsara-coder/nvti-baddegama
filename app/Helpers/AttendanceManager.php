<?php
/**
 * Attendance Manager Helper & Service
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../../config/database.php';

class AttendanceManager {

    /**
     * Get the current active batch (most recent active class)
     */
    public static function getActiveBatch(): ?array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM classes WHERE status = 'active' ORDER BY id DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
        // Fallback: most recent class if no active exists
        $fallback = $db->query("SELECT * FROM classes ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        return $fallback ?: null;
    }

    /**
     * Get the active batch ID dynamically
     */
    public static function getActiveClassId(): int {
        $batch = self::getActiveBatch();
        return $batch ? (int)$batch['id'] : 1;
    }

    /**
     * Get batch details by ID
     */
    public static function getBatchById(int $classId): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM classes WHERE id = ? LIMIT 1");
        $stmt->execute([$classId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get all batches ordered by status (active first) and ID descending
     */
    public static function getAllBatches(): array {
        $db = Database::getConnection();
        return $db->query("SELECT * FROM classes ORDER BY FIELD(status, 'active', 'archived'), id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a student is an active monitor for an active class
     */
    public static function getActiveMonitorStatus(int $userId, int $classId = 0): ?array {
        if ($classId <= 0) {
            $classId = self::getActiveClassId();
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT am.*, u.name as student_name, sp.student_code, sp.mis_number
            FROM attendance_monitors am
            JOIN users u ON am.student_id = u.id
            JOIN student_profiles sp ON u.id = sp.user_id
            JOIN classes c ON am.class_id = c.id
            WHERE am.student_id = ? AND am.class_id = ? AND am.status = 'active' AND u.status = 'active' AND c.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$userId, $classId]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Get all monitors (active and disabled) for a class
     */
    public static function getClassMonitors(int $classId = 1): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT am.*, u.name as student_name, u.email as student_email,
                   sp.student_code, sp.mis_number, sp.phone, sp.whatsapp_number,
                   assigner.name as assigned_by_name
            FROM attendance_monitors am
            JOIN users u ON am.student_id = u.id
            LEFT JOIN student_profiles sp ON u.id = sp.user_id
            LEFT JOIN users assigner ON am.assigned_by = assigner.id
            WHERE am.class_id = ?
            ORDER BY am.monitor_role ASC, am.created_at ASC
        ");
        $stmt->execute([$classId]);
        return $stmt->fetchAll();
    }

    /**
     * Assign or update a student as monitor (primary or backup)
     */
    public static function setMonitor(int $classId, int $studentId, string $role, string $status, int $assignedBy): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO attendance_monitors (class_id, student_id, monitor_role, status, assigned_by)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                monitor_role = VALUES(monitor_role),
                status = VALUES(status),
                assigned_by = VALUES(assigned_by),
                updated_at = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([$classId, $studentId, $role, $status, $assignedBy]);
    }

    /**
     * Toggle monitor status (active / disabled)
     */
    public static function toggleMonitorStatus(int $monitorId, string $newStatus): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE attendance_monitors SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$newStatus, $monitorId]);
    }

    /**
     * Remove a monitor record (Note: all attendance already marked by them is permanently preserved)
     */
    public static function deleteMonitor(int $monitorId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM attendance_monitors WHERE id = ?");
        return $stmt->execute([$monitorId]);
    }

    /**
     * Check if attendance for a specific date is locked / submitted
     */
    public static function getDayLock(int $classId, string $date): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT adl.*, u.name as marked_by_name, u.role as marked_by_user_role,
                   sp.student_code, sp.mis_number,
                   vu.name as verified_by_name
            FROM attendance_day_locks adl
            LEFT JOIN users u ON adl.marked_by_user_id = u.id
            LEFT JOIN student_profiles sp ON u.id = sp.user_id
            LEFT JOIN users vu ON adl.verified_by_user_id = vu.id
            WHERE adl.class_id = ? AND adl.date = ?
            LIMIT 1
        ");
        $stmt->execute([$classId, $date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Unlock a day's attendance (Teacher / Admin authority only)
     */
    public static function unlockDay(int $classId, string $date, int $unlockedBy): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE attendance_day_locks 
            SET is_locked = 0, verification_status = 'pending_verification', unlocked_by = ?, unlocked_at = CURRENT_TIMESTAMP
            WHERE class_id = ? AND date = ?
        ");
        return $stmt->execute([$unlockedBy, $classId, $date]);
    }

    /**
     * Get students in a class (strictly active students in active classes unless $includeArchived is true)
     */
    public static function getClassStudents(int $classId = 0, bool $includeArchived = false): array {
        if ($classId <= 0) {
            $classId = self::getActiveClassId();
        }
        $db = Database::getConnection();
        $sql = "
            SELECT u.id as user_id, u.name, u.email, u.profile_image, u.status as user_status,
                   sp.id as profile_id, sp.student_code, sp.mis_number, sp.nic, sp.phone, sp.whatsapp_number,
                   c.class_name, c.status as class_status
            FROM users u
            JOIN student_profiles sp ON u.id = sp.user_id
            JOIN classes c ON sp.class_id = c.id
            WHERE sp.class_id = ? AND u.role = 'student'
        ";
        if (!$includeArchived) {
            $sql .= " AND u.status = 'active' AND c.status = 'active'";
        }
        $sql .= " ORDER BY sp.student_code ASC, u.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$classId]);
        return $stmt->fetchAll();
    }

    /**
     * Get attendance records for a specific date
     */
    public static function getAttendanceForDate(int $classId, string $date): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT a.*, u.name as marked_by_name
            FROM attendance a
            JOIN student_profiles sp ON a.student_id = sp.user_id
            LEFT JOIN users u ON a.marked_by_user_id = u.id
            WHERE sp.class_id = ? AND a.date = ?
        ");
        $stmt->execute([$classId, $date]);
        $records = $stmt->fetchAll();

        $indexed = [];
        foreach ($records as $r) {
            $indexed[$r['student_id']] = $r;
        }
        return $indexed;
    }

    /**
     * Save attendance records for a batch date
     * Checks if locked. If locked and user is a student, aborts to prevent duplicate marking.
     */
    public static function saveBatchAttendance(int $classId, string $date, array $attendanceData, int $userId, string $userRole): array {
        $db = Database::getConnection();

        // Check lock status
        $lock = self::getDayLock($classId, $date);
        if ($lock && (int)$lock['is_locked'] === 1 && $userRole === 'student') {
            return [
                'success' => false,
                'error' => "Attendance for " . htmlspecialchars($date) . " has already been submitted by " . htmlspecialchars($lock['marked_by_name'] ?? 'Class Monitor') . " and sent to the instructor (Sir) for verification."
            ];
        }

        $db->beginTransaction();
        try {
            $upsertStmt = $db->prepare("
                INSERT INTO attendance (student_id, date, status, remarks, marked_by_user_id)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    remarks = VALUES(remarks),
                    marked_by_user_id = VALUES(marked_by_user_id),
                    created_at = CURRENT_TIMESTAMP
            ");

            foreach ($attendanceData as $studentId => $data) {
                $status = in_array($data['status'] ?? '', ['present', 'absent', 'late', 'excused']) ? $data['status'] : 'present';
                $remarks = trim($data['remarks'] ?? '');
                $upsertStmt->execute([(int)$studentId, $date, $status, $remarks, $userId]);
            }

            // Lock & set verification status
            if ($userRole === 'student') {
                $lockStmt = $db->prepare("
                    INSERT INTO attendance_day_locks (class_id, date, is_locked, marked_by_user_id, marked_by_role, verification_status, submitted_at)
                    VALUES (?, ?, 1, ?, 'student', 'pending_verification', CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE 
                        is_locked = 1,
                        marked_by_user_id = VALUES(marked_by_user_id),
                        marked_by_role = 'student',
                        verification_status = 'pending_verification',
                        submitted_at = CURRENT_TIMESTAMP
                ");
                $lockStmt->execute([$classId, $date, $userId]);
            } else {
                // Teacher / Admin verification
                $roleLabel = ($userRole === 'admin') ? 'admin' : 'teacher';
                $lockStmt = $db->prepare("
                    INSERT INTO attendance_day_locks (class_id, date, is_locked, marked_by_user_id, marked_by_role, verification_status, verified_by_user_id, verified_at, submitted_at)
                    VALUES (?, ?, 1, ?, ?, 'verified', ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE 
                        is_locked = 1,
                        marked_by_user_id = VALUES(marked_by_user_id),
                        marked_by_role = VALUES(marked_by_role),
                        verification_status = 'verified',
                        verified_by_user_id = ?,
                        verified_at = CURRENT_TIMESTAMP
                ");
                $lockStmt->execute([$classId, $date, $userId, $roleLabel, $userId, $userId]);
            }

            $db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if a date is declared as a holiday / institute closure
     */
    public static function getHoliday(string $date, int $classId = 1): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM attendance_holidays WHERE date = ? AND (class_id = ? OR class_id IS NULL) LIMIT 1");
        $stmt->execute([$date, $classId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get all holidays for a month
     */
    public static function getMonthlyHolidays(string $yearMonth, int $classId = 1): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM attendance_holidays WHERE date LIKE ? AND (class_id = ? OR class_id IS NULL) ORDER BY date ASC");
        $stmt->execute(["$yearMonth%", $classId]);
        $rows = $stmt->fetchAll();
        $indexed = [];
        foreach ($rows as $r) {
            $indexed[$r['date']] = $r;
        }
        return $indexed;
    }

    /**
     * Declare a date as a holiday / closure with reason
     */
    public static function setHoliday(string $date, string $holidayName, string $holidayType, string $description, int $classId, int $userId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO attendance_holidays (class_id, date, holiday_name, holiday_type, description, marked_by_user_id)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                holiday_name = VALUES(holiday_name),
                holiday_type = VALUES(holiday_type),
                description = VALUES(description),
                marked_by_user_id = VALUES(marked_by_user_id)
        ");
        return $stmt->execute([$classId, $date, $holidayName, $holidayType, $description, $userId]);
    }

    /**
     * Remove a holiday declaration
     */
    public static function removeHoliday(string $date, int $classId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM attendance_holidays WHERE date = ? AND (class_id = ? OR class_id IS NULL)");
        return $stmt->execute([$date, $classId]);
    }

    /**
     * Get monthly attendance matrix for all students in class
     */
    public static function getMonthlyMatrix(int $classId = 0, string $yearMonth = '', bool $includeArchived = false): array {
        if ($classId <= 0) {
            $classId = self::getActiveClassId();
        }
        if (empty($yearMonth)) {
            $yearMonth = date('Y-m');
        }
        $db = Database::getConnection();
        $students = self::getClassStudents($classId, $includeArchived);

        // Fetch distinct recorded dates in this month
        $datesStmt = $db->prepare("
            SELECT DISTINCT a.date, adl.is_locked, adl.marked_by_role, adl.verification_status,
                   u.name as marked_by_name, vu.name as verified_by_name
            FROM attendance a
            JOIN student_profiles sp ON a.student_id = sp.user_id
            LEFT JOIN attendance_day_locks adl ON (adl.class_id = sp.class_id AND adl.date = a.date)
            LEFT JOIN users u ON adl.marked_by_user_id = u.id
            LEFT JOIN users vu ON adl.verified_by_user_id = vu.id
            WHERE sp.class_id = ? AND a.date LIKE ?
            ORDER BY a.date ASC
        ");
        $datesStmt->execute([$classId, "$yearMonth%"]);
        $dates = $datesStmt->fetchAll();

        // Fetch all attendance rows for this month
        $attStmt = $db->prepare("
            SELECT a.student_id, a.date, a.status, a.remarks
            FROM attendance a
            JOIN student_profiles sp ON a.student_id = sp.user_id
            WHERE sp.class_id = ? AND a.date LIKE ?
        ");
        $attStmt->execute([$classId, "$yearMonth%"]);
        $rows = $attStmt->fetchAll();

        $matrix = [];
        foreach ($rows as $r) {
            $matrix[$r['student_id']][$r['date']] = $r['status'];
        }

        return [
            'students' => $students,
            'dates'    => $dates,
            'matrix'   => $matrix
        ];
    }
}
