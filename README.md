# 🚄 RailFlow - Modern Train Booking System with AI-Driven Safety

RailFlow is a premium, high-performance train ticket booking application built with Laravel. It provides a seamless experience for passengers to discover schedules, select seats interactively, and manage their bookings with ease. Additionally, it incorporates an advanced AI-powered safety monitoring system (CAPE - Context-Aware Prompt Engine) for real-time railway hazard assessment, making it suitable for both passenger services and operational safety management.

## ✨ Key Features

### 🔍 Smart Search & Discovery

- **Modern Search UI**: A sleek, glassmorphism-inspired "Find Your Train" interface.
- **Real-time Availability**: Instant feedback on seat availability and pricing.
- **Dynamic Schedules**: Easily browse upcoming journeys with detailed departure/arrival information.

### 💺 Premium Seat Selection

- **Interactive Seat Picker**: A visual, coach-style seat map.
- **Class-based Pricing**: Dynamic pricing for Economy, Business, First, and Premium classes.
- **Visual Feedback**: Real-time selection summary and total cost calculation.

### 💳 Secure Booking Flow

- **Seamless Checkout**: A streamlined process from seat selection to payment.
- **Simulated Payment Gateway**: Integrated dummy payment processor for a complete end-to-end flow.
- **Digital Receipts**: Instant confirmation with detailed booking summaries.

### 🎫 Smart Tickets & Management

- **QR Code Integration**: Every ticket includes a unique QR code for easy verification.
- **Booking History**: Comprehensive history tracking for users.
- **Admin Management**: Powerful dashboard for administrators to monitor all system bookings.
- **Role-Based Access**: Secure environment with Admin, Staff, and Customer roles.

### 🛡️ AI-Driven Safety Monitoring (CAPE System)

- **Real-time IoT Integration**: Collects sensor data including speed, distance, temperature, humidity, light, and rain levels.
- **Disaster History Tracking**: Records past incidents with geographic coordinates and risk assessments.
- **Object Detection**: AI-powered detection of obstacles on tracks with GPS positioning.
- **Risk Area Management**: Defines and monitors hazardous railway segments.
- **Intelligent Risk Assessment**: Uses Google Gemini LLM for context-aware hazard evaluation, with fallback deterministic logic.
- **Event Memory System**: Maintains historical context for pattern recognition in safety incidents.
- **Automated Notifications**: System-wide alerts for critical safety events via SMS and in-app notifications.

## 🛠 Tech Stack

- **Core**: [Laravel 12](https://laravel.com) & PHP 8.2+
- **Styling**: [Bootstrap 5](https://getbootstrap.com) with Premium Custom CSS
- **Interactions**: jQuery, AJAX, and Modern Web Design tokens
- **Data Management**: Spatie Laravel-Permission, Yajra DataTables
- **AI Integration**: Google Gemini API for intelligent safety reasoning
- **Utilities**: [GoQR API](https://goqr.me/) for dynamic ticket generation, SMS services for notifications
- **Database**: Eloquent ORM with migrations for structured data models

## 🏗️ Architecture Overview

### Core Components

- **Models**: User, Train, Schedule, Seat, Booking, IotData, DisasterHistory, ObjectDetection, RiskArea, CapeRiskLog, Notification
- **Services**: BookingService (transaction-based bookings), CapeEngine (AI prompt building), LlmReasoningService (Gemini API integration), ContextBuilderService (sensor data processing), EventMemoryService (historical context), SmsService (notifications)
- **Controllers**: Handle booking flows, admin dashboards, IoT data ingestion, safety assessments, and user management

### Data Flow

**Booking Flow**: User search → Schedule query → Seat selection → Payment → Booking creation → QR ticket generation

**Safety Assessment Flow**: IoT sensor data → Context building → CAPE processing → LLM reasoning → Risk logging → Notifications (if critical)

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & NPM

### Installation

1. **Clone the repository**

    ```bash
    git clone https://github.com/adppriyashan/RailFlow.git
    cd RailFlow
    ```

2. **Automated Setup**
   We've included a custom setup script for convenience:

    ```bash
    composer run setup
    ```

3. **Manual Setup (If needed)**

    ```bash
    cp .env.example .env
    php artisan key:generate
    php artisan migrate --seed
    npm install
    npm run build
    ```

4. **Start the Engine**
   Using our custom concurrent command:
    ```bash
    composer run dev
    ```

## 🚉 Project Structure

- `app/Http/Controllers/PaymentController.php`: Handles the end-to-end payment and checkout logic.
- `resources/views/booking/`: Contains the interactive seat picker and search interfaces.
- `resources/views/payment/`: Checkout and success page designs.
- `database/migrations/`: Structured data models including recent enhancements for user profiles.
- `app/Services/CapeEngine.php`: AI prompt building for safety assessments.
- `app/Services/LlmReasoningService.php`: Google Gemini API integration for intelligent reasoning.

---

RailFlow - _Engineering the Future of Rail Travel with AI Safety_
