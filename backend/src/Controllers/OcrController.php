<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

/**
 * OcrController - Handles document OCR extraction for auto-filling forms.
 * 
 * Accepts uploaded documents (passport, birth certificate) and forwards them
 * to the Python OCR microservice for extraction.
 * 
 * Endpoints:
 * - POST /ocr/extract - Extract data from uploaded document
 */
class OcrController extends BaseController
{
    // Python OCR service URL
    private const OCR_SERVICE_URL = 'http://127.0.0.1:5001/api/ocr/extract';
    
    // Allowed file types
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'application/pdf'
    ];
    
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf'];
    
    // Maximum file size (10MB)
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;
    
    // Secure temp directory (relative to backend root)
    private const TEMP_DIR = __DIR__ . '/../../temp/ocr';

    /**
     * Extract data from uploaded document.
     * POST /ocr/extract
     * 
     * Request:
     * - file: Uploaded document (multipart/form-data)
     * - document_type: 'passport' or 'birth_certificate'
     * 
     * Response:
     * - success: bool
     * - extracted_data: object with form fields
     * - confidence: object with scores
     * - warnings: array of warning messages
     */
    public function extractFromDocument(array $data, array $params): array
    {
        // Require user authentication
        $auth = $this->requireAuth($data);
        if (!$auth) {
            return $this->error('Unauthorized', 401);
        }

        // Check if file was uploaded
        if (empty($data['_files']['file'])) {
            return $this->error('No file uploaded', 400);
        }

        $file = $data['_files']['file'];
        $documentType = $data['document_type'] ?? ($data['documentType'] ?? null);

        // Validate document type
        if (!$documentType || !in_array($documentType, ['passport', 'birth_certificate'])) {
            return $this->error('Invalid document type. Must be "passport" or "birth_certificate"', 400);
        }

        // Validate file
        $validationError = $this->validateUploadedFile($file);
        if ($validationError) {
            return $this->error($validationError, 400);
        }

        // Create temp directory if it doesn't exist
        if (!$this->ensureTempDirectory()) {
            return $this->error('Server error: Cannot create temp directory', 500);
        }

        $tempPath = null;
        
        try {
            // Save file to secure temp location
            $tempPath = $this->saveToTemp($file);
            if (!$tempPath) {
                return $this->error('Failed to save uploaded file', 500);
            }

            // Call Python OCR service
            $ocrResult = $this->callOcrService($tempPath, $documentType);
            
            if ($ocrResult === null) {
                return $this->error('OCR service unavailable. Please try again later.', 503);
            }

            // Log OCR usage
            $this->logService->logUserActivity(
                (string)$auth['id'],
                'OCR_EXTRACTION',
                [
                    'document_type' => $documentType,
                    'success' => $ocrResult['success'] ?? false,
                    'confidence' => $ocrResult['confidence']['overall'] ?? 0
                ],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            // Return OCR result to frontend
            return $this->success($ocrResult);

        } catch (\Exception $e) {
            error_log("OCR extraction error: " . $e->getMessage());
            return $this->error('Document extraction failed', 500);
        } finally {
            // Always delete temp file
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Validate uploaded file for security and type.
     */
    private function validateUploadedFile(array $file): ?string
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder on server',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload blocked by extension',
            ];
            return $errorMessages[$file['error']] ?? 'Unknown upload error';
        }

        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return 'File too large. Maximum size is 10MB';
        }

        // Check file extension
        $filename = $file['name'] ?? '';
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return 'Invalid file type. Allowed: JPG, PNG, PDF';
        }

        // Check MIME type (more reliable than extension)
        $mimeType = $file['type'] ?? '';
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            // Also check with finfo for more accuracy
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->file($file['tmp_name']);
            if (!in_array($detectedMime, self::ALLOWED_MIME_TYPES)) {
                return 'Invalid file content type';
            }
        }

        // Additional security: check for PHP code in image files
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $content = file_get_contents($file['tmp_name'], false, null, 0, 1024);
            if (preg_match('/<\?php|<\?=|<%/i', $content)) {
                return 'File contains invalid content';
            }
        }

        return null; // No errors
    }

    /**
     * Ensure temp directory exists and is secure.
     */
    private function ensureTempDirectory(): bool
    {
        $tempDir = self::TEMP_DIR;
        
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0750, true)) {
                error_log("Failed to create temp directory: $tempDir");
                return false;
            }
            
            // Create .htaccess to deny direct access
            $htaccess = $tempDir . '/.htaccess';
            file_put_contents($htaccess, "Deny from all\n");
        }
        
        return true;
    }

    /**
     * Save uploaded file to secure temp location.
     */
    private function saveToTemp(array $file): ?string
    {
        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $uniqueName = bin2hex(random_bytes(16)) . '.' . $extension;
        $tempPath = self::TEMP_DIR . '/' . $uniqueName;
        
        if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
            error_log("Failed to move uploaded file to: $tempPath");
            return null;
        }
        
        return $tempPath;
    }

    /**
     * Call Python OCR service via HTTP.
     */
    private function callOcrService(string $filePath, string $documentType): ?array
    {
        // Check if file exists
        if (!file_exists($filePath)) {
            error_log("OCR: File not found: $filePath");
            return null;
        }

        // Prepare cURL request
        $ch = curl_init();
        
        // Create CURLFile object for multipart upload
        $cfile = new \CURLFile($filePath, mime_content_type($filePath), basename($filePath));
        
        $postData = [
            'file' => $cfile,
            'document_type' => $documentType
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => self::OCR_SERVICE_URL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60, // 60 second timeout for OCR processing
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("OCR service cURL error: $curlError");
            return null;
        }

        if ($httpCode !== 200) {
            error_log("OCR service returned HTTP $httpCode: $response");
            // Try to parse error response
            $errorData = json_decode($response, true);
            if ($errorData && isset($errorData['detail'])) {
                return [
                    'success' => false,
                    'error' => $errorData['detail'],
                    'warnings' => []
                ];
            }
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("OCR service returned invalid JSON: " . json_last_error_msg());
            return null;
        }

        return $data;
    }

    /**
     * Health check for OCR service.
     * GET /ocr/health
     */
    public function health(array $data, array $params): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'http://127.0.0.1:5001/health',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $healthData = json_decode($response, true);
            return $this->success([
                'ocr_service' => 'available',
                'tesseract' => $healthData['tesseract_available'] ?? false,
                'version' => $healthData['version'] ?? 'unknown'
            ]);
        }

        return $this->success([
            'ocr_service' => 'unavailable',
            'tesseract' => false,
            'message' => 'OCR service is not running'
        ]);
    }
}
