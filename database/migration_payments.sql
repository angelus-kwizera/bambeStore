-- Migration: Add payment fields to existing orders table
-- Run only if upgrading from an older schema without payment columns

USE bambe;

ALTER TABLE orders
    ADD COLUMN payment_method ENUM('cod', 'paypal') DEFAULT 'cod' AFTER status,
    ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' AFTER payment_method,
    ADD COLUMN paypal_order_id VARCHAR(50) NULL AFTER payment_status,
    ADD COLUMN paypal_capture_id VARCHAR(50) NULL AFTER paypal_order_id;
