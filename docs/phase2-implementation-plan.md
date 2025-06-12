# Student Productivity Hub - Phase 2 Implementation Plan
**Date**: June 10, 2025
**Phase**: MVC Backend Implementation
**Status**: Planning

## Phase 2 Overview
Building on the foundation established in Phase 1, Phase 2 focuses on implementing a robust PHP MVC backend with task management CRUD operations, proper routing, and API endpoints for frontend consumption.

## Key Objectives
1. Implement complete MVC architecture
2. Develop task management backend with CRUD operations
3. Create API endpoints for frontend integration
4. Implement security features and input validation
5. Integrate with PWA offline functionality

## Implementation Timeline

### Week 1: MVC Core Framework (June 11-17)
1. **Day 1-2: Router Implementation**
   - Create Router class with route registration
   - Implement URL pattern matching
   - Support for HTTP methods (GET, POST, PUT, DELETE)

2. **Day 3-4: Base Controller & View System**
   - Create AbstractController base class
   - Implement view rendering system
   - Develop response formatting (HTML, JSON)

3. **Day 5-7: Model & Database Layer**
   - Create AbstractModel base class
   - Implement Repository pattern
   - Develop transaction support
   - Setup data validation framework

### Week 2: Task Management Implementation (June 18-24)
1. **Day 1-2: Task Model & Repository**
   - Create TaskModel with validation
   - Implement TaskRepository for database operations
   - Setup category relationships

2. **Day 3-4: Task Controller**
   - Create TaskController with CRUD actions
   - Implement listing, filtering, and sorting
   - Add pagination support

3. **Day 5-7: Category & Settings Backend**
   - Implement CategoryController and Model
   - Create SettingsController and Model
   - Setup default data handling

### Week 3: API & Security Implementation (June 25-July 1)
1. **Day 1-2: RESTful API Endpoints**
   - Create API route structure
   - Implement JSON response formatting
   - Setup API error handling

2. **Day 3-4: Security Features**
   - Implement input sanitization
   - Add CSRF protection
   - Setup request validation

3. **Day 5-7: PWA Integration**
   - Enhance service worker for API caching
   - Implement background sync for offline operations
   - Add conflict resolution for synced data

## Implementation Details

### 1. MVC Architecture

#### 1.1 Router Implementation
```php
namespace ProductivityHub\Core;

class Router {
    private array $routes = [];
    
    public function addRoute(string $method, string $pattern, array $handler): void 
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }
    
    public function dispatch(string $method, string $uri): mixed
    {
        // Match route and dispatch to controller
    }
}
```

#### 1.2 Base Controller
```php
namespace ProductivityHub\Core;

abstract class AbstractController {
    protected function render(string $view, array $data = []): string 
    {
        // Render view template with data
    }
    
    protected function json(array $data, int $statusCode = 200): void
    {
        // Output JSON response
    }
}
```

#### 1.3 Model System
```php
namespace ProductivityHub\Core;

abstract class AbstractModel {
    protected array $attributes = [];
    protected array $rules = [];
    
    public function validate(): bool
    {
        // Validate model data against rules
    }
    
    public function save(): bool
    {
        // Save model to database
    }
}
```

### 2. Task Management

#### 2.1 Task Model
```php
namespace ProductivityHub\Models;

use ProductivityHub\Core\AbstractModel;

class TaskModel extends AbstractModel {
    protected array $rules = [
        'title' => 'required|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'priority' => 'in:low,medium,high,urgent',
        'status' => 'in:pending,in_progress,completed,cancelled',
        'due_date' => 'nullable|date'
    ];
    
    // Task-specific methods
}
```

#### 2.2 Task Controller
```php
namespace ProductivityHub\Controllers;

use ProductivityHub\Core\AbstractController;
use ProductivityHub\Models\TaskModel;
use ProductivityHub\Repositories\TaskRepository;

class TaskController extends AbstractController {
    private TaskRepository $taskRepository;
    
    public function __construct() {
        $this->taskRepository = new TaskRepository();
    }
    
    public function index(): void
    {
        $tasks = $this->taskRepository->findAll();
        $this->render('tasks/index', ['tasks' => $tasks]);
    }
    
    // Other CRUD methods
}
```

#### 2.3 API Controllers
```php
namespace ProductivityHub\Controllers\Api;

use ProductivityHub\Core\AbstractController;
use ProductivityHub\Models\TaskModel;
use ProductivityHub\Repositories\TaskRepository;

class TaskApiController extends AbstractController {
    private TaskRepository $taskRepository;
    
    public function __construct() {
        $this->taskRepository = new TaskRepository();
    }
    
    public function index(): void
    {
        $tasks = $this->taskRepository->findAll();
        $this->json(['data' => $tasks]);
    }
    
    // Other API methods
}
```

### 3. Database Layer

#### 3.1 Repository Pattern
```php
namespace ProductivityHub\Repositories;

use ProductivityHub\Config\Database;

abstract class AbstractRepository {
    protected \PDO $db;
    protected string $table;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE is_deleted = 0");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    // Other common repository methods
}
```

#### 3.2 Task Repository
```php
namespace ProductivityHub\Repositories;

class TaskRepository extends AbstractRepository {
    protected string $table = 'tasks';
    
    public function findByCategory(int $categoryId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE category_id = :category_id AND is_deleted = 0");
        $stmt->bindParam(':category_id', $categoryId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    // Other task-specific repository methods
}
```

### 4. Security Implementation

#### 4.1 CSRF Protection
```php
namespace ProductivityHub\Core;

class Security {
    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    public static function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
```

#### 4.2 Input Sanitization
```php
namespace ProductivityHub\Core;

class Input {
    public static function sanitize($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::sanitize($value);
            }
            return $input;
        }
        
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}
```

## Testing Strategy

### 1. Unit Testing
- Test each component in isolation
- Focus on model validation and business logic
- Verify repository pattern functionality

### 2. Integration Testing
- Test the interaction between components
- Verify database operations
- Test routing and controller dispatch

### 3. API Testing
- Verify all API endpoints
- Test request/response formats
- Validate error handling

### 4. PWA Integration Testing
- Test offline functionality
- Verify background sync
- Test cache invalidation

## Deliverables

1. **Core MVC Framework**
   - Router, Controller, Model base classes
   - View rendering system
   - Repository pattern implementation

2. **Task Management Backend**
   - Complete CRUD for tasks, categories
   - Filtering, sorting, and pagination
   - Relationship handling

3. **API Layer**
   - RESTful endpoints for all resources
   - JSON response formatting
   - Error handling and status codes

4. **Security Features**
   - Input sanitization
   - CSRF protection
   - Data validation

5. **PWA Integration**
   - Enhanced service worker for APIs
   - Background sync implementation
   - Offline operation support

## Success Criteria

1. All unit and integration tests pass
2. API endpoints respond with correct data
3. Offline functionality works seamlessly
4. Database operations maintain data integrity
5. Security features prevent common vulnerabilities

## Risk Management

1. **Database Connection Issues**
   - Implement robust error handling
   - Add connection pooling for performance

2. **Security Vulnerabilities**
   - Follow OWASP guidelines
   - Implement proper input validation

3. **Performance Bottlenecks**
   - Optimize database queries
   - Implement proper indexing

4. **PWA Integration Complexity**
   - Develop incrementally
   - Test thoroughly in offline mode

## Next Steps After Phase 2

1. Implement frontend JavaScript components (Phase 3)
2. Develop study session and timer functionality
3. Create analytics dashboard
4. Enhance PWA features with push notifications

---

This implementation plan will be updated with progress and decisions throughout Phase 2 development.
