# NenaCare - AI-Powered K3 Incident Reporting & Monitoring System

A full-stack incident reporting and Occupational Health and Safety (K3 / HSE) monitoring platform tailored for the Food and Beverage (F&B) industry.

The system integrates Google Gemini AI for automated hazard risk evaluation, Telegram Bot for real-time alerts and remote status dispatching, interactive Chart.js analytics, instant search filtering, CSV dataset export, and automated PDF compliance audit reporting.

---

## Anti-AI Slop Design System

NenaCare adheres strictly to an **Anti-AI Slop** design philosophy, eliminating generic aesthetic cliches commonly produced by unchecked AI generators:

- **Banned Clutter**: No neon text gradients, no floating blurry background orbs, no glassmorphism blur layers that sacrifice data readability, and no decorative pulse animations.
- **Strict Typographic Hierarchy**: Engineered with *Plus Jakarta Sans* for high-legibility interface copy and *JetBrains Mono* for incident identifiers (`#LAP-0042`), timestamps, and tabular data.
- **Industrial Safety Palette**: Grounded in obsidian-slate neutrals with steel azure accents and high-contrast semantic status tokens (Crimson for High Priority, Amber for Normal/Warning, Emerald for Resolved).
- **Tactile Materiality**: Crisp 1px hairline borders (`rgba(255, 255, 255, 0.08)` / `#2d3b55`) with subtle inset highlights and structured geometric radius.
- **Zero Emojis**: Every interface element, select option, timeline step, and alert banner uses crisp vector icons (Bootstrap Icons) and authoritative professional terminology.

---

## Architectural Flow

```
[ Incident Occurrence ]
         |
         v
[ Reporting Portal ] ---> (Optional Anonymous Mode)
         |
         v
[ Backend Dispatcher & Google Gemini AI ]
   |-- Severity Scoring (Rendah / Normal / Tinggi)
   `-- Actionable Mitigation Advisory
         |
         +---> [ MySQL Relational Database ]
         |
         +---> [ Telegram Bot Alert & Remote Actions ]
         |
         `---> [ Admin Console & Chart.js Analytics ]
                     |
                     +---> [ Formal PDF Audit Export (Dompdf) ]
                     |
                     `---> [ Raw CSV Dataset Export ]
