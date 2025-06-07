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
    
    <title><?= $title ?? 'Student Productivity Hub' ?></title>
    
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
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= htmlspecialchars(\ProductivityHub\Core\Security::generateCsrfToken()) ?>">
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
                        <li><a href="/tasks" class="nav-link" data-section="tasks">Tasks</a></li>
                        <li><a href="/timer" class="nav-link" data-section="timer">Timer</a></li>
                        <li><a href="/habits" class="nav-link" data-section="habits">Habits</a></li>
                        <li><a href="/analytics" class="nav-link" data-section="analytics">Analytics</a></li>
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
            <?= $content ?? '' ?>
        </div>
    </main>
    
    <!-- App footer -->
    <footer class="app-footer" role="contentinfo">
        <div class="container">
            <div class="footer__content">
                <div class="footer__info">
                    <p class="footer__text">Student Productivity Hub v1.0</p>
                    <p class="footer__status">
                        Database: <span id="db-status" class="status-indicator"><?= htmlspecialchars($connectionStatus ?? 'Unknown') ?></span>
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
            dbStatus: '<?= htmlspecialchars($connectionStatus ?? 'Unknown') ?>',
            route: '<?= htmlspecialchars($route ?? 'home') ?>',
            csrfToken: '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>'
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
