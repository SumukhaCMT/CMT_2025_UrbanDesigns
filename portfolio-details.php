<?php
include 'db.php'; // Your frontend DB connection

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (empty($slug)) {
    header("Location: portfolio");
    exit();
}

// Fetch current project details
$stmt = $connection->prepare("SELECT p.*, c.name as category_name FROM portfolio p JOIN portfolio_categories c ON p.category_id = c.id WHERE p.slug = ? AND p.status = 'active'");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo "Project not found.";
    exit();
}
$project = $result->fetch_assoc();
$stmt->close();

// Fetch gallery images
$gallery_stmt = $connection->prepare("SELECT image_name, alt_text FROM portfolio_gallery_images WHERE project_id = ?");
$gallery_stmt->bind_param("i", $project['id']);
$gallery_stmt->execute();
$gallery_result = $gallery_stmt->get_result();
$gallery_images = [];
while ($row = $gallery_result->fetch_assoc()) {
    $gallery_images[] = $row;
}
$gallery_stmt->close();

// Fetch previous and next projects for navigation
$prev_stmt = $connection->prepare("SELECT slug, project_title FROM portfolio WHERE id < ? AND status = 'active' ORDER BY id DESC LIMIT 1");
$prev_stmt->bind_param("i", $project['id']);
$prev_stmt->execute();
$prev_project = $prev_stmt->get_result()->fetch_assoc();
$prev_stmt->close();

$next_stmt = $connection->prepare("SELECT slug, project_title FROM portfolio WHERE id > ? AND status = 'active' ORDER BY id ASC LIMIT 1");
$next_stmt->bind_param("i", $project['id']);
$next_stmt->execute();
$next_project = $next_stmt->get_result()->fetch_assoc();
$next_stmt->close();

$connection->close();
?>
<!DOCTYPE html>
<html lang="en">

<head><?php include 'head.php' ?></head>

<body>
    <div class="page ttm-bgcolor-grey">
        <?php include 'header.php' ?>
        <div class="ttm-page-title-row">
            <div class="ttm-page-title-row-inner">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="page-title-heading">
                                <h2 class="title"><?php echo htmlspecialchars($project['project_title']); ?></h2>
                            </div>
                            <div class="breadcrumb-wrapper">
                                <div class="container">
                                    <div class="breadcrumb-wrapper-inner">
                                        <span><a title="Go to The Urban Design." href="./" class="home"><i
                                                    class="themifyicon ti-home"></i>&nbsp;&nbsp;Home</a></span>
                                        <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                        <span><a title="Go to Portfolio." href="portfolio">Portfolio</a></span>
                                        <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                        <span><?php echo htmlspecialchars($project['project_title']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="site-main">
            <section class="ttm-row project-single-section clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ttm-pf-single-content-wrapper-innerbox ttm-pf-view-top-image">
                                <div class="ttm-pf-single-content-wrapper">
                                    <div class="ttm_single_image-wrapper">
                                        <img width="1200" height="800" class="img-fluid"
                                            src="uploads/portfolio/<?php echo htmlspecialchars($project['detail_image']); ?>"
                                            alt="<?php echo htmlspecialchars($project['project_title']); ?>">
                                    </div>
                                    <div class="ttm-pf-single-detail-box ttm-bgcolor-darkgrey ttm-textcolor-white"
                                        style="background:#07332F;">
                                        <h2 class="ttm-pf-detailbox-title">Project Details:</h2>
                                        <ul class="ttm-pf-detailbox-list">
                                            <li>
                                                <div class="ttm-pf-data-title">Project</div>
                                                <div class="ttm-pf-data-details">
                                                    <?php echo htmlspecialchars($project['project_title']); ?>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="ttm-pf-data-title">Category</div>
                                                <div class="ttm-pf-data-details">
                                                    <?php echo htmlspecialchars($project['category_name']); ?>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="ttm-pf-data-title">Clients</div>
                                                <div class="ttm-pf-data-details">
                                                    <?php echo htmlspecialchars($project['client_name']); ?>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="ttm-pf-data-title">Location</div>
                                                <div class="ttm-pf-data-details">
                                                    <?php echo htmlspecialchars($project['location']); ?>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="ttm-pf-data-title">Project Year</div>
                                                <div class="ttm-pf-data-details">
                                                    <?php echo htmlspecialchars($project['project_year']); ?>
                                                </div>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                                <div class="ttm-pf-single-content-area">
                                    <h2><?php echo htmlspecialchars($project['content_heading']); ?></h2>
                                    <?php echo $project['detailed_content']; // This content comes from CKEditor, so it's expected to have HTML ?>

                                    <?php if (!empty($gallery_images)): ?>
                                        <div class="row mt-25 mb-30">
                                            <?php foreach ($gallery_images as $image): ?>
                                                <div class="col-lg-4 col-md-6 col-sm-12">
                                                    <div
                                                        class="ttm_single_image-wrapper mt-15 mb-15 res-991-mt-20 detail-gallery-image-container">
                                                        <img width="580" height="610" class="img-fluid"
                                                            src="uploads/portfolio/gallery/<?php echo htmlspecialchars($image['image_name']); ?>"
                                                            alt="<?php echo htmlspecialchars($image['alt_text']); ?>">
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="ttm-nextprev-bottom-nav d-flex justify-content-between">
                                                <?php if ($prev_project): ?>
                                                    <a class="ttm-btn ttm-btn-size-md ttm-btn-shape-squar ttm-btn-style-fill ttm-btn-color-grey"
                                                        href="portfolio/<?php echo $prev_project['slug']; ?>">Previous</a>
                                                <?php else: ?>
                                                    <span></span> <?php endif; ?>

                                                <?php if ($next_project): ?>
                                                    <a class="ttm-btn ttm-btn-size-md ttm-btn-shape-squar ttm-icon-btn-right ttm-btn-style-fill ttm-btn-color-grey"
                                                        href="portfolio/<?php echo $next_project['slug']; ?>">Next</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div><?php include 'footer.php' ?>
    </div>
    <?php include 'scripts.php' ?>


</body>

</html>