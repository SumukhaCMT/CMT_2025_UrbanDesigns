<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include 'admin/shared_components/db.php';
    // Only include email functions if the file exists
    if (file_exists('email_functions.php')) {
        include 'email_functions.php';
    }
} catch (Exception $e) {
    die("Include error: " . $e->getMessage());
}

// Part 1: Handle the FINAL submission to save the order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    try {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];

        // Validate inputs
        if (!is_numeric($product_id) || !is_numeric($quantity) || $quantity <= 0) {
            throw new Exception("Invalid product ID or quantity");
        }

        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found");
        }

        $total_price = $product['price'] * $quantity;

        // Insert order - Fixed: Remove status from column list since it has a default value
        $sql = "INSERT INTO orders (product_id, customer_name, customer_email, customer_phone, customer_alt_phone, customer_address, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql);
        $result = $stmt_insert->execute([
            $product_id,
            $_POST['customer_name'],
            $_POST['customer_email'],
            $_POST['customer_phone'],
            $_POST['customer_alt_phone'] ?? '',
            $_POST['customer_address'],
            $quantity,
            $total_price
        ]);

        if (!$result) {
            throw new Exception("Failed to insert order");
        }

        $order_id = $pdo->lastInsertId();

        // Try to send emails if functions exist
        if (function_exists('sendOrderConfirmationEmails')) {
            try {
                // Get full product details for email
                $product_stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $product_stmt->execute([$product_id]);
                $product_data = $product_stmt->fetch(PDO::FETCH_ASSOC);

                // Prepare order data for emails
                $order_data = [
                    'id' => $order_id,
                    'customer_name' => $_POST['customer_name'],
                    'customer_email' => $_POST['customer_email'],
                    'customer_phone' => $_POST['customer_phone'],
                    'customer_alt_phone' => $_POST['customer_alt_phone'] ?? '',
                    'customer_address' => $_POST['customer_address'],
                    'quantity' => $quantity,
                    'total_price' => $total_price
                ];

                // Send emails
                sendOrderConfirmationEmails($order_data, $product_data);
                error_log("Order emails sent successfully for Order #$order_id");
            } catch (Exception $e) {
                // Log email error but don't fail the order
                error_log("Failed to send order emails: " . $e->getMessage());
            }
        }

        header("Location: thank-you.php");
        exit();

    } catch (Exception $e) {
        error_log("Order processing error: " . $e->getMessage());
        die("Error processing order: " . $e->getMessage());
    }
}

// Part 2: Display the verification page from the checkout form
try {
    $product_id = $_POST['product_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;

    if (!is_numeric($product_id) || $product_id <= 0) {
        throw new Exception("Invalid product ID");
    }

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Product not found");
    }

    $total_price = $product['price'] * $quantity;

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    if (file_exists('head.php')) {
        include 'head.php';
    } else {
        echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Verify Order</title>';
    }
    ?>
</head>

<body>
    <div class="page ttm-bgcolor-grey">
        <?php
        if (file_exists('header.php')) {
            include 'header.php';
        }
        ?>

        <div class="ttm-page-title-row">
            <div class="ttm-page-title-row-inner">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="page-title-heading">
                                <h2 class="title">Verify Your Order</h2>
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
                            <h4>Confirm Your Details</h4>
                            <table class="table mt-4">
                                <tr>
                                    <th>Name</th>
                                    <td><?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?></td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td><?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?></td>
                                </tr>
                                <tr>
                                    <th>Alt. Phone</th>
                                    <td><?php echo htmlspecialchars($_POST['customer_alt_phone'] ?? ''); ?></td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td><?php echo nl2br(htmlspecialchars($_POST['customer_address'] ?? '')); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-lg-5 res-991-mt-40">
                            <h4>Order Summary</h4>
                            <table class="table mt-4">
                                <tr>
                                    <td><img src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>"
                                            width="80" alt="Product"></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?><br>Qty:
                                        <?php echo htmlspecialchars($quantity); ?>
                                    </td>
                                    <td class="text-end">₹<?php echo number_format($total_price, 2); ?></td>
                                </tr>
                                <tr class="fw-bold">
                                    <td colspan="2">Total</td>
                                    <td class="text-end">₹<?php echo number_format($total_price, 2); ?></td>
                                </tr>
                            </table>
                            <form action="verify-order.php" method="POST" class="mt-4">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>">
                                <input type="hidden" name="customer_name"
                                    value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>">
                                <input type="hidden" name="customer_email"
                                    value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>">
                                <input type="hidden" name="customer_phone"
                                    value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>">
                                <input type="hidden" name="customer_alt_phone"
                                    value="<?php echo htmlspecialchars($_POST['customer_alt_phone'] ?? ''); ?>">
                                <input type="hidden" name="customer_address"
                                    value="<?php echo htmlspecialchars($_POST['customer_address'] ?? ''); ?>">

                                <input type="hidden" name="confirm_order" value="1">
                                <button type="submit"
                                    class="ttm-btn ttm-btn-size-lg ttm-btn-shape-rounded ttm-btn-style-fill ttm-btn-color-skincolor w-100">Confirm
                                    & Place Order</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php
        if (file_exists('footer.php')) {
            include 'footer.php';
        }
        if (file_exists('scripts.php')) {
            include 'scripts.php';
        }
        ?>
    </div>
</body>

</html>