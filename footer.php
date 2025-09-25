<?php
// Include the database connection file once to ensure $pdo is available.
require_once 'admin/shared_components/db.php';

// --- Fetch ALL Data for Footer ---
try {
    // Fetch active services for the links section (limit to 4)
    $services_stmt = $pdo->prepare("SELECT service_title, redirection_link FROM service WHERE status = 'active' ORDER BY sort_order ASC LIMIT 4");
    $services_stmt->execute();
    $footer_services = $services_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch recent projects for the footer (limit to 2)
    $projects_stmt = $pdo->prepare("SELECT project_title, slug, list_image, short_description FROM portfolio WHERE status = 'active' ORDER BY project_year DESC, created_at DESC LIMIT 2");
    $projects_stmt->execute();
    $recent_projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

    // NEW: Fetch About Us content for the footer (id = 3)
    $footer_about_stmt = $pdo->prepare("SELECT * FROM about_us WHERE id = 3 LIMIT 1");
    $footer_about_stmt->execute();
    $footer_about_data = $footer_about_stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If any query fails, create empty arrays to prevent errors in the HTML
    $footer_services = $footer_services ?? [];
    $recent_projects = $recent_projects ?? [];
    $footer_about_data = $footer_about_data ?? []; // NEW: Initialize about data on failure
    // You might want to log the error for debugging: error_log($e->getMessage());
}

/**
 * Function to limit text to a specific number of words.
 * @param string $text The text to limit.
 * @param int $word_limit The number of words to allow.
 * @return string The truncated text.
 */
function limit_words($text, $word_limit)
{
    // Ensure $text is a string before exploding
    $text = $text ?? '';
    $words = explode(' ', $text);
    $limited_text = implode(' ', array_slice($words, 0, $word_limit));
    if (count($words) > $word_limit) {
        $limited_text .= '...';
    }
    return $limited_text;
}
?>

