<?php
include('db.php');
header('Content-Type: application/xml; charset=UTF-8');
?>
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://theurbandesign.in/</loc>
    <lastmod><?php echo date('c'); ?></lastmod>
    <priority>1.00</priority>
  </url>
  <url>
    <loc>https://theurbandesign.in/index</loc>
    <lastmod><?php echo date('c'); ?></lastmod>
    <priority>0.80</priority>
  </url>
  <url>
    <loc>https://theurbandesign.in/about-us</loc>
    <lastmod><?php echo date('c'); ?></lastmod>
    <priority>0.80</priority>
  </url>
  <url>
    <loc>https://theurbandesign.in/contact-us</loc>
    <lastmod><?php echo date('c'); ?></lastmod>
    <priority>0.80</priority>
  </url>
  <url>
    <loc>https://theurbandesign.in/services</loc>
    <lastmod><?php echo date('c'); ?></lastmod>
    <priority>0.80</priority>
  </url>
  <url>
    <loc>https://theurbandesign.in/portfolio</loc>
    <lastmod><?php echo date('c'); ?></lastmod>
    <priority>0.80</priority>
  </url>
<?php
if ($connection && !$connection->connect_errno) {
    
    // Portfolio pages
    $query = "SELECT slug, updated_at FROM portfolio WHERE status = 'active'";
    $result = $connection->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo '  <url>' . "\n";
            echo '    <loc>https://theurbandesign.in/portfolio/' . htmlspecialchars($row['slug']) . '</loc>' . "\n";
            echo '    <lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>' . "\n";
            echo '    <priority>0.80</priority>' . "\n";
            echo '  </url>' . "\n";
        }
        $result->free();
    }
    
    // Product pages  
    $query = "SELECT slug, updated_at FROM products WHERE status = 'active'";
    $result = $connection->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo '  <url>' . "\n";
            echo '    <loc>https://theurbandesign.in/products/' . htmlspecialchars($row['slug']) . '</loc>' . "\n";
            echo '    <lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>' . "\n";
            echo '    <priority>0.80</priority>' . "\n";
            echo '  </url>' . "\n";
        }
        $result->free();
    }
    
    // Service pages
    $query = "SELECT redirection_link, updated_at FROM service WHERE status = 'active'";
    $result = $connection->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo '  <url>' . "\n";
            echo '    <loc>https://theurbandesign.in/services/' . htmlspecialchars($row['redirection_link']) . '</loc>' . "\n";
            echo '    <lastmod>' . date('c', strtotime($row['updated_at'])) . '</lastmod>' . "\n";
            echo '    <priority>0.80</priority>' . "\n";
            echo '  </url>' . "\n";
        }
        $result->free();
    }
    
    $connection->close();
}
?>
</urlset>
