# Social Network Platform

A modern social networking platform built with Laravel, designed to facilitate user connections, content sharing, and real-time interactions. This application provides a comprehensive social media experience with features like user profiles, posts, comments, likes, friend requests, and real-time notifications.

## Features

### Core Functionality
- **User Authentication**: Secure registration and login system with Laravel Sanctum for API token management
- **User Profiles**: Customizable profiles with avatar images and bio information
- **Posts Management**: Create, edit, delete, and view posts with optional image uploads
- **Comments System**: Add, edit, and delete comments on posts
- **Like System**: Like and unlike posts and comments
- **Friend Requests**: Send, accept, reject, and manage friend requests
- **Real-time Notifications**: Live updates using Laravel Broadcasting with Pusher
- **Responsive Design**: Mobile-friendly interface built with Tailwind CSS and Alpine.js

### Technical Features
- **RESTful API**: Complete API endpoints for all features
- **WebSocket Broadcasting**: Real-time events for friend requests, comments, and likes
- **File Upload**: Secure image storage with Laravel's storage system
- **Authorization Policies**: Granular permissions for user actions
- **Database Migrations**: Structured database schema with relationships
- **Testing Suite**: PHPUnit tests for critical functionality
- **API Documentation**: Auto-generated documentation with Laravel Scribe

## Tech Stack

### Backend
- **Laravel 12**: PHP framework for robust web application development
- **PHP 8.2+**: Server-side scripting language
- **MySQL/PostgreSQL**: Database management system
- **Laravel Sanctum**: API authentication for SPA and mobile apps
- **Pusher**: Real-time WebSocket communication
- **Composer**: PHP dependency management

### Frontend
- **Alpine.js**: Lightweight JavaScript framework for reactive components
- **Tailwind CSS**: Utility-first CSS framework for responsive design
- **Vite**: Fast build tool and development server
- **Laravel Echo**: JavaScript library for real-time broadcasting
- **Axios**: HTTP client for API requests

### Development Tools
- **Laravel Breeze**: Authentication scaffolding
- **Laravel Scribe**: API documentation generator
- **Laravel Pail**: Log viewer
- **Laravel Pint**: Code style fixer
- **PHPUnit**: Testing framework
- **Postman**: API testing and documentation

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL or PostgreSQL database
- Pusher account (for real-time features)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd social-network-platform
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Configure your `.env` file with:
   - Database credentials
   - Pusher configuration
   - Mail settings (if needed)
   - App URL and other environment variables

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed  # Optional: seed with sample data
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the application**
   ```bash
   # Development mode (recommended for development)
   composer run dev
   
   # Or run components separately:
   php artisan serve
   php artisan queue:work
   npm run dev
   ```

## Usage

### Web Interface
- Access the application at `http://localhost:8000`
- Register a new account or login
- Navigate through the dashboard to view posts from friends
- Create posts, add comments, and interact with content
- Manage friend requests and view profiles

### API Usage
The application provides a complete REST API. Use tools like Postman to interact with the API endpoints.

**Authentication**: Use Laravel Sanctum tokens for API access.

Example API endpoints:
- `POST /api/auth/login` - User login
- `GET /api/posts` - Retrieve posts
- `POST /api/posts` - Create a new post
- `POST /api/friend-requests` - Send friend request

### Real-time Features
- Friend request notifications appear instantly
- New comments and likes update in real-time
- Broadcasting requires Pusher configuration

## API Documentation

API documentation is auto-generated using Laravel Scribe. After setup, access the documentation at:
```
http://localhost:8000/docs
```

The documentation includes:
- Interactive API explorer
- Request/response examples
- Authentication details
- Endpoint descriptions

## Project Structure

```
├── app/
│   ├── Events/          # Broadcasting events
│   ├── Http/Controllers/ # Web and API controllers
│   ├── Models/          # Eloquent models
│   ├── Policies/        # Authorization policies
│   └── Providers/       # Service providers
├── database/
│   ├── factories/       # Model factories
│   ├── migrations/      # Database migrations
│   └── seeders/         # Database seeders
├── public/              # Public assets
├── resources/
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── views/          # Blade templates
├── routes/
│   ├── api.php         # API routes
│   ├── web.php         # Web routes
│   └── channels.php    # Broadcasting channels
├── tests/              # Test files
└── config/             # Configuration files
```

## Testing

Run the test suite using PHPUnit:
```bash
php artisan test
```

Tests cover:
- User authentication
- Post CRUD operations
- Comment functionality
- Friend request management
- API endpoints

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Style
- Follow PSR-12 coding standards
- Use Laravel Pint for code formatting: `composer run pint`
- Write tests for new features
- Update documentation as needed

## Security

This application implements several security measures:
- CSRF protection on web forms
- Input validation and sanitization
- Authorization policies for resource access
- Secure file upload handling
- API token authentication

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions:
- Check the API documentation
- Review the codebase comments
- Create an issue in the repository

---

**Built with ❤️ using Laravel**</content>
<parameter name="filePath">d:\social network platform\README.md
