<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

// All gallery actions require admin auth
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
csrf_verify_request();

$action = $_REQUEST['action'] ?? 'list';
$uploadDir = __DIR__ . '/../uploads/';
$imagesDir = __DIR__ . '/../images/';

// Ensure uploads dir exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

switch ($action) {

    case 'list':
        $files = [];
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        // Scan uploads/ directory
        foreach (glob($uploadDir . '*') as $file) {
            if (!is_file($file)) continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) continue;
            $name = basename($file);
            $files[] = [
                'name' => $name,
                'path' => 'uploads/' . $name,
                'size' => filesize($file),
                'modified' => filemtime($file),
                'dir' => 'uploads'
            ];
        }

        // Scan images/ directory
        foreach (glob($imagesDir . '*') as $file) {
            if (!is_file($file)) continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) continue;
            $name = basename($file);
            $files[] = [
                'name' => $name,
                'path' => 'images/' . $name,
                'size' => filesize($file),
                'modified' => filemtime($file),
                'dir' => 'images'
            ];
        }

        // Sort by modified date descending (newest first)
        usort($files, function($a, $b) { return $b['modified'] - $a['modified']; });

        echo json_encode(['success' => true, 'data' => $files]);
        break;

    case 'upload':
        if (empty($_FILES)) {
            echo json_encode(['success' => false, 'message' => 'No files uploaded']);
            break;
        }

        $uploaded = [];
        $errors = [];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

        // Handle both single and multi-file uploads
        $fileKeys = array_keys($_FILES);
        foreach ($fileKeys as $key) {
            $fileData = $_FILES[$key];
            // Normalize to array format for multi-file
            if (!is_array($fileData['name'])) {
                $fileData = [
                    'name' => [$fileData['name']],
                    'tmp_name' => [$fileData['tmp_name']],
                    'error' => [$fileData['error']],
                    'size' => [$fileData['size']],
                    'type' => [$fileData['type']]
                ];
            }

            for ($i = 0; $i < count($fileData['name']); $i++) {
                if ($fileData['error'][$i] !== UPLOAD_ERR_OK) {
                    $errors[] = $fileData['name'][$i] . ': upload error';
                    continue;
                }

                if ($fileData['size'][$i] > 10 * 1024 * 1024) {
                    $errors[] = $fileData['name'][$i] . ': exceeds 10MB limit';
                    continue;
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $fileData['tmp_name'][$i]);
                finfo_close($finfo);

                if (!in_array($mime, $allowedMimes)) {
                    $errors[] = $fileData['name'][$i] . ': invalid file type';
                    continue;
                }

                // Sanitize original filename
                $origName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($fileData['name'][$i]));
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $baseName = pathinfo($origName, PATHINFO_FILENAME);
                $finalName = $baseName . '.' . $ext;

                // Avoid overwriting: append counter if exists
                $counter = 1;
                while (file_exists($uploadDir . $finalName)) {
                    $finalName = $baseName . '_' . $counter . '.' . $ext;
                    $counter++;
                }

                if (move_uploaded_file($fileData['tmp_name'][$i], $uploadDir . $finalName)) {
                    $uploaded[] = [
                        'name' => $finalName,
                        'path' => 'uploads/' . $finalName,
                        'size' => $fileData['size'][$i]
                    ];
                } else {
                    $errors[] = $fileData['name'][$i] . ': failed to save';
                }
            }
        }

        $msg = count($uploaded) . ' file(s) uploaded';
        if (!empty($errors)) $msg .= ', ' . count($errors) . ' failed';

        echo json_encode([
            'success' => count($uploaded) > 0,
            'message' => $msg,
            'uploaded' => $uploaded,
            'errors' => $errors
        ]);
        break;

    case 'rename':
        $oldPath = trim($_POST['old_path'] ?? '');
        $newName = trim($_POST['new_name'] ?? '');

        if (empty($oldPath) || empty($newName)) {
            echo json_encode(['success' => false, 'message' => 'Old path and new name are required']);
            break;
        }

        // Sanitize new name
        $newName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $newName);
        $ext = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));
        if (pathinfo($newName, PATHINFO_EXTENSION) !== $ext) {
            $newName .= '.' . $ext;
        }

        $fullOld = __DIR__ . '/../' . $oldPath;
        $dir = dirname($fullOld);
        $fullNew = $dir . '/' . $newName;

        // Path traversal protection
        $realOld = realpath($fullOld);
        $realUploads = realpath($uploadDir);
        $realImages = realpath($imagesDir);
        if ($realOld === false || (strpos($realOld, $realUploads) !== 0 && strpos($realOld, $realImages) !== 0)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file path']);
            break;
        }

        if (!file_exists($fullOld)) {
            echo json_encode(['success' => false, 'message' => 'File not found']);
            break;
        }

        if (file_exists($fullNew)) {
            echo json_encode(['success' => false, 'message' => 'A file with that name already exists']);
            break;
        }

        if (rename($fullOld, $fullNew)) {
            $newPath = dirname($oldPath) . '/' . $newName;
            echo json_encode(['success' => true, 'message' => 'File renamed', 'new_path' => $newPath]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to rename file']);
        }
        break;

    case 'delete':
        $path = trim($_POST['path'] ?? '');
        if (empty($path)) {
            echo json_encode(['success' => false, 'message' => 'Path is required']);
            break;
        }

        $fullPath = __DIR__ . '/../' . $path;

        // Safety: only allow deleting from uploads/ or images/
        $realPath = realpath($fullPath);
        $realUploads = realpath($uploadDir);
        $realImages = realpath($imagesDir);

        if ($realPath === false || (strpos($realPath, $realUploads) !== 0 && strpos($realPath, $realImages) !== 0)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file path']);
            break;
        }

        if (unlink($realPath)) {
            echo json_encode(['success' => true, 'message' => 'File deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete file']);
        }
        break;

    case 'bulk_delete':
        $paths = $_POST['paths'] ?? [];
        if (empty($paths) || !is_array($paths)) {
            echo json_encode(['success' => false, 'message' => 'No paths provided']);
            break;
        }

        $deleted = 0;
        $failed  = [];
        $realUploads = realpath($uploadDir);
        $realImages  = realpath($imagesDir);

        foreach ($paths as $path) {
            $path = trim($path);
            if (empty($path)) continue;

            $fullPath = __DIR__ . '/../' . $path;
            $realPath = realpath($fullPath);

            if ($realPath === false ||
                (strpos($realPath, $realUploads) !== 0 && strpos($realPath, $realImages) !== 0)) {
                $failed[] = basename($path);
                continue;
            }

            if (unlink($realPath)) {
                $deleted++;
            } else {
                $failed[] = basename($path);
            }
        }

        $msg = $deleted . ' file(s) deleted';
        if (!empty($failed)) $msg .= ', ' . count($failed) . ' failed';

        echo json_encode([
            'success' => $deleted > 0,
            'message' => $msg,
            'deleted' => $deleted,
            'failed'  => $failed
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
