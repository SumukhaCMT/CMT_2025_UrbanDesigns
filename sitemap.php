<?php
// sitemap.php
include('db.php');

// Set content type and start output buffering to catch any errors
ob_start();
header("Content-Type: application/xml; charset=UTF-8");

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;

echo '<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

// Check if database connection exists
if (!isset($connection) || !$connection) {
    // If no database connection, just output static URLs and close
    error_log("Database connection not available in sitemap.php");
} else {
    // Static URLs
    $base_url = "https://theurbandesign.in/";

    $static_urls = [
        ['loc' => '', 'priority' => '1.00'],
        ['loc' => 'index', 'priority' => '0.80'],
        ['loc' => 'about-us', 'priority' => '0.80'],
        ['loc' => 'contact-us', 'priority' => '0.80']
    ];

    foreach ($static_urls as $url) {
        echo '<url>' . PHP_EOL;
        echo '  <loc>' . $base_url . $url['loc'] . '</loc>' . PHP_EOL;
        echo '  <lastmod>' . date("c") . '</lastmod>' . PHP_EOL;
        echo '  <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
        echo '</url>' . PHP_EOL;
    }

    // Dynamic URLs for portfolio
    $select = mysqli_query($connection, "SELECT `slug`, `updated_at` FROM `portfolio` WHERE `status` = 'active' ORDER BY `updated_at` DESC");
    if ($select) {
        while ($row = mysqli_fetch_array($select)) {
            $c_url = htmlspecialchars($row["slug"]);
            $cdate = date("c", strtotime($row['updated_at']));
            echo '<url>' . PHP_EOL;
            echo '  <loc>' . $base_url . 'portfolio/' . $c_url . '</loc>' . PHP_EOL;
            echo '  <lastmod>' . $cdate . '</lastmod>' . PHP_EOL;
            echo '  <changefreq>weekly</changefreq>' . PHP_EOL;
            echo '  <priority>0.80</priority>' . PHP_EOL;
            echo '</url>' . PHP_EOL;
        }
        mysqli_free_result($select);
    } else {
        error_log("Portfolio query failed: " . mysqli_error($connection));
    }

    // Dynamic URLs for products
    $select = mysqli_query($connection, "SELECT `slug`, `updated_at` FROM `products` WHERE `status` = 'active' ORDER BY `updated_at` DESC");
    if ($select) {
        while ($row = mysqli_fetch_array($select)) {
            $c_url = htmlspecialchars($row["slug"]);
            $cdate = date("c", strtotime($row['updated_at']));
            echo '<url>' . PHP_EOL;
            echo '  <loc>' . $base_url . 'products/' . $c_url . '</loc>' . PHP_EOL;
            echo '  <lastmod>' . $cdate . '</lastmod>' . PHP_EOL;
            echo '  <changefreq>weekly</changefreq>' . PHP_EOL;
            echo '  <priority>0.80</priority>' . PHP_EOL;
            echo '</url>' . PHP_EOL;
        }
        mysqli_free_result($select);
    } else {
        error_log("Products query failed: " . mysqli_error($connection));
    }

    // Dynamic URLs for services
    $select = mysqli_query($connection, "SELECT `redirection_link`, `updated_at` FROM `service` WHERE `status` = 'active' ORDER BY `updated_at` DESC");
    if ($select) {
        while ($row = mysqli_fetch_array($select)) {
            $c_url = htmlspecialchars($row["redirection_link"]);
            $cdate = date("c", strtotime($row['updated_at']));
            echo '<url>' . PHP_EOL;
            echo '  <loc>' . $base_url . 'services/' . $c_url . '</loc>' . PHP_EOL;
            echo '  <lastmod>' . $cdate . '</lastmod>' . PHP_EOL;
            echo '  <changefreq>weekly</changefreq>' . PHP_EOL;
            echo '  <priority>0.80</priority>' . PHP_EOL;
            echo '</url>' . PHP_EOL;
        }
        mysqli_free_result($select);
    } else {
        error_log("Services query failed: " . mysqli_error($connection));
    }

    // Close database connection if we opened it
    if (isset($connection) && $connection) {
        mysqli_close($connection);
    }
}

// Always close the urlset tag
echo '</urlset>' . PHP_EOL;

// Flush output buffer
ob_end_flush();
?>