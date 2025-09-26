<?php
// sitemap.php
include('db.php');

header("Content-Type: application/xml; charset=UTF-8");

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;

echo '<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

echo '<!-- created with Free Online Sitemap Generator www.xml-sitemaps.com -->' . PHP_EOL;

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

// Dynamic URLs for impact stories
$select = mysqli_query($connection, "SELECT * FROM `portfolio` ORDER BY `updated_at` DESC");
while ($row = mysqli_fetch_array($select)) {
    $c_url = $row["slug"];
    $cdate = date("c", strtotime($row['updated_at']));

    echo '<url>' . PHP_EOL;
    echo '  <loc>' . $base_url . 'portfolio/' . $c_url . '</loc>' . PHP_EOL;
    echo '  <lastmod>' . $cdate . '</lastmod>' . PHP_EOL;
    echo '  <changefreq>weekly</changefreq>' . PHP_EOL;
    echo '  <priority>0.80</priority>' . PHP_EOL;
    echo '</url>' . PHP_EOL;
}

$select = mysqli_query($connection, "SELECT * FROM `products` ORDER BY `updated_at` DESC");
while ($row = mysqli_fetch_array($select)) {
    $c_url = $row["slug"];
    $cdate = date("c", strtotime($row['updated_at']));

    echo '<url>' . PHP_EOL;
    echo '  <loc>' . $base_url . 'portfolio/' . $c_url . '</loc>' . PHP_EOL;
    echo '  <lastmod>' . $cdate . '</lastmod>' . PHP_EOL;
    echo '  <changefreq>weekly</changefreq>' . PHP_EOL;
    echo '  <priority>0.80</priority>' . PHP_EOL;
    echo '</url>' . PHP_EOL;
}
$select = mysqli_query($connection, "SELECT * FROM `service` ORDER BY `updated_at` DESC");
while ($row = mysqli_fetch_array($select)) {
    $c_url = $row["redirection_link"];
    $cdate = date("c", strtotime($row['updated_at']));

    echo '<url>' . PHP_EOL;
    echo '  <loc>' . $base_url . 'portfolio/' . $c_url . '</loc>' . PHP_EOL;
    echo '  <lastmod>' . $cdate . '</lastmod>' . PHP_EOL;
    echo '  <changefreq>weekly</changefreq>' . PHP_EOL;
    echo '  <priority>0.80</priority>' . PHP_EOL;
    echo '</url>' . PHP_EOL;
}

echo '</urlset>';
?>