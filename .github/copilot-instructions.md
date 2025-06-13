# Student Productivity Hub - GitHub Copilot Instructions

## Project Overview
A local-first Progressive Web App (PWA) designed to help students manage tasks, schedules, and study habits. Built as a university project showcasing modern web development skills with offline-first capabilities and localhost deployment.

## Technology Stack & Preferences
- **Frontend**: Vanilla JavaScript (ES6+), HTML5, CSS3 (mobile-first responsive design)
- **Backend**: PHP 8+ with object-oriented patterns and PSR-12 compliance
- **Database**: MySQL 8+ with prepared statements, proper indexing, and optimization
- **PWA Core**: Service Workers, Web App Manifest, Cache API, Background Sync
- **Development Environment**: XAMPP/Laragon with mkcert for HTTPS
- **Testing**: Playwright for E2E PWA testing, manual testing for core features
- **Architecture**: MVC pattern with clear separation of concerns

## Code Style Guidelines
- Use semantic HTML5 elements and WCAG 2.1 AA accessibility compliance
- Write clean, readable CSS with BEM methodology and CSS custom properties
- Use modern JavaScript (ES6+) with proper error handling and async/await
- PHP code follows PSR-12 coding standards with meaningful variable names
- Use double quotes for strings consistently across all languages
- Use 2-space indentation for HTML/CSS/JS, 4-space for PHP
- Include comprehensive documentation with JSDoc/PHPDoc comments
- Follow conventional commit messages for version control

## Architecture Patterns
- **PWA-First Design**: Offline functionality is core, not an afterthought
- **Local-First Data**: All data stored locally with MySQL as primary source
- **Progressive Enhancement**: Base functionality works without JS, enhanced with JS
- **Modular PHP**: Object-oriented classes with dependency injection
- **Separation of Concerns**: Clear MVC pattern with services layer
- **Cache Strategies**: Cache-first for static assets, network-first for dynamic data
- **Database Patterns**: Repository pattern with prepared statements and transactions

## PWA Development Guidelines
- **Service Worker**: Comprehensive caching with cache versioning and cleanup
- **Manifest**: Proper app metadata, multiple icon sizes, theme colors
- **Responsive Design**: Mobile-first with touch-friendly 44px minimum targets
- **Performance**: Lighthouse PWA score ≥ 90, Core Web Vitals optimization
- **Accessibility**: Full keyboard navigation, screen reader support, focus management
- **Installation**: Native install prompts with proper beforeinstallprompt handling
- **Offline Functionality**: Background sync for data operations, offline fallbacks

## Development Practices
- **Critical Rule**: ALWAYS update `docs/changes.md` with ALL changes, decisions, todos, and progress
- **Database Design**: Use 3NF normalization, proper indexing, audit trails, soft deletes
- **Security**: Input sanitization, prepared statements, CSRF protection, XSS prevention
- **Error Handling**: Graceful degradation, proper error logging, user-friendly messages
- **Performance**: Optimize for fast loading, lazy loading, efficient queries
- **Testing Strategy**: PWA feature testing (offline, install, caching) with Playwright

## Local Development & Deployment
- **HTTPS Required**: Use mkcert for local SSL certificates (PWA requirement)
- **Development URL**: https://productivity_hub.local (virtual host configured)
- **Web Server**: Apache with virtual host configuration
- **Database Setup**: MySQL with proper user privileges, connection pooling, and monitoring
- **File Structure**: Follow MVC pattern with clear directory organization
- **Environment Config**: Use PHP configuration files with environment variables
- **PWA Testing**: Regular Lighthouse audits and cross-browser compatibility
- **Performance Monitoring**: Track Core Web Vitals and PWA metrics

## Student Productivity Features
- **Task Management**: CRUD operations with priorities, due dates, categories, and subtasks
- **Calendar Integration**: Visual task distribution, deadline management, and time blocking
- **Study Sessions**: Pomodoro timers, session tracking, break reminders, and analytics
- **Streak Tracking**: Habit formation through visual progress, achievements, and milestones
- **Analytics Dashboard**: Progress visualization, productivity insights, and goal tracking
- **Offline Sync**: Queue operations when offline, sync when online with conflict resolution

## Data Management
- **Local Storage**: User preferences, app state, and temporary data
- **MySQL Database**: Primary data store with ACID compliance and backup strategies
- **Caching Strategy**: Service worker handles asset caching, IndexedDB for offline data
- **Data Validation**: Client-side and server-side validation with sanitization
- **Export/Import**: Data portability in JSON format with privacy considerations
- **Performance**: Query optimization, proper indexing, and connection pooling

