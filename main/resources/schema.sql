
CREATE TABLE IF NOT EXISTS users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phno VARCHAR(20) NULL,
    secret VARCHAR(255) NOT NULL,
    role ENUM('PARENT', 'CHILD') NOT NULL,
    parent_email VARCHAR(150) NULL,
    balance DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    monthly_budget DECIMAL(10, 2) NOT NULL DEFAULT 150.00,
    total_spending DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_parent_email
        FOREIGN KEY (parent_email) REFERENCES users(email)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(150) NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    type ENUM('CREDIT', 'DEBIT') NOT NULL,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaction_user
        FOREIGN KEY (user_email) REFERENCES users(email)
        ON DELETE CASCADE
);
