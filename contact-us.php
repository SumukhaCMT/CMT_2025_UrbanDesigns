<?php
$currentPage = 'contact-us';

// Set timezone to IST
date_default_timezone_set('Asia/Kolkata');

// Include the database connection
require 'admin/shared_components/db.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust path as needed

// Function to format days smartly (Mon-Thu, Fri-Sat, etc.)
function formatDaysRange($daysString) {
    if (empty($daysString)) {
        return '';
    }
    
    $dayOrder = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $dayMap = array_flip($dayOrder);
    
    // Parse the days string and convert to array
    $days = array_map('trim', explode(',', $daysString));
    
    // Convert day names to indices and sort
    $dayIndices = [];
    foreach ($days as $day) {
        if (isset($dayMap[$day])) {
            $dayIndices[] = $dayMap[$day];
        }
    }
    
    if (empty($dayIndices)) {
        return '';
    }
    
    sort($dayIndices);
    
    // Group consecutive days
    $ranges = [];
    $start = $dayIndices[0];
    $end = $dayIndices[0];
    
    for ($i = 1; $i < count($dayIndices); $i++) {
        if ($dayIndices[$i] == $end + 1) {
            $end = $dayIndices[$i];
        } else {
            // Add current range
            if ($start == $end) {
                $ranges[] = $dayOrder[$start];
            } else if ($end == $start + 1) {
                $ranges[] = $dayOrder[$start] . ', ' . $dayOrder[$end];
            } else {
                $ranges[] = $dayOrder[$start] . '-' . $dayOrder[$end];
            }
            $start = $end = $dayIndices[$i];
        }
    }
    
    // Add the last range
    if ($start == $end) {
        $ranges[] = $dayOrder[$start];
    } else if ($end == $start + 1) {
        $ranges[] = $dayOrder[$start] . ', ' . $dayOrder[$end];
    } else {
        $ranges[] = $dayOrder[$start] . '-' . $dayOrder[$end];
    }
    
    return implode(', ', $ranges);
}

// Fetch site details from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM site_details WHERE id = 1");
    $stmt->execute();
    $site_details = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle database errors gracefully
    $site_details = []; // Set to empty array to avoid errors later
    error_log("Failed to fetch site details: " . $e->getMessage());
}

