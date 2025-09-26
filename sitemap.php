<?php
// Clean start - prevent any output
if (ob_get_level())
    ob_end_clean();
ob_start();

// Suppress all errors for clean XML output
error_reporting(0);
ini_set('display_errors', 0);

// Include database
include('db.php');

// Set headers
header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: no-cache');

// Generate XML content
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$base_url = "https://theurbandesign.in/";

// Static pages
$static_pages = [
    '' => '1.00',
    'index' => '0.80',
    'about-us' => '0.80',
    'contact-us' => '0.80'
];

foreach ($static_pages as $page => $priority) {
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . $base_url . $page . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('c') . '</lastmod>' . "\n";
    $xml .= '    <priority>' . $priority . '</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

// Add dynamic pages if database connection works
if (isset($connection) && $connection && !mysqli_connect_error()) {

    // Portfolio pages
    $result = mysqli_query($connection, "SELECT slug, updated_at FROM portfolio WHERE status = 'active' ORDER BY updated_at DESC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['slug'])) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . $base_url . 'portfolio/' . htmlspecialchars($row['slug']) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>' . "\n";
                $xml .= '    <changefreq>weekly</changefreq>' . "\n";
                $xml .= '    <priority>0.80</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }
        }
        mysqli_free_result($result);
    }

    // Product pages
    $result = mysqli_query($connection, "SELECT slug, updated_at FROM products WHERE status = 'active' ORDER BY updated_at DESC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['slug'])) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . $base_url . 'products/' . htmlspecialchars($row['slug']) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>' . "\n";
                $xml .= '    <changefreq>weekly</changefreq>' . "\n";
                $xml .= '    <priority>0.80</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }
        }
        mysqli_free_result($result);
    }

    // Service pages  
    $result = mysqli_query($connection, "SELECT redirection_link, updated_at FROM service WHERE status = 'active' ORDER BY updated_at DESC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['redirection_link'])) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . $base_url . 'services/' . htmlspecialchars($row['redirection_link']) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>' . "\n";
                $xml .= '    <changefreq>weekly</changefreq>' . "\n";
                $xml .= '    <priority>0.80</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }
        }
        mysqli_free_result($result);
    }
}

$xml .= '</urlset>';

// Clean output and send XML
ob_clean();
echo $xml;
exit;