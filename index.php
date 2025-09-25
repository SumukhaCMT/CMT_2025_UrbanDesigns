<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone to IST
date_default_timezone_set('Asia/Kolkata');

require_once 'admin/shared_components/db.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust path as needed

$success_message = '';
$error_message = '';
$show_modal = false; // Flag to control modal display

// Initialize variables to preserve form data
$first_name = '';
$last_name = '';
$email = '';
$phone_number = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form_index'])) {
    // Get and sanitize input
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    $errors = [];

    if (empty($first_name))
        $errors[] = "First name is required";
    if (empty($last_name))
        $errors[] = "Last name is required";
    if (empty($email))
        $errors[] = "Email is required";
    if (empty($phone_number))
        $errors[] = "Phone number is required";
    if (empty($subject))
        $errors[] = "Subject is required";
    if (empty($message))
        $errors[] = "Message is required";

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }

    if (!empty($errors)) {
        $error_message = implode("<br>", $errors);
    } else {
        $name = $first_name . ' ' . $last_name;

        try {
            // Insert into database
            $stmt = $pdo->prepare("
                INSERT INTO contact_enquiry (name, email, phone_number, subject, message, created_at) 
                VALUES (:name, :email, :phone, :subject, :message, NOW())
            ");

            $result = $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone_number,
                ':subject' => $subject,
                ':message' => $message
            ]);

            if ($result) {
                // Send email to admin using PHPMailer
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.hostinger.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'connect@theurbandesign.in'; // Your email
                    $mail->Password = 'TheUrbanDesign@123';    // Your app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom($email, $name);
                    $mail->addAddress('connect@theurbandesign.in', 'Admin'); // Admin email
                    $mail->addReplyTo($email, $name);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'New Appointment Request: ' . $subject;

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
                                <h2>New Appointment Request</h2>
                            </div>
                            <div class='content'>
                                <table>
                                    <thead>
                                        <tr>
                                            <th colspan='2'>Appointment Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{$name}</td>
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
                    $success_message = "Your appointment has been booked successfully!";
                    $show_modal = true;

                    // Clear form data after successful submission
                    $first_name = '';
                    $last_name = '';
                    $email = '';
                    $phone_number = '';
                    $subject = '';
                    $message = '';

                } catch (Exception $e) {
                    error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                    $success_message = "Your appointment has been booked successfully!";
                    $show_modal = true;

                    // Clear form data even if email fails
                    $first_name = '';
                    $last_name = '';
                    $email = '';
                    $phone_number = '';
                    $subject = '';
                    $message = '';
                }

            } else {
                $error_message = "Failed to submit your request. Please try again.";
            }

        } catch (PDOException $e) {
            $error_message = "Database error. Please try again later.";
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head><?php include 'head.php' ?></head>

<body>
    <!--page start-->
    <div class="page ttm-bgcolor-grey">

        <?php include 'header.php' ?>

        <?php
        // Include your database connection file
        require 'admin/shared_components/db.php';

        // Fetch all active slider items from the database, ordered by slide_order
        try {
            $stmt = $pdo->prepare("SELECT * FROM main_slider WHERE is_active = 1 ORDER BY slide_order ASC");
            $stmt->execute();
            $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle database errors gracefully
            // For a live site, you might log the error and show a generic message
            // For development, you can display the error:
            // die("Database error: " . $e->getMessage());
            $sliders = []; // Set sliders to an empty array to prevent further errors
        }
        ?>

        <div class="ttm-rev_slider-wide">
            <rs-module-wrap id="rev_slider_1_1_wrapper" data-source="gallery">
                <rs-module id="rev_slider_1_1" style="display:none;" data-version="6.1.8">
                    <rs-slides>
                        <?php if (!empty($sliders)): ?>
                            <?php foreach ($sliders as $slider): ?>
                                <?php
                                // Split the title into two lines based on the newline character
                                // This allows for the two-part title effect
                                $title_parts = preg_split("/\r\n|\n|\r/", htmlspecialchars($slider['title']));
                                $title_line_1 = $title_parts[0] ?? '';
                                $title_line_2 = $title_parts[1] ?? '';
                                ?>
                                <rs-slide data-key="rs-<?php echo $slider['id']; ?>"
                                    data-title="<?php echo htmlspecialchars($slider['image_alt']); ?>"
                                    data-thumb="admin/assets/images/backgrounds/<?php echo htmlspecialchars($slider['image']); ?>"
                                    data-anim="ei:d;eo:d;s:d;r:0;t:grayscalecross;sl:d;">

                                    <img src="admin/assets/images/backgrounds/<?php echo htmlspecialchars($slider['image']); ?>"
                                        title="<?php echo htmlspecialchars($slider['image_alt']); ?>"
                                        alt="<?php echo htmlspecialchars($slider['image_alt']); ?>" width="1920" height="853"
                                        class="rev-slidebg" data-no-retina>

                                    <?php if (!empty($title_line_1)): ?>
                                        <rs-layer id="slider-<?php echo $slider['id']; ?>-layer-2" class="tm-solution-text"
                                            data-type="text" data-rsp_ch="on" data-xy="x:c;yo:387px,360px,192px,150px;"
                                            data-text="w:normal;s:70,70,50,40;l:72,72,45,38;fw:700;"
                                            data-border="bor:1px,1px,1px,1px;" data-frame_0="y:100%;" data-frame_0_mask="u:t;"
                                            data-frame_1="sp:1200;" data-frame_1_mask="u:t;" data-frame_999="o:0;st:w;"
                                            style="z-index:15;font-family:Raleway; background-color: transparent; -webkit-text-stroke: 1px #fff; -webkit-text-fill-color: transparent;">
                                            <?php echo $title_line_1; ?>
                                        </rs-layer>
                                    <?php endif; ?>

                                    <?php if (!empty($title_line_2)): ?>
                                        <rs-layer id="slider-<?php echo $slider['id']; ?>-layer-3" data-type="text" data-rsp_ch="on"
                                            data-xy="x:c;xo:1px,1px,0,0;yo:464px,437px,255px,202px;"
                                            data-text="w:normal;s:70,70,50,40;l:72,72,45,38;fw:700;" data-frame_0="y:100%;"
                                            data-frame_0_mask="u:t;" data-frame_1="sp:1200;" data-frame_1_mask="u:t;"
                                            data-frame_999="o:0;st:w;" style="z-index:14;font-family:Raleway;">
                                            <?php echo $title_line_2; ?>
                                        </rs-layer>
                                    <?php endif; ?>

                                    <?php if (!empty($slider['subtitle'])): ?>
                                        <rs-layer id="slider-<?php echo $slider['id']; ?>-layer-4" data-type="text" data-rsp_ch="on"
                                            data-xy="x:c;yo:560px,533px,283px,174px;"
                                            data-text="w:normal;s:15,15,15,15;l:26,26,26,26;a:center;" data-frame_0="y:100%;"
                                            data-vbility="t,t,f,f" data-frame_0_mask="u:t;" data-frame_1="sp:1200;"
                                            data-frame_1_mask="u:t;" data-frame_999="o:0;st:w;"
                                            style="z-index:13;font-family:Spartan;">
                                            <?php echo nl2br(htmlspecialchars($slider['subtitle'])); ?>
                                        </rs-layer>
                                    <?php endif; ?>

                                    <a id="slider-<?php echo $slider['id']; ?>-layer-5" class="rs-layer" href="about-us"
                                        target="_self" data-type="text" data-color="#ea8031" data-rsp_ch="on"
                                        data-xy="x:c;yo:645px,618px,330px,258px;"
                                        data-text="w:normal;s:16,16,14,12;l:25,25,9,6;fw:700;a:center;"
                                        data-padding="t:13,13,15,10;r:35,35,30,22;b:12,12,15,10;l:35,35,30,22;"
                                        data-border="bor:5px,5px,5px,5px;" data-frame_0="y:100%;" data-frame_0_mask="u:t;"
                                        data-frame_1="sp:1200;" data-frame_1_mask="u:t;" data-frame_999="o:0;st:w;"
                                        data-frame_hover="c:#ea8031;bgc:#1f232c;bor:5px,5px,5px,5px;"
                                        style="z-index:12;background-color:#ffffff;font-family:Raleway;">
                                        View More!
                                    </a>

                                    <rs-layer id="slider-<?php echo $slider['id']; ?>-layer-6" data-type="shape"
                                        data-rsp_ch="on" data-xy="x:c;yo:307px,280px,123px,75px;"
                                        data-text="w:normal;s:20,20,7,4;l:0,0,9,6;"
                                        data-dim="w:853px,853px,344px,212px;h:10px,10px,3px,1px;" data-vbility="t,t,f,f"
                                        data-frame_0="y:50,50,19,11;" data-frame_1="sp:1000;" data-frame_999="o:0;st:w;"
                                        style="z-index:9;background-color:rgba(255,255,255,0.4);"></rs-layer>
                                    <rs-layer id="slider-<?php echo $slider['id']; ?>-layer-7" data-type="shape"
                                        data-rsp_ch="on" data-xy="x:c;yo:730px,703px,295px,182px;"
                                        data-text="w:normal;s:20,20,7,4;l:0,0,9,6;"
                                        data-dim="w:853px,853px,344px,212px;h:10px,10px,3px,1px;" data-vbility="t,t,f,f"
                                        data-frame_0="y:50,50,19,11;" data-frame_1="sp:1000;" data-frame_999="o:0;st:w;"
                                        style="z-index:8;background-color:rgba(255,255,255,0.4);"></rs-layer>
                                    <rs-layer id="slider-<?php echo $slider['id']; ?>-layer-8" data-type="shape"
                                        data-rsp_ch="on" data-xy="xo:533px,533px,-134px,-82px;yo:317px,290px,128px,78px;"
                                        data-text="w:normal;s:20,20,7,4;l:0,0,9,6;"
                                        data-dim="w:10px,10px,3px,1px;h:413px,413px,167px,103px;" data-vbility="t,t,f,f"
                                        data-frame_0="y:50,50,19,11;" data-frame_1="sp:1000;" data-frame_999="o:0;st:w;"
                                        style="z-index:17;background-color:rgba(255,255,255,0.4);"></rs-layer>
                                    <rs-layer id="slider-<?php echo $slider['id']; ?>-layer-9" data-type="shape"
                                        data-rsp_ch="on" data-xy="x:c;xo:421px,421px,-435px,-268px;yo:317px,290px,128px,78px;"
                                        data-text="w:normal;s:20,20,7,4;l:0,0,9,6;"
                                        data-dim="w:10px,10px,3px,1px;h:413px,413px,167px,103px;" data-vbility="t,t,f,f"
                                        data-frame_0="y:50,50,19,11;" data-frame_1="sp:1000;" data-frame_999="o:0;st:w;"
                                        style="z-index:16;background-color:rgba(255,255,255,0.4);"></rs-layer>
                                </rs-slide>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <rs-slide data-key="rs-fallback" data-title="Slide"
                                data-thumb="images/slides/slider-mainbg-001.jpg"
                                data-anim="ei:d;eo:d;s:d;r:0;t:grayscalecross;sl:d;">
                                <img src="images/slides/slider-mainbg-001.jpg" title="slider-bg001" width="1920"
                                    height="853" class="rev-slidebg" data-no-retina>
                                <rs-layer id="fallback-layer-3" data-type="text" data-rsp_ch="on"
                                    data-xy="x:c;yo:464px,437px,255px,202px;"
                                    data-text="w:normal;s:70,70,50,40;l:72,72,45,38;fw:700;" data-frame_999="o:0;st:w;"
                                    style="z-index:14;font-family:Raleway;">
                                    Welcome To Our Site
                                </rs-layer>
                            </rs-slide>
                        <?php endif; ?>
                    </rs-slides>
                    <rs-progress class="rs-bottom" style="visibility: hidden !important;"></rs-progress>
                </rs-module>
            </rs-module-wrap>
        </div>

        <!--site-main start-->
        <div class="site-main">


            <!--top_zero_padding-section-->
            <section class="ttm-row top_zero_padding-section clearfix">
                <div class="container">
                    <!--row-->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt_105 res-1199-mt-0 res-991-mt-30">
                                <div class="row">
                                    <div class="col-lg-4 col-md-12 col-sm-12">
                                        <!--featured-icon-box-->
                                        <div class="featured-icon-box icon-align-top-content style15">
                                            <div class="featured-icon">
                                                <div
                                                    class="ttm-icon ttm-icon_element-onlytxt ttm-icon_element-color-skincolor ttm-icon_element-size-lg">
                                                    <i class="flaticon flaticon-apartment" style="color:#d62828;"></i>
                                                </div>
                                            </div>
                                            <div class="featured-content">
                                                <span class="ttm-subheading">01</span>
                                                <div class="featured-title">
                                                    <h3><a href="service-details" tabindex="0">Residential Solutions</a>
                                                    </h3>
                                                </div>
                                                <div class="featured-desc">
                                                    <p>Expertly crafted residential design and planning services.</p>
                                                </div>
                                            </div>
                                            <div class="ttm-iconbox-button">
                                                <a href="service-details" class="mega-menu-link"><i
                                                        class="ti ti-arrow-up"></i></a>
                                            </div>
                                        </div><!--featured-icon-box end-->
                                    </div>
                                    <div class="col-lg-4 col-md-12 col-sm-12">
                                        <!--featured-icon-box-->
                                        <div class="featured-icon-box icon-align-top-content style15">
                                            <div class="featured-icon">
                                                <div
                                                    class="ttm-icon ttm-icon_element-onlytxt ttm-icon_element-color-skincolor ttm-icon_element-size-lg">
                                                    <i class="flaticon flaticon-interior-design"
                                                        style="color:#d62828;"></i>
                                                </div>
                                            </div>
                                            <div class="featured-content">
                                                <span class="ttm-subheading">02</span>
                                                <div class="featured-title">
                                                    <h3><a href="service-details" tabindex="0">Commercial Solutions</a>
                                                    </h3>
                                                </div>
                                                <div class="featured-desc">
                                                    <p>Innovative commercial space planning and execution.</p>
                                                </div>
                                            </div>
                                            <div class="ttm-iconbox-button">
                                                <a href="service-details" class="mega-menu-link"><i
                                                        class="ti ti-arrow-up"></i></a>
                                            </div>
                                        </div><!--featured-icon-box end-->
                                    </div>
                                    <div class="col-lg-4 col-md-12 col-sm-12">
                                        <!--featured-icon-box-->
                                        <div class="featured-icon-box icon-align-top-content style15">
                                            <div class="featured-icon">
                                                <div
                                                    class="ttm-icon ttm-icon_element-onlytxt ttm-icon_element-color-skincolor ttm-icon_element-size-lg">
                                                    <i class="flaticon flaticon-home-decoration"
                                                        style="color:#d62828;"></i>
                                                </div>
                                            </div>
                                            <div class="featured-content">
                                                <span class="ttm-subheading">03</span>
                                                <div class="featured-title">
                                                    <h3><a href="service-details" tabindex="0">Turnkey Project
                                                            Management</a></h3>
                                                </div>
                                                <div class="featured-desc">
                                                    <p>Tailored designs to meet your project needs.</p>
                                                </div>
                                            </div>
                                            <div class="ttm-iconbox-button">
                                                <a href="service-details" class="mega-menu-link"><i
                                                        class="ti ti-arrow-up"></i></a>
                                            </div>
                                        </div><!--featured-icon-box end-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!--row end-->

            </section>
            <!--top_zero_padding-section end-->


            <?php
            // Include your database connection file
            require 'admin/shared_components/db.php';

            // Fetch the content for the INDEX page (id = 2)
            try {
                // MODIFIED: The query now selects the correct row for the index page.
                $about_stmt = $pdo->prepare("SELECT * FROM about_us WHERE id = 2 LIMIT 1");
                $about_stmt->execute();
                $about_data = $about_stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // To prevent site errors, initialize as an empty array on failure
                $about_data = [];
            }
            ?>

            <section class="ttm-row about-section_1 clearfix">
                <div class="container">
                    <div class="row no-gutters">
                        <div class="col-lg-4">
                            <div class="ttm_single_image-wrapper mt_52 res-991-mt-0 mr_120 res-991-mr-0">
                                <img width="500" height="630" class="img-fluid"
                                    src="admin/assets/images/about/<?php echo htmlspecialchars($about_data['image'] ?? ''); ?>"
                                    alt="<?php echo htmlspecialchars($about_data['img_alt'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="ttm-bgcolor-grey position-relative z-index-1 spacing-15">

                                <?php if ($about_data): ?>
                                    <div class="section-title">
                                        <div class="title-heade">
                                            <h3><?php echo htmlspecialchars($about_data['subtitle'] ?? ''); ?></h3>
                                            <h2 class="title"><?php echo htmlspecialchars($about_data['title'] ?? ''); ?>
                                            </h2>
                                        </div>
                                    </div>
                                    <div class="pt-10 res-991-pt-0">
                                        <p><?php echo $about_data['content'] ?? ''; ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="section-title">
                                        <div class="title-heade">
                                            <h3>About us</h3>
                                            <h2 class="title">Your Vision, Our Expertise</h2>
                                        </div>
                                    </div>
                                    <div class="pt-10 res-991-pt-0">
                                        <p>At The Urban Design, we specialize in turnkey projects...</p>
                                    </div>
                                <?php endif; ?>

                                <a class="ttm-btn btn-inline ttm-btn-size-md ttm-icon-btn-right ttm-btn-color-skincolor mt-15"
                                    href="about-us">Know More</a>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="ttm_single_image-wrapper ml_115 res-991-ml-0">
                                <img width="500" height="630" class="img-fluid"
                                    src="admin/assets/images/about/<?php echo htmlspecialchars($about_data['image_2'] ?? ''); ?>"
                                    alt="<?php echo htmlspecialchars($about_data['img_alt_2'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="ttm-row services-section_3 bg-layer-equal-height bg-img1 ttm-bgcolor-grey clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            <div class="section-title style2">
                                <div class="title-header">
                                    <h3>Our Services</h3>
                                    <h2 class="title">Our Featured Services Interior Design</h2>
                                </div>
                                <div class="title-desc">
                                    <p>We offer expertly planned and executed commercial and residential turnkey
                                        projects tailored to your requirements.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="ttm_single_image-wrapper">
                                <img width="370" height="630" class="img-fluid position-relative z-index-1"
                                    src="images/single-img-14.jpg" alt="single_14">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="row">
                                <?php
                                try {
                                    // CORRECTED: Fetch active services using PDO, consistent with the rest of the page
                                    $stmt = $pdo->prepare("SELECT service_title, service_description, img_name, redirection_link FROM service WHERE status = 'active' ORDER BY sort_order ASC LIMIT 6");
                                    $stmt->execute();
                                    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    // Check if there are any services to display
                                    if ($services) {
                                        // Loop through each service and display it
                                        foreach ($services as $service) {
                                            // Define the path to the icon, with a fallback for missing icons
                                            $icon_path = !empty($service['img_name']) ? 'admin/assets/images/service_icons/' . htmlspecialchars($service['img_name']) : 'images/icon-img-default.svg';
                                            ?>
                                            <div class="col-lg-4 col-md-6">
                                                <a href="services/<?php echo htmlspecialchars($service['redirection_link']); ?>"
                                                    class="text-decoration-none">
                                                    <div class="featured-icon-box top-icon style9">
                                                        <div class="featured-icon">
                                                            <div
                                                                class="ttm-icon ttm-icon_element-style-square ttm-icon_element-fill ttm-icon_element-size-lg ttm-icon_element-color-white">
                                                                <img width="64" height="64" class="img-fluid"
                                                                    src="<?php echo $icon_path; ?>"
                                                                    alt="<?php echo htmlspecialchars($service['service_title']); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="featured-content">
                                                            <div class="featured-title">
                                                                <h3><?php echo htmlspecialchars($service['service_title']); ?></h3>
                                                            </div>
                                                            <div class="featured-desc">
                                                                <p><?php echo htmlspecialchars($service['service_description']); ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <?php
                                        } // end foreach loop
                                    } else {
                                        // Optional: Display a message if no services are found
                                        echo '<div class="col-12"><p>No services are available at the moment.</p></div>';
                                    }
                                } catch (PDOException $e) {
                                    // Handle potential database errors gracefully
                                    echo '<div class="col-12"><p>Unable to load services at this time.</p></div>';
                                    // For development, you might want to see the error:
                                    // echo 'Database Error: ' . $e->getMessage();
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


            <?php
            // Include your database connection file
// This path should be correct relative to your page's location
            require 'admin/shared_components/db.php';

            // Fetch the content from the why_choose_us table
            try {
                $wcu_stmt = $pdo->prepare("SELECT * FROM why_choose_us WHERE id = 1 LIMIT 1");
                $wcu_stmt->execute();
                $wcu_data = $wcu_stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Set to null on error to prevent breaking the page
                $wcu_data = null;
            }
            ?>

            <section class="ttm-row process-section clearfix">
                <div class="container">
                    <div class="row equal-height-row">
                        <?php if ($wcu_data):  // Only display the section if data was found ?>
                            <div class="col-xl-7 col-lg-6 col-md-12">
                                <div class="pr-50 res-991-pr-0 full-height-wrapper">
                                    <div class="ttm_single_image-wrapper full-height-bg"
                                        style="background-image: url('<?php echo htmlspecialchars($wcu_data['background_image']); ?>');">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-5 col-lg-6 col-md-12">
                                <div class="pt-20 res-991-pt-40 pl-30 res-991-pl-0 ml_30 res-991-ml-0 border-left">
                                    <div class="section-title">
                                        <div class="title-header">
                                            <h3>WHY CHOOSE US</h3>
                                            <h2 class="title"> Where Quality Meets Innovation.</h2>
                                        </div>
                                        <div class="title-desc">
                                            <p><?php echo htmlspecialchars($wcu_data['section_description']); ?></p>
                                        </div>
                                    </div>
                                    <div class="pt-10 res-991-pt-15">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12">

                                                <?php
                                                // Loop through all 5 points dynamically
                                                for ($i = 1; $i <= 5; $i++) {
                                                    $point_title = $wcu_data["point_{$i}_title"];
                                                    $point_description = $wcu_data["point_{$i}_description"];
                                                    $point_icon = $wcu_data["point_{$i}_icon"] ?? 'mdi mdi-check-circle';
                                                    $point_icon_type = $wcu_data["point_{$i}_icon_type"] ?? 'class';
                                                    $point_icon_file = $wcu_data["point_{$i}_icon_file"] ?? '';

                                                    // Only display if title is not empty
                                                    if (!empty($point_title)):
                                                        ?>
                                                        <div
                                                            class="featured-icon-box icon-align-before-content icon-ver_align-top style20">
                                                            <div class="featured-icon">
                                                                <div
                                                                    class="ttm-icon ttm-icon_element-fill ttm-icon_element-size-xs ttm-icon_element-style-rounded custom-icon-wrapper">
                                                                    <?php if ($point_icon_type === 'upload' && !empty($point_icon_file)): ?>
                                                                        <!-- Custom uploaded icon -->
                                                                        <img src="<?php echo htmlspecialchars($point_icon_file); ?>"
                                                                            alt="<?php echo htmlspecialchars($point_title); ?> Icon"
                                                                            class="custom-uploaded-icon">
                                                                    <?php else: ?>
                                                                        <!-- MDI Icon class/font icon -->
                                                                        <i class="<?php echo htmlspecialchars($point_icon); ?>"></i>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="featured-content">
                                                                <div class="featured-title">
                                                                    <h3><?php echo htmlspecialchars($point_title); ?></h3>
                                                                </div>
                                                                <?php if (!empty($point_description)): ?>
                                                                    <div class="featured-desc">
                                                                        <p><?php echo htmlspecialchars($point_description); ?></p>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    endif;
                                                }
                                                ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <?php
            // Include your database connection file
// Make sure the path is correct from your front-end file's location
            require 'admin/shared_components/db.php';

            // Fetch the section heading and title
            $title_stmt = $pdo->prepare("SELECT * FROM testimonials_title WHERE id = 1");
            $title_stmt->execute();
            $testimonial_section = $title_stmt->fetch(PDO::FETCH_ASSOC);

            // Fetch all testimonials from the database
            $testimonials_stmt = $pdo->prepare("SELECT * FROM testimonials ORDER BY created_at DESC");
            $testimonials_stmt->execute();
            $testimonials_data = $testimonials_stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <section class="ttm-row services-section_1 bg-img5 clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="pr-100 res-991-pr-0">
                                <div class="row-title style2">
                                    <div class="section-title">
                                        <div class="title-header">
                                            <h2 class="title">We Create The Art Of <span>Services</span> Living
                                                <span>Stylishly</span>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="col-bg-img-thirteen ttm-bg ttm-col-bgimage-yes ttm-bgcolor-grey">
                                <div class="ttm-col-wrapper-bg-layer ttm-bg-layer"></div>
                                <div class="layer-content">
                                    <div class="spacing-14">
                                        <div class="section-title style1">
                                            <div class="title-header">
                                                <h3><?php echo htmlspecialchars($testimonial_section['section_heading'] ?? 'TESTIMONIALS'); ?>
                                                </h3>
                                                <h2 class="title">
                                                    <?php echo htmlspecialchars($testimonial_section['title'] ?? 'What Our Clients Say'); ?>
                                                </h2>
                                            </div>
                                        </div><?php if (!empty($testimonials_data)): ?>
                                            <div class="row slick_slider"
                                                data-slick='{"slidesToShow": 1, "slidesToScroll": 1 , "dots":false, "arrows":false, "autoplay":true, "infinite":true, "centerMode":false, "responsive": [{"breakpoint":500,"settings":{"slidesToShow": 1}}]}'>

                                                <?php foreach ($testimonials_data as $testimonial): ?>
                                                    <div class="col-lg-7">
                                                        <div class="testimonials style2">
                                                            <div class="testimonial-content">
                                                                <div class="testimonial-avatar">
                                                                    <div class="testimonial-caption">
                                                                        <div class="testimonial-title">
                                                                            <h3><?php echo htmlspecialchars($testimonial['name']); ?>
                                                                            </h3>
                                                                            <label><?php echo htmlspecialchars($testimonial['designation']); ?></label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <blockquote class="testimonial-text">
                                                                    <?php echo htmlspecialchars($testimonial['review']); ?>
                                                                </blockquote>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>

                                            </div>
                                        <?php else: ?>
                                            <p class="text-center">No testimonials yet. Check back soon!</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


            <!--welcome-section_1-->
            <section class="ttm-row welcome-section_1 clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row">


                            </div>
                        </div>
                        <div class="col-lg-6"></div>
                    </div>
                </div>
            </section>
            <!--welcome-section_1 end-->


            <!--about-section-->
            <section class="ttm-row about-section clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- ttm_single_image-wrapper -->

                        </div>
                    </div>
                </div>
            </section>
            <!--about-section end-->


            <?php
            // This code assumes your db.php file has already been included on the page
// and a $pdo object is available.
            
            // Fetch all partner logos from the database
            try {
                $logos_stmt = $pdo->prepare("SELECT * FROM partner_logos ORDER BY display_order ASC, created_at DESC");
                $logos_stmt->execute();
                $logos = $logos_stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // If there's a DB error, set to an empty array to prevent page errors
                $logos = [];
            }
            ?>

            <section
                class="ttm-row client-section_3 bg-img7 ttm-bgcolor-grey mt_365 res-991-mt-0 bg-layer-equal-height clearfix">
                <div class="container">
                    <div class="row">
                        <div class="spacing-16 border-bottom">
                            <div class="row">
                                <div class="col-xl-6 col-lg-5">
                                    <div class="pl-25 res-991-pl-0">
                                        <div class="section-title">
                                            <div class="title-header">
                                                <h3>Clients</h3>
                                                <h2 class="title">Our Honorary Clients</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="spacing-17">
                        <?php if (!empty($logos)):  // Only show the slider if there are logos to display ?>
                            <div class="row slick_slider"
                                data-slick='{"slidesToShow": 4, "slidesToScroll": 1, "arrows":false, "autoplay":true, "infinite":true, "responsive": [{"breakpoint":1200,"settings":{"slidesToShow": 5}}, {"breakpoint":1024,"settings":{"slidesToShow": 4}}, {"breakpoint":777,"settings":{"slidesToShow": 3}}, {"breakpoint":575,"settings":{"slidesToShow": 2}}]}'>

                                <?php foreach ($logos as $logo): ?>
                                    <div class="col-lg-12">
                                        <div class="client-box">
                                            <div class="client-thumbnail">
                                                <img width="250" height="96" class="img-fluid"
                                                    src="admin/assets/images/partners/<?php echo htmlspecialchars($logo['image_filename']); ?>"
                                                    alt="<?php echo htmlspecialchars($logo['image_alt']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            </div><?php endif; ?>
                    </div>
                </div>
            </section>


            <?php
            // This code assumes your db.php file has already been included on the page
// and a $pdo object is available.
            
            // Fetch the 3 most recent, active projects, joining with categories
            try {
                $stmt = $pdo->prepare("
        SELECT
            p.project_title,
            p.slug AS project_slug,
            p.short_description,
            p.list_image,
            pc.name AS category_name
        FROM
            portfolio AS p
        JOIN
            portfolio_categories AS pc ON p.category_id = pc.id
        WHERE
            p.status = 'active'
        ORDER BY
            p.created_at DESC
        LIMIT 3
    ");
                $stmt->execute();
                $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // If there's a DB error, set to an empty array to prevent page errors
                $projects = [];
            }
            ?>

            <section class="ttm-row bolg-section clearfix">
                <div class="container">
                    <div class="row">
                        <div class="ttm-box-col-wrapper col-lg-6">
                            <div class="spacing-18">
                                <div class="section-title">
                                    <div class="title-header">
                                        <h3>Our Projects</h3>
                                        <h2 class="title">We Create The Art Of Stylish Living Stylishly</h2>
                                    </div>
                                    <div class="title-desc">
                                        <p>Explore a selection of our finest work, showcasing our commitment to quality,
                                            innovation, and client satisfaction. Each project tells a unique story of
                                            collaboration and meticulous execution, from serene residential sanctuaries
                                            to dynamic commercial hubs. We invite you to see how we turn our clients'
                                            visions into reality.</p>
                                    </div>
                                </div><a
                                    class="ttm-btn ttm-btn-size-md ttm-btn-shape-rounded ttm-btn-style-fill ttm-icon-btn-right ttm-btn-color-skincolor mt-20"
                                    href="portfolio">View All Projects</a>
                            </div>

                            <?php if (isset($projects[0])): ?>
                                <div class="featured-imagebox featured-imagebox-post style1">
                                    <div class="featured-thumbnail">
                                        <a
                                            href="portfolio-details.php?slug=<?php echo htmlspecialchars($projects[0]['project_slug']); ?>">
                                            <img class="img-fluid" width="1120" height="780"
                                                src="uploads/portfolio/<?php echo htmlspecialchars($projects[0]['list_image']); ?>"
                                                alt="<?php echo htmlspecialchars($projects[0]['project_title']); ?>">
                                        </a>
                                    </div>
                                    <div class="featured-content">
                                        <div class="featured-title">
                                            <h3><a href="portfolio-details.php?slug=<?php echo htmlspecialchars($projects[0]['project_slug']); ?>"
                                                    tabindex="0"><?php echo htmlspecialchars($projects[0]['category_name']); ?></a>
                                            </h3>
                                        </div>
                                        <div class="featured-desc">
                                            <p><?php echo htmlspecialchars($projects[0]['short_description']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ttm-box-col-wrapper col-lg-6">
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php if (isset($projects[1])): ?>
                                        <div class="featured-imagebox featured-imagebox-post style1">
                                            <div class="featured-thumbnail">
                                                <a
                                                    href="portfolio-details.php?slug=<?php echo htmlspecialchars($projects[1]['project_slug']); ?>">
                                                    <img class="img-fluid" width="1120" height="780"
                                                        src="uploads/portfolio/<?php echo htmlspecialchars($projects[1]['list_image']); ?>"
                                                        alt="<?php echo htmlspecialchars($projects[1]['project_title']); ?>">
                                                </a>
                                            </div>
                                            <div class="featured-content">
                                                <div class="featured-title">
                                                    <h3><a href="portfolio-details.php?slug=<?php echo htmlspecialchars($projects[1]['project_slug']); ?>"
                                                            tabindex="0"><?php echo htmlspecialchars($projects[1]['category_name']); ?></a>
                                                    </h3>
                                                </div>
                                                <div class="featured-desc">
                                                    <p><?php echo htmlspecialchars($projects[1]['short_description']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-lg-12">
                                    <?php if (isset($projects[2])): ?>
                                        <div class="featured-imagebox featured-imagebox-post style1">
                                            <div class="featured-thumbnail">
                                                <a
                                                    href="portfolio-details.php?slug=<?php echo htmlspecialchars($projects[2]['project_slug']); ?>">
                                                    <img class="img-fluid" width="1120" height="780"
                                                        src="uploads/portfolio/<?php echo htmlspecialchars($projects[2]['list_image']); ?>"
                                                        alt="<?php echo htmlspecialchars($projects[2]['project_title']); ?>">
                                                </a>
                                            </div>
                                            <div class="featured-content">
                                                <div class="featured-title">
                                                    <h3><a href="portfolio-details.php?slug=<?php echo htmlspecialchars($projects[2]['project_slug']); ?>"
                                                            tabindex="0"><?php echo htmlspecialchars($projects[2]['category_name']); ?></a>
                                                    </h3>
                                                </div>
                                                <div class="featured-desc">
                                                    <p><?php echo htmlspecialchars($projects[2]['short_description']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <?php
            // This code assumes your db.php file has already been included on the page
            // and a $pdo object is available.
            
            // Fetch the stats from the database
            try {
                $stats_stmt = $pdo->prepare("SELECT * FROM stats WHERE id = 1 LIMIT 1");
                $stats_stmt->execute();
                $stats_data = $stats_stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // If there's a DB error, set to null to avoid breaking the page
                $stats_data = null;
            }
            ?>
            <div class="bg-img5">
                <section class="ttm-row fid-section_1 bg-img5 position-relative clearfix">
                    <div class="container">
                        <div class="row ttm-textcolor-white">
                            <div class="col-lg-4 col-md-12 col-sm-12">
                                <div class="pt-40 text-left res-575-mb-15">
                                    <div class="section-title">
                                        <div class="title-header">
                                            <h2 class="title">Our Proven Track Record</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-12 col-sm-12">
                                <?php if ($stats_data): // Only show the stats if data was successfully fetched ?>
                                    <div class="row no-gutters">
                                        <div class="col-lg-3 col-md-6 col-sm-6 ttm-box-col-wrapper">
                                            <div class="ttm-fid inside ttm-fid-with-icon ttm-fid-view-top_icon style4">
                                                <div class="ttm-fid-icon-wrapper">
                                                    <i class="flaticon flaticon-workspace"></i>
                                                </div>
                                                <div class="ttm-fid-contents">
                                                    <h3 class="ttm-fid-title">Projects</h3>
                                                    <h4 class="ttm-fid-inner">
                                                        <span data-appear-animation="animateDigits" data-from="0"
                                                            data-to="<?php echo htmlspecialchars($stats_data['projects_completed']); ?>"
                                                            data-interval="1" data-before="" data-before-style="sup"
                                                            data-after="" data-after-style="sub" class="numinate">
                                                            <?php echo htmlspecialchars($stats_data['projects_completed']); ?>
                                                        </span>
                                                        <span>+</span>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 ttm-box-col-wrapper">
                                            <div class="ttm-fid inside ttm-fid-with-icon ttm-fid-view-top_icon style4">
                                                <div class="ttm-fid-icon-wrapper">
                                                    <i class="flaticon flaticon-blueprint"></i>
                                                </div>
                                                <div class="ttm-fid-contents">
                                                    <h3 class="ttm-fid-title">Happy Clients</h3>
                                                    <h4 class="ttm-fid-inner">
                                                        <span data-appear-animation="animateDigits" data-from="0"
                                                            data-to="<?php echo htmlspecialchars($stats_data['Client_Satisfaction_Rate']); ?>"
                                                            data-interval="2" data-before="" data-before-style="sup"
                                                            data-after="" data-after-style="sub" class="numinate">
                                                            <?php echo htmlspecialchars($stats_data['Client_Satisfaction_Rate']); ?>
                                                        </span>
                                                        <span>%</span>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 ttm-box-col-wrapper">
                                            <div class="ttm-fid inside ttm-fid-with-icon ttm-fid-view-top_icon style4">
                                                <div class="ttm-fid-icon-wrapper">
                                                    <i class="flaticon flaticon-decorating"></i>
                                                </div>
                                                <div class="ttm-fid-contents">
                                                    <h3 class="ttm-fid-title">Area Transformed</h3>
                                                    <h4 class="ttm-fid-inner">
                                                        <span data-appear-animation="animateDigits" data-from="0"
                                                            data-to="<?php echo htmlspecialchars($stats_data['Sq_Ft_Transformed']); ?>"
                                                            data-interval="10" data-before="" data-before-style="sup"
                                                            data-after="" data-after-style="sub" class="numinate">
                                                            <?php echo htmlspecialchars($stats_data['Sq_Ft_Transformed']); ?>
                                                        </span>
                                                        <span>k</span>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 ttm-box-col-wrapper">
                                            <div class="ttm-fid inside ttm-fid-with-icon ttm-fid-view-top_icon style4">
                                                <div class="ttm-fid-icon-wrapper">
                                                    <i class="flaticon flaticon-interior-design"></i>
                                                </div>
                                                <div class="ttm-fid-contents">
                                                    <h3 class="ttm-fid-title">Years Experience</h3>
                                                    <h4 class="ttm-fid-inner">
                                                        <span data-appear-animation="animateDigits" data-from="0"
                                                            data-to="<?php echo htmlspecialchars($stats_data['Years_of_Combined_Team_Experience']); ?>"
                                                            data-interval="1" data-before="" data-before-style="sup"
                                                            data-after="" data-after-style="sub" class="numinate">
                                                            <?php echo htmlspecialchars($stats_data['Years_of_Combined_Team_Experience']); ?>
                                                        </span>
                                                        <span>+</span>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <!--broken-section-->
                <section class="ttm-row broken-section mt_230 res-991-mt-0 bg-layer-equal-height clearfix"
                    style="background:#07332F;">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-5">
                                <div class="ttm-bg ttm-col-bgcolor-yes ttm-bgcolor-darkgrey ttm-right-span spacing-129">
                                    <div class="ttm-col-wrapper-bg-layer ttm-bg-layer"></div>
                                    <div class="layer-content">
                                        <h2>Let's collaborate to build visionary and enduring communities.</h2>
                                        <p style="color:white;">Reach out to our planning and design team to discuss
                                            your
                                            project's scope and potential.</p>
                                        <!-- ttm-progress-bar -->
                                        <div class="pt-20 res-991-pt-40">
                                            <!-- ttm-progress-bar -->
                                            <div class="ttm-progress-bar style1" data-percent="95%">
                                                <div class="progressbar-title">Exterior Designer</div>
                                                <div class="progress-bar-inner">
                                                    <div class="progress-bar progress-bar-color-bar_skincolor"></div>
                                                </div>
                                                <div class="progress-bar-percent" data-percentage="95">95%</div>
                                            </div><!-- ttm-progress-bar end -->
                                            <!-- ttm-progress-bar -->
                                            <div class="ttm-progress-bar style1" data-percent="100%">
                                                <div class="progressbar-title">Happy Clients</div>
                                                <div class="progress-bar-inner">
                                                    <div class="progress-bar progress-bar-color-bar_skincolor"></div>
                                                </div>
                                                <div class="progress-bar-percent" data-percentage="100">100%</div>
                                            </div><!-- ttm-progress-bar end -->
                                            <!-- ttm-progress-bar -->
                                            <div class="ttm-progress-bar style1" data-percent="75%">
                                                <div class="progressbar-title">Riding Trainer</div>
                                                <div class="progress-bar-inner">
                                                    <div class="progress-bar progress-bar-color-bar_skincolor"></div>
                                                </div>
                                                <div class="progress-bar-percent" data-percentage="75">75%</div>
                                            </div><!-- ttm-progress-bar end -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div
                                    class="col-bg-img-eleven ttm-bg ttm-col-bgimage-yes ttm-bgcolor-grey box-shadow spacing-139">
                                    <div class="ttm-col-wrapper-bg-layer ttm-bg-layer"></div>
                                    <div class="layer-content">
                                        <h3 class="text-center font-size-24">Book Online Appointment</h3>
                                        <div class="padding_top30">

                                            <?php if (!empty($success_message)): ?>
                                                <div class="alert alert-success" role="alert"
                                                    style="color: green; border: 1px solid green; padding: 15px; margin-bottom: 20px;">
                                                    <?php echo htmlspecialchars($success_message); ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($error_message)): ?>
                                                <div class="alert alert-danger" role="alert"
                                                    style="color: red; border: 1px solid red; padding: 15px; margin-bottom: 20px;">
                                                    <?php echo $error_message; ?>
                                                </div>
                                            <?php endif; ?>

                                            <form id="contact_form_index" class="contact_form_index wrap-form clearfix"
                                                method="post" action="">

                                                <input type="hidden" name="contact_form_index" value="1">
                                                <div class="row ttm-boxes-spacing-20px">
                                                    <div class="col-md-6">
                                                        <label>
                                                            <span class="text-input">
                                                                <input name="first_name" type="text"
                                                                    value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>"
                                                                    placeholder="First Name..." required="required">
                                                            </span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>
                                                            <span class="text-input">
                                                                <input name="last_name" type="text"
                                                                    value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>"
                                                                    placeholder="Last Name..." required="required">
                                                            </span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>
                                                            <span class="text-input">
                                                                <input name="email" type="email"
                                                                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                                                    placeholder="Email Address..." required="required">
                                                            </span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>
                                                            <span class="text-input">
                                                                <input name="phone_number" type="text"
                                                                    value="<?php echo isset($phone_number) ? htmlspecialchars($phone_number) : ''; ?>"
                                                                    placeholder="Phone Number..." required="required">
                                                            </span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label>
                                                            <span class="text-input">
                                                                <input name="subject" type="text"
                                                                    value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>"
                                                                    placeholder="Subject..." required="required">
                                                            </span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label>
                                                            <span class="text-input">
                                                                <textarea name="message" cols="50" rows="5"
                                                                    placeholder="Enter Message Here..."
                                                                    required="required"><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                                                            </span>
                                                        </label>
                                                    </div>
                                                    <div class="col-lg-12 d-flex justify-content-center">
                                                        <button id="appointmentBtn"
                                                            class="ttm-btn ttm-btn-size-md ttm-btn-shape-rounded ttm-btn-style-fill ttm-icon-btn-right ttm-btn-color-skincolor w-50 text-center mb-50"
                                                            type="submit">Make an
                                                            Appointment</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- row end -->
                    </div>
                </section>
                <!--broken-section end-->

                <?php include 'footer.php'; ?>

            </div>
        </div><!--site-main end-->

    </div>
    <?php include 'scripts.php' ?>

    <style>
        /*
 * Makes the columns in the "Why Choose Us" section equal height.
 */
        .equal-height-row {
            display: flex;
            flex-wrap: wrap;
        }

        /*
 * UPDATED: Ensures BOTH the intermediate div AND the image's wrapper
 * take up the full height of the column.
 */
        .equal-height-row .col-xl-7>div,
        .equal-height-row .ttm_single_image-wrapper {
            height: 100%;
            display: flex;
            /* Helps the child element (the image) fill the space */
            flex-direction: column;
        }

        /*
 * Makes the image itself fill its wrapper, cropping to fit.
 */
        .equal-height-row .ttm_single_image-wrapper img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            /* This crops the image to fill the space without distortion */
        }
    </style>
    <!-- Success Modal -->
    <div id="appointmentModal" class="success-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Thank You!</h2>
            </div>
            <div class="modal-body">
                <p><strong>Your appointment request has been submitted successfully!</strong></p>
                <p>We will contact you shortly to confirm your appointment.</p>
            </div>
            <button class="modal-close" onclick="closeAppointmentModal()">OK</button>
        </div>
    </div>
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
    <script>
        // Show modal if form was successfully submitted
        <?php if ($show_modal): ?>
            window.onload = function () {
                document.getElementById('appointmentModal').style.display = 'block';
                document.body.style.overflow = 'hidden';
            };
        <?php endif; ?>

        // Handle form submission button state
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('contact_form_index');
            var submitBtn = document.getElementById('appointmentBtn');

            if (form && submitBtn) {
                form.addEventListener('submit', function (e) {
                    submitBtn.innerHTML = 'Submitting...';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.7';
                });
            }
        });

        // Function to close the modal
        function closeAppointmentModal() {
            document.getElementById('appointmentModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('contact_form_index').reset();

            var submitBtn = document.getElementById('appointmentBtn');
            submitBtn.innerHTML = 'Make an Appointment';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            var modal = document.getElementById('appointmentModal');
            if (event.target == modal) {
                closeAppointmentModal();
            }
        }
    </script>

    <style>
        /* Add this to your index.php in the existing style section */

        /*
 * Makes the columns in the "Why Choose Us" section equal height.
 */
        .equal-height-row {
            display: flex;
            flex-wrap: wrap;
        }

        /*
 * UPDATED: Ensures BOTH the intermediate div AND the image's wrapper
 * take up the full height of the column.
 */
        .equal-height-row .col-xl-7>div,
        .equal-height-row .ttm_single_image-wrapper {
            height: 100%;
            display: flex;
            /* Helps the child element (the image) fill the space */
            flex-direction: column;
        }

        /*
 * Makes the image itself fill its wrapper, cropping to fit.
 */
        .equal-height-row .ttm_single_image-wrapper img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            /* This crops the image to fill the space without distortion */
        }

        /* Why Choose Us Icon Styling */
        .custom-icon-wrapper {
            width: 40px !important;
            height: 40px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;


            margin-right: 15px;
            flex-shrink: 0;
        }

        .custom-icon-wrapper i {
            font-size: 18px !important;
            color: #007bff !important;
            line-height: 1;
        }

        /* Custom uploaded icon styling */
        .custom-uploaded-icon {
            width: 36px !important;
            height: 36px !important;
            border-radius: 50%;
            object-fit: cover;
            border: none;
        }

        /* Ensure Font Awesome icons load properly */
        .fas,
        .far,
        .fab,
        .fal {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", sans-serif !important;
        }

        .fas {
            font-weight: 900;
        }

        .far {
            font-weight: 400;
        }

        /* Fallback for missing icon fonts */
        .ttm-icon i:before {
            display: inline-block;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
        }

        /* Specific icon fallbacks for common classes */
        .fontello.icon-ok:before,
        .fas.fa-check:before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        .flaticon-apartment:before {
            content: "\f1ad";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        .flaticon-interior-design:before,
        .fas.fa-couch:before {
            content: "\f4f8";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        .flaticon-home-decoration:before,
        .fas.fa-home:before {
            content: "\f015";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        .flaticon-workspace:before,
        .fas.fa-briefcase:before {
            content: "\f0b1";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        .flaticon-blueprint:before,
        .fas.fa-drafting-compass:before {
            content: "\f568";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        .flaticon-decorating:before,
        .fas.fa-paint-brush:before {
            content: "\f1fc";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .custom-icon-wrapper {
                width: 35px !important;
                height: 35px !important;
                margin-right: 12px;
            }

            .custom-icon-wrapper i {
                font-size: 16px !important;
            }

            .custom-uploaded-icon {
                width: 31px !important;
                height: 31px !important;
            }
        }
    </style>
</body>

</html>