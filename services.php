<?php $currentPage = 'services'; ?>
<!DOCTYPE html>
<html lang="en">

<head><?php include 'head.php' ?></head>

<body>
    <div class="page ttm-bgcolor-grey">
        <?php include 'header.php'; ?>

        <div class="ttm-page-title-row">
            <div class="ttm-page-title-row-inner">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="page-title-heading">
                                <h2 class="title">Services</h2>
                            </div>
                            <div class="breadcrumb-wrapper">
                                <div class="container">
                                    <div class="breadcrumb-wrapper-inner">
                                        <span>
                                            <a title="Go to UD." href="./" class="home"><i
                                                    class="themifyicon ti-home"></i>&nbsp;&nbsp;Home</a>
                                        </span>
                                        <span class="ttm-bread-sep">&nbsp; / &nbsp;</span>
                                        <span>Services</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="site-main">
            <section class="ttm-row services-section ttm-bgcolor-grey pt-90 bg-img1 clearfix">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-9 col-md-9 col-sm-10 m-auto">
                            <div class="section-title title-style-center_text">
                                <div class="title-header">
                                    <h2 class="title">We Offer a Full Spectrum of Design Solutions.</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                        // Use the same consistent PDO connection as your other files
                        require 'admin/shared_components/db.php';

                        // Fetch all active services using PDO
                        try {
                            $sql = "SELECT s.service_title, s.service_description, s.redirection_link, s.img_name as icon_name, sd.detail_image 
                                    FROM service s
                                    JOIN service_details sd ON s.id = sd.service_id
                                    WHERE s.status = 'active' 
                                    ORDER BY s.sort_order ASC";

                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        } catch (PDOException $e) {
                            // If there's an error, create an empty array to prevent breaking the page
                            $services = [];
                            error_log("Services page query failed: " . $e->getMessage());
                        }

                        if (!empty($services)) {
                            foreach ($services as $service) {
                                ?>
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <div class="featured-imagebox featured-imagebox-portfolio style2">
                                        <div class="featured-thumbnail">
                                            <a href="services/<?php echo htmlspecialchars($service['redirection_link']); ?>">
                                                <img width="740" height="576" class="img-fluid"
                                                    src="assets/img/service/<?php echo htmlspecialchars($service['detail_image']); ?>"
                                                    alt="<?php echo htmlspecialchars($service['service_title']); ?>">
                                            </a>
                                        </div>
                                        <div class="featured-content">
                                            <div class="featured-icon">
                                                <div
                                                    class="ttm-icon ttm-icon_element-onlytxt ttm-icon_element-color-skincolor ttm-icon_element-size-lg">
                                                    <img src="admin/assets/images/service_icons/<?php echo htmlspecialchars($service['icon_name']); ?>"
                                                        style="width: 40px; height: 40px;" alt="icon">
                                                </div>
                                            </div>
                                            <div class="featured-title">
                                                <h3><a
                                                        href="services/<?php echo htmlspecialchars($service['redirection_link']); ?>"><?php echo htmlspecialchars($service['service_title']); ?></a>
                                                </h3>
                                            </div>
                                            <div class="featured-desc">
                                                <p><?php echo htmlspecialchars($service['service_description']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            } // End of foreach loop
                        } else {
                            echo '<div class="col-12"><p class="text-center">No services available at the moment.</p></div>';
                        }
                        ?>
                    </div>
                </div>
            </section>
        </div><?php include 'footer.php' ?>
    </div>
    <?php include 'scripts.php' ?>
</body>

</html>