<?php
/**
 * API Controller
 * 
 * Base controller for API endpoints
 * 
 * @package ProductivityHub\Controllers\Api
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Controllers\Api;

use ProductivityHub\Core\AbstractController;
use ProductivityHub\Core\Security;

abstract class ApiController extends AbstractController
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setApiHeaders();
    }
    
    /**
     * Set API response headers
     * 
     * @return void
     */
    protected function setApiHeaders(): void
    {
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'] ?? '*');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }
    
    /**
     * Send API success response
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function success($data = null, string $message = 'Success', int $statusCode = 200): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    /**
     * Send API error response
     * 
     * @param string $message Error message
     * @param mixed $errors Validation errors
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function error(string $message, $errors = null, int $statusCode = 400): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        $this->json($response, $statusCode);
    }
    
    /**
     * Get JSON request data
     * 
     * @return array Request data
     */
    protected function getJsonData(): array
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? [];
        
        return Security::sanitize($data);
    }
    
    /**
     * Validate API request data
     * 
     * @param array $data Request data
     * @param array $rules Validation rules
     * @return bool True if validation passes
     */
    protected function validateRequest(array $data, array $rules): bool
    {
        $errors = $this->validate($data, $rules);
        
        if (!empty($errors)) {
            $this->error('Validation failed', $errors, 422);
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if CSRF token is valid
     * 
     * @param string $token CSRF token
     * @return bool True if token is valid
     */
    protected function validateCsrf(string $token): bool
    {
        if (!Security::validateCsrfToken($token)) {
            $this->error('Invalid CSRF token', null, 403);
            return false;
        }
        
        return true;
    }
}
