# Inventory Management System

A PHP and MySQL based Inventory Management System for small businesses.

## Features

- Admin/User Login
- Session Authentication
- Category Management
- Product Management
- Stock In
- Stock Out
- Current Stock Calculation
- Search and Filter
- Stock Reports
- JSON APIs
- Input Validation
- Secure Password Handling
- Unauthorized Page Protection
- Error Handling

## Technologies

- PHP
- MySQL
- Apache
- XAMPP
- Sparrow Database Toolkit
- Postman

## Database

Database name:

inventory

Tables:

- users
- categories
- products
- stock_movements

## Setup

1. Install XAMPP.
2. Start Apache and MySQL.
3. Copy the project into:

C:\xampp\htdocs\inventory

4. Create the MySQL database:

inventory

5. Create the required tables.

6. Configure the database connection in:

config/database.php

7. Open:

http://localhost/inventory/

8. Login using the created admin account.

## Stock Calculation

Current stock is calculated using:

Current Stock = Total Stock In - Total Stock Out

Example:

Opening Stock = 100
Stock In = 20
Stock Out = 15

Current Stock = 100 + 20 - 15

Current Stock = 105

## API Endpoints

### Product List

GET

/api/products.php

### API Login

POST

/api/auth.php

### Stock In

POST

/api/stock_in.php

### Stock Out

POST

/api/stock_out.php

## API Response

Successful responses contain:

- success
- message
- data

Example:

{
    "success": true,
    "message": "Products retrieved successfully",
    "data": []
}

## Security

- Passwords are stored using password_hash().
- Passwords are verified using password_verify().
- User input is validated.
- Database queries are protected.
- Admin pages require authentication.
- Stock Out cannot exceed available stock.
- SQL errors and sensitive information are not exposed to clients.