<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php'; // Adjust path if needed

// Fetch Categories
$categories_stmt = $pdo->query("SELECT * FROM product_categories ORDER BY name ASC");
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Products
$products_stmt = $pdo->query("SELECT p.*, pc.slug as category_slug FROM products p JOIN product_categories pc ON p.category_id = pc.id WHERE p.status = 'active' ORDER BY p.created_at DESC");
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
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
                                <h2 class="title">Our Products</h2>
                            </div>
                            <div class="breadcrumb-wrapper">
                                <div class="container">
                                    <div class="breadcrumb-wrapper-inner">
                                        <span><a title="Go to Home." href="./" class="home"><i
                                                    class="themifyicon ti-home"></i>&nbsp;&nbsp;Home</a></span>
                                        <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                        <span>Products</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="site-main">
            <section class="ttm-row filter-section clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ttm-tabs ttm-tab-style-02 mb_15">
                                <ul class="tabs portfolio-filter text-center">
                                    <li class="tab active"><a href="#" data-filter="*">All Products</a></li>
                                    <?php foreach ($categories as $cat): ?>
                                        <li class="tab"><a href="#"
                                                data-filter=".<?php echo htmlspecialchars($cat['slug']); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="content-tab">
                                    <div class="row ttm-boxes-spacing-30px isotope-project">
                                        <?php if (!empty($products)): ?>
                                            <?php foreach ($products as $product): ?>
                                                <div
                                                    class="col-lg-4 col-md-6 col-sm-6 project_item <?php echo htmlspecialchars($product['category_slug']); ?>">
                                                    <div class="featured-imagebox featured-imagebox-portfolio style5">
                                                        <div class="ttm-box-view-overlay">
                                                            <div class="featured-thumbnail">
                                                                <img width="610" height="750" class="img-fluid"
                                                                    src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>"
                                                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                            </div>
                                                            <div class="ttm-footer">
                                                                <a class="ttm-btn btn-inline ttm-btn-size-md ttm-icon-btn-right ttm-btn-color-skincolor"
                                                                    href="products/<?php echo htmlspecialchars($product['slug']); ?>"><i
                                                                        class="fa fa-long-arrow-right"></i></a>
                                                            </div>
                                                            <div class="featured-content">
                                                                <div class="featured-title">
                                                                    <h3><a
                                                                            href="products/<?php echo htmlspecialchars($product['slug']); ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                                                                    </h3>
                                                                </div>
                                                                <div class="product-price"><span
                                                                        class="price">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-center w-100">No products found.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <?php include 'footer.php' ?>
    </div>
    <?php include 'scripts.php' ?>
</body>

</html>