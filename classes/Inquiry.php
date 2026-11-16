<?php
require_once __DIR__ . '/BaseModel.php';

class Inquiry extends BaseModel
{
    /** Allowed attachment types for the Contact Us form. */
    public const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'docx', 'txt', 'md'];

    protected function table(): string
    {
        return 'inquiries';
    }

    /**
     * Validate and store an optional attachment under storage/inquiries/ (not web-accessible).
     * Returns ['path' => ..., 'name' => ...], ['error' => ...], or [] when nothing was attached.
     */
    public static function saveAttachment(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'That file is larger than the server allows.' : 'The file could not be uploaded. Please try again.'];
        }
        if ($file['size'] > MAX_CONTACT_FILE_MB * 1024 * 1024) {
            return ['error' => 'The file must be ' . MAX_CONTACT_FILE_MB . ' MB or smaller.'];
        }

        $original = basename((string) $file['name']);
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return ['error' => 'Attachments can be PNG, JPG, JPEG, DOCX, TXT or MD files only.'];
        }

        // Check the contents match the extension, not just the name.
        $head = (string) file_get_contents($file['tmp_name'], false, null, 0, 8192);
        $ok = match ($ext) {
            'png'          => @getimagesize($file['tmp_name']) !== false && str_starts_with($head, "\x89PNG"),
            'jpg', 'jpeg'  => @getimagesize($file['tmp_name']) !== false && str_starts_with($head, "\xFF\xD8"),
            'docx'         => str_starts_with($head, 'PK'),
            default        => !str_contains($head, "\0"),   // txt / md must be plain text
        };
        if (!$ok) {
            return ['error' => 'That file does not look like a real ' . strtoupper($ext) . ' file.'];
        }

        $dir = __DIR__ . '/../storage/inquiries';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return ['error' => 'The file could not be stored.'];
        }
        $stored = bin2hex(random_bytes(12)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $stored)) {
            return ['error' => 'The file could not be stored.'];
        }
        return ['path' => 'storage/inquiries/' . $stored, 'name' => mb_strimwidth_safe($original, 255)];
    }

    public function create(array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO inquiries (user_id, first_name, last_name, email, message, attachment_path, attachment_name)
                 VALUES (?,?,?,?,?,?,?)'
            );
            return $stmt->execute([
                $d['user_id'], $d['first_name'], $d['last_name'], $d['email'], $d['message'],
                $d['attachment_path'] ?? null, $d['attachment_name'] ?? null,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
