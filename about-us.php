<?php $currentPage = 'about-us'; ?>
<!DOCTYPE html>
<html lang="en">

<head><?php include 'head.php' ?>
    <?php include 'db.php' ?>
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
                                <h2 class="title">About Us</h2>
                            </div>
                            <div class="breadcrumb-wrapper">
                                <div class="container">
                                    <div class="breadcrumb-wrapper-inner">
                                        <span>
                                            <a title="Go to The Urban Design." href="./" class="home"><i
                                                    class="themifyicon ti-home"></i>&nbsp;&nbsp;Home</a>
                                        </span>
                                        <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                        <span>About us</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!--site-main start-->
        <div class="site-main">

            <?php
            // Include your database connection file.
// NOTE: This assumes you are using the PDO connection ($pdo) established in previous examples for consistency and security.
            require 'admin/shared_components/db.php';

            // Fetch the single 'about_us' record
            try {
                $about_stmt = $pdo->prepare("SELECT * FROM about_us WHERE id = 1 LIMIT 1");
                $about_stmt->execute();
                $about_data = $about_stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $about_data = null; // Set to null on error to prevent breaking the page
            }
            ?>

            <section class="ttm-row welcome-section clearfix">
                <div class="container">
                    <div class="row">
                        <?php
                        // Check if the data was successfully fetched
                        if ($about_data):
                            ?>
                            <div class="col-lg-6 col-md-6">
                                <div class="ttm_single_image-wrapper">
                                    <img class="img_fluid"
                                        src="admin/assets/images/about/<?php echo htmlspecialchars($about_data['image']); ?>"
                                        alt="<?php echo htmlspecialchars($about_data['img_alt']); ?>">
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <div class="pl-30 res-991-pl-0 res-991-mt-40">
                                    <div class="section-title">
                                        <div class="title-header">
                                            <h2 class="title"><?php echo htmlspecialchars($about_data['title']); ?></h2>
                                        </div>
                                    </div>
                                    <div class="pb-10 res-991-pb-30">
                                        <p style="text-align: justify;">
                                            <?php echo nl2br(htmlspecialchars($about_data['content'])); ?>
                                        </p>
                                    </div>
                                    <div class="d-sm-flex align-items-center mt-60 res-767-mt-0">
                                        <a class="ttm-btn ttm-btn-size-md ttm-btn-shape-rounded ttm-btn-style-border ttm-icon-btn-right ttm-btn-color-darkgrey mr-30 res-767-mt-20"
                                            href="#founder">View More</a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        else:
                            // You can include fallback static content here if no data is found in the DB
                            echo "<p>About information is not available at the moment.</p>";
                        endif;
                        ?>
                    </div>
                </div>
            </section>

            <?php
            // Include your database connection file.
