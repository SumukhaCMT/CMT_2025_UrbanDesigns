<header id="masthead" class="header ttm-header-style-01">
    <!-- top_bar -->
    <?php
    // Include your database connection file
    require_once 'admin/shared_components/db.php';

    // Prepare and execute the query to fetch site details
    $sql = "SELECT * FROM site_details WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => 1]);

    // Fetch the result directly into the $siteDetails variable
    $siteDetails = $stmt->fetch();
    ?>

    <div class="top_bar ttm-topbar-wrapper clearfix">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12 d-flex flex-row align-items-center">

                    <div class="top_bar_contact_item">
                        <div class="top_bar_icon"><i class="flaticon flaticon-email ttm-textcolor-skincolor"></i></div>
                        <a href="mailto:<?php echo htmlspecialchars($siteDetails['email']); ?>">
                            <span>Email Address: <?php echo htmlspecialchars($siteDetails['email']); ?></span>
                        </a>
                    </div>

                    <div class="top_bar_contact_item">
                        <div class="top_bar_icon"><i class="flaticon flaticon-location ttm-textcolor-skincolor"></i>
                        </div>
                        <?php echo htmlspecialchars($siteDetails['address']); ?>
                    </div>

                    <div class="top_bar_contact_item header_extra ms-auto">

                    </div>

                    <div class="top_bar_contact_item header_extra">

                    </div>

                    <div class="top_bar_contact_item top_bar_social">
                        <div class="social-icons">
                            <ul class="list-inline">
                                <?php if (!empty($siteDetails['facebook_link'])): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($siteDetails['facebook_link']); ?>"
                                            rel="noopener" aria-label="facebook">
                                            <i class="fa fa-facebook"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if (!empty($siteDetails['twitter_link'])): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($siteDetails['twitter_link']); ?>"
                                            rel="noopener" aria-label="twitter">
                                            <i class="fa fa-twitter"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if (!empty($siteDetails['linkedin_link'])): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($siteDetails['linkedin_link']); ?>"
                                            rel="noopener" aria-label="linkedin">
                                            <i class="fa fa-linkedin"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if (!empty($siteDetails['instagram_link'])): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($siteDetails['instagram_link']); ?>"
                                            rel="noopener" aria-label="instagram">
                                            <i class="fa fa-instagram"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- site-header-menu -->
    <div id="site-header-menu" class="site-header-menu">
        <div class="site-header-menu-inner ttm-stickable-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <!--site-navigation -->
                        <div class="site-navigation d-flex flex-row align-items-center">
                            <!-- site-branding -->
                            <div class="site-branding ">
                                <a class="home-link" href="./" title="Inoterior" rel="home">
                                    <img id="logo-img" width="195" height="190" class="img-fluid"
                                        src="images/favicons/Urban_Designs.webp" alt="logo-img">
                                </a>
                            </div><!-- site-branding end -->
                            <div class="d-flex flex-row ms-auto pl-75 res-991-pl-0">
                                <div class="btn-show-menu-mobile menubar menubar--squeeze">
                                    <span class="menubar-box">
                                        <span class="menubar-inner"></span>
                                    </span>
                                </div>
                                <!-- menu -->
                                <nav class="main-menu menu-mobile" id="menu">
                                    <ul class="menu">
                                        <li class="<?php if (isset($currentPage) && $currentPage == 'home') {
                                            echo 'active';
                                        } ?>">
                                            <a href="./">Home</a>
                                        </li>
                                        <li class="mega-menu-item <?php if (isset($currentPage) && $currentPage == 'about-us') {
                                            echo 'active';
                                        } ?>">
                                            <a href="about-us">About Us</a>
                                        </li>
                                        <?php
                                        // This part remains the same
                                        require_once 'db.php';
                                        $sql = "SELECT service_title, redirection_link FROM service WHERE status = 'active' ORDER BY sort_order ASC";
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->execute();
                                        $services = $stmt->fetchAll();
                                        ?>
                                        <li class="mega-menu-item <?php if (isset($currentPage) && $currentPage == 'services') {
                                            echo 'active';
                                        } ?>">
                                            <a href="services">Services</a>
                                            <ul class="mega-submenu">
                                                <?php if ($services): ?>
                                                    <?php foreach ($services as $service): ?>
                                                        <li>
                                                            <a
                                                                href="services/<?php echo htmlspecialchars($service['redirection_link']); ?>">
                                                                <?php echo htmlspecialchars($service['service_title']); ?>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <li><a href="#">No services available</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </li>
                                        <li class="mega-menu-item <?php if (isset($currentPage) && $currentPage == 'portfolio') {
                                            echo 'active';
                                        } ?>">
                                            <a href="portfolio">Portfolio</a>
                                        </li>
                                        <li class="mega-menu-item <?php if (isset($currentPage) && $currentPage == 'products') {
                                            echo 'active';
                                        } ?>">
                                            <a href="products">Products</a>
                                        </li>
                                        <li class="mega-menu-item <?php if (isset($currentPage) && $currentPage == 'contact-us') {
                                            echo 'active';
                                        } ?>">
                                            <a href="contact-us">Contact Us</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>

                            <!-- <div class="widget_info d-flex flex-row align-items-center justify-content-end">
                                <div class="header_btn">
                                    <a class="ttm-btn ttm-btn-size-md ttm-btn-shape-rounded ttm-btn-style-fill ttm-icon-btn-right ttm-btn-color-skincolor pulse-button"
                                        href="contact-us"><span>Get a Quote!</span></a>
                                </div>
                            </div> -->

                            <div class="widget_info d-flex flex-row align-items-center justify-content-end">
                                <div class="widget_icon"><i class="flaticon flaticon-phone"></i></div>
                                <div class="widget_content">
                                    <h3 class="widget_title">Have any Questions?</h3>
                                    <p class="widget_desc">9886306672</p>
                                </div>
                            </div>
                        </div><!-- site-navigation end-->
                    </div>
                </div>
            </div>
        </div>
    </div>

</header><!--header end-->

<style>
    .pulse-button {
        background: linear-gradient(124deg, #ff2400, #e81d1d, #e8b71d, #e3e81d, #1de840, #1ddde8, #2b1de8, #dd00f3);
        background-size: 400% 400%;
        animation:
            rainbow-slide 6s ease infinite,
            white-pulse 2s infinite;
    }


    .pulse-button span {

        color: white;
        mix-blend-mode: hard-light;
    }


    /* Keyframes remain the same */
    @keyframes rainbow-slide {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    @keyframes white-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(255, 255, 255, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
        }
    }
</style>