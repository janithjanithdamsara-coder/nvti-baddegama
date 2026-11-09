<?php
/**
 * Student Verification Document Manager Helper
 * EduCore LMS - NVTI Baddegama
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/Uploader.php';

class DocumentManager {

    /**
     * The 7 Official verification document types required by NVTI
     */
    public static function getDocTypes(): array {
        return [
            'ol_certificate' => [
                'type'        => 'ol_certificate',
                'title'       => 'G.C.E. O/L Certificate',
                'title_si'    => 'සාමාන්‍ය පෙළ (O/L) සහතිකය',
                'description' => 'Official O/L results sheet or certified department certificate.',
                'icon'        => 'bi-award-fill',
                'color'       => 'primary'
            ],
            'al_certificate' => [
                'type'        => 'al_certificate',
                'title'       => 'G.C.E. A/L Certificate',
                'title_si'    => 'උසස් පෙළ (A/L) සහතිකය',
                'description' => 'Official A/L examination results sheet or certified statement.',
                'icon'        => 'bi-mortarboard-fill',
                'color'       => 'indigo'
            ],
            'grama_niladhari' => [
                'type'        => 'grama_niladhari',
                'title'       => 'Grama Niladhari Certificate',
                'title_si'    => 'ග්‍රාම නිලධාරී සහතිකය (GN Certificate)',
                'description' => 'Residency & character certificate issued by your Grama Niladhari division.',
                'icon'        => 'bi-house-check-fill',
                'color'       => 'info'
            ],
            'character_certificate' => [
                'type'        => 'character_certificate',
                'title'       => 'Character Certificate',
                'title_si'    => 'චරිත සහතිකය (Principal Testimonial)',
                'description' => 'Official testimonial issued by school principal or recognized referee.',
                'icon'        => 'bi-file-earmark-person-fill',
                'color'       => 'teal'
            ],
            'school_leaving' => [
                'type'        => 'school_leaving',
                'title'       => 'School Leaving Certificate',
                'title_si'    => 'පාසල් හැරයාමේ සහතිකය (School Leaving)',
                'description' => 'Official school leaving certificate from your last attended school.',
                'icon'        => 'bi-journal-check',
                'color'       => 'warning'
            ],
            'birth_certificate' => [
                'type'        => 'birth_certificate',
                'title'       => 'Birth Certificate',
                'title_si'    => 'උප්පැන්න සහතිකය (Birth Certificate)',
                'description' => 'Certified copy of official birth certificate (both sides if applicable).',
                'icon'        => 'bi-file-earmark-medical-fill',
                'color'       => 'danger'
            ],
            'nic_copy' => [
                'type'        => 'nic_copy',
                'title'       => 'National Identity Card (NIC)',
                'title_si'    => 'ජාතික හැඳුනුම්පත් පිටපත (NIC Copy)',
                'description' => 'Clear scanned copy of your NIC (both front and reverse side).',
                'icon'        => 'bi-person-vcard-fill',
                'color'       => 'dark'
            ],
        ];
    }

    /**
     * Fetch all documents uploaded by a student indexed by doc_type
     */
    public static function getStudentDocuments(int $studentId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT sd.*, u.name as verifier_name
            FROM student_documents sd
            LEFT JOIN users u ON sd.verified_by_user_id = u.id
            WHERE sd.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $docs = [];
        foreach ($rows as $row) {
            $docs[$row['doc_type']] = $row;
        }
        return $docs;
    }

    /**
     * Get summary metrics for a student's documents
     */
    public static function getStudentSummary(int $studentId): array {
        $types = self::getDocTypes();
        $totalTypes = count($types); // 7
        $docs = self::getStudentDocuments($studentId);

        $uploaded = 0;
        $verified = 0;
        $pending = 0;
        $rejected = 0;

        foreach ($types as $key => $meta) {
            if (isset($docs[$key])) {
                $uploaded++;
                $st = $docs[$key]['status'];
                if ($st === 'verified') $verified++;
                elseif ($st === 'rejected') $rejected++;
                else $pending++;
            }
        }

        $isComplete = ($uploaded === $totalTypes);
        $isAllVerified = ($verified === $totalTypes);
        $percent = ($totalTypes > 0) ? round(($uploaded / $totalTypes) * 100) : 0;

        return [
            'total'          => $totalTypes,
            'uploaded'       => $uploaded,
            'missing'        => $totalTypes - $uploaded,
            'verified'       => $verified,
            'pending'        => $pending,
            'rejected'       => $rejected,
            'is_complete'    => $isComplete,
            'is_all_verified'=> $isAllVerified,
            'percent'        => $percent,
            'docs'           => $docs
        ];
    }

    /**
     * Get directory of all students with their document completion status
     */
    public static function getStudentsDocDirectory(?int $classId = null): array {
        $db = Database::getConnection();
        $sql = "
            SELECT u.id as user_id, u.name, u.email,
                   sp.phone, sp.student_code, sp.mis_number, sp.nic, sp.class_id,
                   c.class_name
            FROM users u
            JOIN student_profiles sp ON u.id = sp.user_id
            LEFT JOIN classes c ON sp.class_id = c.id
            WHERE u.role = 'student'
        ";
        $params = [];
        if ($classId !== null) {
            $sql .= " AND sp.class_id = ?";
            $params[] = $classId;
        }
        $sql .= " ORDER BY sp.student_code ASC, u.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $types = self::getDocTypes();
        $totalRequired = count($types);

        // Fetch all student documents in batch
        $docStmt = $db->query("
            SELECT student_id, doc_type, status, id, original_filename, file_size_bytes, uploaded_at, verified_at, admin_notes
            FROM student_documents
        ");
        $allDocs = $docStmt->fetchAll(PDO::FETCH_ASSOC);

        $docsByStudent = [];
        foreach ($allDocs as $d) {
            $docsByStudent[$d['student_id']][$d['doc_type']] = $d;
        }

        $result = [];
        foreach ($students as $st) {
            $sId = (int)$st['user_id'];
            $stDocs = $docsByStudent[$sId] ?? [];
            $uploadedCount = count($stDocs);

            $verifiedCount = 0;
            $pendingCount = 0;
            $rejectedCount = 0;

            foreach ($stDocs as $d) {
                if ($d['status'] === 'verified') $verifiedCount++;
                elseif ($d['status'] === 'rejected') $rejectedCount++;
                else $pendingCount++;
            }

            $isComplete = ($uploadedCount >= $totalRequired);
            $isAllVerified = ($verifiedCount >= $totalRequired);

            $statusBadge = 'none';
            if ($isAllVerified) {
                $statusBadge = 'verified';
            } elseif ($isComplete) {
                $statusBadge = 'completed';
            } elseif ($uploadedCount > 0) {
                $statusBadge = 'partial';
            }

            $result[] = [
                'user_id'        => $sId,
                'name'           => $st['name'],
                'student_code'   => $st['student_code'],
                'mis_number'     => $st['mis_number'],
                'nic'            => $st['nic'],
                'phone'          => $st['phone'],
                'class_name'     => $st['class_name'],
                'uploaded_count' => $uploadedCount,
                'total_required' => $totalRequired,
                'verified_count' => $verifiedCount,
                'pending_count'  => $pendingCount,
                'rejected_count' => $rejectedCount,
                'is_complete'    => $isComplete,
                'status_badge'   => $statusBadge,
                'documents'      => $stDocs
            ];
        }

        return $result;
    }

    /**
     * Upload / Replace a student document
     */
    public static function uploadDocument(int $studentId, string $docType, array $file): array {
        $types = self::getDocTypes();
        if (!isset($types[$docType])) {
            return ['success' => false, 'error' => 'Invalid document type selected.'];
        }

        $uploadResult = Uploader::upload($file, DOCUMENT_UPLOAD_PATH, ['pdf', 'jpg', 'jpeg', 'png']);
        if (!$uploadResult['success']) {
            return ['success' => false, 'error' => $uploadResult['error']];
        }

        $db = Database::getConnection();
        $title = $types[$docType]['title'];

        // Check if existing record
        $stmt = $db->prepare("SELECT id, file_path FROM student_documents WHERE student_id = ? AND doc_type = ?");
        $stmt->execute([$studentId, $docType]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Delete old file if present
            if (!empty($existing['file_path']) && file_exists($existing['file_path'])) {
                @unlink($existing['file_path']);
            }

            $update = $db->prepare("
                UPDATE student_documents
                SET title = ?,
                    file_path = ?,
                    original_filename = ?,
                    file_size_bytes = ?,
                    file_mime = ?,
                    status = 'pending',
                    admin_notes = NULL,
                    verified_by_user_id = NULL,
                    verified_at = NULL,
                    uploaded_at = NOW()
                WHERE id = ?
            ");
            $update->execute([
                $title,
                $uploadResult['path'],
                $uploadResult['original_filename'],
                $uploadResult['size'],
                $uploadResult['mime'],
                $existing['id']
            ]);
        } else {
            $insert = $db->prepare("
                INSERT INTO student_documents
                (student_id, doc_type, title, file_path, original_filename, file_size_bytes, file_mime, status, uploaded_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $insert->execute([
                $studentId,
                $docType,
                $title,
                $uploadResult['path'],
                $uploadResult['original_filename'],
                $uploadResult['size'],
                $uploadResult['mime']
            ]);
        }

        return ['success' => true, 'doc_type' => $docType, 'title' => $title];
    }

    /**
     * Verify or reject a student document (Instructor / Admin action)
     */
    public static function verifyDocument(int $docId, string $status, ?string $adminNotes, int $verifierUserId): array {
        if (!in_array($status, ['verified', 'rejected', 'pending'], true)) {
            return ['success' => false, 'error' => 'Invalid status.'];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE student_documents
            SET status = ?,
                admin_notes = ?,
                verified_by_user_id = ?,
                verified_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $status,
            $adminNotes ? trim($adminNotes) : null,
            $verifierUserId,
            $docId
        ]);

        return ['success' => true];
    }

    /**
     * Fetch single document by ID
     */
    public static function getDocumentById(int $docId): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT sd.*, u.name as student_name, sp.student_code, sp.mis_number, sp.nic
            FROM student_documents sd
            JOIN users u ON sd.student_id = u.id
            LEFT JOIN student_profiles sp ON u.id = sp.user_id
            WHERE sd.id = ?
        ");
        $stmt->execute([$docId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
