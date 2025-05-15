# FreshLink Management System

FreshLink is an online sales platform focused on agricultural products. It connects customers directly with farmers and sellers, allowing users to browse and purchase fresh produce while enabling farmers/sellers to manage their stock.

## Features

- User authentication (customer, seller, admin roles)
- Product listings with search and filtering
- Stock management for sellers
- Order processing
- Responsive design using Tailwind CSS

## Technologies Used

- HTML
- CSS (Tailwind CSS)
- JavaScript
- PHP
- MySQL

## Installation

1. Clone the repository to your local XAMPP htdocs folder:
   ```
   git clone https://github.com/yourusername/freshlink.git
   ```

2. Create a MySQL database named `freshlink`

3. Import the database schema:
   ```
   mysql -u root -p freshlink < database.sql
   ```

4. Configure the database connection in `config/database.php` if needed

5. Access the application through your web browser:
   ```
   http://localhost/Freshlink%20Management%20System/
   ```

## Project Structure

- `assets/` - Contains CSS, JavaScript, and image files
- `config/` - Configuration files
- `includes/` - Reusable PHP components
- `pages/` - Main application pages
- `api/` - API endpoints for AJAX functionality
- `database.sql` - Database schema

## Usage

### For Customers
- Register/login as a customer
- Browse products by category
- Add products to cart
- Place orders

### For Sellers
- Register/login as a seller
- Add and manage products
- Update stock quantities
- View orders

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- [Tailwind CSS](https://tailwindcss.com/)
- [XAMPP](https://www.apachefriends.org/)
- [PHP](https://www.php.net/)
- [MySQL](https://www.mysql.com/)