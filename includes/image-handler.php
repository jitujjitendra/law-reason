<?php
/**
 * Law & Reason - Image Handler
 * Auto-resize, compress to WebP, generate thumbnails
 * Maintains quality while saving storage space
 */

require_once __DIR__ . '/../config/config.php';

class ImageHandler {
    
    /**
     * Process uploaded image: validate, resize, convert to WebP, create thumbnail
     * 
     * @param array $file $_FILES array element
     * @param string $subDir Subdirectory within uploads (e.g., 'blog', 'topics')
     * @return array|false ['original' => path, 'thumb' => path] or false on failure
     */
    public static function processUpload($file, $subDir = 'blog') {
        // Validate file
        if (!self::validateImage($file)) {
            return false;
        }
        
        $uploadDir = UPLOADS_PATH . $subDir . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $filename = time() . '_' . bin2hex(random_bytes(4));
        
        // Create image resource from uploaded file
        $sourceImage = self::createImageFromFile($file['tmp_name'], $file['type']);
        if (!$sourceImage) {
            return false;
        }
        
        // Get original dimensions
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);
        
        // Resize if larger than max width
        $resizedImage = self::resizeImage($sourceImage, $origWidth, $origHeight, IMAGE_MAX_WIDTH);
        
        // Save as WebP (main image)
        $mainPath = $uploadDir . $filename . '.webp';
        $saved = imagewebp($resizedImage, $mainPath, IMAGE_QUALITY);
        
        if (!$saved) {
            imagedestroy($sourceImage);
            imagedestroy($resizedImage);
            return false;
        }
        
        // Create thumbnail
        $thumbImage = self::resizeImage($sourceImage, $origWidth, $origHeight, IMAGE_THUMB_WIDTH);
        $thumbPath = $uploadDir . $filename . '_thumb.webp';
        imagewebp($thumbImage, $thumbPath, IMAGE_QUALITY);
        
        // Cleanup
        imagedestroy($sourceImage);
        if ($resizedImage !== $sourceImage) imagedestroy($resizedImage);
        if ($thumbImage !== $sourceImage) imagedestroy($thumbImage);
        
        // Return relative paths (for storing in DB)
        $relativePath = 'uploads/' . $subDir . '/' . $filename . '.webp';
        $relativeThumb = 'uploads/' . $subDir . '/' . $filename . '_thumb.webp';
        
        return [
            'original' => $relativePath,
            'thumb' => $relativeThumb,
            'filename' => $filename . '.webp'
        ];
    }
    
    /**
     * Validate uploaded image
     */
    private static function validateImage($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Check file size
        if ($file['size'] > MAX_IMAGE_SIZE) {
            return false;
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            return false;
        }
        
        // Verify it's actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Create GD image resource from file
     */
    private static function createImageFromFile($filepath, $mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($filepath);
            case 'image/png':
                $img = imagecreatefrompng($filepath);
                // Preserve transparency
                imagealphablending($img, false);
                imagesavealpha($img, true);
                return $img;
            case 'image/webp':
                return imagecreatefromwebp($filepath);
            case 'image/gif':
                return imagecreatefromgif($filepath);
            default:
                return false;
        }
    }
    
    /**
     * Resize image maintaining aspect ratio
     */
    private static function resizeImage($sourceImage, $origWidth, $origHeight, $maxWidth) {
        if ($origWidth <= $maxWidth) {
            return $sourceImage;
        }
        
        $ratio = $maxWidth / $origWidth;
        $newWidth = $maxWidth;
        $newHeight = (int)($origHeight * $ratio);
        
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        
        imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        return $resized;
    }
    
    /**
     * Delete image and its thumbnail
     */
    public static function deleteImage($relativePath) {
        $fullPath = ROOT_PATH . 'public/' . $relativePath;
        $thumbPath = str_replace('.webp', '_thumb.webp', $fullPath);
        
        if (file_exists($fullPath)) unlink($fullPath);
        if (file_exists($thumbPath)) unlink($thumbPath);
    }
}
