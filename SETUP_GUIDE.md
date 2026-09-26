# EventDNA Developer Setup Guide

Welcome to the EventDNA project! Follow these steps to get your local environment running smoothly so you can start contributing.

## 1. Prerequisites

- **XAMPP / MAMP / WAMP** (PHP 8.1+ and MySQL)
- **Composer** (Dependency Manager for PHP)
- **Git**

## 2. Project Setup

1. **Clone the Repository**
   Navigate to your local server's root folder (`htdocs` for XAMPP) and clone the repo:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs # Mac (Adjust for Windows/Linux)
   git clone https://github.com/YasiruLaki/EventDNA.git eventDNA
   cd eventDNA
   ```

2. **Install Dependencies**
   We use `PHPMailer` for sending emails. Install it by running:
   ```bash
   composer install
   ```

3. **Environment Variables (`.env`)**
   We keep sensitive information out of version control.
   - Create a file named `.env` in the root folder (`eventDNA/.env`).
   - Copy the following template and replace with your local credentials:
   ```ini
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=eventdna
   DB_USER=root
   DB_PASSWORD=

   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your.email@gmail.com
   MAIL_PASSWORD=your_app_password
   MAIL_FROM_ADDRESS=your.email@gmail.com
   MAIL_FROM_NAME=EventDNA
   ```
   *(Note: For Gmail, you will need to generate an "App Password" in your Google Account settings to send emails locally).*

## 3. Database Setup

We have an initial database dump that includes our schema and default data (like pre-filled skills and networking goals).

1. Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`).
2. Create a new database named **`eventdna`** (must match your `DB_NAME` in `.env`).
3. Click on the new `eventdna` database, go to the **Import** tab.
4. Choose the file located at `data/eventdna.sql` in our project folder.
5. Click **Import**.

## 4. Architecture Guidelines (Important!)

We are strictly following a **3-Tier Architecture** for this project. When you build new features, structure your code as follows:

1. **Data Layer (`data/`)**: 
   - Contains Repository classes (e.g., `UserRepository.php`, `OnboardingRepository.php`).
   - **Rule:** ONLY execute SQL queries here. Do not process HTTP requests or render HTML here.
2. **Application Layer (`application/controllers/`)**: 
   - Contains Controller classes (e.g., `AuthController.php`).
   - **Rule:** Contains business logic. This layer calls methods from the Repository and returns arrays/responses back to the presentation layer. Do NOT put `$_POST` or `$_GET` directly in the controller methods; pass them as arguments from the presentation layer.
3. **Presentation Layer (`presentation/`)**: 
   - Contains our UI files (`.php`, `.css`, `.js`). 
   - **Rule:** These files should handle sessions, read `$_POST`/`$_GET` data, instantiate the Controllers, and render the HTML. Do NOT write SQL queries in presentation files. All URLs should point to `.php` files, not `.html`.

## 5. Development Workflow

1. Always pull the latest changes before starting work:
   ```bash
   git pull origin main
   ```
2. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. After completing your work, stage and commit:
   ```bash
   git add .
   git commit -m "feat: brief description of what you did"
   ```
4. Push and create a Pull Request on GitHub:
   ```bash
   git push origin feature/your-feature-name
   ```

Happy coding! 🚀
