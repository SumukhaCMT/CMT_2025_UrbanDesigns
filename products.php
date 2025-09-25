<?php
include 'admin/shared_components/db.php'; // Adjust path if needed
$currentPage = 'products';

// Fetch Categories
$categories_stmt = $pdo->query("SELECT * FROM product_categories ORDER BY name ASC");
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Products
$products_stmt = $pdo->query("SELECT p.*, pc.slug as category_slug, pc.name as category_name FROM products p JOIN product_categories pc ON p.category_id = pc.id WHERE p.status = 'active' ORDER BY p.created_at DESC");
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.php' ?>
    <style>
        :root {
            --primary-color: #232f3e;
            --secondary-color: #ff9900;
            --accent-color: #37475a;
            --text-primary: #0f1111;
            --text-secondary: #565959;
            --border-color: #d5d9d9;
            --hover-color: #f7f8f8;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        /* Search and Filter Section */
        .search-filter-section {
            background: white;
            padding: 2rem 0;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .search-bar {
            position: relative;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: flex-end;
        }

        .search-input {
            width: 300px;
            max-width: 100%;
            padding: 12px 50px 12px 20px;
            border: 2px solid var(--border-color);
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .search-input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(255, 153, 0, 0.1);
            outline: none;
        }

        .search-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 18px;
            pointer-events: none;
        }

        /* Main Content Styling */
        .site-main {
            padding: 2rem 0;
        }

        /* Category Filters */
        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-bottom: 1rem;
            padding: 0 1rem;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 2px solid var(--border-color);
            background: white;
            border-radius: 25px;
            color: var(--text-primary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .filter-btn .count {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }

        /* Sort Controls */
        .sort-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem 0;
            padding: 0 1rem;
        }

        .results-count {
            color: var(--text-secondary);
            font-size: 13px;
            flex: 1;
        }

        .sort-select {
            padding: 6px 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: white;
            color: var(--text-primary);
            cursor: pointer;
            font-size: 13px;
            width: auto;
            min-width: 180px;
        }

        /* Product Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 24px;
            padding: 0 1rem;
        }

        /* Product Cards */
        .product-card {
            background: white;
            border-radius: 0;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
            aspect-ratio: 1;
            background: #f8f9fa;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.02);
        }

        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--secondary-color);
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-category {
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 1.5rem;
        }

        .current-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .original-price {
            font-size: 1rem;
            color: var(--text-secondary);
            text-decoration: line-through;
        }

        .discount {
            color: #c7511f;
            font-size: 14px;
            font-weight: 600;
        }

        .product-actions {
            display: flex;
            gap: 8px;
        }

        .btn-primary {
            flex: 1;
            padding: 12px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .btn-primary:hover {
            background: #e47911;
            transform: translateY(-1px);
        }

        /* Loading States */
        .loading {
            display: none;
            text-align: center;
            padding: 3rem;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--secondary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            display: none;
        }

        .empty-state.active {
            display: block;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .search-bar {
                justify-content: center;
                margin-bottom: 1rem;
            }

            .search-input {
                width: 280px;
            }

            .category-filters {
                justify-content: flex-start;
                overflow-x: auto;
                padding-bottom: 10px;
                margin-bottom: 1rem;
            }

            .filter-btn {
                flex-shrink: 0;
                font-size: 13px;
                padding: 8px 16px;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 16px;
            }

            .sort-controls {
                flex-direction: column;
                gap: 0.8rem;
                align-items: stretch;
                padding: 0 1rem;
            }

            .results-count {
                text-align: center;
                order: 2;
            }

            .sort-select {
                order: 1;
                min-width: auto;
                width: 100%;
                max-width: 250px;
                margin: 0 auto;
            }
        }

        @media (max-width: 480px) {
            .search-input {
                width: 100%;
                max-width: 280px;
                font-size: 14px;
            }

            .search-bar {
                padding: 0 1rem;
            }

            .category-filters {
                padding: 0 1rem 10px;
            }

            .filter-btn {
                font-size: 12px;
                padding: 6px 12px;
            }

            .filter-btn .count {
                font-size: 10px;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 12px;
                padding: 0 0.5rem;
            }

            .product-info {
                padding: 1rem;
            }

            .product-title {
                font-size: 0.9rem;
            }

            .current-price {
                font-size: 1.2rem;
            }

            .btn-primary {
                font-size: 13px;
                padding: 8px;
            }

            .sort-controls {
                margin: 0.5rem 0;
            }
        }

        @media (max-width: 360px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .product-info {
                padding: 0.8rem;
            }

            .product-title {
                font-size: 0.85rem;
                margin-bottom: 8px;
            }

            .current-price {
                font-size: 1.1rem;
            }

            .btn-primary {
                font-size: 12px;
                padding: 6px;
            }
        }

        /* Animation for product cards appearing */
        .product-card {
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="page ttm-bgcolor-grey">
        <?php include 'header.php' ?>

        <!-- Original Breadcrumb Section -->
        <div class="ttm-page-title-row">
            <div class="ttm-page-title-row-inner">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="page-title-heading">
                                <h2 class="title">Products</h2>
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

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <div class="container">
                <!-- Search Bar -->
                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Search products..." id="productSearch">
                    <i class="fa fa-search search-icon"></i>
                </div>

                <!-- Category Filters -->
                <div class="category-filters">
                    <button class="filter-btn active" data-filter="*">
                        <i class="fa fa-th"></i>
                        All Products
                        <span class="count"><?php echo count($products); ?></span>
                    </button>
                    <?php foreach ($categories as $cat): ?>
                        <?php
                        $cat_count = array_reduce($products, function ($count, $product) use ($cat) {
                            return $count + ($product['category_slug'] === $cat['slug'] ? 1 : 0);
                        }, 0);
                        ?>
                        <button class="filter-btn" data-filter=".<?php echo htmlspecialchars($cat['slug']); ?>">
                            <i class="fa fa-tag"></i>
                            <?php echo htmlspecialchars($cat['name']); ?>
                            <span class="count"><?php echo $cat_count; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="site-main">
            <section class="ttm-row filter-section clearfix">
                <div class="container">
                    <!-- Sort Controls -->
                    <div class="sort-controls">
                        <div class="results-count">
                            <span id="resultsCount"><?php echo count($products); ?></span> products found
                        </div>
                        <select class="sort-select" id="sortSelect">
                            <option value="newest">Sort by: Newest First</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="name-asc">Name: A to Z</option>
                            <option value="name-desc">Name: Z to A</option>
                        </select>
                    </div>

                    <!-- Loading State -->
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <p>Loading products...</p>
                    </div>

                    <!-- Products Grid -->
                    <div class="products-grid" id="productsGrid">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $index => $product): ?>
                                <div class="product-card <?php echo htmlspecialchars($product['category_slug']); ?>"
                                    style="animation-delay: <?php echo ($index * 0.1); ?>s"
                                    data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-category="<?php echo htmlspecialchars($product['category_slug']); ?>">

                                    <div class="product-image-container">
                                        <img class="product-image"
                                            src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">

                                        <div class="product-badge">New</div>
                                    </div>

                                    <div class="product-info">
                                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?>
                                        </div>

                                        <h3 class="product-title">
                                            <a href="products/<?php echo htmlspecialchars($product['slug']); ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h3>

                                        <div class="product-price">
                                            <span
                                                class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                                        </div>

                                        <div class="product-actions">
                                            <button class="btn-primary" onclick="buyNow(<?php echo $product['id']; ?>)">
                                                <i class="fa fa-shopping-bag"></i> Buy Now
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Empty State -->
                    <div class="empty-state" id="emptyState">
                        <div class="empty-icon">
                            <i class="fa fa-search"></i>
                        </div>
                        <h3 class="empty-title">No products found</h3>
                        <p class="empty-text">Try adjusting your search or filter to find what you're looking for.</p>
                    </div>
                </div>
            </section>
        </div>

        <?php include 'footer.php' ?>
    </div>

    <?php include 'scripts.php' ?>

    <script>
        // Modern Product Filter and Search Functionality
        class ProductFilter {
            constructor() {
                this.products = document.querySelectorAll('.product-card');
                this.filterButtons = document.querySelectorAll('.filter-btn');
                this.searchInput = document.getElementById('productSearch');
                this.sortSelect = document.getElementById('sortSelect');
                this.resultsCount = document.getElementById('resultsCount');
                this.emptyState = document.getElementById('emptyState');
                this.productsGrid = document.getElementById('productsGrid');

                this.currentFilter = '*';
                this.currentSearch = '';

                this.init();
            }

            init() {
                // Filter button events
                this.filterButtons.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.setActiveFilter(btn);
                        this.currentFilter = btn.dataset.filter;
                        this.filterProducts();
                    });
                });

                // Search input events
                this.searchInput.addEventListener('input', (e) => {
                    this.currentSearch = e.target.value.toLowerCase();
                    this.filterProducts();
                });

                // Sort select events
                this.sortSelect.addEventListener('change', (e) => {
                    this.sortProducts(e.target.value);
                });

                // Initial filter
                this.filterProducts();
            }

            setActiveFilter(activeBtn) {
                this.filterButtons.forEach(btn => btn.classList.remove('active'));
                activeBtn.classList.add('active');
            }

            filterProducts() {
                let visibleCount = 0;

                this.products.forEach(product => {
                    const matchesFilter = this.currentFilter === '*' ||
                        product.classList.contains(this.currentFilter.substring(1));

                    const matchesSearch = this.currentSearch === '' ||
                        product.dataset.name.includes(this.currentSearch);

                    if (matchesFilter && matchesSearch) {
                        product.style.display = 'block';
                        visibleCount++;
                    } else {
                        product.style.display = 'none';
                    }
                });

                this.updateResultsCount(visibleCount);
                this.toggleEmptyState(visibleCount === 0);
            }

            sortProducts(sortBy) {
                const productsArray = Array.from(this.products);

                productsArray.sort((a, b) => {
                    switch (sortBy) {
                        case 'price-low':
                            return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                        case 'price-high':
                            return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                        case 'name-asc':
                            return a.dataset.name.localeCompare(b.dataset.name);
                        case 'name-desc':
                            return b.dataset.name.localeCompare(a.dataset.name);
                        default:
                            return 0;
                    }
                });

                // Re-append sorted products
                productsArray.forEach(product => {
                    this.productsGrid.appendChild(product);
                });
            }

            updateResultsCount(count) {
                this.resultsCount.textContent = count;
            }

            toggleEmptyState(show) {
                if (show) {
                    this.emptyState.classList.add('active');
                    this.productsGrid.style.display = 'none';
                } else {
                    this.emptyState.classList.remove('active');
                    this.productsGrid.style.display = 'grid';
                }
            }
        }

        // Buy now functionality - Fixed to redirect to checkout
        function buyNow(productId) {
            const button = event.target;
            const productCard = button.closest('.product-card');

            // Get product slug from the link in the product card
            const productLink = productCard.querySelector('.product-title a');
            const href = productLink.getAttribute('href');
            const slug = href.replace('products/', '');

            // Show loading state
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
            button.disabled = true;

            // Redirect to checkout with default quantity 1
            setTimeout(() => {
                window.location.href = `checkout/${encodeURIComponent(slug)}?qty=1`;
            }, 500);
        }

        // Initialize filter system
        document.addEventListener('DOMContentLoaded', () => {
            new ProductFilter();
        });
    </script>
</body>

</html>