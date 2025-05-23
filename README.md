PHP OOP Password Manager
A lightweight PHP OOP application that lets users register, log in, generate random passwords, and securely store them in a MySQL database. All passwords are encrypted with AES‐256‐CBC using a per‐user “master key” that’s itself encrypted under the user’s login password.

🚀 Overview
I built this project to practice PHP OOP and strong encryption practices. When a user registers, their login password is hashed (bcrypt) and a random “master key” is generated. That master key is encrypted (AES‐256‐CBC) using the user’s plain password and saved in the database. On login, I decrypt the master key, store it in the session, and use it to encrypt/decrypt any password entries the user creates. It’s simple, effective, and fully functional.

🔑 Features
User Registration & Login

Login password hashed with password_hash()

Master key (32 bytes) AES‐256‐CBC encrypted under the login password

Secure session management

Password Generation

Specify total length, lowercase count, uppercase count, digits count, special characters count

Returns a shuffled random password

Encrypted Storage

Each saved password is AES‐256‐CBC encrypted with the user’s master key + random IV

Stored in password_entries table

View / Delete / Update Entries

Decrypt and display all saved passwords in a table

Delete any entry with a confirmation prompt

(Optionally update an existing entry with a newly encrypted value)

Change Login Password

Re‐encrypt the existing master key under the new password so users never lose access to their stored data

Simple, Consistent UI

Every page has a light blue background and a centered semi‐transparent white box

Forms use rounded inputs, blue focus outlines, and grayscale buttons that darken on hover

Inline CSS ensures no broken link issues

📦 Installation
Clone the repository
Set up your web server

Copy this project folder into your local webserver’s htdocs (XAMPP) or www directory.

Example for XAMPP:

makefile
Kopyala
Düzenle
C:\xampp\htdocs\php-password-manager
Create the MySQL database
Open phpMyAdmin (or your preferred MySQL client) and run these queries:

sql
CREATE DATABASE IF NOT EXISTS password_manager;
USE password_manager;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    encrypted_master_key VARBINARY(512) NOT NULL,
    master_key_iv VARBINARY(16) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE password_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    site_name VARCHAR(100) NOT NULL,
    encrypted_password VARBINARY(512) NOT NULL,
    password_iv VARBINARY(16) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
Configure database credentials
Edit models/DatabaseConnection.php if your MySQL username/password or host is different:

php
private $host = 'localhost';
private $dbname = 'password_manager';
private $username = 'root';
private $password = ''; // set your MySQL root password if any
Run the app
Open your browser and go to:
http://localhost/php-oop-password-manager/index.php
🔧 Usage
Register a new user (username + password).

Log in with the same credentials.

On Dashboard, you have buttons to:

Generate a new password (choose length and character breakdown)

View all saved passwords

Change your login password

Logout

Generate Password page:

Adjust “Total Length” and “Lowercase/Uppercase/Digits/Special” counts.

Click Generate and a random password appears.

Enter “Site or App Name” and click Save to encrypt + store it.

View Passwords page:

See a table of all saved passwords (decrypted on the fly).

Click Delete next to any entry to remove it.

Change Password page:

Enter your current (old) password and new password twice.

On success, your master key is re‐encrypted under the new password.

Logout button ends your session.

