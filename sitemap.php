<?php
// Prevent any output before headers
ob_start();

// Include database connection
include('db.php');

// Check if connection exists
if (!$connection || $connection->connect_errno) {
    http_response_code(500);
    exit('Database connection failed');
}

// Set proper headers
header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate');

// Clear any previous output
ob_clean();

// Start XML output
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$base_url = "https://theurbandesign.in/";

// Static pages
$static_pages = [
    '' => '1.00',
    'index' => '0.80',
    'about-us' => '0.80',
    'contact-us' => '0.80',
    'services' => '0.80',
    'portfolio' => '0.80'
];

foreach ($static_pages as $page => $priority) {
    echo '  <url>' . "\n";
    echo '    <loc>' . $base_url . $page . '</loc>' . "\n";
    echo '    <lastmod>' . date('c') . '</lastmod>' . "\n";
    echo '    <priority>' . $priority . '</priority>' . "\n";
    echo '  </url>' . "\n";
}

// Portfolio pages
$query = "SELECT slug, updated_at FROM portfolio WHERE status = 'active' ORDER BY updated_at DESC";
$result = $connection->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['slug'])) {
            echo '  <url>' . "\n";
            echo '    <loc>' . $base_url . 'portfolio/' . htmlspecialchars($row['slug'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";
            echo '    <lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>' . "\n";
            echo '    <changefreq>weekly</changefreq>' . "\n";
            echo '    <priority>0.80</priority>' . "\n";
            echo '  </url>' . "\n";
        }
    }
    $result->free();
}

// Product pages
$query = "SELECT slug, updated_at FROM products WHERE status = 'active' ORDER BY updated_at DESC";
$result = $connection->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['slug'])) {
            echo '  <url>' . "\n";
            echo '    <loc>' . $base_url . 'products/' . htmlspecialchars($row['slug'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";
            echo '    <lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>' . "\n";
            echo '    <changefreq>weekly</changefreq>' . "\n";
            echo '    <priority>0.80</priority>' . "\n";
            echo '  </url>' . "\n";
        }
    }
    $result->free();
}

// Service pages
$query = "SELECT redirection_link, updated_at FROM service WHERE status = 'active' ORDER BY updated_at DESC";
$result = $connection->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['redirection_link'])) {
            echo '  <url>' . "\n";
            echo '    <loc>' . $base_url . 'services/' . htmlspecialchars($row['redirection_link'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";
            echo '    <lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>' . "\n";
            echo '    <changefreq>weekly</changefreq>' . "\n";
            echo '    <priority>0.80</priority>' . "\n";
            echo '  </url>' . "\n";
        }
    }
    $result->free();
}

// Close XML
echo '</urlset>' . "\n";

// Close database connection
$connection->close();

// Send output and exit
ob_end_flush();
exit;
?>