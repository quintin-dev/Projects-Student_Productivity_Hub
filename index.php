<?php
/**
 * Student Productivity Hub - Main Entry Point
 * 
 * Progressive Web App entry point with routing and PWA features
 * Environment: https://productivity_hub.local
 * 
 * @package ProductivityHub
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('UTC');

// Start session for CSRF protection and user state
session_start();

// Include autoloader and configuration
require_once __DIR__ . '/src/php/config/database.php';
require_once __DIR__ . '/src/php/core/Router.php';
require_once __DIR__ . '/src/php/core/AbstractController.php';
require_once __DIR__ . '/src/php/core/AbstractModel.php';
require_once __DIR__ . '/src/php/core/AbstractRepository.php';
require_once __DIR__ . '/src/php/core/Security.php';

// Include controllers
require_once __DIR__ . '/src/php/controllers/TaskController.php';
require_once __DIR__ . '/src/php/controllers/api/ApiController.php';
require_once __DIR__ . '/src/php/controllers/api/TaskApiController.php';

// Include models
require_once __DIR__ . '/src/php/models/TaskModel.php';
require_once __DIR__ . '/src/php/models/CategoryModel.php';

// Include repositories
require_once __DIR__ . '/src/php/repositories/TaskRepository.php';

use ProductivityHub\Config\Database;
use ProductivityHub\Core\Router;
use ProductivityHub\Controllers\TaskController;
use ProductivityHub\Controllers\Api\TaskApiController;

// Test database connection
try {
    $db = Database::getInstance();
    $connectionStatus = $db->testConnection() ? 'Connected' : 'Failed';
} catch (Exception $e) {
    $connectionStatus = 'Error: ' . $e->getMessage();
}

// Get request URI and method
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Initialize router
$router = new Router();

// Define routes
$router->get('/', [TaskController::class, 'index']);
$router->get('/tasks', [TaskController::class, 'index']);
$router->get('/tasks/create', [TaskController::class, 'create']);
$router->post('/tasks', [TaskController::class, 'store']);
$router->get('/tasks/{id}', [TaskController::class, 'show']);
$router->get('/tasks/{id}/edit', [TaskController::class, 'edit']);
$router->put('/tasks/{id}', [TaskController::class, 'update']);
$router->delete('/tasks/{id}', [TaskController::class, 'destroy']);

// API routes
$router->get('/api/tasks', [TaskApiController::class, 'index']);
$router->get('/api/tasks/{id}', [TaskApiController::class, 'show']);
$router->post('/api/tasks', [TaskApiController::class, 'store']);
$router->put('/api/tasks/{id}', [TaskApiController::class, 'update']);
$router->delete('/api/tasks/{id}', [TaskApiController::class, 'destroy']);

// Try to dispatch route or capture any exceptions
try {
    // Dispatch to the appropriate controller
    $routeResult = $router->dispatch($requestMethod, $requestUri);
    
    // If route handler returned a string, output it
    if (is_string($routeResult)) {
        echo $routeResult;
        exit;
    }
    
    // If we reach here, the route was found but no content was returned
    // Continue to the default frontend view
    $route = $requestUri === '/' ? 'home' : trim($requestUri, '/');
    
} catch (\Exception $e) {
    // Route not found or other error
    if ($e->getCode() === 404) {
        // If API request, return JSON error
        if (str_starts_with($requestUri, '/api/')) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Endpoint not found',
                'code' => 404
            ]);
            exit;
        }
        
        // Otherwise continue to frontend view
        $route = $requestUri === '/' ? 'home' : trim($requestUri, '/');
    } else {
        // Server error
        http_response_code($e->getCode() ?: 500);
        echo 'Error: ' . $e->getMessage();
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student Productivity Hub - A local-first PWA for task management, study sessions, and habit tracking">
    <meta name="theme-color" content="#3498db">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ProductivityHub">
    
    <title>Student Productivity Hub</title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="/src/css/main.css" as="style">
    <link rel="preload" href="/src/js/app.js" as="script">
    
    <!-- Progressive Web App manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Favicon and app icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/src/assets/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/src/assets/icons/favicon-16x16.png">
    <link rel="apple-touch-icon" href="/src/assets/icons/apple-touch-icon.png">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/src/css/main.css">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- App header -->
    <header class="app-header" role="banner">
        <div class="container">
            <div class="header__content">
                <div class="header__brand">
                    <img src="/src/assets/icons/icon-72x72.png" alt="" class="header__logo" width="32" height="32">
                    <h1 class="header__title">Productivity Hub</h1>
                </div>
                
                <nav class="header__nav" role="navigation" aria-label="Main navigation">
                    <ul class="nav-list">
                        <li><a href="#tasks" class="nav-link" data-section="tasks">Tasks</a></li>
                        <li><a href="#timer" class="nav-link" data-section="timer">Timer</a></li>
                        <li><a href="#habits" class="nav-link" data-section="habits">Habits</a></li>
                        <li><a href="#analytics" class="nav-link" data-section="analytics">Analytics</a></li>
                    </ul>
                </nav>
                
                <button class="header__menu-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                    <span class="hamburger"></span>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Main content area -->
    <main id="main-content" class="main-content" role="main">
        <div class="container">
            <!-- Welcome section for first-time users -->
            <section class="welcome-section" id="welcome">
                <div class="welcome__content">
                    <h2 class="welcome__title">Welcome to Your Productivity Hub</h2>
                    <p class="welcome__description">
                        Manage tasks, track study sessions, build habits, and analyze your productivity - all in one place, working completely offline.
                    </p>
                    
                    <div class="welcome__stats">
                        <div class="stat-card">
                            <div class="stat-card__value" id="task-count">0</div>
                            <div class="stat-card__label">Active Tasks</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card__value" id="study-time">0m</div>
                            <div class="stat-card__label">Study Time Today</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card__value" id="habit-streak">0</div>
                            <div class="stat-card__label">Current Streak</div>
                        </div>
                    </div>
                    
                    <div class="welcome__actions">
                        <button class="btn btn--primary" data-action="create-task">
                            <span class="btn__icon">+</span>
                            Create First Task
                        </button>
                        <button class="btn btn--secondary" data-action="start-timer">
                            <span class="btn__icon">▶</span>
                            Start Study Session
                        </button>
                    </div>
                </div>
            </section>
            
            <!-- Quick actions section -->
            <section class="quick-actions" id="quick-actions">
                <h3 class="section-title">Quick Actions</h3>
                <div class="action-grid">
                    <button class="action-card" data-action="new-task">
                        <div class="action-card__icon">📝</div>
                        <div class="action-card__title">New Task</div>
                        <div class="action-card__description">Add a task to your list</div>
                    </button>
                    
                    <button class="action-card" data-action="pomodoro">
                        <div class="action-card__icon">🍅</div>
                        <div class="action-card__title">Pomodoro Timer</div>
                        <div class="action-card__description">Start a 25-minute focus session</div>
                    </button>
                    
                    <button class="action-card" data-action="view-calendar">
                        <div class="action-card__icon">📅</div>
                        <div class="action-card__title">Calendar View</div>
                        <div class="action-card__description">See your tasks by date</div>
                    </button>
                    
                    <button class="action-card" data-action="track-habit">
                        <div class="action-card__icon">✅</div>
                        <div class="action-card__title">Track Habit</div>
                        <div class="action-card__description">Mark today's habits complete</div>
                    </button>
                </div>
            </section>
            
            <!-- Dynamic content area -->
            <section class="content-area" id="content-area">
                <!-- Content will be loaded dynamically via JavaScript -->
            </section>
        </div>
    </main>
    
    <!-- App footer -->
    <footer class="app-footer" role="contentinfo">
        <div class="container">
            <div class="footer__content">
                <div class="footer__info">
                    <p class="footer__text">Student Productivity Hub v1.0</p>
                    <p class="footer__status">
                        Database: <span id="db-status" class="status-indicator"><?= htmlspecialchars($connectionStatus) ?></span>
                    </p>
                </div>
                
                <div class="footer__actions">
                    <button class="btn btn--small" id="install-app" style="display: none;">Install App</button>
                    <button class="btn btn--small" id="export-data">Export Data</button>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Loading spinner -->
    <div class="loading-spinner" id="loading-spinner" aria-hidden="true">
        <div class="spinner"></div>
        <p class="loading-text">Loading...</p>
    </div>
    
    <!-- Offline indicator -->
    <div class="offline-indicator" id="offline-indicator" aria-hidden="true">
        <span class="offline-indicator__icon">📶</span>
        <span class="offline-indicator__text">You're offline. Changes will sync when connected.</span>
    </div>
    
    <!-- PWA install prompt -->
    <div class="install-prompt" id="install-prompt" aria-hidden="true">
        <div class="install-prompt__content">
            <h3 class="install-prompt__title">Install Productivity Hub</h3>
            <p class="install-prompt__description">Install this app for a better experience and offline access.</p>
            <div class="install-prompt__actions">
                <button class="btn btn--primary" id="install-confirm">Install</button>
                <button class="btn btn--secondary" id="install-dismiss">Not now</button>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        // Remove no-js class and add js class for progressive enhancement
        document.documentElement.classList.remove('no-js');
        document.documentElement.classList.add('js');
        
        // Initialize app configuration
        window.ProductivityHub = {
            version: '1.0.0',
            baseUrl: '<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']) ?>',
            dbStatus: '<?= htmlspecialchars($connectionStatus) ?>',
            route: '<?= htmlspecialchars($route) ?>'
        };
    </script>
    
    <!-- Main application script -->
    <script src="/src/js/app.js" defer></script>
    
    <!-- Service Worker registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then((registration) => {
                        console.log('SW registered: ', registration);
                    })
                    .catch((registrationError) => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>
</body>
</html>
