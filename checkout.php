<?php
include 'admin/shared_components/db.php';

$slug = $_GET['slug'] ?? '';
$qty = max(1, (int) ($_GET['qty'] ?? 1)); // Ensure minimum quantity is 1

if (!$slug) {
    die("Invalid Product.");
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ? AND status = 'active'");
$stmt->execute([$slug]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.php' ?>
    <style>
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 15px 0;
            height: 35px;
            /* Fixed height for container */
        }

        .quantity-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            width: 35px;
            height: 35px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            color: #495057;
            font-size: 16px;
            line-height: 1;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        .quantity-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .quantity-input {
            width: 60px !important;
            height: 35px !important;
            text-align: center !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 5px !important;
            padding: 0 !important;
            margin: 0 !important;
            font-weight: bold !important;
            color: #000000 !important;
            background-color: #ffffff !important;
            -webkit-appearance: none !important;
            -moz-appearance: textfield !important;
            outline: none !important;
            box-sizing: border-box !important;
            font-size: 16px !important;
            line-height: 35px !important;
            /* Match the height */
            vertical-align: top !important;
        }

        /* Remove number input spinners */
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }

        .quantity-input:focus {
            color: #000000 !important;
            background-color: #ffffff !important;
            border-color: #007bff !important;
        }

        .product-image-checkout {
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .order-summary-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #dee2e6;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
        }

        .price-row.total {
            border-top: 2px solid #dee2e6;
            margin-top: 15px;
            padding-top: 15px;
            font-weight: bold;
            font-size: 1.2em;
        }

        .product-info-checkout {
            margin-left: 15px;
        }

        .product-name-checkout {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .unit-price {
            color: #6c757d;
            font-size: 0.9em;
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
                                <h2 class="title">Checkout</h2>
                            </div>
                            <div class="breadcrumb-wrapper">
                                <div class="container">
                                    <div class="breadcrumb-wrapper-inner">
                                        <span><a title="Go to Home." href="./" class="home"><i
                                                    class="themifyicon ti-home"></i>&nbsp;&nbsp;Home</a></span>
                                        <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                        <span><a href="products.php">Products</a></span>
                                        <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                        <span>Checkout</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="site-main">
            <section class="ttm-row clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7">
                            <h4>Shipping Details</h4>
                            <form action="verify-order.php" method="POST" class="mt-4" id="checkoutForm">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="<?php echo htmlspecialchars($qty); ?>"
                                    id="hiddenQuantity">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" name="customer_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" name="customer_email" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number *</label>
                                        <input type="tel" name="customer_phone" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Alternate Phone</label>
                                        <input type="tel" name="customer_alt_phone" class="form-control">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Full Address *</label>
                                        <textarea name="customer_address" class="form-control" rows="4" required
                                            placeholder="Enter your complete address including pin code"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit"
                                            class="ttm-btn ttm-btn-size-md ttm-btn-shape-rounded ttm-btn-style-fill ttm-btn-color-skincolor">
                                            Proceed to Verify Order
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-5 res-991-mt-40">
                            <div class="order-summary-card">
                                <h4>Order Summary</h4>

                                <div class="d-flex align-items-start mt-4">
                                    <img src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>"
                                        width="100" height="100" class="product-image-checkout"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>">

                                    <div class="product-info-checkout flex-grow-1">
                                        <div class="product-name-checkout">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </div>
                                        <div class="unit-price">
                                            Unit Price: ₹<?php echo number_format($product['price'], 2); ?>
                                        </div>

                                        <div class="quantity-controls">
                                            <label for="quantity"
                                                style="margin-right: 10px; font-weight: 600;">Quantity:</label>
                                            <button type="button" class="quantity-btn" id="decreaseQty">-</button>
                                            <input type="number" id="quantity" class="quantity-input"
                                                value="<?php echo $qty; ?>" min="1" max="99"
                                                style="color: #000000 !important; background-color: #ffffff !important;">
                                            <button type="button" class="quantity-btn" id="increaseQty">+</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="price-row">
                                        <span>Subtotal:</span>
                                        <span
                                            id="subtotal">₹<?php echo number_format($product['price'] * $qty, 2); ?></span>
                                    </div>
                                    <div class="price-row">
                                        <span>Shipping:</span>
                                        <span class="text-success">Free</span>
                                    </div>
                                    <div class="price-row total">
                                        <span>Total:</span>
                                        <span
                                            id="total">₹<?php echo number_format($product['price'] * $qty, 2); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 d-flex align-items-center gap-3 flex-wrap">
                                <a href="products/<?php echo htmlspecialchars($product['slug']); ?>"
                                    class="ttm-btn ttm-btn-size-md ttm-btn-shape-rounded ttm-btn-style-border ttm-btn-color-dark">
                                    View Product
                                </a>
                                <a href="products"
                                    class="ttm-btn ttm-btn-size-md ttm-btn-shape-rounded ttm-btn-style-border ttm-btn-color-dark">
                                    Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php include 'footer.php' ?>
        <?php include 'scripts.php' ?>

        <script>
            // Quantity controls functionality
            const quantityInput = document.getElementById('quantity');
            const decreaseBtn = document.getElementById('decreaseQty');
            const increaseBtn = document.getElementById('increaseQty');
            const subtotalSpan = document.getElementById('subtotal');
            const totalSpan = document.getElementById('total');
            const hiddenQuantity = document.getElementById('hiddenQuantity');

            const unitPrice = <?php echo $product['price']; ?>;

            function updatePrice() {
                const qty = parseInt(quantityInput.value) || 1;
                const subtotal = unitPrice * qty;

                subtotalSpan.textContent = `₹${subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                totalSpan.textContent = `₹${subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                // Update hidden input for form submission
                hiddenQuantity.value = qty;

                // Update URL without reloading page
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('qty', qty);
                window.history.replaceState({}, '', newUrl);

                // Update button states
                decreaseBtn.disabled = qty <= 1;
            }

            // Decrease quantity
            decreaseBtn.addEventListener('click', () => {
                const currentQty = parseInt(quantityInput.value) || 1;
                if (currentQty > 1) {
                    quantityInput.value = currentQty - 1;
                    updatePrice();
                }
            });

            // Increase quantity
            increaseBtn.addEventListener('click', () => {
                const currentQty = parseInt(quantityInput.value) || 1;
                if (currentQty < 99) {
                    quantityInput.value = currentQty + 1;
                    updatePrice();
                }
            });

            // Handle direct input
            quantityInput.addEventListener('input', () => {
                let qty = parseInt(quantityInput.value) || 1;

                // Enforce limits
                if (qty < 1) qty = 1;
                if (qty > 99) qty = 99;

                quantityInput.value = qty;
                updatePrice();
            });

            // Initialize
            updatePrice();

            // Form validation enhancement
            document.getElementById('checkoutForm').addEventListener('submit', function (e) {
                const requiredFields = this.querySelectorAll('[required]');
                let hasErrors = false;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.style.borderColor = '#dc3545';
                        hasErrors = true;
                    } else {
                        field.style.borderColor = '#ced4da';
                    }
                });

                if (hasErrors) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                    return false;
                }

                // Update quantity one more time before submission
                hiddenQuantity.value = quantityInput.value;
            });
        </script>
    </div>
</body>

</html>