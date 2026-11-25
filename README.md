# KidSWallet

KidSWallet is a smart wallet that allows parents to manage their children's spendings. 

**Main functions:**
1. Parent registration
2. Child registration (done by parent)
3. Login (via email for parent, via username for child)
4. View parent dashboard
5. View child dashboard
6. Wallet balance and transaction history display

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

## Security-related configuration

**1. Key Storage**

The database connection settings are stored in `connection.php`. Because this prototype is only for local development only, it uses the default `root` username and an empty password. However, for actual development, the database should be secured with strong password and access control.

**2. Environment variables**

The current prototype does not use environment variables. Database connection details are configured directly in `connection.php`. For actual deployment, environment variables can be used to store sensitive configuration values separately from the application source code.

**3. TLS**

The prototype runs locally using XAMPP through `http://localhost`, so HTTPS/TLS is not configured.For actual deployment, HTTPS should be enabled using a valid TLS certificate to encrypt communication between the user's browser and the server.

## Security features implemented

**1. Input Validation**

User input entered through registration page and login page are validated before being sent to the database. This prevents attacks such as SQL injection and XSS. For example, the system checks whether the email is in valid format, and names only include alphabetical letters.

**2. Secure Error Handling**

The system displays meaningful error messages to the user without exposing credentials or internal code. For example, `Invalid email format` or `Incorrect password`. 

**3. Role based access control**

Each user is assigned a role `Parent` or `Child` after authentication to redirect them to the correct dashboard. Dashboard pages also checks the role and ID of the user before allowing them to access the dashboard. The system also only establishes child session after parent approves the login request. This prevents elevation of privileges. 

**4. Parameterized queries**

MySQLi prepared statements such as `prepare()` and `bind_param()` are used before sending user input to the database. This prevents SQL injection. 

**5. Secure password storage**

All passwords and pins are hashed using `password_hash()` before being stored in the database to ensure it is not stored as plain text in the database. 

**6. Tampering protection**

IDs are checked before used in SQL query to retrieve data from the database. This prevents the user from accessing other user's data by changing the ID in the URL. For example, `parent_id` is checked before allowing a parent to approve a child's login request to ensure they do not approve another child's request. `child_id` is also checked to ensure wallet data are fetched based on the correct ID rather than the ID the user changed in the URL. 

**7. Session timeout**

The system automatically logs out the user after 30 minutes of inactive session. Absolute timeout will log the user out of all sessions after 8 hours, requiring them to log in again. 

## Limitations

1. Because this is a prototype, it does not perform actual financial transactions. 
2. This prototype uses a simulated MFA, where it accepts any 6 digit numbers entered. It does not send real OTPs. 
3. Wallet balances and transactions are dummy data.
