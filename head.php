<?php include 'db.php'; ?>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <?php
    // Initialize variables
    $seo = null;

    try {
        // Get current page for SEO lookup
        $pagename = $_SERVER['REQUEST_URI'];
        $pagename = basename($pagename);
        $pagename = basename($pagename, ".php");

        $actualpagename = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $actualpagename = basename($actualpagename);
        $actualpagename = basename($actualpagename, ".php");

        // IMPROVED slug determination logic
        $pageSlug = '';
        $seo = null;
        $isDetailMode = false;

        // PRIORITY 1: Check if we're in detail pages with parameters
        if ($actualpagename === 'service-details' && isset($_GET['surl'])) {
            $pageSlug = $_GET['surl'];
            $seo = getSeoData($pageSlug);
            $isDetailMode = true;
        } elseif ($actualpagename === 'portfolio-details' && isset($_GET['slug'])) {
            $pageSlug = $_GET['slug'];
            $seo = getSeoData($pageSlug);
            $isDetailMode = true;
        } elseif ($actualpagename === 'product-details' && isset($_GET['slug'])) {
            $pageSlug = $_GET['slug'];
            $seo = getSeoData($pageSlug);
            $isDetailMode = true;
        }

        // PRIORITY 2: Standard URL parsing for other pages (only if not in detail mode)
        if (!$isDetailMode) {
            $requestUri = $_SERVER['REQUEST_URI'];
            $uri = parse_url($requestUri, PHP_URL_PATH);

            // Remove base path and leading/trailing slashes
            $uri = str_replace('/Urban_Designs/', '', $uri);
            $uri = trim($uri, '/');

            // Handle different cases for page slug
            if (empty($uri) || $uri === 'index.php') {
                $pageSlug = 'index';
            } else {
                // Remove .php extension if present
                $pageSlug = preg_replace('/\.php$/', '', $uri);

                // For URLs like /services/interior-design, try multiple slug variations
                $slugVariations = [];

                // Original full path
                $slugVariations[] = $pageSlug;

                // Just the last segment
                if (strpos($pageSlug, '/') !== false) {
                    $segments = explode('/', $pageSlug);
                    $slugVariations[] = end($segments);
                }

                // Replace slashes with hyphens
                $slugVariations[] = str_replace('/', '-', $pageSlug);

                // Try each variation until we find a match
                foreach ($slugVariations as $testSlug) {
                    $seo = getSeoData($testSlug);
                    if ($seo) {
                        $pageSlug = $testSlug;
                        break;
                    }
                }
            }

            // PRIORITY 3: Fallback to script name method
            if (!$seo) {
                $currentScript = basename($_SERVER['SCRIPT_NAME'], '.php');
                if ($currentScript !== 'index') {
                    $pageSlug = $currentScript;
                    $seo = getSeoData($pageSlug);
                }
            }
        }

    } catch (Exception $e) {
        error_log("Head.php error: " . $e->getMessage());
        $seo = null;
    }

    // Function to get SEO data with multiple database connection support
    function getSeoData($slug)
    {
        global $pdo, $connection, $conn;

        $seo = null;

        if (isset($pdo) && $pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM seo WHERE page_name = :page_name LIMIT 1");
                $stmt->execute(['page_name' => $slug]);
                $seo = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("PDO SEO query error for slug '$slug': " . $e->getMessage());
            }
        } elseif (isset($connection) && $connection instanceof mysqli) {
            try {
                $query = "SELECT * FROM seo WHERE page_name = '" . mysqli_real_escape_string($connection, $slug) . "' LIMIT 1";
                $result = mysqli_query($connection, $query);
                if ($result) {
                    $seo = mysqli_fetch_assoc($result);
                }
            } catch (Exception $e) {
                error_log("MySQLi SEO query error for slug '$slug': " . $e->getMessage());
            }
        } elseif (isset($conn) && $conn instanceof mysqli) {
            try {
                $query = "SELECT * FROM seo WHERE page_name = '" . mysqli_real_escape_string($conn, $slug) . "' LIMIT 1";
                $result = mysqli_query($conn, $query);
                if ($result) {
                    $seo = mysqli_fetch_assoc($result);
                }
            } catch (Exception $e) {
                error_log("MySQLi (conn) SEO query error for slug '$slug': " . $e->getMessage());
            }
        }

        return $seo;
    }

    // Default SEO values for The Urban Design
    $default_title = "The Urban Design - Inside Innovation | Interior Design & Architecture";
    $default_keywords = "Interior Design, Urban Design, Architecture, Commercial Design, Residential Design, Turnkey Services, Design Expertise, Innovation, Modern Interiors, Space Planning";
    $default_description = "The Urban Design brings innovative interior design and architectural solutions. Specializing in commercial design, residential design, turnkey services and comprehensive design expertise for modern spaces.";
    $default_canonical = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // Use SEO data if available, otherwise use defaults
    $page_title = $seo ? $seo['meta_title'] : $default_title;
    $page_keywords = $seo ? $seo['meta_keywords'] : $default_keywords;
    $page_description = $seo ? $seo['meta_description'] : $default_description;
    $page_canonical = $seo && !empty($seo['meta_canonical_link']) ? $seo['meta_canonical_link'] : $default_canonical;
    ?>

    <base href="https://theurbandesign.in/">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2">

    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>" />
    <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>" />
    <link rel="canonical" href="<?php echo htmlspecialchars($page_canonical); ?>" />

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($page_canonical); ?>">
    <meta property="og:image" content="/Urban_Designs/images/favicons/light-logo-1.webp">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="twitter:image" content="/Urban_Designs/images/favicons/light-logo-1.webp">

    <!-- Additional SEO Meta Tags -->
    <meta name="robots" content="index, follow">
    <meta name="author" content="The Urban Design">
    <meta name="language" content="English">

    <!-- Favicons -->
    <?php
    try {
        if (isset($pdo) && $pdo instanceof PDO) {
            $stmt = $pdo->prepare("SELECT * FROM site_details WHERE id = 1 LIMIT 1");
            $stmt->execute();
            $site_data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($site_data && !empty($site_data['favicon_name'])) {
                ?>
                <link rel="shortcut icon"
                    href="admin/assets/images/favicons/<?php echo htmlspecialchars($site_data['favicon_name']); ?>">
                <?php
            } else {
                ?>
                <link rel="shortcut icon" href="images/favicons/logo.webp">
                <?php
            }
        } elseif (isset($connection) && $connection instanceof mysqli) {
            $select = mysqli_query($connection, "SELECT * FROM site_details WHERE id = 1 LIMIT 1");
            if ($select && $row = mysqli_fetch_array($select)) {
                ?>
                <link rel="shortcut icon" href="admin/assets/images/favicons/<?php echo htmlspecialchars($row['favicon_name']); ?>">
                <?php
            } else {
                ?>
                <link rel="shortcut icon" href="images/favicons/logo.webp">
                <?php
            }
        } elseif (isset($conn) && $conn instanceof mysqli) {
            $select = mysqli_query($conn, "SELECT * FROM site_details WHERE id = 1 LIMIT 1");
            if ($select && $row = mysqli_fetch_array($select)) {
                ?>
                <link rel="shortcut icon" href="admin/assets/images/favicons/<?php echo htmlspecialchars($row['favicon_name']); ?>">
                <?php
            } else {
                ?>
                <link rel="shortcut icon" href="images/favicons/logo.webp">
                <?php
            }
        } else {
            ?>
            <link rel="shortcut icon" href="images/favicons/logo.webp">
            <?php
        }
    } catch (Exception $e) {
        error_log("Favicon error: " . $e->getMessage());
        ?>
        <link rel="shortcut icon" href="images/favicons/logo.webp">
        <?php
    }
    ?>

    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/animate.css">
    <link rel="stylesheet" type="text/css" href="css/flaticon.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="css/slick.css">
    <link rel="stylesheet" type="text/css" href="css/prettyPhoto.css">
    <link rel="stylesheet" type="text/css" href="css/twentytwenty.css">
    <link rel="stylesheet" type="text/css" href="css/shortcodes.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" type="text/css" href="css/megamenu.css">
    <link rel="stylesheet" type="text/css" href="css/responsive.css">
    <link rel='stylesheet' id='rs-plugin-settings-css' href="revolution/css/rs6.css">
    <link rel="stylesheet" type="text/css" href="css/custom.css">
</head>