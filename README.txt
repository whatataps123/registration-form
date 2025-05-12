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
- `README.txt`: Documentation for the project.

## Requirements

- PHP 7.4 or higher
- MySQL database
- A web server (e.g., Apache)

## Setup Instructions

1. Clone or download the project files into your web server's root directory (e.g., `htdocs` for XAMPP).
2. Import the database schema into your MySQL server. Create a database named `conference_registration` and add the necessary tables:
   - `users`: Stores user information.
   - `registrations`: Stores registration details.
3. Update the database credentials in `config.php`:
   ```php
   $host = 'localhost';
   $dbname = 'conference_registration';
   $username = 'root';
   $password = '';
