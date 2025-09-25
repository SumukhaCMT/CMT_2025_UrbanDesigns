<?php
function sendOrderConfirmationEmails($order_data, $product_data)
{
    error_log("=== STARTING EMAIL PROCESS ===");
    error_log("Customer Email: " . $order_data['customer_email']);
    error_log("Admin Email: connect@theurbandesign.in");

    // Send customer invoice
    error_log("--- Sending customer invoice ---");
    $customer_result = sendCustomerInvoice($order_data, $product_data);
    error_log("Customer email result: " . ($customer_result ? "SUCCESS" : "FAILED"));

    // Send admin notification  
    error_log("--- Sending admin notification ---");
    $admin_result = sendAdminNotification($order_data, $product_data);
    error_log("Admin email result: " . ($admin_result ? "SUCCESS" : "FAILED"));

    error_log("=== EMAIL PROCESS COMPLETE ===");
}

function sendCustomerInvoice($order_data, $product_data)
{
    // Debug: Log which template we're trying to load
    $template_path = __DIR__ . '/email_templates/customer-invoice.html';
    error_log("Loading customer template from: " . $template_path);

    $html_template = file_get_contents($template_path);

    if ($html_template === false) {
        error_log("Failed to load customer invoice template");
        return false;
    }

    // Calculate amounts (NO GST)
    $subtotal = $product_data['price'] * $order_data['quantity'];
    $tax_amount = 0; // No GST
    $total_amount = $subtotal; // Total = Subtotal (no tax)

    // Set timezone to IST
    date_default_timezone_set('Asia/Kolkata');

    // Replace placeholders
    $html_content = strtr($html_template, [
        '{{ORDER_ID}}' => str_pad($order_data['id'], 8, '0', STR_PAD_LEFT),
        '{{ORDER_DATE}}' => date('M j, Y, g:i A'), // Now in IST
        '{{CUSTOMER_NAME}}' => htmlspecialchars($order_data['customer_name']),
        '{{CUSTOMER_EMAIL}}' => htmlspecialchars($order_data['customer_email']),
        '{{CUSTOMER_PHONE}}' => htmlspecialchars($order_data['customer_phone']),
        '{{CUSTOMER_ADDRESS}}' => nl2br(htmlspecialchars($order_data['customer_address'])),
        '{{PRODUCT_NAME}}' => htmlspecialchars($product_data['name']),
        '{{PRODUCT_SKU}}' => htmlspecialchars($product_data['slug']),
        '{{PRODUCT_PRICE}}' => number_format($product_data['price'], 2),
        '{{PRODUCT_QUANTITY}}' => $order_data['quantity'],
        '{{PRODUCT_TOTAL}}' => number_format($subtotal, 2),
        '{{SUBTOTAL}}' => number_format($subtotal, 2),
        '{{TAX_AMOUNT}}' => number_format($tax_amount, 2), // Will show 0.00
        '{{TOTAL_AMOUNT}}' => number_format($total_amount, 2),

        // Update these with your company details
        '{{PHONE_NUMBER}}' => '+91 9886306672',
        '{{SUPPORT_EMAIL}}' => 'connect@theurbandesign.in',
        '{{COMPANY_ADDRESS}}' => 'Kalluri Paradise, NO.401, 6th Cross Road, JP Nagar Phase 1, Bengaluru - 560078',
        '{{WEBSITE_URL}}' => 'https://theurbandesign.in/'
    ]);

    // Debug: Log who we're sending to
    error_log("Sending CUSTOMER invoice to: " . $order_data['customer_email']);

    return sendEmailWithPHPMailer(
        $order_data['customer_email'],
        $order_data['customer_name'],
        'Invoice - Order #' . str_pad($order_data['id'], 8, '0', STR_PAD_LEFT),
        $html_content
    );
}

function sendAdminNotification($order_data, $product_data)
{
    // Debug: Log which template we're trying to load
    $template_path = __DIR__ . '/email_templates/admin_notification.html';
    error_log("Loading admin template from: " . $template_path);

    $html_template = file_get_contents($template_path);

    if ($html_template === false) {
        error_log("Failed to load admin notification template");
        return false;
    }

    // Calculate amounts (NO GST)
    $subtotal = $product_data['price'] * $order_data['quantity'];
    $tax_amount = 0; // No GST
    $total_amount = $subtotal; // Total = Subtotal (no tax)

    // Set timezone to IST
    date_default_timezone_set('Asia/Kolkata');

    // Replace placeholders
    $html_content = strtr($html_template, [
        '{{ORDER_ID}}' => str_pad($order_data['id'], 8, '0', STR_PAD_LEFT),
        '{{ORDER_DATE}}' => date('M j, Y, g:i A'), // Now in IST
        '{{ORDER_TIMESTAMP}}' => date('Y-m-d H:i:s'), // Now in IST
        '{{CUSTOMER_NAME}}' => htmlspecialchars($order_data['customer_name']),
        '{{CUSTOMER_EMAIL}}' => htmlspecialchars($order_data['customer_email']),
        '{{CUSTOMER_PHONE}}' => htmlspecialchars($order_data['customer_phone']),
        '{{CUSTOMER_ALT_PHONE}}' => htmlspecialchars($order_data['customer_alt_phone'] ?? 'N/A'),
        '{{CUSTOMER_ADDRESS}}' => nl2br(htmlspecialchars($order_data['customer_address'])),
        '{{PRODUCT_NAME}}' => htmlspecialchars($product_data['name']),
        '{{PRODUCT_SKU}}' => htmlspecialchars($product_data['slug']),
        '{{PRODUCT_PRICE}}' => number_format($product_data['price'], 2),
        '{{PRODUCT_QUANTITY}}' => $order_data['quantity'],
        '{{SUBTOTAL}}' => number_format($subtotal, 2),
        '{{TOTAL_AMOUNT}}' => number_format($total_amount, 2),

        // Company details with logo
        '{{COMPANY_LOGO}}' => 'images/favicons/Urban_Designs.webp',
        '{{COMPANY_NAME}}' => 'The Urban Designs',
        '{{ADMIN_URL}}' => 'https://theurbandesign.in/admin/login',
        '{{WEBSITE_URL}}' => 'https://theurbandesign.in/'
    ]);

    // Debug: Log who we're sending to
    error_log("Sending ADMIN notification to: connect@theurbandesign.in");

    return sendEmailWithPHPMailer(
        'connect@theurbandesign.in',  // Changed to your alternate email
        'Admin',
        'New Order Received - #' . str_pad($order_data['id'], 8, '0', STR_PAD_LEFT),
        $html_content
    );
}

// Include PHPMailer files (OLD VERSION 5.x)
require_once 'admin/Mail/phpmailer/class.phpmailer.php';
require_once 'admin/Mail/phpmailer/class.smtp.php';

function sendEmailWithPHPMailer($to_email, $to_name, $subject, $html_content)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'connect@theurbandesign.in';
        $mail->Password = 'TheUrbanDesign@123';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('connect@theurbandesign.in', 'The Urban Design');
        $mail->addAddress($to_email, $to_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_content;

        $result = $mail->send();

        if ($result) {
            error_log("Email sent successfully to: $to_email");
            return true;
        }

    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }

    return false;
}
?>