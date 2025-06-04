<?php
/**
 * Abstract Controller Class
 * 
 * Base controller class for all controllers in the application
 * Provides common functionality for rendering views and handling responses
 * 
 * @package ProductivityHub\Core
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Core;

abstract class AbstractController
{
    /**
     * Base path for views
     * 
     * @var string
     */
    protected string $viewPath = __DIR__ . '/../../views/';
    
    /**
     * Render a view template with data
     * 
     * @param string $view View template path
     * @param array $data Data to pass to the view
     * @return string Rendered view
     * @throws \Exception If view file not found
     */
    protected function render(string $view, array $data = []): string
    {
        $viewFile = $this->viewPath . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: $viewFile", 500);
        }
        
        // Extract data to make variables available in the view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        include $viewFile;
        
        // Get the contents of the buffer and clean it
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * Send a JSON response
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Redirect to another URL
     * 
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }
    
    /**
     * Get request method
     * 
     * @return string HTTP method
     */
    protected function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get request data based on method
     * 
     * @return array Request data
     */
    protected function getRequestData(): array
    {
        $method = $this->getMethod();
        
        switch ($method) {
            case 'GET':
                return $_GET;
            case 'POST':
                return $_POST;
            case 'PUT':
            case 'DELETE':
                $input = file_get_contents('php://input');
                parse_str($input, $data);
                return $data;
            default:
                return [];
        }
    }
    
    /**
     * Get a specific request parameter
     * 
     * @param string $key Parameter key
     * @param mixed $default Default value
     * @return mixed Parameter value or default
     */
    protected function getParam(string $key, $default = null)
    {
        $data = $this->getRequestData();
        return $data[$key] ?? $default;
    }
    
    /**
     * Validate request data against rules
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return array Array of validation errors
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $ruleArray = explode('|', $rule);
            
            foreach ($ruleArray as $singleRule) {
                // Check if rule has parameters
                if (strpos($singleRule, ':') !== false) {
                    [$ruleName, $ruleParam] = explode(':', $singleRule, 2);
                } else {
                    $ruleName = $singleRule;
                    $ruleParam = null;
                }
                
                // Apply rule
                switch ($ruleName) {
                    case 'required':
                        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                            $errors[$field][] = "$field is required";
                        }
                        break;
                    
                    case 'min':
                        if (isset($data[$field]) && strlen((string) $data[$field]) < (int) $ruleParam) {
                            $errors[$field][] = "$field must be at least $ruleParam characters";
                        }
                        break;
                        
                    case 'max':
                        if (isset($data[$field]) && strlen((string) $data[$field]) > (int) $ruleParam) {
                            $errors[$field][] = "$field must be less than $ruleParam characters";
                        }
                        break;
                        
                    case 'email':
                        if (isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "$field must be a valid email address";
                        }
                        break;
                        
                    case 'date':
                        if (isset($data[$field]) && !strtotime($data[$field])) {
                            $errors[$field][] = "$field must be a valid date";
                        }
                        break;
                        
                    case 'numeric':
                        if (isset($data[$field]) && !is_numeric($data[$field])) {
                            $errors[$field][] = "$field must be numeric";
                        }
                        break;
                        
                    case 'in':
                        $allowedValues = explode(',', $ruleParam);
                        if (isset($data[$field]) && !in_array($data[$field], $allowedValues)) {
                            $errors[$field][] = "$field must be one of: " . implode(', ', $allowedValues);
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
}
