<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// Handle Product Delete Request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $uploadDir = '../uploads/products/';
    $pdo->beginTransaction();
    try {
        $img_stmt = $pdo->prepare("SELECT main_image FROM products WHERE id = ?");
        $img_stmt->execute([$delete_id]);
        $main_image = $img_stmt->fetchColumn();
        $gallery_stmt = $pdo->prepare("SELECT image_name FROM product_gallery WHERE product_id = ?");
        $gallery_stmt->execute([$delete_id]);
        $gallery_images = $gallery_stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$delete_id]);
        if ($main_image && file_exists($uploadDir . $main_image)) {
            unlink($uploadDir . $main_image);
        }
        foreach ($gallery_images as $img) {
            if ($img && file_exists($uploadDir . $img)) {
                unlink($uploadDir . $img);
            }
        }
        $pdo->commit();
        echo "<script>alert('Product deleted successfully!'); window.location.href = 'view-products.php';</script>";
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Error deleting product: " . $e->getMessage() . "'); window.location.href = 'view-products.php';</script>";
        exit();
    }
}

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $allowed_statuses = ['pending', 'shipped', 'delivered', 'cancelled'];

    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        header("Location: view-products.php#orders");
        exit();
    }
}

// Fetch All Products
$products_stmt = $pdo->query("SELECT p.*, pc.name as category_name FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.id ORDER BY p.created_at DESC");
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch All Orders
$orders_stmt = $pdo->query("SELECT o.*, p.name as product_name FROM orders o LEFT JOIN products p ON o.product_id = p.id ORDER BY o.order_date DESC");
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
</head>

<body>
    <div id="layout-wrapper">
        <?php require './shared_components/header.php'; ?>
        <?php require './shared_components/navbar.php'; ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Products & Orders</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-end mb-4">
                                        <a href="add-product.php" class="btn btn-primary"><i
                                                class="bx bx-plus me-2"></i>Add New Product</a>
                                    </div>
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <button class="nav-link active" data-bs-toggle="tab"
                                                data-bs-target="#products" type="button" role="tab">Manage
                                                Products</button>
                                        </li>
                                        <li class="nav-item">
                                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#orders"
                                                type="button" role="tab">Customer Orders</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content pt-4">
                                        <div class="tab-pane fade show active" id="products" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Image</th>
                                                            <th>Name</th>
                                                            <th>Category</th>
                                                            <th>Price</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($products as $index => $product): ?>
                                                            <tr>
                                                                <td><?php echo $index + 1; ?></td>
                                                                <td><img src="../uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>"
                                                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                                        width="60" class="img-thumbnail"></td>
                                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                                <td><?php echo htmlspecialchars($product['category_name']); ?>
                                                                </td>
                                                                <td>₹<?php echo htmlspecialchars($product['price']); ?></td>
                                                                <td><span
                                                                        class="badge <?php echo $product['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo ucfirst($product['status']); ?></span>
                                                                </td>
                                                                <td>
                                                                    <a href="edit-product.php?id=<?php echo $product['id']; ?>"
                                                                        class="btn btn-sm btn-primary"><i
                                                                            class="bx bxs-pencil"></i></a>
                                                                    <form action="view-products.php" method="POST"
                                                                        class="d-inline"
                                                                        onsubmit="return confirm('Are you sure you want to delete this product and all its images?');">
                                                                        <input type="hidden" name="delete_id"
                                                                            value="<?php echo $product['id']; ?>">
                                                                        <button type="submit"
                                                                            class="btn btn-sm btn-danger"><i
                                                                                class="bx bx-trash"></i></button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="orders" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Order ID</th>
                                                            <th>Customer Name</th>
                                                            <th>Contact Info</th>
                                                            <th>Address</th>
                                                            <th>Product</th>
                                                            <th>Qty</th>
                                                            <th>Total</th>
                                                            <th>Date</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($orders as $order): ?>
                                                            <tr>
                                                                <td>#<?php echo $order['id']; ?></td>
                                                                <td><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                                                </td>
                                                                <td>
                                                                    <?php echo htmlspecialchars($order['customer_email']); ?><br>
                                                                    P:
                                                                    <?php echo htmlspecialchars($order['customer_phone']); ?><br>
                                                                    A:
                                                                    <?php echo htmlspecialchars($order['customer_alt_phone']); ?>
                                                                </td>
                                                                <td><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($order['product_name']); ?>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                                                                <td>₹<?php echo htmlspecialchars($order['total_price']); ?>
                                                                </td>
                                                                <td><?php echo date('d M Y, h:ia', strtotime($order['order_date'])); ?>
                                                                </td>
                                                                <td>
                                                                    <form action="view-products.php" method="POST">
                                                                        <input type="hidden" name="order_id"
                                                                            value="<?php echo $order['id']; ?>">
                                                                        <select name="new_status"
                                                                            class="form-select form-select-sm"
                                                                            onchange="this.form.submit()">
                                                                            <option value="pending" <?php if ($order['status'] == 'pending')
                                                                                echo 'selected'; ?>>Pending</option>
                                                                            <option value="shipped" <?php if ($order['status'] == 'shipped')
                                                                                echo 'selected'; ?>>Shipped</option>
                                                                            <option value="delivered" <?php if ($order['status'] == 'delivered')
                                                                                echo 'selected'; ?>>Delivered</option>
                                                                            <option value="cancelled" <?php if ($order['status'] == 'cancelled')
                                                                                echo 'selected'; ?>>Cancelled</option>
                                                                        </select>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php require './shared_components/footer.php'; ?>
        </div>
    </div>
    <?php require './shared_components/scripts.php'; ?>
</body>

</html>