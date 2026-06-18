==============================================================================
  PASTIMES — Pre-Loved Fashion E-Store
  WEDE6021 — WEB DEVELOPMENT (INTERMEDIATE)
  Portfolio of Evidence (POE) — Final Submission
==============================================================================

Group:    Group 02
Members:  Vukosi Rikhotso — Student No: ST10439408
          Theo Golele     — Student No: ST10439863
Campus:   IIE Rosebank International, Pretoria
Year:     2026

------------------------------------------------------------------------------
1. PROJECT OVERVIEW
------------------------------------------------------------------------------

Pastimes is a PHP/MySQL web application that enables South African users to buy
and sell pre-owned branded clothing online. The platform connects sellers (who
submit items for review) with buyers (who browse, cart, and purchase items).
An administrator verifies accounts, manages listings, and liaises between
parties to ensure delivery.

URL (local development): http://localhost/pastimes/

------------------------------------------------------------------------------
2. SYSTEM REQUIREMENTS
------------------------------------------------------------------------------

  - PHP  >= 8.0
  - MySQL >= 5.7  (or MariaDB >= 10.4)
  - Apache with mod_rewrite enabled  (XAMPP / WAMP recommended)
  - A modern browser (Chrome, Firefox, Edge)

------------------------------------------------------------------------------
3. INSTALLATION & SETUP
------------------------------------------------------------------------------

Step 1 — Copy files
  Place the entire "pastimes" folder inside your web server root:
    XAMPP:   C:\xampp\htdocs\pastimes\
    WAMP:    C:\wamp64\www\pastimes\

Step 2 — Create the database
  Open http://localhost/pastimes/setup.php in your browser.
  This script automatically:
    a) Creates the ClothingStore database.
    b) Drops and recreates all tables (tblUser, tblAdmin, tblClothes,
       tblOrder, tblOrderItem, tblMessages, tblSellRequest).
    c) Seeds each table with realistic sample data.
    d) Creates the default admin account (see Section 4).

  Alternatively, import the SQL file manually via phpMyAdmin:
    database/myClothingStore.sql

Step 3 — Verify the tblUser table
  Open http://localhost/pastimes/createTable.php
  This script drops/recreates tblUser and loads userData.txt (10 users).

Step 4 — Browse the site
  Open http://localhost/pastimes/index.php

------------------------------------------------------------------------------
4. DEFAULT ACCOUNTS
------------------------------------------------------------------------------

  ADMIN
    Email:    admin@pastimes.co.za
    Password: Password1
    URL:      http://localhost/pastimes/admin_login.php

  TEST USERS (all share the same password: "password1")
    Username  | Email
    ----------+----------------------------------
    thabo01   | thabo.nkosi@gmail.com
    naledi01  | naledi.dlamini@webmail.co.za
    jvdb01    | james.vdb@gmail.com
    fatima01  | fatima.patel@outlook.com
    sipho01   | sipho.mokoena@pastimes.co.za

  Note: New registrations start with status 'pending' and must be verified
  by an admin before the user can log in.