// Initialize variables for user feedback
$success_message = "";
$error_message = "";
$show_modal = false; // Flag to control modal display

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $first_name = !empty($_POST['first_name']) ? htmlspecialchars(trim($_POST['first_name'])) : null;
    $last_name = !empty($_POST['last_name']) ? htmlspecialchars(trim($_POST['last_name'])) : null;
    $email = !empty($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : null;
    $phone_number = !empty($_POST['phone_number']) ? htmlspecialchars(trim($_POST['phone_number'])) : null;
    $subject = !empty($_POST['subject']) ? htmlspecialchars(trim($_POST['subject'])) : null;
    $message = !empty($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : null;

    $full_name = trim($first_name . " " . $last_name);

    // Basic Server-Side Validation
    $errors = [];
    if (empty($full_name))
        $errors[] = "Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "A valid email address is required.";
    if (empty($phone_number) || !preg_match('/^\+?[0-9]{10,14}$/', $phone_number))
        $errors[] = "A valid phone number is required.";
    if (empty($subject))
        $errors[] = "The subject is required.";
    if (empty($message))
        $errors[] = "A message is required.";

    // If validation passes, insert the data and send email
    if (empty($errors)) {
        try {
            // Insert into database
            $stmt = $pdo->prepare(
                "INSERT INTO contact_enquiry (name, email, phone_number, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$full_name, $email, $phone_number, $subject, $message]);

            // Send email to admin using PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com'; // Set your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'connect@theurbandesign.in'; // Your email
                $mail->Password = 'TheUrbanDesign@123';    // Your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
               // Recipients - Use your admin email as sender with a clear identifier
$mail->setFrom('connect@theurbandesign.in', 'The Urban Design Contact Form');
$mail->addAddress('connect@theurbandesign.in', 'Admin'); // Admin email
$mail->addReplyTo($email, $full_name); // When admin replies, it goes to the user

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'New Contact Form Submission: ' . $subject;

                $emailBody = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                        .content { background-color: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                        th { background-color: #007bff; color: white; padding: 12px; text-align: left; font-weight: bold; }
                        td { padding: 12px; border-bottom: 1px solid #dee2e6; }
                        tr:last-child td { border-bottom: none; }
                        .message-row td { vertical-align: top; }
                        .message-content { background-color: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; }
                        a { color: #007bff; text-decoration: none; }
                        a:hover { text-decoration: underline; }
                        .footer { text-align: center; margin-top: 20px; color: #6c757d; font-size: 14px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>New Contact Form Submission</h2>
                        </div>
                        <div class='content'>
                            <table>
                                <thead>
                                    <tr>
                                        <th colspan='2'>Contact Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td>{$full_name}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><a href='mailto:{$email}'>{$email}</a></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td><a href='tel:{$phone_number}'>{$phone_number}</a></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Subject:</strong></td>
                                        <td>{$subject}</td>
                                    </tr>
                                    <tr class='message-row'>
                                        <td><strong>Message:</strong></td>
                                        <td>
                                            <div class='message-content'>
                                                " . nl2br(htmlspecialchars($message)) . "
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Submitted:</strong></td>
                                        <td>" . date('l, F j, Y \a\t g:i A') . "</td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        </div>
                    </div>
                </body>
                </html>
                ";

                $mail->Body = $emailBody;

                $mail->send();
                $success_message = "Your message has been sent successfully!";
                $show_modal = true;

            } catch (Exception $e) {
                error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                $success_message = "Your message has been sent successfully!";
                $show_modal = true;
            }

        } catch (PDOException $e) {
            $error_message = "We're sorry, but there was an error sending your message. Please try again later.";
            error_log($e->getMessage());
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Process the days and closed days for display
$formatted_open_days = formatDaysRange($site_details['open_days'] ?? '');
$formatted_closed_days = formatDaysRange($site_details['closed_days'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.php' ?>
    <style>
        /* Modal Styles */
        .success-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 30px;
            border: none;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            margin-bottom: 15px;
            justify-content: center;
        }

        .modal-header h2 {
            color: #28a745;
            margin: 0;
            font-size: 1.8rem;
            font-weight: bold;
        }

        .modal-body {
            margin-bottom: 25px;
        }

        .modal-body p {
            font-size: 1rem;
            color: #333;
            margin: 8px 0;
            line-height: 1.5;
        }

        .modal-close {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }

        .modal-close:hover {
            background-color: #0056b3;
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
                                <h2 class="title">Contact Us</h2>
                            </div>
                            <div class="breadcrumb-wrapper">
                                <div class="container">
                                    <div class="breadcrumb-wrapper-inner">
                                        <span>
                                            <a title="Go to Delmont." href="index.php" class="home"><i
                                                    class="themifyicon ti-home"></i>&nbsp;&nbsp;Home</a>
                                        </span>
                                        <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                        <span>Contact Us</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="site-main">

            <section class="ttm-row pt-85 res-991-pt-45 pb-0 res-991-pb-0 clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="featured-icon-box icon-align-top-content style7 w-100">
                                <div class="featured-inner">
                                    <div class="featured-icon">
                                        <div
                                            class="ttm-icon ttm-icon_element-onlytxt ttm-icon_element-color-skincolor ttm-icon_element-size-md ttm-icon_element-style-square">
                                            <i class="flaticon flaticon-call-1"></i>
                                        </div>
                                    </div>
                                    <div class="featured-content">
                                        <div class="featured-title">
                                            <h3>Phone Number</h3>
                                        </div>
                                        <div class="featured-desc">
                                            <p><?php echo htmlspecialchars($site_details['phone_number'] ?? 'Not Available'); ?></p>
                                            <?php if (!empty($site_details['alternate_phone'])): ?>
                                                <p><?php echo htmlspecialchars($site_details['alternate_phone']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="featured-icon-box icon-align-top-content style7 w-100">
                                <div class="featured-inner">
                                    <div class="featured-icon">
                                        <div
                                            class="ttm-icon ttm-icon_element-onlytxt ttm-icon_element-color-skincolor ttm-icon_element-size-md ttm-icon_element-style-square">
                                            <i class="flaticon flaticon-email"></i>
                                        </div>
                                    </div>
                                    <div class="featured-content">
                                        <div class="featured-title">
                                            <h3>Email Address</h3>
                                        </div>
                                        <div class="featured-desc">
                                            <p><?php echo htmlspecialchars($site_details['email'] ?? 'Not Available'); ?></p>
                                            <?php if (!empty($site_details['alternate_email'])): ?>
                                                <p><?php echo htmlspecialchars($site_details['alternate_email']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="featured-icon-box icon-align-top-content style7 w-100">
                                <div class="featured-inner">
                                    <div class="featured-icon">
                                        <div
                                            class="ttm-icon ttm-icon_element-onlytxt ttm-icon_element-color-skincolor ttm-icon_element-size-md ttm-icon_element-style-square">
                                            <i class="flaticon flaticon-navigation"></i>
                                        </div>
                                    </div>
                                    <div class="featured-content">
                                        <div class="featured-title">
                                            <h3>Visit Us On</h3>
                                        </div>
                                        <div class="featured-desc"  style="padding: 0px 30px 0px 0px;">
                                            <p><?php echo nl2br(htmlspecialchars($site_details['address'] ?? 'Not Available')); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="featured-icon-box icon-align-top-content style7 w-100">
                                <div class="featured-inner">
                                    <div class="featured-icon">
                                        <div
                                            class="ttm-icon ttm-icon_element-onlytxt ttm-icon_element-color-skincolor ttm-icon_element-size-md ttm-icon_element-style-square">
                                            <i class="flaticon flaticon-wall-clock"></i>
                                        </div>
                                    </div>
                                    <div class="featured-content">
                                        <div class="featured-title">
                                            <h3>Visit Between</h3>
                                        </div>
                                        <div class="featured-desc">
                                            <?php if ($formatted_open_days && $site_details['open_time']): ?>
                                                <p><strong><?php echo $formatted_open_days; ?></strong>: 
                                                   <?php echo htmlspecialchars($site_details['open_time']); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if ($formatted_closed_days): ?>
                                                <p><strong><?php echo $formatted_closed_days; ?></strong>: Closed</p>
                                            <?php endif; ?>
                                            
                                            <?php if (!$formatted_open_days && !$formatted_closed_days): ?>
                                                <p>Business hours not set</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="ttm-row conatact-section bg-layer-equal-height mt_15 clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4">
                            <div
                                class="col-bg-img-eight ttm-bg ttm-col-bgimage-yes ttm-textcolor-white spacing-11 z-index-2">
                                <div class="ttm-col-wrapper-bg-layer ttm-bg-layer"></div>
                                <div class="layer-content">
                                    <h3 class="mb-5 fontweight-bold">Ready to create a space that truly inspires?</h3>
                                    <p>We'd love to learn more about it. Get in touch with us, and let's explore how we
                                        can work together.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="ttm-bg ttm-col-bgcolor-yes ttm-right-span spacing-12">
                                <div class="ttm-col-wrapper-bg-layer ttm-bg-layer"></div>
                                <div class="layer-content">
                                    <div class="section-title">
                                        <div class="title-header">
                                            <h2 class="title">Share Your Vision With Us</h2>
                                        </div>
                                    </div>
                                    <div class="padding_top30">
                                        <?php if (!empty($error_message)): ?>
                                            <div class="alert alert-danger" role="alert"
                                                style="color: red; border: 1px solid red; padding: 15px; margin-bottom: 20px;">
                                                <?php echo $error_message; ?>
                                            </div>
                                        <?php endif; ?>

                                        <form id="contact_form" class="contact_form wrap-form clearfix" method="post"
                                            action="contact-us">
                                            <div class="row ttm-boxes-spacing-20px">
                                                <div class="col-md-6">
                                                    <label>
                                                        <span class="text-input"><input name="first_name" type="text"
                                                                value="" placeholder="First Name..."
                                                                required="required"></span>
                                                    </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>
                                                        <span class="text-input"><input name="last_name" type="text"
                                                                value="" placeholder="Last Name..."
                                                                required="required"></span>
                                                    </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>
                                                        <span class="text-input"><input name="email" type="email"
                                                                value="" placeholder="Email Address..."
                                                                required="required"></span>
                                                    </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>
                                                        <span class="text-input"><input name="phone_number" type="text"
                                                                value="" placeholder="Phone Number..."
                                                                required="required"></span>
                                                    </label>
                                                </div>
                                                <div class="col-md-12">
                                                    <label>
                                                        <span class="text-input"><input name="subject" type="text"
                                                                value="" placeholder="Subject..."
                                                                required="required"></span>
                                                    </label>
                                                </div>
                                                <div class="col-md-12">
                                                    <label>
                                                        <span class="text-input"><textarea name="message" cols="50"
                                                                rows="5" placeholder="Enter Message Here..."
                                                                required="required"></textarea></span>
                                                    </label>
                                                </div>
                                                <!-- This should be placed before the submit button (you already have this) -->
<div class="g-recaptcha" data-sitekey="6LcWjNQrAAAAAOgD4ER00Ac-ojGN6cFUpSN74Mrf"></div>
                                                <div class="col-lg-12">
                                                    <button id="submitBtn"
                                                        class="ttm-btn ttm-btn-size-md ttm-btn-shape-rounded ttm-btn-style-fill ttm-icon-btn-right ttm-btn-color-skincolor mt-15"
                                                        type="submit">Submit</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

           <div id="google_map" class="google_map">
    <div class="map_container" style="cursor: pointer;">
        <div id="map">
            <?php echo $site_details['map_iframe'] ?? '<p>Map not available.</p>'; ?>
        </div>
    </div>
</div>



        </div>

        <div id="successModal" class="success-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Thank You!</h2>
                </div>
                <div class="modal-body">
                    <p><strong>Your message has been sent successfully!</strong></p>
                    <p>We will contact you shortly.</p>
                </div>
                <button class="modal-close" onclick="closeModal()">OK</button>
            </div>
        </div>

        
    </div>
    <?php include 'footer.php' ?>
    <?php include 'scripts.php' ?>

 <script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Part 1: Show the success modal if PHP says so ---
    <?php if ($show_modal): ?>
        var successModal = document.getElementById('successModal');
        if (successModal) {
            successModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    <?php endif; ?>

    // --- Part 2: Handle the form submission with reCAPTCHA validation ---
    var form = document.getElementById('contact_form');
    var submitBtn = document.getElementById('submitBtn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            // Check if reCAPTCHA is completed
            var recaptchaResponse = grecaptcha.getResponse();
            
            if (!recaptchaResponse) {
                e.preventDefault(); // Prevent form submission
                alert('Please complete the reCAPTCHA verification.');
                return false;
            }
            
            // If reCAPTCHA is completed, proceed with form submission
            submitBtn.innerHTML = 'Submitting...';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';
            submitBtn.style.cursor = 'not-allowed';
        });
    }
});

// This function is called by the 'onclick' attribute, so it should remain in the global scope.
function closeModal() {
    var modal = document.getElementById('successModal');
    var form = document.getElementById('contact_form');
    var submitBtn = document.getElementById('submitBtn');

    if (modal) {
        modal.style.display = 'none';
    }
    document.body.style.overflow = 'auto';
    
    if (form) {
        form.reset();
        // Reset reCAPTCHA
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.reset();
        }
    }
    
    if (submitBtn) {
        submitBtn.innerHTML = 'Submit';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }
}

// This can also remain global.
window.onclick = function(event) {
    var modal = document.getElementById('successModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<script>
document.querySelector('#google_map').addEventListener('click', function() {
    var iframe = this.querySelector('iframe');
    if (iframe && iframe.src) {
        window.open(iframe.src, '_blank');
    }
});
</script>
    <!-- Add this in your head.php or before closing </head> tag -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</body>

</html>