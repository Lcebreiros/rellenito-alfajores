<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureFileUpload implements ValidationRule
{
    /**
     * Allowed MIME types
     */
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp',
        'image/gif',
    ];

    /**
     * Allowed file extensions
     */
    protected array $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif',
    ];

    /**
     * Maximum file size in bytes (5MB)
     */
    protected int $maxSize = 5242880;

    /**
     * Create a new rule instance.
     */
    public function __construct(?array $allowedMimeTypes = null, ?array $allowedExtensions = null, ?int $maxSize = null)
    {
        if ($allowedMimeTypes !== null) {
            $this->allowedMimeTypes = $allowedMimeTypes;
        }
        if ($allowedExtensions !== null) {
            $this->allowedExtensions = $allowedExtensions;
        }
        if ($maxSize !== null) {
            $this->maxSize = $maxSize;
        }
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('El archivo no es válido.');
            return;
        }

        // Validar que el archivo fue subido correctamente
        if (!$value->isValid()) {
            $fail('El archivo no se subió correctamente.');
            return;
        }

        // Validar tamaño del archivo
        if ($value->getSize() > $this->maxSize) {
            $maxSizeMB = round($this->maxSize / 1048576, 2);
            $fail("El archivo no puede exceder {$maxSizeMB}MB.");
            return;
        }

        // Validar MIME type
        $mimeType = $value->getMimeType();
        if (!in_array($mimeType, $this->allowedMimeTypes, true)) {
            $fail('El tipo de archivo no está permitido.');
            return;
        }

        // Validar extensión del archivo
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $fail('La extensión del archivo no está permitida.');
            return;
        }

        // Validar que la extensión coincida con el MIME type
        if (!$this->mimeTypeMatchesExtension($mimeType, $extension)) {
            $fail('El contenido del archivo no coincide con su extensión.');
            return;
        }

        // Validar que no sea un archivo ejecutable disfrazado
        if ($this->isExecutable($value)) {
            $fail('Los archivos ejecutables no están permitidos.');
            return;
        }
    }

    /**
     * Check if MIME type matches the file extension
     */
    protected function mimeTypeMatchesExtension(string $mimeType, string $extension): bool
    {
        $validCombinations = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
            'image/gif' => ['gif'],
        ];

        if (!isset($validCombinations[$mimeType])) {
            return false;
        }

        return in_array($extension, $validCombinations[$mimeType], true);
    }

    /**
     * Check if file is executable
     */
    protected function isExecutable(UploadedFile $file): bool
    {
        $dangerousExtensions = [
            'php', 'phtml', 'php3', 'php4', 'php5', 'php7',
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr',
            'sh', 'bash', 'zsh',
            'js', 'vbs', 'jar',
        ];

        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, $dangerousExtensions, true)) {
            return true;
        }

        // Check file content for PHP tags
        $content = file_get_contents($file->getRealPath());
        if ($content && (
            stripos($content, '<?php') !== false ||
            stripos($content, '<?=') !== false ||
            stripos($content, '<script') !== false
        )) {
            return true;
        }

        return false;
    }
}
