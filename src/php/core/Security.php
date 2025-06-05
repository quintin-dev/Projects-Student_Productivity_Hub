<?php
/**
 * Security Class
 * 
 * Provides security-related functionality like CSRF protection,
 * input sanitization, and other security measures
 * 
 * @package ProductivityHub\Core
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Core;

class Security
{
    /**
     * Generate a CSRF token
     * 
     * @return string CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate a CSRF token
     * 
     * @param string $token Token to validate
     * @return bool True if token is valid
     */
    public static function validateCsrfToken(string $token): bool
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitize input data
     * 
     * @param mixed $input Input data to sanitize
     * @return mixed Sanitized data
     */
    public static function sanitize($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::sanitize($value);
            }
            return $input;
        }
        
        if (is_string($input)) {
            return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $input;
    }
    
    /**
     * Escape HTML output
     * 
     * @param string $output Output to escape
     * @return string Escaped output
     */
    public static function escape(string $output): string
    {
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Generate a random token
     * 
     * @param int $length Token length
     * @return string Random token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Hash a password
     * 
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify a password
     * 
     * @param string $password Password to verify
     * @param string $hash Password hash
     * @return bool True if password is valid
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password needs rehash
     * 
     * @param string $hash Password hash
     * @return bool True if password needs rehash
     */
    public static function passwordNeedsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
    
    /**
     * Set secure headers
     * 
     * @return void
     */
    public static function setSecureHeaders(): void
    {
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'");
        
        // X-Content-Type-Options
        header("X-Content-Type-Options: nosniff");
        
        // X-Frame-Options
        header("X-Frame-Options: SAMEORIGIN");
        
        // X-XSS-Protection
        header("X-XSS-Protection: 1; mode=block");
        
        // Referrer-Policy
        header("Referrer-Policy: strict-origin-when-cross-origin");
        
        // Feature-Policy
        header("Feature-Policy: geolocation 'self'; microphone 'none'; camera 'none'");
        
        // Strict-Transport-Security
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}
