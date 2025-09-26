<?php
// Prevent any output before XML starts
ob_start();
ob_clean();

// Suppress any errors/warnings that could contaminate XML output
error_reporting(0);
ini_set('display_errors', 0);

// Include database connection
include('db.php');

// Clear any output that db.php might have generated
ob_clean();

// Set proper headers
header("Content-Type: application/xml; charset=UTF-8");
header("Cache-Control: no-cache, must-revalidate");

// Start XML output
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n";

echo '<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
echo "\n";

$base_url = "https://theurbandesign.in/";

// Static URLs
$static_urls = [
    ['loc' => '', 'priority' => '1.00'],
    ['loc' => 'index', 'priority' => '0.80'],
    ['loc' => 'about-us', 'priority' => '0.80'],
    ['loc' => 'contact-us', 'priority' => '0.80']
];

foreach ($static_urls as $url) {
    echo '<url>';
    echo "\n";
    echo '  <loc>' . $base_url . htmlspecialchars($url['loc']) . '</loc>';
    echo "\n";
    echo '  <lastmod>' . date("c") . '</lastmod>';
    echo "\n";
    echo '  <priority>' . $url['priority'] . '</priority>';
    echo "\n";
    echo '</url>';
    echo "\n";
}

// Check if database connection exists and is working
if (isset($connection) && $connection && !mysqli_connect_error()) {

    // Portfolio URLs
    $query = "SELECT `slug`, `updated_at` FROM `portfolio` WHERE `status` = 'active' ORDER BY `updated_at` DESC LIMIT 1000";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['slug'])) {
                echo '<url>';
                echo "\n";
                echo '  <loc>' . $base_url . 'portfolio/' . htmlspecialchars($row['slug']) . '</loc>';
                echo "\n";
                echo '  <lastmod>' . date("c", strtotime($row['updated_at'])) . '</lastmod>';
                echo "\n";
                echo '  <changefreq>weekly</changefreq>';
                echo "\n";
                echo '  <priority>0.80</priority>';
                echo "\n";
                echo '</url>';
                echo "\n";
            }
        }
        mysqli_free_result($result);
    }

    // Products URLs
    $query = "SELECT `slug`, `updated_at` FROM `products` WHERE `status` = 'active' ORDER BY `updated_at` DESC LIMIT 1000";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['slug'])) {
                echo '<url>';
                echo "\n";
                echo '  <loc>' . $base_url . 'products/' . htmlspecialchars($row['slug']) . '</loc>';
                echo "\n";
                echo '  <lastmod>' . date("c", strtotime($row['updated_at'])) . '</lastmod>';
                echo "\n";
                echo '  <changefreq>weekly</changefreq>';
                echo "\n";
                echo '  <priority>0.80</priority>';
                echo "\n";
                echo '</url>';
                echo "\n";
            }
        }
        mysqli_free_result($result);
    }

    // Services URLs
    $query = "SELECT `redirection_link`, `updated_at` FROM `service` WHERE `status` = 'active' ORDER BY `updated_at` DESC LIMIT 1000";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['redirection_link'])) {
                echo '<url>';
                echo "\n";
                echo '  <loc>' . $base_url . 'services/' . htmlspecialchars($row['redirection_link']) . '</loc>';
                echo "\n";
                echo '  <lastmod>' . date("c", strtotime($row['updated_at'])) . '</lastmod>';
                echo "\n";
                echo '  <changefreq>weekly</changefreq>';
                echo "\n";
                echo '  <priority>0.80</priority>';
                echo "\n";
                echo '</url>';
                echo "\n";
            }
        }
        mysqli_free_result($result);
    }
}

// Close XML
echo '</urlset>';

// Ensure clean exit
ob_end_flush();
exit;