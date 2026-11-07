# NenaCare - AI-Powered K3 Incident Reporting & Monitoring System

A full-stack incident reporting and Occupational Health and Safety (K3) monitoring platform tailored for the Food and Beverage (F&B) industry. The system integrates Google Gemini AI for automated hazard risk evaluation, Telegram Bot for real-time alerts and remote status dispatching, interactive Chart.js analytics, and automated PDF compliance reporting.

## System Overview

In fast-paced hospitality environments such as commercial kitchens and dining halls, safety hazards (gas leaks, electrical faults, physical slips, and ergonomic risks) require immediate reporting and rapid mitigation. NenaCare bridges the gap between on-site reporting and operational action by providing an intuitive reporting interface with privacy-preserving anonymous options, automated AI-driven triage, and instant communication channels for facility managers.

## Key Capabilities

- Multi-Category Hazard Reporting: Standardized intake forms capturing incidents across critical categories: Fire & Gas, Electrical, Environmental & Cleanliness, and Ergonomics & PPE.
- Whistleblower Privacy Protection: Optional anonymous mode that sanitizes reporter identity in database records, fostering an open safety culture without fear of retaliation.
- Automated AI Risk Assessment: Powered by Google Gemini 2.5 Flash-Lite API, evaluating incident severity into tiered priority levels (Rendah, Normal, Tinggi) and generating actionable mitigation protocols.
- Structured AI Output Extraction: Backend parsing routines that extract structured JSON payloads directly from Gemini responses for relational persistence.
- Telegram Bot Two-Way Integration: Instant mobile alert delivery to safety managers with inline callback buttons for remote status progression (Menunggu, Diproses, Selesai).
- Live Administrative Dashboard: Real-time incident feed, KPI performance counters, and interactive visual charts (Doughnut chart for status distribution and Bar chart for category breakdown via Chart.js).
- Regulatory PDF Export: Automated server-side generation of standardized A4 landscape audit documents via Dompdf, including date-range filtering and incident chronologies.
- Role-Based Administrative Control: Protected session management, password hashing (BCRYPT), and administrative audit trail logging.

## Architectural Flow

```
[ Incident Occurrence ]
         │
         ▼
[ Reporting Interface ] ──> (Optional Anonymous Mode)
         │
         ▼
[ Backend Dispatcher & Google Gemini AI ]
   ├── Severity Scoring (Rendah / Normal / Tinggi)
   └── Mitigation Advisory Generation
         │
         ├───► [ MySQL Relational Database ]
         │
         ├───► [ Telegram Bot Alert & Remote Actions ]
         │
         └───► [ Admin Dashboard & Chart.js Analytics ]
                     │
                     └───► [ Formal PDF Audit Export (Dompdf) ]
```

## Technology Stack

- Backend: PHP 8.x (Object-Oriented Programming, Prepared Statements)
- Database: MySQL / MariaDB (Relational schema with foreign keys and indexes)
- Artificial Intelligence: Google Gemini 2.5 Flash-Lite API (REST endpoint integration)
- Messaging & Bot API: Telegram Bot API (Webhooks / Polling & Inline Keyboards)
- PDF Generation: Dompdf 3.1
- Front-End: HTML5, CSS3, JavaScript (ES6+), Bootstrap
- Data Visualization: Chart.js

## Project Structure

```
NenaCare/
├── assets/                  # Application icons, styling stylesheets, and client scripts
├── classes/                 # Core domain logic and handler classes
│   └── GeminiRiskEvaluator  # Integration client for Gemini AI API
├── admin.php                # Administrative management dashboard and live feed
├── bot_listener.php         # Telegram webhook handler and callback query listener
├── config.php               # Environment variable loader and database connection pool
├── database.sql             # Relational database schema and initial seed data
├── debug_ai.php             # Diagnostic utility for Gemini API testing
├── detail.php               # Deep-dive incident inspection view
├── export_pdf.php           # Server-side PDF export compiler powered by Dompdf
├── index.php                # Public hazard reporting portal
├── login.php                # Administrative authentication interface
├── logout.php               # Session termination and invalidation handler
├── composer.json            # PHP package manager dependencies definition
└── README.md                # Technical documentation
```

## Installation and Configuration

### Prerequisites

- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Composer (PHP Dependency Manager)
- Google Gemini API Key
- Telegram Bot Token and Admin Chat ID

### Installation Steps

1. Clone the repository:
   ```bash
   git clone https://github.com/ANTARTICA1/NenaCare.git
   cd NenaCare
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Configure Environment Variables:
   Copy `.env.example` to `.env` and populate your credentials:
   ```ini
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=
   DB_NAME=db_nena_k3

   # Google Gemini API
   GEMINI_API_KEY=your_gemini_api_key_here

   # Telegram Bot Configuration
   BOT_TOKEN=your_telegram_bot_token_here
   ADMIN_ID=your_telegram_chat_id_here
   ```

4. Database Setup:
   Import `database.sql` into your MySQL server, or allow `config.php` to automatically construct tables upon first request:
   ```bash
   mysql -u root -p db_nena_k3 < database.sql
   ```

5. Run Local Server:
   ```bash
   php -S localhost:8000
   ```

6. Access the Application:
   - Reporting Portal: `http://localhost:8000/index.php`
   - Admin Dashboard: `http://localhost:8000/admin.php` (Default: `admin` / `admin123`)

## Author

Anak Agung Ngurah Krisna Artha Wibawa ([ANTARTICA1](https://github.com/ANTARTICA1))

## License

This project is licensed under the MIT License.