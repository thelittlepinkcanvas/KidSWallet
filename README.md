# KidSWallet

KidSWallet is a smart wallet that allows parents to manage their children's spendings. 

**Main functions:**
1. Parent registration
2. Child registration (done by parent)
3. Login (via email for parent, via username for child)
4. View parent dashboard
5. View child dashboard
6. Wallet balance and transaction history display


***Note:***
- Because this is a prototype, it does not perform actual financial transactions. Wallet and transaction history in the database are dummy data. 
- This prototype uses a simulated MFA, where it accepts any 6 digit numbers entered. 

## Technology Used

| Technology | Purpose |
| ----------- | ----------- |
| HTML | Frontend |
| CSS | Website styling |
| Javascript | Frontend interactions |
| PHP | Backend processing and authentication |
| MySQL | Database storage |
| XAMPP | Local Apache and MySQL server |
| phpmyadmin | Database management |

## System Requirements

Before setting up KidSWallet, the following applications are needed:
1. XAMPP
2. Visual Studio Code
3. A web browser

## Setup

**Step 1: Download the project**
1. Download the zip file containing KidSWallet.
2. Extract the folder. 

**Step 2: Place project in XAMPP**
1. Move the KidSWallet folder to the htdocs folder in XAMPP. The path should be as follows:
`C:\xampp\htdocs\KidSWallet`

**Step 3: Start XAMPP**
1. Open XAMPP application
2. Start Apache
3. Start MySQL

**Step 4: Create database**
1. Click "admin" beside MySQL on XAMPP, phpmyadmin will be open in the web browser.
2. Create a database called "kidswallet". 
3. Import the SQL file given in sql folder of KidSWallet. 

## How to run the prototype

1. Go to the web browser and enter the URL: `http://localhost/KidSWallet/login.php`. It should redirect to the KidSWallet login page. 
2. Create an account through the registration page. If you already have an account, log in.