```

---

## Key Capabilities

### 1. Multi-Category Hazard Intake & Whistleblower Privacy
- **Public & Anonymous Reporting**: Reporters can submit incidents with their name and operational role or enable anonymous mode to protect whistleblower privacy.
- **Role Categorization**: Captures role context (Customer, Staf Kafe, Barista, Dapur / Kitchen, Teknisi).
- **Specialized Hazard Categories**: Covers Fire & Gas (Api & Gas), Electrical (Kelistrikan), Environment & Cleanliness (Lingkungan & Kebersihan), and Ergonomics & PPE (Ergonomi & APD).

### 2. Automated AI Risk Assessment & Mitigation
- **Severity Evaluation**: Evaluates hazard severity into structured priority tiers (Tinggi, Normal, Rendah) powered by the Google Gemini API.
- **Actionable Corrective Guidance**: Automatically generates practical on-site mitigation advice for facility and HSE teams.

### 3. Real-Time Telegram Bot Integration
- **Instant Dispatch**: Delivers urgent hazard alerts directly to safety supervisors.
- **Two-Way Inline Actions**: Enables remote status transitions (Diproses, Selesai) directly from Telegram chat callback buttons.

### 4. Interactive Monitoring & Live Feed
- **Live Search & Category Filtering**: Instant client-side search across incidents, locations, and descriptions, paired with instant category filter tabs.
- **Responsive Incident Cards**: Structured cards featuring unique tracking codes, status indicators, and AI advisory snippets.

### 5. Supervisor Command Console (Admin Dashboard)
- **KPI Performance Counters**: Real-time counters for Total Incidents, Pending Review, In Progress, Resolved, High Priority, and Resolution Rate percentage.
- **Visual Analytics**: Interactive Doughnut chart for status distribution and Bar chart for hazard category breakdown via Chart.js.
- **Multi-Parameter Filter**: Filter records by search keyword, status, category, priority, and date range.
- **Inline Status & Priority Controls**: Seamlessly update incident parameters directly from the data table.

### 6. Investigation Detail, Audit Trail & Notes
- **Linear Progress Timeline**: Tracks lifecycle progression through three verified milestones: Laporan Diterima, Penanganan Lapangan, and Verifikasi Tuntas.
- **One-Click Quick Actions**: Quick buttons to transition status directly to "Mulai Penanganan" or "Tandai Selesai".
- **Internal Investigation Notes**: Supervisor audit trail for recording corrective actions, technician assignments, and field verification.
- **Print View Ready**: Integrated print stylesheet for generating instant physical paper dossiers.

### 7. Dual Export Modalities
- **Official PDF Compliance Export**: Landscape A4 audit document formatted with Dompdf, complete with document metadata, filter summaries, and formal sign-off signature blocks.
- **Raw CSV Dataset Export**: Structured CSV download with UTF-8 BOM encoding for seamless analysis in Microsoft Excel or business intelligence tools.

---

## Technology Stack

- **Backend**: PHP 8.x (Object-Oriented Programming, Prepared Statements, MySQLi)
- **Database**: MySQL / MariaDB (Relational schema with indexes and relational constraints)
- **Artificial Intelligence**: Google Gemini API (v1beta)
- **Messaging API**: Telegram Bot API (Webhooks / Polling & Inline Keyboards)
- **PDF Generation**: Dompdf 3.1
- **Front-End**: Semantic HTML5, Custom Anti-Slop CSS System, JavaScript (ES6+), Bootstrap Icons
- **Data Visualization**: Chart.js 4.4

---

## Project Structure

```
NenaCare/
|-- assets/
|   |-- css/
|   |   `-- style.css          # Anti-AI Slop Design System stylesheet
|   `-- nenacare.jpg           # Application screenshot preview
|-- classes/
|   |-- AuthManager.php        # Authentication, session, and role security
|   |-- Facility.php           # Facility abstraction model
|   |-- ElectronicFacility.php # Electronic facility model
|   |-- FurnitureFacility.php  # Furniture facility model
|   `-- ReportManager.php      # Incident data engine, analytics, and external integrations
|-- admin.php                  # Supervisor command console and analytics dashboard
|-- bot_listener.php           # Telegram polling daemon for remote callbacks
|-- config.php                 # Environment loader and database connection pool
|-- database.sql               # Relational database DDL schema
|-- debug_ai.php               # Diagnostic test script for Gemini API
|-- detail.php                 # Investigation dossier, timeline, and audit notes
|-- export_csv.php             # Raw CSV dataset exporter (Excel compatible)
|-- export_pdf.php             # Official PDF compliance document compiler
|-- index.php                  # Public incident intake portal and live monitoring feed
|-- login.php                  # Supervisor authentication interface
|-- logout.php                 # Session invalidation handler
|-- composer.json              # Composer package definitions
`-- README.md                  # System documentation and operational manual
```

---

## Installation & Setup Guide

### Prerequisites

- Web Server (Apache / Nginx via Laragon, XAMPP, or Linux LEMP/LAMP)
- PHP 8.1 or higher with `mysqli`, `curl`, `mbstring`, and `openssl` extensions enabled
- MySQL 5.7+ or MariaDB 10.4+
- Composer (PHP Dependency Manager)
- Google Gemini API Key
- Telegram Bot Token & Admin Chat ID

### Installation Steps

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/ANTARTICA1/NenaCare.git
   cd NenaCare
   ```

2. **Install Composer Dependencies**:
   ```bash
   composer install
   ```

3. **Configure the Database**:
   - Create a database named `db_nena_k3` in your MySQL server.
   - Import `database.sql`:
     ```bash
     mysql -u root -p db_nena_k3 < database.sql
     ```

4. **Set Up Environment Variables**:
   Copy `.env.example` to `.env` and configure your credentials:
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

5. **Start Telegram Listener (Optional)**:
   ```bash
   php bot_listener.php
   ```

6. **Access the Application**:
   - Incident Reporting Portal: `http://localhost/NenaCare/index.php`
   - Supervisor Console: `http://localhost/NenaCare/login.php`

---

## Author

Anak Agung Ngurah Krisna Artha Wibawa ([ANTARTICA1](https://github.com/ANTARTICA1))

---

## License

This project is licensed under the MIT License.
