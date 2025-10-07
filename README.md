# 🏨 LodgeEase - Hotel Management System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Firebase](https://img.shields.io/badge/Firebase-Realtime%20Database-orange.svg)](https://firebase.google.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.0-blue.svg)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A comprehensive hotel and lodge management system built with Laravel 12, Firebase, and modern web technologies. LodgeEase provides real-time booking management, business analytics, and streamlined operations for hospitality businesses.

## 🌟 Features

### 🏠 **Client Portal**
- **Responsive Home Interface** - Modern, mobile-friendly booking interface
- **Room Browse & Search** - Interactive room catalog with filtering
- **Real-time Availability** - Live room availability checking
- **Booking System** - Streamlined reservation process
- **Guest Authentication** - Secure client login and profiles

### 🔧 **Admin Dashboard**
- **Comprehensive Dashboard** - Real-time metrics and KPI monitoring
- **Room Management** - Complete room inventory control
- **Booking Requests** - Centralized reservation management
- **Business Analytics** - Advanced reporting and insights
- **Activity Logging** - Real-time system activity tracking
- **Settings Management** - System configuration and preferences
- **AI Chatbot Integration** - Automated customer support

### 📊 **Analytics & Reporting**
- **Real-time KPIs** - Total sales, occupancy rates, seasonal performance
- **Revenue Analytics** - Monthly revenue tracking and forecasting
- **Occupancy Trends** - Historical and predictive occupancy analysis
- **Booking Trends** - Comprehensive booking pattern analysis
- **Room Performance** - Individual room profitability metrics
- **Guest Demographics** - Customer analytics and insights
- **Seasonal Analysis** - Performance tracking across seasons
- **Export Capabilities** - Data export in JSON and CSV formats

### 🔍 **Activity Monitoring**
- **Real-time Activity Logs** - Live system activity tracking with automatic refresh
- **Advanced Filtering** - Filter by action, category, severity, admin, and date
- **Comprehensive Logging** - Login/logout, room updates, booking approvals, exports
- **Performance Metrics** - Memory usage, request duration tracking
- **Firebase Integration** - Cloud-based activity storage
- **Visual Status Indicators** - Real-time active status with pulsing indicators

### 🚀 **Technical Features**
- **Firebase Integration** - Real-time database with offline capabilities
- **Responsive Design** - Mobile-first, fully responsive UI
- **Real-time Updates** - Live data synchronization
- **Modal Optimization** - Performance-optimized loading screens that exclude modal interactions
- **Modern Asset Pipeline** - Vite-powered build system
- **Component Architecture** - Reusable UI components

## 🛠️ Technology Stack

### Backend
- **Laravel 12.x** - Modern PHP framework
- **PHP 8.2+** - Latest PHP features and performance
- **Firebase Realtime Database** - Cloud-hosted NoSQL database
- **Firebase Authentication** - Secure user management

### Frontend
- **TailwindCSS 4.0** - Utility-first CSS framework
- **Vite 7.x** - Fast build tool and dev server
- **Alpine.js** - Lightweight JavaScript framework
- **Chart.js** - Interactive charts and graphs
- **Axios** - HTTP client for API requests

### Development Tools
- **Laravel Vite** - Asset compilation and HMR
- **Laravel Pint** - PHP code style fixer
- **PHPUnit** - Testing framework
- **Laravel Sail** - Docker development environment

## 📋 Requirements

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18.x or higher
- npm or yarn
- Firebase project with Realtime Database
- Web server (Apache/Nginx) or Laravel development server

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/lodgeease.git
cd lodgeease
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Firebase
1. Create a Firebase project at [Firebase Console](https://console.firebase.google.com)
2. Enable Realtime Database
3. Download the service account JSON file
4. Place it in `storage/app/firebase/firebase_credentials.json`
5. Update `.env` with Firebase configuration:

```env
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_DATABASE_URL=https://your-project-default-rtdb.firebaseio.com/
```

### 6. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Initialize Firebase Database
```bash
php artisan firebase:init
```

### 8. Start Development Server
```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## 📁 Project Structure

```
LodgeEaseRE/
├── app/
│   ├── Http/Controllers/          # Application controllers
│   │   ├── AdminController.php
│   │   ├── BusinessAnalyticsController.php
│   │   ├── ActivityLogController.php
│   │   └── ...
│   ├── Models/                    # Eloquent models
│   └── Services/
│       └── FirebaseService.php    # Firebase integration
├── resources/
│   ├── views/                     # Blade templates
│   │   ├── admin/                 # Admin dashboard views
│   │   ├── client/                # Client portal views
│   │   └── components/            # Reusable components
│   ├── js/                        # JavaScript assets
│   │   ├── activity-log.js
│   │   ├── business-analytics.js
│   │   ├── loading-screen.js
│   │   └── ...
│   └── css/                       # Stylesheets
├── routes/
│   └── web.php                    # Application routes
└── public/
    └── build/                     # Compiled assets
```

## 🎯 Key Features Deep Dive

### Business Analytics Dashboard
- **KPI Monitoring**: Real-time tracking of total sales, occupancy rates, and seasonal performance
- **Revenue Analysis**: Monthly revenue trends with comparative analysis
- **Booking Insights**: Comprehensive booking pattern analysis with status tracking
- **Room Performance**: Individual room profitability and utilization metrics
- **Export Functionality**: Data export capabilities for external analysis

### Activity Log System
- **Real-time Tracking**: Live activity monitoring with automatic refresh (always active)
- **Advanced Filtering**: Multi-parameter filtering by action, category, severity, admin, and date range
- **Performance Metrics**: Built-in performance monitoring with memory and request duration tracking
- **Firebase Integration**: Cloud-based storage with real-time synchronization
- **Comprehensive Logging**: Covers all system activities including logins, room updates, booking approvals, and data exports
- **Visual Indicators**: Green pulsing "Real-time Active" status indicator

### Loading Screen Optimization
- **Modal-aware Loading**: Smart loading screens that exclude modal interactions for better performance
- **Automatic Detection**: Intelligent modal context detection
- **Performance Optimized**: Instant modal responses without loading delays
- **Backward Compatible**: Maintains all existing functionality while improving UX

## 🔧 Configuration

### Firebase Setup
Ensure your Firebase Realtime Database has the following structure:
```json
{
  "activity_logs": {},
  "admins": {},
  "bookings": {},
  "rooms": {},
  "settings": {}
}
```

### Environment Variables
Key environment variables to configure:
```env
APP_NAME=LodgeEase
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

FIREBASE_PROJECT_ID=your-project-id
FIREBASE_DATABASE_URL=https://your-project-default-rtdb.firebaseio.com/
```

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

## 📈 Performance Features

- **Optimized Loading**: Modal-aware loading screens for instant interactions
- **Real-time Updates**: Live data synchronization without page refreshes
- **Efficient Queries**: Optimized Firebase queries for large datasets
- **Asset Optimization**: Vite-powered build system with code splitting
- **Responsive Design**: Mobile-first approach for all device types
- **Simplified Database Structure**: Single activity logs collection (no category indexing for better performance)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel team for the amazing framework
- Firebase team for real-time database capabilities
- TailwindCSS team for the utility-first CSS framework
- Chart.js team for interactive charts
- All contributors and testers

## 📞 Support

For support, create an issue on GitHub or contact the development team.

---

Made with ❤️ by the LodgeEase Team

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
#   L o d g e E a s e R E  
 