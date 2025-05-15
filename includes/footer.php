    </div>
    <!-- Footer is now included directly in index.php for the homepage -->
    <?php if (basename($_SERVER['PHP_SELF']) !== 'index.php'): ?>
    <footer class="bg-green-600 text-white p-4 mt-8">
        <div class="container mx-auto text-center">
            <p>&copy; <?php echo date('Y'); ?> FreshLink Management System. All rights reserved.</p>
        </div>
    </footer>
    <?php endif; ?>
</body>
</html>