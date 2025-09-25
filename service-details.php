<?php
// Use the same consistent path to your database connection file
require 'admin/shared_components/db.php';

// Get the URL slug from the .htaccess redirect and trim any whitespace
$surl = isset($_GET['surl']) ? trim($_GET['surl']) : '';

if (empty($surl)) {
    header("Location: services");
    exit();
}

// Fetch the current service using the corrected PDO query
$current_service = null;
try {
    // ** FIXED: Removed non-existent 'breadcrumb_title' column from query **
    $stmt_current = $pdo->prepare(
        "SELECT s.id, s.service_title, sd.page_title, sd.detail_image, sd.content_heading, sd.detailed_content 
         FROM service s
         JOIN service_details sd ON s.id = sd.service_id
         WHERE s.redirection_link = ? AND s.status = 'active'"
    );
    $stmt_current->execute([$surl]);
    $current_service = $stmt_current->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Service details query failed: " . $e->getMessage());
    $current_service = null;
}

// If no service is found, show a custom 404 message
if (!$current_service) {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>The service you requested could not be found.</p>";
    exit();
}

// Fetch all other services for the sidebar navigation
$other_services = [];
try {
    $current_service_id = $current_service['id'];
    $stmt_other = $pdo->prepare(
        "SELECT service_title, redirection_link FROM service WHERE status = 'active' AND id != ? ORDER BY sort_order ASC"
    );
    $stmt_other->execute([$current_service_id]);
    $other_services = $stmt_other->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Other services query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head><?php include 'head.php'?></head>

<body>
    <div class="page ttm-bgcolor-grey">
    <?php include 'header.php'?>
     <div class="ttm-page-title-row">
                <div class="ttm-page-title-row-inner">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-12">
                                <div class="page-title-heading">
                                    <h2 class="title"><?php echo htmlspecialchars($current_service['page_title']); ?></h2>
                                </div>
                                <div class="breadcrumb-wrapper">
                                    <div class="container">
                                        <div class="breadcrumb-wrapper-inner">
                                            <span>
                                                <a title="Go to The Urban Design." href="./" class="home"><i class="themifyicon ti-home"></i>&nbsp;&nbsp;Home</a>
                                            </span>
                                            <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                            <span><?php echo htmlspecialchars($current_service['page_title']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                    
            </div>

        <div class="site-main">
            <div class="ttm-row sidebar ttm-sidebar-left pb-45 ttm-bgcolor-white clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4 widget-area sidebar-left">
                            <aside class="widget widget-nav-menu">
                                <h3 class="widget-title">More Services</h3>
                                <ul class="widget-menu">
                                     <li class="active">
                                        <a href="services/<?php echo htmlspecialchars($surl); ?>"><?php echo htmlspecialchars($current_service['service_title']); ?></a>
                                     </li>
                                     <?php foreach ($other_services as $other_service): ?>
                                        <li>
                                            <a href="services/<?php echo htmlspecialchars($other_service['redirection_link']); ?>">
                                                <?php echo htmlspecialchars($other_service['service_title']); ?>
                                            </a>
                                        </li>
                                     <?php endforeach; ?>
                                </ul>
                            </aside>
                        </div>
                        <div class="col-lg-8 content-area">
                            <article class="ttm-service-single-content-area">
                                <div class="ttm-service-featured-wrapper ttm-featured-wrapper">
                                    <div class="ttm_single_image-wrapper mb-40 res-991-mb-30">
                                        <img width="1200" height="800" class="img-fluid" 
                                             src="assets/img/service/<?php echo htmlspecialchars($current_service['detail_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($current_service['content_heading']); ?>">
                                    </div>
                                </div>
                                <div class="ttm-service-classic-content">
                                    <h2><?php echo htmlspecialchars($current_service['content_heading']); ?></h2>
                                    
                                    <?php echo $current_service['detailed_content']; ?>
                                </div>
                            </article>
                        </div>
                    </div></div>
            </div>
        </div><?php include 'footer.php'?>
</div>
    <?php include 'scripts.php'?>
</body>
</html>