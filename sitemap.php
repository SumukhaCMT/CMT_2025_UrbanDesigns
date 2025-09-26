<?php
header('Content-Type: application/xml; charset=UTF-8');
include('db.php');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

$base_url = "https://theurbandesign.in/";
$current_date = date('c');

// Static pages
$static_pages = [
    '',
    'index',
    'about-us',
    'contact-us',
    'services',
    'portfolio'
];

foreach ($static_pages as $page) {
    echo '<url>';
    echo '<loc>' . $base_url . $page . '</loc>';
    echo '<lastmod>' . $current_date . '</lastmod>';
    echo '<priority>0.80</priority>';
    echo '</url>';
}

// Portfolio pages
$query = "SELECT slug, updated_at FROM portfolio WHERE status = 'active'";
$result = mysqli_query($connection, $query);
while ($row = mysqli_fetch_array($result)) {
    echo '<url>';
    echo '<loc>' . $base_url . 'portfolio/' . $row['slug'] . '</loc>';
    echo '<lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>';
    echo '<priority>0.80</priority>';
    echo '</url>';
}

// Product pages
$query = "SELECT slug, updated_at FROM products WHERE status = 'active'";
$result = mysqli_query($connection, $query);
while ($row = mysqli_fetch_array($result)) {
    echo '<url>';
    echo '<loc>' . $base_url . 'products/' . $row['slug'] . '</loc>';
    echo '<lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>';
    echo '<priority>0.80</priority>';
    echo '</url>';
}

// Service pages
$query = "SELECT redirection_link, updated_at FROM service WHERE status = 'active'";
$result = mysqli_query($connection, $query);
while ($row = mysqli_fetch_array($result)) {
    echo '<url>';
    echo '<loc>' . $base_url . 'services/' . $row['redirection_link'] . '</loc>';
    echo '<lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>';
    echo '<priority>0.80</priority>';
    echo '</url>';
}

echo '</urlset>';
?>