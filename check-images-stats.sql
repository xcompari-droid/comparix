SELECT COUNT(*) as total_products FROM products;
SELECT COUNT(*) as local_storage FROM products WHERE image_url LIKE '/storage%';
SELECT COUNT(*) as remote_http FROM products WHERE image_url LIKE 'http%';
SELECT COUNT(*) as images_folder FROM products WHERE image_url LIKE '/images%';
