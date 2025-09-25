<?php
include 'admin/shared_components/db.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    http_response_code(404);
    die("Product not found.");
}

// Fetch Product Details
$product_stmt = $pdo->prepare("SELECT p.*, pc.name as category_name FROM products p JOIN product_categories pc ON p.category_id = pc.id WHERE p.slug = ? AND p.status = 'active'");
$product_stmt->execute([$slug]);
$product = $product_stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    die("Product not found.");
}

// Fetch Gallery Images
$gallery_stmt = $pdo->prepare("SELECT * FROM product_gallery WHERE product_id = ? ORDER BY id ASC");
$gallery_stmt->execute([$product['id']]);
$gallery_images = $gallery_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Related Products
$related_stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 'active' ORDER BY RAND() LIMIT 4");
$related_stmt->execute([$product['category_id'], $product['id']]);
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.php' ?>
    <style>
        .product-info-wrapper {
            padding-left: 30px;
        }

        @media (max-width: 991px) {
            .product-info-wrapper {
                padding-left: 0;
                margin-top: 40px;
            }
        }

        .product-category-tag {
            font-size: 12px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }

        .product-title {
            font-size: 42px;
            line-height: 1.2;
            margin-bottom: 15px;
        }

        .price-box {
            margin: 20px 0;
        }

        .price-value {
            font-size: 28px;
            font-weight: 700;
            color: #c89328;
        }

        .info-section {
            border-top: 1px solid #ebebeb;
            padding: 25px 0;
        }

        .info-section h4 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-content {
            color: #666;
        }

        .info-content ul {
            list-style: disc;
            padding-left: 20px;
        }

        .info-content p,
        .info-content li {
            margin-bottom: 1em;
        }

        .quantity-controls {
            margin-top: 25px;
            display: flex;
            align-items: center;
        }

        .quantity-controls button {
            background: transparent;
            border: 1px solid #ddd;
            cursor: pointer;
            width: 40px;
            height: 40px;
            font-size: 20px;
            color: #000000;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            height: 40px;
            border: 1px solid #ddd;
            border-left: none;
            border-right: none;
        }

        .product-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .product-actions .ttm-btn {
            flex: 1;
            text-align: center;
        }

        .product-gallery .main-image {
            margin-bottom: 15px;
        }

        .product-gallery .main-image img {
            width: 100%;
            height: 550px;
            object-fit: cover;
            border-radius: 4px;
        }

        .product-gallery .thumbnail-images {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .product-gallery .thumbnail-images img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid #eee;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        .product-gallery .thumbnail-images img.active {
            border-color: #c89328;
        }

        .collapsible-section {
            border-bottom: 1px solid #e5e5e5;
        }

        .collapsible-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            cursor: pointer;
            user-select: none;
        }

        .collapsible-header h3 {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .collapsible-arrow {
            transition: transform 0.3s ease;
        }

        .collapsible-section.active .collapsible-arrow {
            transform: rotate(180deg);
        }

        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .collapsible-content-inner {
            padding-bottom: 20px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="page ttm-bgcolor-grey">
        <?php include 'header.php' ?>
        <div class="ttm-page-title-row">
            <div class="ttm-page-title-row-inner">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="page-title-heading">
                                <h2 class="title"><?php echo htmlspecialchars($product['name']); ?></h2>
                            </div>
                            <div class="breadcrumb-wrapper">
                                <div class="breadcrumb-wrapper-inner">
                                    <span><a title="Go to Home." href="./" class="home"><i
                                                class="themifyicon ti-home"></i>&nbsp;&nbsp;Home</a></span>
                                    <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                    <span><a href="products.php">Products</a></span>
                                    <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                    <span><?php echo htmlspecialchars($product['name']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="site-main">
            <div class="ttm-row ttm-bgcolor-white pt-70 pb-70 res-991-pt-30 res-991-pb-30 clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7 col-md-12">
                            <div class="product-gallery">
                                <div class="main-image">
                                    <img id="main-product-image"
                                        src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                                <div class="thumbnail-images">
                                    <img src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>" class="active"
                                        onclick="changeMainImage(this)">
                                    <?php foreach ($gallery_images as $img): ?>
                                        <img src="uploads/products/<?php echo htmlspecialchars($img['image_name']); ?>"
                                            alt="<?php echo htmlspecialchars($img['alt_text']); ?>"
                                            onclick="changeMainImage(this)">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-12">
                            <div class="product-info-wrapper">
                                <div class="product-category-tag">
                                    <?php echo htmlspecialchars($product['category_name']); ?> COLLECTION
                                </div>
                                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                                <div class="price-box">
                                    <div class="price-value">₹<?php echo htmlspecialchars($product['price']); ?></div>
                                </div>
                                <div class="info-section">
                                    <h4>Description</h4>
                                    <div class="info-content"><?php echo $product['long_description']; ?></div>
                                </div>
                                <div class="collapsible-section">
                                    <div class="collapsible-header" onclick="toggleSection(this)">
                                        <h3>Specifications</h3>
                                        <svg class="collapsible-arrow" width="12" height="8" viewBox="0 0 12 8"
                                            fill="currentColor">
                                            <path d="M6 8L0 2L1.41 0.59L6 5.17L10.59 0.59L12 2L6 8Z" />
                                        </svg>
                                    </div>
                                    <div class="collapsible-content">
                                        <div class="collapsible-content-inner"><?php echo $product['specifications']; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="collapsible-section">
                                    <div class="collapsible-header" onclick="toggleSection(this)">
                                        <h3>Fabric Details</h3>
                                        <svg class="collapsible-arrow" width="12" height="8" viewBox="0 0 12 8"
                                            fill="currentColor">
                                            <path d="M6 8L0 2L1.41 0.59L6 5.17L10.59 0.59L12 2L6 8Z" />
                                        </svg>
                                    </div>
                                    <div class="collapsible-content">
                                        <div class="collapsible-content-inner"><?php echo $product['fabric_details']; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="collapsible-section">
                                    <div class="collapsible-header" onclick="toggleSection(this)">
                                        <h3>Delivery & Returns</h3>
                                        <svg class="collapsible-arrow" width="12" height="8" viewBox="0 0 12 8"
                                            fill="currentColor">
                                            <path d="M6 8L0 2L1.41 0.59L6 5.17L10.59 0.59L12 2L6 8Z" />
                                        </svg>
                                    </div>
                                    <div class="collapsible-content">
                                        <div class="collapsible-content-inner">
                                            <?php echo $product['delivery_returns']; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="quantity-controls">
                                    <button type="button" onclick="decreaseQuantity()">-</button>
                                    <input type="number" id="quantity" class="quantity-input" value="1" min="1">
                                    <button type="button" onclick="increaseQuantity()">+</button>
                                </div>
                                <div class="product-actions">

                                    <a id="buy-now-btn"
                                        href="checkout.php?slug=<?php echo htmlspecialchars($product['slug']); ?>&qty=1"
                                        class="ttm-btn ttm-btn-size-md ttm-btn-shape-rounded ttm-btn-style-fill ttm-btn-color-skincolor">Buy
                                        Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <section class="ttm-row related-products ttm-bgcolor-grey clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="section-title text-center">
                                <div class="title-header">
                                    <h2 class="title">Related Products</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-40">
                        <?php foreach ($related_products as $related): ?>
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="featured-imagebox featured-imagebox-portfolio style5">
                                    <div class="ttm-box-view-overlay">
                                        <div class="featured-thumbnail">
                                            <img width="610" height="750" class="img-fluid"
                                                src="uploads/products/<?php echo htmlspecialchars($related['main_image']); ?>"
                                                alt="<?php echo htmlspecialchars($related['name']); ?>">
                                        </div>
                                        <div class="ttm-footer">
                                            <a class="ttm-btn btn-inline ttm-btn-size-md ttm-icon-btn-right ttm-btn-color-skincolor"
                                                href="products/<?php echo htmlspecialchars($related['slug']); ?>"><i
                                                    class="fa fa-long-arrow-right"></i></a>
                                        </div>
                                        <div class="featured-content">
                                            <div class="featured-title">
                                                <h3><a
                                                        href="products/<?php echo htmlspecialchars($related['slug']); ?>"><?php echo htmlspecialchars($related['name']); ?></a>
                                                </h3>
                                            </div>
                                            <div class="product-price"><span
                                                    class="price">₹<?php echo htmlspecialchars($related['price']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
        <?php include 'footer.php' ?>
        <?php include 'scripts.php' ?>
        <script>
            function changeMainImage(thumbnail) {
                document.getElementById('main-product-image').src = thumbnail.src;
                document.querySelectorAll('.thumbnail-images img').forEach(img => img.classList.remove('active'));
                thumbnail.classList.add('active');
            }
            function updateBuyNowLink() {
                const qty = document.getElementById('quantity').value;
                const slug = "<?php echo htmlspecialchars($product['slug']); ?>";
                const buyNowBtn = document.getElementById('buy-now-btn');
                buyNowBtn.href = `checkout.php?slug=${slug}&qty=${qty}`;
            }
            function increaseQuantity() {
                const qty = document.getElementById('quantity');
                qty.value = parseInt(qty.value) + 1;
                updateBuyNowLink();
            }
            function decreaseQuantity() {
                const qty = document.getElementById('quantity');
                if (parseInt(qty.value) > 1) {
                    qty.value = parseInt(qty.value) - 1;
                }
                updateBuyNowLink();
            }
            function toggleSection(header) {
                const section = header.parentElement;
                const content = section.querySelector('.collapsible-content');
                if (section.classList.contains('active')) {
                    section.classList.remove('active');
                    content.style.maxHeight = '0px';
                } else {
                    section.classList.add('active');
                    content.style.maxHeight = content.scrollHeight + 'px';
                }
            }
        </script>
    </div>
</body>

</html>