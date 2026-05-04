# RailFlow Project Overview

## Project Description
RailFlow is a modern, high-performance train ticket booking application built with Laravel, featuring an AI-driven safety monitoring system called CAPE (Context-Aware Prompt Engine). It provides a seamless booking experience for passengers while incorporating real-time railway hazard assessment using IoT data and Google Gemini LLM for intelligent risk evaluation.

## Tech Stack
- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Bootstrap 5, jQuery, AJAX, SASS, Tailwind CSS
- **Database**: MySQL/PostgreSQL (via Eloquent ORM)
- **AI Integration**: Google Gemini API
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel-Permission
- **Data Tables**: Yajra Laravel DataTables
- **Build Tools**: Vite, Composer, NPM
- **Testing**: PHPUnit
- **Other**: GoQR API for QR codes, SMS services for notifications

## Key Features

### Booking System
- **Smart Search & Discovery**: Glassmorphism UI for finding trains with real-time availability
- **Interactive Seat Selection**: Visual coach-style seat map with class-based pricing (Economy, Business, First, Premium)
- **Secure Booking Flow**: End-to-end booking with simulated payment gateway
- **Ticket Management**: QR code generation, booking history, admin dashboard
- **Role-Based Access**: Admin, Staff, Customer roles

### AI-Driven Safety Monitoring (CAPE System)
- **IoT Integration**: Real-time sensor data (speed, temperature, humidity, light, rain, distance)
- **Disaster History Tracking**: Geographic incident records with risk assessments
- **Object Detection**: AI-powered obstacle detection with GPS
- **Risk Area Management**: Hazardous segment monitoring
- **Intelligent Assessment**: Context-aware prompt building with LLM reasoning and deterministic fallback
- **Event Memory**: Historical pattern recognition
- **Automated Notifications**: SMS and in-app alerts for critical events

## Architecture

### Core Components
- **Models**: User, Train, Schedule, Seat, SeatClass, Booking, IotData, DisasterHistory, ObjectDetection, RiskArea, CapeRiskLog, Notification, Setting
- **Services**:
  - BookingService: Handles transaction-based bookings with atomic constraints
  - CapeEngine: Builds weighted prompts for LLM safety assessment
  - LlmReasoningService: Integrates with Google Gemini API
  - ContextBuilderService: Processes sensor data into contextual factors
  - EventMemoryService: Maintains historical safety context
  - SmsService: Manages SMS notifications
- **Controllers**: Handle booking flows, admin dashboards, IoT ingestion, safety assessments
- **Enums**: TrainType for categorizing trains

### Data Flow

#### Booking Flow
1. User searches for trains → Schedule query
2. Seat selection → Interactive picker
3. Payment → Simulated gateway
4. Booking creation → QR ticket generation

#### Safety Assessment Flow
1. IoT sensor data ingestion
2. Context building (weather, obstacles, speed, etc.)
3. CAPE prompt assembly with weights
4. LLM reasoning (with fallback)
5. Risk logging and notifications

## Data Models

### Core Booking Models
- **User**: Authentication, roles, phone number
- **Train**: Name, number, total seats, description
- **SeatClass**: Class types with pricing
- **Seat**: Individual seats with status and class
- **Schedule**: Train journeys with departure/arrival, pricing
- **Booking**: User bookings with references, status

### Safety Models
- **IotData**: Sensor readings (speed, temp, humidity, lux, rain, distances)
- **DisasterHistory**: Past incidents with coordinates and risk levels
- **ObjectDetection**: Detected obstacles with GPS
- **RiskArea**: Hazardous railway segments
- **CapeRiskLog**: AI assessment results
- **Notification**: System alerts
- **Setting**: Configuration parameters

## API Endpoints
- Authentication: GET /api/user (Sanctum)
- IoT: POST /api/iot/history, GET /api/iot/latest, POST /api/settings/update, GET /api/iot/nearest-station
- Notifications: POST /api/notifications/enroll, GET /api/notifications, POST /api/notifications/{id}/read
- Disaster: GET /api/disaster-history, POST /api/disaster-history
- Object Detection: POST /api/object-detection
- CAPE: GET /api/cape/assess, POST /api/cape/chat

## Installation and Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- Database (MySQL/PostgreSQL)

### Quick Setup
```bash
composer run setup  # Installs dependencies, generates key, migrates DB, builds assets
composer run dev    # Starts server, queue, logs, and Vite concurrently
```

### Manual Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

## Usage
- **Booking**: Users can search trains, select seats visually, complete payment, receive QR tickets
- **Admin**: Manage trains, schedules, bookings, view dashboards
- **Safety**: IoT devices send data, system assesses risks, sends alerts
- **AI Integration**: CAPE uses weighted prompts for context-aware risk evaluation

## Configuration
- CAPE weights in config/cape.php for prompt prioritization
- Database connections in config/database.php
- AI API keys in .env for Gemini integration

This overview provides a complete understanding of RailFlow's architecture, features, and implementation for AI analysis or development purposes.