// This uses the consistent and secure PDO connection from previous examples.
            

            // Fetch the single 'founder_details' record
            try {
                $founder_stmt = $pdo->prepare("SELECT * FROM founder_details WHERE id = 1 LIMIT 1");
                $founder_stmt->execute();
                $founder_data = $founder_stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $founder_data = null; // Set to null on error to prevent breaking the page
            }
            ?>

            <section id="founder" class="ttm-row welcome-section clearfix">
                <div class="container">
                    <div class="row">
                        <?php
                        // Check if founder data exists before trying to display it
                        if ($founder_data):
                            ?>
                            <div class="col-lg-6 col-md-6">
                                <div class="ttm_single_image-wrapper">
                                    <img width="570" height="484" class="img-fluid"
                                        src="admin/assets/images/about/<?php echo htmlspecialchars($founder_data['image_name']); ?>"
                                        alt="<?php echo htmlspecialchars($founder_data['image_alt']); ?>">
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <div class="pl-30 res-991-pl-0 res-991-mt-40">
                                    <div class="section-title">
                                        <div class="title-header">
                                            <h2 class="title">Founder's Message</h2>
                                        </div>
                                    </div>
                                    <div class="pb-10 res-991-pb-30">
                                        <div style="text-align:justify;"><?php echo $founder_data['content']; ?></div>
                                    </div>

                                    <?php if (!empty($founder_data['subtext'])): ?>
                                        <div class="d-flex pl-5 mt-20 mb-30 ttm-bgcolor-skincolor position-relative z-index-2">
                                            <div
                                                class="d-inline pl-25 pr-25 pt-15 pb-15 w-100 ttm-bgcolor-grey ttm-textcolor-darkgrey">
                                                <p class="mb-0 font-size-15 fontweight-Medium">
                                                    "<?php echo htmlspecialchars($founder_data['subtext']); ?>"
                                                </p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-sm-flex align-items-center mt-60 res-767-mt-0">
                                        <div class="res-767-mt-20">
                                            <img width="186" height="53" class="img-fluid" src="images/author-sign.png"
                                                alt="sign">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        else:
                            // Fallback message if no data is found
                            echo "<p>Founder's message is currently unavailable.</p>";
                        endif;
                        ?>
                    </div>
                </div>
            </section>
            <section class="ttm-row faq-section pb-90 clearfix">
                <div class="container">
                    <div class="row">
                        <?php
                        $select = mysqli_query($connection, "SELECT * FROM `mission_vision`");
                        while ($row = mysqli_fetch_array($select)) {
                            ?>
                            <div class="col-lg-6">
                                <div class="res-991-pb-40 pr-10 res-991-pr-0">
                                    <!-- section title -->
                                    <div class="section-title mb-10">
                                        <div class="title-header">
                                            <h2 class="title">Vision</h2>
                                        </div>
                                    </div><!-- section title end -->
                                    <p style="text-align:justify;"><?php echo $row['vision_content']; ?> </p>
                                    <div class="row">


                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="res-991-pb-40 pr-10 res-991-pr-0">
                                    <!-- section title -->
                                    <div class="section-title mb-10">
                                        <div class="title-header">
                                            <h2 class="title">Mission</h2>
                                        </div>
                                    </div><!-- section title end -->
                                    <p style="text-align:justify;"><?php echo $row['mission_content']; ?> </p>
                                    <div class="row">


                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div><!-- row end -->
                </div>
            </section><!--faq-section end-->

            <?php
            // This code assumes your db.php file has already been included on the page
// and a $pdo object is available.
            
            // Fetch all active services from the database
            try {
                $services_stmt = $pdo->prepare("SELECT * FROM service WHERE status = 'active' ORDER BY sort_order ASC");
                $services_stmt->execute();
                $services = $services_stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // If there's a DB error, set to an empty array to prevent page errors
                $services = [];
            }
            ?>

            <section class="ttm-row services-section ttm-bgcolor-darkgrey bg-img2 clearfix" style="background:#07332F;">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-sm-6">
                            <div class="pt-10 text-left">
                                <div class="section-title">
                                    <div class="title-header">
                                        <h3>Services</h3>
                                        <h2 class="title">Services We’re Providing</h2>
                                    </div>
                                </div>
                                <div class="title-desc">
                                    <p>We offer expertly planned and executed commercial and residential turnkey
                                        projects tailored to your requirements.</p>
                                </div>
                            </div>
                        </div>

                        <?php
                        // Loop through each service fetched from the database
                        if (!empty($services)):
                            foreach ($services as $service):
                                ?>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <div class="featured-icon-box icon-align-top-content style2">

                                        <div class="featured-content">
                                            <div class="featured-title">
                                                <h3><a
                                                        href="services/<?php echo htmlspecialchars($service['redirection_link']); ?>"><?php echo htmlspecialchars($service['service_title']); ?></a>
                                                </h3>
                                            </div>
                                            <div class="featured-desc">
                                                <p><?php echo htmlspecialchars($service['service_description']); ?></p>
                                            </div>
                                            <div class="ttm-footer">
                                                <a class="ttm-btn btn-inline ttm-btn-size-md ttm-icon-btn-right ttm-btn-color-white"
                                                    href="services/<?php echo htmlspecialchars($service['redirection_link']); ?>">View
                                                    More</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            endforeach;
                        endif;
                        ?>

                    </div>
                </div>
            </section>

            <section class="ttm-row procedure-section clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7 col-md-8 col-sm-10 m-auto">
                            <!-- section title -->
                            <div class="section-title title-style-center_text">
                                <div class="title-header">
                                    <h3>Work Stages</h3>
                                    <h2 class="title">Best Solutions For Your Dream</h2>
                                </div>
                                <p>Client’s often don’t know what to expect during the interior design process, so we’ve
                                    put together our guide work stages</p>
                            </div>
                            <!-- section title end -->
                        </div>
                    </div>
                    <!-- row -->
                    <div class="col-lg-12">
                        <div class="featuredbox-number processbox">
                            <div class="row">
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <!-- featured-imagebox -->
                                    <div class="featured-icon-box icon-align-top-content style4">
                                        <div class="featured-icon">
                                            <div
                                                class="ttm-icon ttm-icon_element-border ttm-icon_element-color-skincolor ttm-icon_element-size-xl ttm-icon_element-style-rounded">
                                                <i class="flaticon flaticon-workspace"></i>
                                                <span class="ttm-num"></span>
                                            </div>
                                        </div>
                                        <div class="featured-content">
                                            <div class="featured-title">
                                                <h3>The Feasibility</h3>
                                            </div>
                                            <div class="featured-desc">
                                                <p>This initial phase of the project includes preliminary studies</p>
                                            </div>
                                        </div>
                                    </div><!-- featured-imagebox end-->
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <!-- featured-imagebox -->
                                    <div class="featured-icon-box icon-align-top-content style4">
                                        <div class="featured-icon">
                                            <div
                                                class="ttm-icon ttm-icon_element-border ttm-icon_element-color-skincolor ttm-icon_element-size-xl ttm-icon_element-style-rounded">
                                                <i class="flaticon flaticon-interior-design-1"></i>
                                                <span class="ttm-num"></span>
                                            </div>
                                        </div>
                                        <div class="featured-content">
                                            <div class="featured-title">
                                                <h3>The Development</h3>
                                            </div>
                                            <div class="featured-desc">
                                                <p>we get into the detail of the scheme. We’ll refine the internal</p>
                                            </div>
                                        </div>
                                    </div><!-- featured-imagebox end-->
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <!-- featured-imagebox -->
                                    <div class="featured-icon-box icon-align-top-content style4">
                                        <div class="featured-icon">
                                            <div
                                                class="ttm-icon ttm-icon_element-border ttm-icon_element-color-skincolor ttm-icon_element-size-xl ttm-icon_element-style-rounded">
                                                <i class="flaticon flaticon-windows"></i>
                                                <span class="ttm-num"></span>
                                            </div>
                                        </div>
                                        <div class="featured-content">
                                            <div class="featured-title">
                                                <h3>Full Mobilization</h3>
                                            </div>
                                            <div class="featured-desc">
                                                <p>Once the contractor is appointed, will workshops to review</p>
                                            </div>
                                        </div>
                                    </div><!-- featured-imagebox end-->
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <!-- featured-imagebox -->
                                    <div class="featured-icon-box icon-align-top-content style4">
                                        <div class="featured-icon">
                                            <div
                                                class="ttm-icon ttm-icon_element-border ttm-icon_element-color-skincolor ttm-icon_element-size-xl ttm-icon_element-style-rounded">
                                                <i class="flaticon flaticon-interior-design"></i>
                                                <span class="ttm-num"></span>
                                            </div>
                                        </div>
                                        <div class="featured-content">
                                            <div class="featured-title">
                                                <h3>Post PC Work</h3>
                                            </div>
                                            <div class="featured-desc">
                                                <p>The project concludes will visit site to inspect all the works</p>
                                            </div>
                                        </div>
                                    </div><!-- featured-imagebox end-->
                                </div>
                            </div>
                        </div>
                    </div><!-- row end -->
                </div>
            </section>
            <!-- procedure-section end -->
            <!--faq-section-->



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

            <div class="ttm-row client-section_1 ttm-bgcolor-darkgrey clearfix" style="background-color: #07332F;">
                <div class="container">
                    <div class="row text-center">
                        <div class="col-md-12">
                            <div class="section-title title-style-center_text">
                                <div class="title-header">
                                    <h3>Our Clients</h3>
                                    <h2 class="title">Our Honorary Clients</h2>
                                </div>
                            </div>
                            <?php if (!empty($logos)): // Only show the slider if logos exist ?>
                                <div class="row slick_slider"
                                    data-slick='{"slidesToShow": 5, "slidesToScroll": 1, "arrows":false, "autoplay":true, "infinite":true, "responsive": [{"breakpoint":1200,"settings":{"slidesToShow": 5}}, {"breakpoint":1024,"settings":{"slidesToShow": 4}}, {"breakpoint":777,"settings":{"slidesToShow": 3}}, {"breakpoint":575,"settings":{"slidesToShow": 2}}]}'>

                                    <?php foreach ($logos as $logo): ?>
                                        <div class="col-lg-12">
                                            <div class="client-box">
                                                <div class="client-thumbnail">
                                                    <img width="178" height="70" class="img-fluid"
                                                        src="admin/assets/images/partners/<?php echo htmlspecialchars($logo['image_filename']); ?>"
                                                        alt="<?php echo htmlspecialchars($logo['image_alt']); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                </div><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!--fid-section-->
            <!-- <section class="ttm-row fid-section ttm-bgcolor-grey clearfix">
                <div class="container">
                    <div class="row no-gutters">
                        <div class="col-lg-12 col-md-12">

                            <?php
                            // Your database connection variable (e.g., $connection) should be available here
                            // We select the latest row from the stats table
                            $sql_query = "SELECT * FROM `stats` ORDER BY id DESC LIMIT 1";
                            $result = mysqli_query($connection, $sql_query);

                            // Fetch the single row of data as an associative array
                            $stats = mysqli_fetch_assoc($result);

                            // We only show the section if we successfully got the stats data
                            if ($stats) {
                                ?>

                                <div class="row no-gutters ttm-vertical_sep">
                                    <div class="col-lg-3 col-md-6 col-sm-6 ttm-box-col-wrapper">
                                        <div class="ttm-fid inside ttm-fid-with-icon ttm-fid-view-top_icon style2">
                                            <div class="ttm-fid-contents">
                                                <h4 class="ttm-fid-inner">
                                                    <span data-appear-animation="animateDigits" data-from="0"
                                                        data-to="<?php echo htmlspecialchars($stats['projects_completed']); ?>"
                                                        data-interval="1" data-before="" data-before-style="sup"
                                                        data-after="" data-after-style="sub" class="numinate">
                                                        <?php echo htmlspecialchars($stats['projects_completed']); ?>
                                                    </span>
                                                    <span class="ml_15 res-991-ml-0">+</span>
                                                </h4>
                                                <h3 class="ttm-fid-title">Projects Completed</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6 ttm-box-col-wrapper">
                                        <div class="ttm-fid inside ttm-fid-with-icon ttm-fid-view-top_icon style2">
                                            <div class="ttm-fid-contents">
                                                <h4 class="ttm-fid-inner">
                                                    <span data-appear-animation="animateDigits" data-from="0"
                                                        data-to="<?php echo htmlspecialchars($stats['Client_Satisfaction_Rate']); ?>"
                                                        data-interval="5" data-before="" data-before-style="sup"
                                                        data-after="" data-after-style="sub" class="numinate">
                                                        <?php echo htmlspecialchars($stats['Client_Satisfaction_Rate']); ?>
                                                    </span>
                                                    <span class="ml_15 res-991-ml-0">%</span>
                                                </h4>
                                                <h3 class="ttm-fid-title">Client Satisfaction Rate</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6 ttm-box-col-wrapper">
                                        <div class="ttm-fid inside ttm-fid-with-icon ttm-fid-view-top_icon style2">
                                            <div class="ttm-fid-contents">
                                                <h4 class="ttm-fid-inner">
                                                    <span data-appear-animation="animateDigits" data-from="0"
                                                        data-to="<?php echo htmlspecialchars($stats['Sq_Ft_Transformed']); ?>"
                                                        data-interval="5" data-before="" data-before-style="sup"
                                                        data-after="" data-after-style="sub" class="numinate">
                                                        <?php echo htmlspecialchars($stats['Sq_Ft_Transformed']); ?>
                                                    </span>
                                                    <span class="ml_15 res-991-ml-0">k</span>
                                                </h4>
                                                <h3 class="ttm-fid-title">Sq. Ft. Transformed</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6 ttm-box-col-wrapper">
                                        <div class="ttm-fid inside ttm-fid-with-icon ttm-fid-view-top_icon style2">
                                            <div class="ttm-fid-contents">
                                                <h4 class="ttm-fid-inner">
                                                    <span data-appear-animation="animateDigits" data-from="0"
                                                        data-to="<?php echo htmlspecialchars($stats['Years_of_Combined_Team_Experience']); ?>"
                                                        data-interval="1" data-before="" data-before-style="sup"
                                                        data-after="" data-after-style="sub" class="numinate">
                                                        <?php echo htmlspecialchars($stats['Years_of_Combined_Team_Experience']); ?>
                                                    </span>
                                                    <span class="ml_15 res-991-ml-0">+</span>
                                                </h4>
                                                <h3 class="ttm-fid-title">Years of Combined Team Experience</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php
                            } // End the if statement
                            ?>

                        </div>
                    </div>
                </div>
            </section> -->

            <section class="ttm-row pt-90 res-991-pt-60 team-section clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-md-8 col-sm-10 m-auto">
                            <div class="section-title title-style-center_text">
                                <div class="title-header">
                                    <h3>Our Teams</h3>
                                    <h2 class="title">Meet Our Leadership Team</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row slick_slider"
                        data-slick='{"slidesToShow": 4, "slidesToScroll": 1, "arrows":false, "dots":false, "autoplay":false, "infinite":true, "responsive": [{"breakpoint":1100,"settings":{"slidesToShow": 3}} , {"breakpoint":768,"settings":{"slidesToShow": 2}}, {"breakpoint":575,"settings":{"slidesToShow": 1}}]}'>

                        <?php
                        // 1. Select all team members from the database
                        $sql_query = "SELECT * FROM `team` ORDER BY id ASC";
                        $result = mysqli_query($connection, $sql_query);

                        // 2. Loop through each row found in the database
                        while ($member = mysqli_fetch_assoc($result)) {
                            ?>

                            <div class="col-lg-3">
                                <div class="featured-imagebox featured-imagebox-team style1">
                                    <div class="featured-thumbnail">
                                        <img width="450" height="500" class="img-fluid"
                                            src="admin/assets/images/team/<?php echo htmlspecialchars($member['img_name']); ?>"
                                            alt="<?php echo htmlspecialchars($member['img_alt']); ?>">
                                    </div>
                                    <div class="featured-iconbox ttm-media-link">
                                        <div class="media-block">
                                            <ul class="social-icons">
                                                <?php if (!empty($member['facebook_link'])): ?>
                                                    <li class="social-facebook">
                                                        <a href="<?php echo htmlspecialchars($member['facebook_link']); ?>"
                                                            target="_blank"><i class="ti ti-facebook"></i></a>
                                                    </li>
                                                <?php endif; ?>

                                                <?php if (!empty($member['twitter_link'])): ?>
                                                    <li class="social-twitter">
                                                        <a href="<?php echo htmlspecialchars($member['twitter_link']); ?>"
                                                            target="_blank"><i class="ti ti-twitter-alt"></i></a>
                                                    </li>
                                                <?php endif; ?>

                                                <?php if (!empty($member['linkedin_link'])): ?>
                                                    <li class="social-linkedin">
                                                        <a href="<?php echo htmlspecialchars($member['linkedin_link']); ?>"
                                                            target="_blank"><i class="ti ti-linkedin"></i></a>
                                                    </li>
                                                <?php endif; ?>

                                            </ul>
                                        </div>
                                    </div>
                                    <div class="featured-content">
                                        <div class="featured-title">
                                            <h3><a href="#"><?php echo htmlspecialchars($member['name']); ?></a></h3>
                                        </div>
                                        <p class="team-position"><?php echo htmlspecialchars($member['designation']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <?php
                        } // 3. End of the while loop
                        ?>

                    </div>
                </div>
            </section>

        </div><!--site-main end-->
        <?php include 'footer.php' ?>
    </div>
    <?php include 'scripts.php' ?>
</body>

</html>