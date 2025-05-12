
# Conference Registration System

This is a simple PHP-based web application for managing conference registrations. It allows users to register, log in, and view their registration details on a dashboard.

## Features

- **User Registration**: Users can create an account by providing personal and address information.
- **Login and Logout**: Secure login and logout functionality.
- **Dashboard**: Displays user information and registration details.
- **Validation**: Includes server-side validation for all inputs.
- **Dynamic Dropdowns**: Address fields dynamically update based on the selected region.

## File Structure

- `config.php`: Contains database connection and geographical data.
- `register.php`: Handles user registration and form validation.
- `login.php`: Manages user login functionality.
- `logout.php`: Logs out the user and destroys the session.
- `dashboard.php`: Displays user and registration details after login.
- `README.md`: Documentation for the project.

## Requirements

- PHP 7.4 or higher  
- MySQL database  
- A web server (e.g., Apache)

## Setup Instructions

1. **Clone or download** the project files into your web server's root directory (e.g., `htdocs` for XAMPP).

2. **Set up the database**  
   Run the following SQL commands:

   ```sql
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        num_attendees INT NOT NULL,
        street VARCHAR(255) NOT NULL,
        barangay VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        district VARCHAR(100) NOT NULL,
        province VARCHAR(100) NOT NULL,
        region VARCHAR(100) NOT NULL,
        zip_code VARCHAR(10) NOT NULL,
        document_path VARCHAR(255) NOT NULL,
        registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );

    CREATE TABLE regions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL
    );

    INSERT INTO regions (name) VALUES 
    ('National Capital Region (NCR)'),
    ('Cordillera Administrative Region (CAR)'),
    ('Ilocos Region (Region I)'),
    ('Cagayan Valley (Region II)'),
    ('Central Luzon (Region III)');

   ```

3. **Configure the application**  
   Edit `config.php` with your database credentials:

   ```php
   $host = 'localhost';
   $dbname = 'conference_registration';
   $username = 'your_db_username';
   $password = 'your_db_password';
   ```

4. **Set up your web server**  
   Make sure your web server is configured to point to the project directory.

## Usage

- Access the registration page at `register.php`
- Create an account and register for the conference
- Log in via `login.php`
- View your dashboard after a successful login
