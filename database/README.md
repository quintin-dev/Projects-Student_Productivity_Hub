# Student Productivity Hub - Database Setup Instructions

## Database Setup (Manual Execution Required)

Since the MCP database server is in read-only mode, please execute the database schema manually:

### 1. Connect to MySQL

```bash
mysql -u root -p
# Enter password: MySql1234
```

### 2. Execute the Schema

Run the SQL file we created:

```bash
mysql -u root -p < database/schema/001_initial_schema.sql
```

Or copy and paste the SQL commands from `database/schema/001_initial_schema.sql` into your MySQL client.

### 3. Verify Database Creation

```sql
USE productivity_hub;
SHOW TABLES;
DESCRIBE tasks;
SELECT * FROM categories;
SELECT * FROM settings;
```

### 4. Test the Connection

After running the schema, visit `https://productivity_hub.local` to test the connection.

The database status should show "Connected" in the footer of the application.

## Database Schema Overview

### Core Tables Created:

-   **tasks** - Main task management with priorities, categories, due dates
-   **categories** - Task categorization with colors and icons
-   **study_sessions** - Pomodoro and study session tracking
-   **habits** - Habit tracking with streaks
-   **habit_logs** - Daily habit completion logs
-   **settings** - User preferences and app configuration
-   **audit_logs** - Change tracking for data integrity

### Default Data Inserted:

-   5 default categories (Personal, Study, Work, Health, Projects)
-   8 default settings (Pomodoro timers, themes, notifications)
-   3 sample habits for demonstration

### Performance Features:

-   Proper indexing on frequently queried columns
-   Foreign key constraints for data integrity
-   Soft deletes for task management
-   Optimized table structure for fast queries
