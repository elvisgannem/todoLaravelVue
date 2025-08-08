# Todo Laravel Vue

A modern todo application built with Laravel, Vue.js, and Inertia.js, featuring a UI with Tailwind CSS and state management with Pinia.

## Tech Stack

- **Backend**: Laravel 12.x, PHP 8.4
- **Frontend**: Vue 3, TypeScript, Inertia.js
- **Styling**: Tailwind CSS, shadcn/ui components
- **State Management**: Pinia
- **Database**: PostgreSQL
- **Development**: Laravel Sail (Docker)

## Getting Started

Follow these steps to get the project up and running on your local machine:

### Prerequisites

- Docker and Docker Compose
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd todoLaravelVue
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Set up environment file**
   ```bash
   cp .env.example .env
   ```

5. **Start the application with Laravel Sail**
   ```bash
   ./vendor/bin/sail up -d
   ```
   This will start the Docker containers in detached mode:
   - Laravel application on `http://localhost`
   - PostgreSQL database
   - Vite development server

6. **Generate application key**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

7. **Run database migrations**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

8. **Run database seeders**
   ```bash
   ./vendor/bin/sail artisan db:seed
   ```
   This will create a default admin user with the following credentials:
   - **Email**: `admin@example.com`
   - **Password**: `password`

9. **Start the frontend development server**
   ```bash
   npm run dev
   ```

### Accessing the Application

- **Frontend**: http://localhost
- **Login**: Use the seeded admin credentials above
- **Database**: PostgreSQL on `localhost:5432`

## API Documentation

This project includes comprehensive API documentation powered by Swagger/OpenAPI 3.0.

### Accessing Swagger Documentation

Once your application is running, you can access the interactive API documentation at:

**🔗 http://localhost/api/documentation**

### Quick Access

You can also access the API documentation directly from within the application:
- Look for the "API Documentation" link in the sidebar navigation
- Or click the documentation icon in the header menu