------------------------------------------------------------------------------
5. FOLDER STRUCTURE
------------------------------------------------------------------------------

  pastimes/
  |
  |-- index.php               Startup / landing page (eShop intro & goals)
  |-- shop.php                Product catalogue with AddToCart + ShowCart
  |-- cart.php                Shopping cart (uses ShoppingCart OOP class)
  |-- checkout.php            Order confirmation (uses ShoppingCart.Checkout)
  |-- orders.php              Purchase history with grand total
  |-- register.php            New user registration (status: pending)
  |-- login.php               User login (hashed password, sticky form)
  |-- logout.php              Session destroy + redirect
  |-- admin_login.php         Admin authentication (email + hashed password)
  |-- admin.php               Admin panel (CRUD clothes, users; verify accounts)
  |-- admin_logout.php        Admin session destroy
  |-- messages.php            Inbox / compose (buyer-seller communication)
  |-- sell_request.php        Seller submits item for admin review
  |-- orders.php              Buyer purchase history + grand total report
  |-- seller-guide.php        Seller information page
  |-- shipping.php            Shipping policy page
  |-- about.php               About Pastimes page
  |-- contact.php             Contact / FAQ page
  |-- setup.php               One-click database setup (dev use only)
  |-- createTable.php         Drops & recreates tblUser from userData.txt
  |-- loadClothingStore.php   Full DB setup with all tables + seed data
  |-- DBConn.php              Thin wrapper — requires includes/db.php
  |
  |-- classes/
  |   |-- ShoppingCart.php    OOP ShoppingCart class (AddItem, RemoveItem,
  |                           Checkout, EmptyCart, Login, ProcessInput, ...)
  |
  |-- includes/
  |   |-- db.php              MySQLi connection factory (getDB())
  |   |-- session.php         Session helpers (isLoggedIn, addToCart, etc.)
  |   |-- header.php          Shared HTML head + navigation
  |   |-- footer.php          Shared HTML footer + scripts
  |
  |-- css/
  |   |-- style.css           Main stylesheet (responsive, dark/light tokens)
  |
  |-- js/
  |   |-- main.js             UI helpers (dropdown toggle, modal close, etc.)
  |
  |-- images/                 Product images (JPG/PNG, 5+ provided)
  |
  |-- database/
      |-- myClothingStore.sql  Full DDL + 30 rows per table (lecturer import)
      |-- add_new_products.sql  Additional product inserts
      |-- userData.txt          Source file for createTable.php (10 users)

------------------------------------------------------------------------------
6. KEY FEATURES
------------------------------------------------------------------------------

  User / Buyer
    - Register with name, email, username, 8-char password, delivery address
    - Login with username + email + hashed password (MD5)
    - Sticky login form on incorrect password
    - "User [Name] is logged in" confirmation string displayed
    - Browse catalogue with keyword search and filters
    - Add items to cart (quantity increments for duplicate adds)
    - View and edit shopping cart (ShowCart / cart.php)
    - Continue shopping without losing cart contents
    - Checkout produces order reference (PP-XXXXXXXX) + session ID
    - Purchase history report with grand total
    - Send messages to sellers about specific items
    - Submit sell requests (brand, description, image, condition)

  Admin
    - Separate admin login (email + hashed password)
    - Verify new customer registrations (pending → active)
    - Add, edit, delete clothing items with image upload
    - Add, update, delete user accounts
    - Approve / reject sell requests
    - Communicate with buyers and sellers via the messaging system
    - View all pending registrations

  Shopping Cart (OOP class — classes/ShoppingCart.php)
    - AddItem()        — adds or increments quantity
    - RemoveItem()     — removes by clothes_id
    - UpdateQuantity() — sets exact quantity
    - EmptyCart()      — clears all items
    - Login()          — sets authenticated user ID
    - ProcessInput()   — handles POST actions (update/remove/clear)
    - Checkout()       — writes tblOrder + tblOrderItem, decrements stock

------------------------------------------------------------------------------
7. DATABASE TABLES
------------------------------------------------------------------------------

  tblUser       — registered buyers/sellers (status: pending/active/seller)
  tblAdmin      — administrator accounts
  tblClothes    — clothing items for sale
  tblOrder      — order headers (ref, total, session ID, shipping address)
  tblOrderItem  — order line items (links order to clothes + qty + price)
  tblMessages   — buyer-seller-admin messaging
  tblSellRequest — seller item submission requests

------------------------------------------------------------------------------
8. KNOWN LIMITATIONS
------------------------------------------------------------------------------

  - Payment gateway is not integrated; orders are placed as 'pending'.
  - Image upload in sell_request.php requires a writable /images directory.
  - The site uses MD5 for password hashing (as required by the assessment).
    Production systems should use password_hash() / password_verify().

------------------------------------------------------------------------------
9. REFERENCES
------------------------------------------------------------------------------

  - PHP Manual: https://www.php.net/manual/en/
  - MySQLi Prepared Statements: https://www.php.net/manual/en/mysqli.prepare.php
  - Lucide Icons: https://lucide.dev
  - HTML5 Form Validation: https://developer.mozilla.org/en-US/docs/Learn/Forms/Form_validation
  - IEEE referencing style used throughout the report documents.

==============================================================================
  Declaration: All code in this submission is our own original work except
  where referenced inline within individual script files.
==============================================================================
