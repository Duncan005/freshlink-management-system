-- Add seller_id column to order_items table
ALTER TABLE order_items ADD COLUMN seller_id INT NOT NULL AFTER product_id;
ALTER TABLE order_items ADD FOREIGN KEY (seller_id) REFERENCES users(id);

-- Update existing order_items with seller_id from products
UPDATE order_items oi
JOIN products p ON oi.product_id = p.id
SET oi.seller_id = p.seller_id;