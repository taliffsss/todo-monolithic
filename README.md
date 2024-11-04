
# Laravel + Vue TODO Application Documentation

## Overview
A full-stack task management application built with Laravel 11 and Vue 3, featuring task management with tags, file attachments, filtering, and sorting capabilities.

## Tech Stack
- **Backend**: Laravel 11
- **Frontend**: Vue 3 with Composition API
- **Authentication**: Laravel Sanctum
- **State Management**: Vuex 4
- **Routing**: Vue Router 4
- **UI**: Tailwind CSS
- **Components**: Headless UI
- **Icons**: Heroicons

## Features
- User Authentication (Register, Login, Logout)
- Task Management (CRUD operations)
- Task Prioritization (Urgent, High, Normal, Low)
- Task Status (Complete/Incomplete)
- Task Archiving
- Tag Management
- File Attachments
- Filtering & Sorting
- Real-time Form Validation
- Responsive Design

## Installation

### Prerequisites
- PHP 8.2+
- Node.js 16+
- MySQL 8.0+
- Composer

### Backend Setup
1. Clone the repository
    ```bash
    git clone https://github.com/taliffsss/todo-monolithic.git
    cd todo-monolithic
    ```
2. Install PHP dependencies
    ```bash
    composer install
    ```
3. Copy environment file
    ```bash
    cp env.sample .env
    ```
4. Configure your `.env` file with database credentials
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```
5. Generate application key
    ```bash
    php artisan key:generate
    ```
6. Run migrations
    ```bash
    php artisan migrate
    ```
7. Create storage link for file uploads
    ```bash
    php artisan storage:link
    ```

### Frontend Setup
1. Install Node dependencies
    ```bash
    npm install
    ```
2. Compile assets
    ```bash
    npm run dev
    ```

## Project Structure

### Laravel Backend

    app/
    ├── Http/
    │   ├── Controllers/
    │   │   └── Api/
    │   │       ├── AuthController.php
    │   │       ├── TaskController.php
    │   │       ├── TaskAttachmentController.php
    │   │       └── TagController.php
    │   ├── Middleware/
    │       └── PreventGuestActions.php
    │   ├── Requests/
    │   │   ├── Auth/
    │   │       ├── LoginRequest.php
    │   │       └── RegisterRequest.php
    │   │   ├── Tag/
    │   │       ├── TagStoreRequest.php
    │   │       └── TagUpdateRequest.php
    │   │   └── Task/
    │   │       ├── TagStoreRequest.php
    │   │       └── TagUpdateRequest.php
    │   └── Resources/
    │       ├── AttachmentResource.php
    │       ├── TagResource.php
    │       ├── TaskResource.php
    │       └── UserResource.php
    ├── Jobs/
    │   ├── DeleteOldArchivedTasks.php
    ├── Models/
    │   ├── Attachment.php
    │   ├── Tag.php
    │   ├── Task.php
    │   ├── TaskTag.php
    │   └── User.php
    ├── Observers/
    │   └── TaskObserver.php
    ├── Policies/
    │   └── TaskPolicy.php
    ├── Providers/
    │   ├── AppServiceProvider.php
    │   ├── AuthServiceProvider.php
    │   └── RouteServiceProvider.php
    └── Services/
        ├── AuthService.php
        ├── TaskService.php
        └── TagService.php

 Vue Front-End

    resources/js/
    ├── components/
    │   ├── shared/
    │   │   ├── FileUpload.vue
    │   │   ├── LoadingSpinner.vue
    │   │   ├── Modal.vue
    │   │   ├── Notification.vue
    │   │   └── TagInput.vue
    │   └── tasks/
    │       ├── CreateTaskModal.vue
    │       ├── TaskDetails.vue
    │       ├── TaskFilters.vue
    │       ├── TaskFormModal.vue
    │       └── TaskItem.vue
    ├── views/
    │   ├── auth/
    │   │   ├── Login.vue
    │   │   └── Register.vue
    │   └── tasks/
    │       └── TaskList.vue
    ├── store/
    │   ├── index.js
    │   └── modules/
    │       ├── auth.js
    │       ├── tasks.js
    │       └── tags.js
    └── router/
        └── index.js

## API Endpoints

### Authentication

-   `POST /api/v1/register` - Register new user
-   `POST /api/v1/login` - Login user
-   `POST /api/v1/guest` - Login as guest
-   `POST /api/v1/logout` - Logout user
-   `GET /api/v1/user` - Get authenticated user

### Tasks

-   `GET /api/v1/tasks` - List all tasks
-   `POST /api/v1/tasks` - Create new task
-   `GET /api/v1/tasks/{id}` - Get single task
-   `PUT /api/v1/tasks/{id}` - Update task
-   `DELETE /api/v1/tasks/{id}` - Delete task
-   `PATCH /api/v1/tasks/complete/{id}` - Mark task as complete
-   `PATCH /api/v1/tasks/incomplete/{id}` - Mark task as incomplete
-   `PATCH /api/v1/tasks/archive/{id}` - Archive task
-   `PATCH /api/v1/tasks/restore/{id}` - Restore archived task

### Tags

-   `GET /api/v1/tags` - List all tags

### Attachments

-   `GET /api/v1/tasks/attachments/{id}` - Download attachment

## Usage

### Task Management

-   **Create Task**:
    
    -   Click "New Task" button
    -   Fill in task details
    -   Add tags (optional)
    -   Upload attachments (optional)
    -   Click "Create"
-   **Update Task**:
    
    -   Click on task to view details
    -   Click "Edit" button
    -   Modify task details
    -   Click "Update"
-   **Delete Task**:
    
    -   Click on task
    -   Click "Delete" button
    -   Confirm deletion

### Task Filtering

Tasks can be filtered by:

-   Search query
-   Priority level
-   Completion status
-   Archive status
-   Due date range
-   Tags

### File Attachments

Supported file types:

-   Images: jpg, jpeg, png, svg
-   Documents: pdf, doc, docx, txt
-   Maximum file size: 10MB

### Security
-   All API endpoints (except login/register) require authentication
-   File uploads are validated and sanitized
-   CSRF protection enabled
-   XSS protection implemented
-   Input validation on all forms
-   Secure file storage configuration

    