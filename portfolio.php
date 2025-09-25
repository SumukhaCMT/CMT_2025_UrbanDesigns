<?php $currentPage = 'portfolio'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.php' ?>
</head>

<body>
    <div class="page ttm-bgcolor-grey">
        <?php
        include 'header.php';
        include 'db.php'; // Your frontend DB connection
        
        // Fetch categories for the filter tabs
        $categories_sql = "SELECT name, slug FROM portfolio_categories ORDER BY id ASC";
        $categories_result = $connection->query($categories_sql);
        $categories = [];
        if ($categories_result) {
            while ($row = $categories_result->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        // Fetch all active portfolio projects
        $projects_sql = "SELECT p.project_title, p.slug, p.short_description, p.list_image, c.slug as category_slug
                         FROM portfolio p
                         JOIN portfolio_categories c ON p.category_id = c.id
                         WHERE p.status = 'active'
                         ORDER BY p.id DESC";
        $projects_result = $connection->query($projects_sql);
        ?>

        <div class="ttm-page-title-row">
            <div class="ttm-page-title-row-inner">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="page-title-heading">
                                <h2 class="title">Portfolio</h2>
                            </div>
                            <div class="breadcrumb-wrapper">
                                <div class="container">
                                    <div class="breadcrumb-wrapper-inner">
                                        <span>
                                            <a title="Go to Delmont." href="./" class="home"><i
                                                    class="themifyicon ti-home"></i>&nbsp;&nbsp;Home</a>
                                        </span>
                                        <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                        <span>Portfolio</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="site-main">
            <section class="ttm-row pf-section clearfix">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ttm-tabs ttm-tab-style-02 mb_15">
                                <ul class="tabs portfolio-filter text-center">
                                    <li class="tab active"><a href="#" data-filter="*">All Projects</a></li>
                                    <?php foreach ($categories as $cat): ?>
                                        <li class="tab"><a href="#"
                                                data-filter=".<?php echo htmlspecialchars($cat['slug']); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="content-tab">
                                    <div class="row ttm-boxes-spacing-30px isotope-project">
                                        <?php if ($projects_result && $projects_result->num_rows > 0): ?>
                                            <?php while ($project = $projects_result->fetch_assoc()): ?>
                                                <div
                                                    class="col-lg-3 col-md-6 project_item <?php echo htmlspecialchars($project['category_slug']); ?>">
                                                    <div class="featured-imagebox featured-imagebox-portfolio style3">
                                                        <div class="featured-thumbnail portfolio-image-container">
                                                            <img width="610" height="750" class="img-fluid"
                                                                src="uploads/portfolio/<?php echo htmlspecialchars($project['list_image']); ?>"
                                                                alt="<?php echo htmlspecialchars($project['project_title']); ?>">
                                                        </div>
                                                        <div class="featured-content-inner">
                                                            <div class="featured-content">
                                                                <div class="featured-title">
                                                                    <h3><a
                                                                            href="portfolio/<?php echo htmlspecialchars($project['slug']); ?>"><?php echo htmlspecialchars($project['project_title']); ?></a>
                                                                    </h3>
                                                                </div>
                                                                <div class="featured-desc">
                                                                    <p><?php echo htmlspecialchars($project['short_description']); ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="ttm-footer">
                                                                <a class="ttm-btn btn-inline ttm-btn-size-md ttm-icon-btn-right ttm-btn-color-dark"
                                                                    href="portfolio/<?php echo htmlspecialchars($project['slug']); ?>">Read
                                                                    More<i class="ti ti-plus"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <p class="text-center w-100">No portfolio projects found.</p>
                                        <?php endif;
                                        $connection->close(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </section>
        </div><?php include 'footer.php'; ?>
    </div>
    <?php include 'scripts.php'; ?>
</body>

</html>x