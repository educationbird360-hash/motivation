# Motivation App

Simple PHP MLM-like app with admin/member login, wallet payments, and passbook tracking.

## Setup

1. Put this folder in your PHP server root (e.g. XAMPP `htdocs`).
2. Create a MySQL database named `motivation`.
3. Run SQL for tables `users` and `payments`.
4. Open `index.php` and create the first admin account using `signup.php`.

## Database schema

```sql
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_no` INT UNSIGNED NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `role` ENUM('admin','member') NOT NULL DEFAULT 'member',
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `wallet_balance` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_account_no` (`account_no`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `account_no` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_mode` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `transaction_type` VARCHAR(20) NOT NULL DEFAULT 'debit',
  `balance_before` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `balance_after` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `processed_by` INT UNSIGNED DEFAULT NULL,
  `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_account_no` (`account_no`),
  KEY `idx_processed_by` (`processed_by`),
  CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
  ,CONSTRAINT `fk_payments_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Existing database migration

If the `payments` table already exists, run this before deploying the updated payment and passbook files:

```sql
ALTER TABLE payments
  ADD COLUMN transaction_type VARCHAR(20) NOT NULL DEFAULT 'debit' AFTER description,
  ADD COLUMN balance_before DECIMAL(12,2) NOT NULL DEFAULT '0.00' AFTER transaction_type,
  ADD COLUMN balance_after DECIMAL(12,2) NOT NULL DEFAULT '0.00' AFTER balance_before,
  ADD COLUMN processed_by INT UNSIGNED DEFAULT NULL AFTER balance_after,
  ADD KEY idx_processed_by (processed_by),
  ADD CONSTRAINT fk_payments_processed_by FOREIGN KEY (processed_by) REFERENCES users(id);
```

## Improvements made

- central auth helper functions
- safer session cookie settings
- login password verification fixed
- safer payment transaction handling
- reusable alert helper
- added README and helpers file