## MCP Server Integration Guidelines

### Project-Specific Servers
- **`chroma`**: Store and retrieve project patterns, templates, and documentation snippets
- **`dbhub-mysql-productivity-hub`**: Database schema management, queries, and migrations
- **`memory-bank`**: Persist project decisions, architecture notes, and lessons learned
- **`playwright`**: Comprehensive PWA testing scenarios and automated regression tests
- **`tree-sitter`**: Code analysis, refactoring assistance across PHP and JavaScript files
- **`context7`**: Library documentation lookup for dependencies and best practices
- **`magic-ui`**: Generate UI components following project design system
- **`filesystem`**: File operations and project structure management

### Global Servers Available
- **`brave-search`**: Web search capabilities for research and documentation lookup
- **`github`**: Repository operations, issue tracking, pull requests management
- **`gitmcp`**: Advanced git operations and version control workflows
- **`notion`**: Documentation and knowledge management integration

## Performance & Optimization
- **Critical Resource Hints**: Preload essential assets, fonts, and critical path resources
- **Code Splitting**: Separate core functionality from progressive enhancements
- **Image Optimization**: WebP format, responsive images, lazy loading
- **Database Queries**: Proper indexing, query analysis, connection optimization
- **Service Worker**: Efficient cache management, update strategies, background sync
- **Bundle Optimization**: Minimize JavaScript, remove unused code, compress assets

## Security & Privacy
- **Input Validation**: Server-side validation, prepared statements, type checking
- **XSS Prevention**: Content Security Policy, output encoding, sanitization
- **CSRF Protection**: Token-based protection, same-site cookies
- **Data Privacy**: Local-first approach, no external data transmission
- **HTTPS Enforcement**: Required for PWA features, secure cookie handling
- **Error Handling**: Safe error messages, proper logging without sensitive data

## Documentation Requirements
- **Critical Rule**: ALWAYS update `docs/changes.md` when making ANY changes, adding features, or making architectural decisions
- **Code Documentation**: Inline comments for complex PWA patterns and business logic
- **Database Documentation**: Schema changes, migration scripts, relationship diagrams
- **API Documentation**: PHP endpoints, request/response formats, error codes
- **Setup Documentation**: Local development environment, dependency installation
- **Testing Documentation**: PWA testing procedures, offline functionality validation

## File Organization Standards
```
productivity-hub/
├── docs/                 # Project documentation
│   ├── changes.md        # CRITICAL: Always update with ALL changes
│   ├── prd.md           # Product Requirements Document
│   ├── api.md           # API endpoint documentation
│   └── setup.md         # Development environment setup
├── src/                 # Source code organized by concern
│   ├── php/             # Backend PHP files (MVC pattern)
│   │   ├── controllers/ # Request handling and routing
│   │   ├── models/      # Data access and business logic
│   │   ├── services/    # Business logic and external integrations
│   │   └── config/      # Configuration and database connection
│   ├── js/              # Frontend JavaScript modules
│   │   ├── components/  # Reusable UI components
│   │   ├── services/    # API communication and business logic
│   │   └── utils/       # Helper functions and utilities
│   ├── css/             # Stylesheets organized by component
│   └── assets/          # Static assets (images, icons, fonts)
├── tests/               # Test files (Playwright, unit tests)
├── database/            # Database schemas and migrations
├── manifest.json        # PWA manifest
├── service-worker.js    # Service worker implementation
└── index.php           # Entry point with routing
```

## PWA-Specific Requirements
- **Installability**: Meet all PWA criteria for browser installation prompts
- **Offline Functionality**: Core features must work without internet connection
- **Performance**: Lighthouse PWA score ≥ 90, all Core Web Vitals in green
- **Security**: HTTPS required for all PWA features and service worker registration
- **Cross-Platform**: Test on multiple devices, browsers, and operating systems
- **Progressive Enhancement**: Ensure graceful degradation when advanced features unavailable

## Quality Assurance Standards
- **Code Review**: Follow checklist for security, performance, and maintainability
- **Testing Coverage**: Unit tests for business logic, E2E tests for user workflows
- **Performance Monitoring**: Regular Lighthouse audits, Core Web Vitals tracking
- **Accessibility Testing**: Screen reader testing, keyboard navigation validation
- **Browser Compatibility**: Test across Chrome, Firefox, Safari, Edge
- **Mobile Testing**: Test on actual devices, various screen sizes and orientations
