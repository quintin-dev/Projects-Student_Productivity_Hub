/**
 * Student Productivity Hub - Main Application
 *
 * Progressive Web App main application script
 * Handles initialization, routing, and core functionality
 *
 * @version 1.0.0
 * @author Edwardking (Edd)
 */

(function () {
    'use strict';

    /**
     * App configuration and state
     */
    const App = {
        version: '1.0.0',
        config: {
            baseUrl: window.ProductivityHub?.baseUrl || window.location.origin,
            dbStatus: window.ProductivityHub?.dbStatus || 'Unknown',
            route: window.ProductivityHub?.route || 'home',
        },
        state: {
            isOnline: navigator.onLine,
            isInstalled: false,
            currentSection: 'welcome',
            tasks: [],
            studySessions: [],
            habits: [],
        },
        cache: new Map(),
        deferredPrompt: null,
    };

    /**
     * DOM elements cache
     */
    const Elements = {
        // Navigation
        navLinks: null,
        menuToggle: null,

        // Content areas
        welcomeSection: null,
        contentArea: null,

        // Stats
        taskCount: null,
        studyTime: null,
        habitStreak: null,
        dbStatus: null,

        // PWA elements
        installButton: null,
        installPrompt: null,
        offlineIndicator: null,
        loadingSpinner: null,
    };

    /**
     * Initialize the application
     */
    function init() {
        console.log('🚀 Initializing Student Productivity Hub v' + App.version);

        // Cache DOM elements
        cacheElements();

        // Set up event listeners
        setupEventListeners();

        // Initialize PWA features
        initPWA();

        // Load initial data
        loadInitialData();

        // Update UI state
        updateConnectionStatus();
        updateStats();

        console.log('✅ App initialized successfully');
    }

    /**
     * Cache frequently used DOM elements
     */
    function cacheElements() {
        Elements.navLinks = document.querySelectorAll('.nav-link');
        Elements.menuToggle = document.querySelector('.header__menu-toggle');
        Elements.welcomeSection = document.querySelector('#welcome');
        Elements.contentArea = document.querySelector('#content-area');
        Elements.taskCount = document.querySelector('#task-count');
        Elements.studyTime = document.querySelector('#study-time');
        Elements.habitStreak = document.querySelector('#habit-streak');
        Elements.dbStatus = document.querySelector('#db-status');
        Elements.installButton = document.querySelector('#install-app');
        Elements.installPrompt = document.querySelector('#install-prompt');
        Elements.offlineIndicator =
            document.querySelector('#offline-indicator');
        Elements.loadingSpinner = document.querySelector('#loading-spinner');
    }

    /**
     * Set up all event listeners
     */
    function setupEventListeners() {
        // Navigation
        if (Elements.navLinks) {
            Elements.navLinks.forEach((link) => {
                link.addEventListener('click', handleNavigation);
            });
        }

        // Mobile menu toggle
        if (Elements.menuToggle) {
            Elements.menuToggle.addEventListener('click', toggleMobileMenu);
        }

        // Quick action buttons
        document.addEventListener('click', handleQuickActions);

        // PWA install events
        if (Elements.installButton) {
            Elements.installButton.addEventListener(
                'click',
                handleInstallClick
            );
        }

        // Connection status
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        // Keyboard navigation
        document.addEventListener('keydown', handleKeyboardNavigation);

        // PWA install prompt
        window.addEventListener('beforeinstallprompt', handleInstallPrompt);

        // Service worker updates
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                console.log('Service worker updated, reloading...');
                window.location.reload();
            });
        }
    }

    /**
     * Initialize PWA features
     */
    function initPWA() {
        // Check if app is installed
        if (
            window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true
        ) {
            App.state.isInstalled = true;
            if (Elements.installButton) {
                Elements.installButton.style.display = 'none';
            }
        }

        // Register for background sync if available
        if (
            'serviceWorker' in navigator &&
            'sync' in window.ServiceWorkerRegistration.prototype
        ) {
            navigator.serviceWorker.ready.then((registration) => {
                console.log('Background sync available');
                // Background sync will be implemented in Phase 2
            });
        }

        // Request notification permission if needed
        if ('Notification' in window && Notification.permission === 'default') {
            // Will be implemented in Phase 4
        }
    }

    /**
     * Load initial data from API
     */
    async function loadInitialData() {
        showLoading(true);

        try {
            // Test API connection
            const response = await fetch('/api/status');
            const data = await response.json();

            console.log('API Status:', data);

            // Load basic data (will be expanded in Phase 2)
            await Promise.all([loadTasks(), loadStudySessions(), loadHabits()]);
        } catch (error) {
            console.error('Failed to load initial data:', error);
            showOfflineMessage();
        } finally {
            showLoading(false);
        }
    }

    /**
     * Handle navigation between sections
     */
    function handleNavigation(event) {
        event.preventDefault();

        const section = event.target.dataset.section;
        if (section) {
            navigateToSection(section);
        }
    }

    /**
     * Navigate to a specific section
     */
    function navigateToSection(section) {
        // Update active nav link
        Elements.navLinks.forEach((link) => {
            link.classList.remove('active');
            if (link.dataset.section === section) {
                link.classList.add('active');
            }
        });

        // Update app state
        App.state.currentSection = section;

        // Load section content
        loadSectionContent(section);

        // Update URL without page reload
        history.pushState({ section }, '', `#${section}`);
    }

    /**
     * Load content for a specific section
     */
    async function loadSectionContent(section) {
        if (!Elements.contentArea) return;

        showLoading(true);

        try {
            let content = '';

            switch (section) {
                case 'tasks':
                    content = await renderTasksSection();
                    break;
                case 'timer':
                    content = await renderTimerSection();
                    break;
                case 'habits':
                    content = await renderHabitsSection();
                    break;
                case 'analytics':
                    content = await renderAnalyticsSection();
                    break;
                default:
                    content = '<h2>Section not found</h2>';
            }

            Elements.contentArea.innerHTML = content;
        } catch (error) {
            console.error('Failed to load section content:', error);
            Elements.contentArea.innerHTML =
                '<h2>Error loading content</h2><p>Please try again later.</p>';
        } finally {
            showLoading(false);
        }
    }

    /**
     * Handle quick action buttons
     */
    function handleQuickActions(event) {
        const action = event.target.closest('[data-action]')?.dataset.action;

        if (!action) return;

        switch (action) {
            case 'create-task':
            case 'new-task':
                handleNewTask();
                break;
            case 'start-timer':
            case 'pomodoro':
                handleStartTimer();
                break;
            case 'view-calendar':
                navigateToSection('calendar');
                break;
            case 'track-habit':
                navigateToSection('habits');
                break;
            default:
                console.log('Unknown action:', action);
        }
    }

    /**
     * Placeholder functions for Phase 2 implementation
     */
    async function loadTasks() {
        // Will be implemented in Phase 2
        App.state.tasks = [];
        return Promise.resolve();
    }

    async function loadStudySessions() {
        // Will be implemented in Phase 2
        App.state.studySessions = [];
        return Promise.resolve();
    }

    async function loadHabits() {
        // Will be implemented in Phase 2
        App.state.habits = [];
        return Promise.resolve();
    }

    async function renderTasksSection() {
        return `
            <section class="tasks-section">
                <h2>Task Management</h2>
                <p>Task management interface will be implemented in Phase 2.</p>
                <button class="btn btn--primary" data-action="new-task">Create New Task</button>
            </section>
        `;
    }

    async function renderTimerSection() {
        return `
            <section class="timer-section">
                <h2>Study Timer</h2>
                <p>Pomodoro timer interface will be implemented in Phase 3.</p>
                <button class="btn btn--primary" data-action="start-pomodoro">Start 25min Session</button>
            </section>
        `;
    }

    async function renderHabitsSection() {
        return `
            <section class="habits-section">
                <h2>Habit Tracking</h2>
                <p>Habit tracking interface will be implemented in Phase 3.</p>
                <button class="btn btn--primary" data-action="new-habit">Add New Habit</button>
            </section>
        `;
    }

    async function renderAnalyticsSection() {
        return `
            <section class="analytics-section">
                <h2>Analytics Dashboard</h2>
                <p>Analytics and insights will be implemented in Phase 4.</p>
                <div class="analytics-placeholder">
                    <p>📊 Productivity insights coming soon...</p>
                </div>
            </section>
        `;
    }

    function handleNewTask() {
        console.log('New task action - will be implemented in Phase 2');
        alert('Task creation will be available in Phase 2!');
    }

    function handleStartTimer() {
        console.log('Start timer action - will be implemented in Phase 3');
        alert('Study timer will be available in Phase 3!');
    }

    /**
     * Mobile menu toggle
     */
    function toggleMobileMenu() {
        const nav = document.querySelector('.header__nav');
        const isExpanded =
            Elements.menuToggle.getAttribute('aria-expanded') === 'true';

        Elements.menuToggle.setAttribute('aria-expanded', !isExpanded);

        if (nav) {
            nav.classList.toggle('nav--open');
        }
    }

    /**
     * Connection status handlers
     */
    function handleOnline() {
        App.state.isOnline = true;
        updateConnectionStatus();
        console.log('🌐 Back online');
    }

    function handleOffline() {
        App.state.isOnline = false;
        updateConnectionStatus();
        console.log('📱 Working offline');
    }

    function updateConnectionStatus() {
        if (Elements.offlineIndicator) {
            Elements.offlineIndicator.style.display = App.state.isOnline
                ? 'none'
                : 'block';
        }
    }

    /**
     * PWA installation handlers
     */
    function handleInstallPrompt(event) {
        event.preventDefault();
        App.deferredPrompt = event;

        if (Elements.installButton) {
            Elements.installButton.style.display = 'block';
        }
    }

    function handleInstallClick() {
        if (App.deferredPrompt) {
            App.deferredPrompt.prompt();

            App.deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                } else {
                    console.log('User dismissed the install prompt');
                }
                App.deferredPrompt = null;
            });
        }
    }

    /**
     * Keyboard navigation for accessibility
     */
    function handleKeyboardNavigation(event) {
        // Escape key to close modals/menus
        if (event.key === 'Escape') {
            const nav = document.querySelector('.header__nav');
            if (nav && nav.classList.contains('nav--open')) {
                toggleMobileMenu();
            }
        }
    }

    /**
     * UI utility functions
     */
    function showLoading(show) {
        if (Elements.loadingSpinner) {
            Elements.loadingSpinner.style.display = show ? 'flex' : 'none';
            Elements.loadingSpinner.setAttribute('aria-hidden', !show);
        }
    }

    function showOfflineMessage() {
        console.log('📱 App working in offline mode');
        // Could show a more prominent offline indicator
    }

    function updateStats() {
        if (Elements.taskCount) {
            Elements.taskCount.textContent = App.state.tasks.length;
        }

        if (Elements.studyTime) {
            Elements.studyTime.textContent = '0m'; // Will be calculated in Phase 2
        }

        if (Elements.habitStreak) {
            Elements.habitStreak.textContent = '0'; // Will be calculated in Phase 2
        }

        if (Elements.dbStatus) {
            Elements.dbStatus.textContent = App.config.dbStatus;
            Elements.dbStatus.className = `status-indicator ${
                App.config.dbStatus.includes('Connected')
                    ? 'status--success'
                    : 'status--error'
            }`;
        }
    }

    /**
     * Export functions for global access
     */
    window.ProductivityHubApp = {
        init,
        navigateToSection,
        state: App.state,
        config: App.config,
    };

    // Initialize app when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
