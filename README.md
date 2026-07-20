# 🌟 Fortune Maroc - MES & Production Entry Application

An advanced Manufacturing Execution System (MES) and Production Entry portal designed to streamline shopfloor activities, manage production lines, track machine downtimes, and monitor performance in real-time.

---

## 🚀 Key Features

*   **Multi-Role Access Control**: Tailored dashboards and permissions for:
    *   **Administrators**: Configure master data, users, machines, and lines.
    *   **Supervisors**: Approve production plans, monitor entries, and run reports.
    *   **Operators (Normal Users)**: Enter production output, declare machine downtimes, and view current shift targets.
*   **Production Planning & Execution**: Manage shifts, production lines, zones, and products.
*   **Downtime Management**: Categorize and log downtime reasons to perform root-cause analysis (OEE metrics).
*   **IoT & Telemetry Integration**: Native structures to sync with **Thingsboard** devices for automated data acquisition.

---

## 🛠️ Tech Stack

*   **Backend**: Laravel (PHP 8.2+)
*   **Frontend**: TailwindCSS, Alpine.js, Vite
*   **Database**: MySQL / SQLite
*   **Interactions**: Laravel Livewire / Blade components

---

## 📦 Installation & Setup

Follow these steps to set up the project locally:

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/ali-chnitifa-12/Fortune-Maroc.git
cd Fortune-Maroc
composer install
npm install
```

### 2. Environment Configuration
Copy the sample environment file and configure your database:
```bash
cp .env.example .env
php artisan key:generate
```
*Update your database credentials in the `.env` file:*
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=production_entry
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migrations & Seeding
Run migrations and populate the database with demonstration data and pre-configured accounts:
```bash
php artisan migrate:fresh
php artisan tinker seed_demo.php
```

---

## 🔑 Demo Accounts

| Role | Email | Password |
| :--- | :--- | :--- |
| **Administrateur** | `admin@example.com` | `password` |
| **Superviseur** | `supervisor@example.com` | `password` |
| **Utilisateur (Operator)** | `operator@example.com` | `password` |

---

## 💻 Running the Application

Start the PHP development server and Vite asset compiler simultaneously:

```bash
# Start Laravel server
php artisan serve

# Start Vite compiler (in a separate terminal)
npm run dev
```

Visit the application at `http://127.0.0.1:8000`.