<footer class="footer widget-footer clearfix">
    <div class="second-footer" style="background:#07332F;">
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-12 widget-area">
                    <div class="widget widget_text mr-20 res-991-mr-0 clearfix">
                        <div class="textwidget widget-text">
                            <div class="footer-logo">
                                <img width="196" height="59" id="footer-logo-img" class="img-fluid"
                                    src="admin/assets/images/about/<?php echo htmlspecialchars($footer_about_data['image'] ?? ''); ?>"
                                    alt="<?php echo htmlspecialchars($footer_about_data['img_alt'] ?? 'The Urban Design Logo'); ?>">
                            </div>
                            <p><?php echo htmlspecialchars($footer_about_data['content'] ?? 'The Urban Design is a premier design firm...'); ?>
                            </p>
                        </div>
                        <div class="border-top pt-20 res-991-pt-25 res-991-pb-0">
                            <div class="widget-area">
                                <div class="social-icons">
                                    <h3 class="widget-title">Social media</h3>
                                    <ul class="list-inline">
                                        <li class="social-facebook"><a class="tooltip-top" target="_blank" href="#"
                                                rel="noopener" aria-label="facebook"><i class="fa fa-facebook"
                                                    aria-hidden="true"></i></a></li>
                                        <li class="social-twitter"><a class="tooltip-top" target="_blank" href="#"
                                                rel="noopener" aria-label="twitter"><i class="fa fa-twitter"
                                                    aria-hidden="true"></i></a></li>
                                        <li class="social-linkedin"><a class="tooltip-bottom" target="_blank" href="#"
                                                rel="noopener" aria-label="linkedin"><i class="fa fa-linkedin"
                                                    aria-hidden="true"></i></a></li>
                                        <li class="social-pinterest"><a class="tooltip-bottom" target="_blank" href="#"
                                                rel="noopener" aria-label="pinterest"><i class="fa fa-pinterest"
                                                    aria-hidden="true"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-8 col-md-12 widget-area">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6 widget-area">
                            <div class="widget widget_nav_menu clearfix">
                                <h3 class="widget-title">Quick Links</h3>
                                <div class="row">
                                    <div class="col-5">
                                        <ul class="menu">
                                            <li style="padding-bottom: 10px;"><i class="fa fa-angle-right"
                                                    style="color: #ea8031; margin-right: 8px;"></i><a
                                                    href="about-us">About Us</a></li>
                                            <li style="padding-bottom: 10px;"><i class="fa fa-angle-right"
                                                    style="color: #ea8031; margin-right: 8px;"></i><a
                                                    href="services">Services</a></li>
                                            <li style="padding-bottom: 10px;"><i class="fa fa-angle-right"
                                                    style="color: #ea8031; margin-right: 8px;"></i><a
                                                    href="portfolio">Portfolio</a></li>
                                            <li style="padding-bottom: 10px;"><i class="fa fa-angle-right"
                                                    style="color: #ea8031; margin-right: 8px;"></i><a
                                                    href="contact-us">Contact Us</a></li>
                                        </ul>
                                    </div>
                                    <div class="col-6">
                                        <ul class="menu">
                                            <?php if (!empty($footer_services)): ?>
                                                <?php foreach ($footer_services as $service): ?>
                                                    <li style="padding-bottom: 10px;">
                                                        <i class="fa fa-angle-right"
                                                            style="color: #ea8031; margin-right: 8px;"></i>
                                                        <a
                                                            href="services/<?php echo htmlspecialchars($service['redirection_link'] ?? '#'); ?>">
                                                            <?php echo htmlspecialchars($service['service_title'] ?? 'Service'); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6 widget-area">
                            <div class="widget style2 widget-out-link clearfix">
                                <h3 class="widget-title">Recent Projects</h3>
                                <?php if (!empty($recent_projects)): ?>
                                    <ul class="widget-post ttm-recent-post-list mt-15">
                                        <?php foreach ($recent_projects as $project): ?>
                                            <li>
                                                <a href="portfolio/<?php echo htmlspecialchars($project['slug'] ?? ''); ?>">
                                                    <img width="70" height="70"
                                                        src="uploads/portfolio/<?php echo htmlspecialchars($project['list_image'] ?? ''); ?>"
                                                        alt="<?php echo htmlspecialchars($project['project_title'] ?? 'Project'); ?>">
                                                </a>
                                                <a href="portfolio/<?php echo htmlspecialchars($project['slug'] ?? ''); ?>">
                                                    <?php echo htmlspecialchars($project['project_title'] ?? ''); ?>
                                                </a>
                                                </a>
                                                <p><?php echo htmlspecialchars(limit_words($project['short_description'], 6)); ?>
                                                </p>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="mt-15">No recent projects to display.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="bottom-footer-text" style="background:#ea8031;">
        <div class="container">
            <div class="row copyright">
                <div class="col-lg-7 col-md-7 col-sm-12">
                    <span>Copyright © <?php echo date("Y"); ?>&nbsp;<a href="./" class="ttm-textcolor-white"
                            target="_blank" rel="noopener" aria-label="ttm">The Urban Design&nbsp;</a> All rights
                        reserved. Maintained By&nbsp;<a href="https://codemythought.com" class="ttm-textcolor-white"
                            target="_blank" rel="noopener" aria-label="ttm">CodeMyThought&nbsp;</a></span>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (!isset($whatsapp_number)) {
        include 'admin/shared_components/db.php';
        $stmt = $pdo->prepare("SELECT whatsapp_number FROM site_details WHERE id = 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $whatsapp_number = $result['whatsapp_number'];
    }
    ?>

    <?php if ($whatsapp_number): ?>
        <div class="quick-contact">
            <a class="whatsapp-chat"
                href="https://wa.me/91<?php echo $whatsapp_number; ?>?text=Hi! I'm interested in your interior design services and would like to discuss my project requirements."
                target="blank">
                <img src="images/whatsapp.png" alt="WhatsApp | +91 <?php echo $whatsapp_number; ?>"
                    title="WhatsApp | +91 <?php echo $whatsapp_number; ?>">
            </a>
        </div>
    <?php endif; ?>
</footer>
<a id="totop" href="#top">
    <i class="fa fa-angle-up"></i>
</a>