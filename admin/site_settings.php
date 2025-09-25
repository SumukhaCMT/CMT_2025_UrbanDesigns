<?php
require 'shared_components/session.php';
require 'shared_components/error.php';

include 'shared_components/db.php';

// Function to format days smartly (Mon-Thu, Fri-Sat, etc.)
function formatDaysRange($daysString)
{
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

// Fetch existing site details
$stmt = $pdo->prepare("SELECT * FROM site_details WHERE id = 1");
$stmt->execute();
$site_details = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$site_details) {
    echo "No data found.";
    exit();
}

// Assign values from the database
$email = $site_details['email'];
$phone_number = $site_details['phone_number'];
$address = $site_details['address'];
$whatsapp_number = $site_details['whatsapp_number'];
$facebook_link = $site_details['facebook_link'];
$instagram_link = $site_details['instagram_link'];
$linkedin_link = $site_details['linkedin_link'];
$twitter_link = $site_details['twitter_link'];
$youtube_link = $site_details['youtube_link'];
$logo_title = $site_details['logo_title'];
$logo_alt = $site_details['logo_alt'];
$open_time = $site_details['open_time'];
$open_days = $site_details['open_days'];
$closed_days = $site_details['closed_days'];
$map_iframe = $site_details['map_iframe'];
$map_link = $site_details['map_link'];
$logo_name = $site_details['logo_name'];
$favicon_name = $site_details['favicon_name'];
$video_link = $site_details['video_link'];


// Format the days for display
$formatted_open_days = formatDaysRange($open_days);
$formatted_closed_days = formatDaysRange($closed_days);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require 'shared_components/head.php'; ?>

</head>

<body>
    <?php
    // echo password_hash('password123', PASSWORD_DEFAULT);
    // exit;
    ?>
    <div id="layout-wrapper">
        <?php
        require 'shared_components/header.php';
        require 'shared_components/navbar.php';
        ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- Breadcrumb -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Site Settings</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li class="breadcrumb-item ">
                                            <a href="site_settings.php">Site Settings</a>
                                        </li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- Breadcrumb end -->

                    <!-- Site Details -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">

                                    <div class="m-3 text-center">
                                        <a href="edit_site_settings.php" class="btn btn-primary mb-4">Edit Site
                                            Settings</a>
                                    </div>

                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <td><strong>Email</strong></td>
                                                <td><?php echo $email; ?></td>
                                            </tr>
                                            <?php if (!empty($alternate_email)): ?>
                                                <tr>
                                                    <td><strong>Alternate Email</strong></td>
                                                    <td><?php echo $alternate_email; ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td><strong>Phone Number</strong></td>
                                                <td><?php echo $phone_number; ?></td>
                                            </tr>
                                            <?php if (!empty($alternate_phone)): ?>
                                                <tr>
                                                    <td><strong>Alternate Phone</strong></td>
                                                    <td><?php echo $alternate_phone; ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td><strong>WhatsApp Number</strong></td>
                                                <td>
                                                    <?php if ($whatsapp_number): ?>
                                                        <?php $full_whatsapp = '+91' . $whatsapp_number; ?>
                                                        <a href="https://wa.me/91<?php echo $whatsapp_number; ?>?text=Hi! , I Would Like To connect with you and discuss about a product im looking for "
                                                            target="_blank"><?php echo $full_whatsapp; ?></a>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td><strong>Address</strong></td>
                                                <td><?php echo $address; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Visit Between</strong></td>
                                                <td>
                                                    <?php if ($formatted_open_days && $open_time): ?>
                                                        <strong>Open Days:</strong> <?php echo $formatted_open_days; ?><br>
                                                        <strong>Time:</strong> <?php echo $open_time; ?><br>
                                                    <?php endif; ?>

                                                    <?php if ($formatted_closed_days): ?>
                                                        <strong>Closed Days:</strong> <?php echo $formatted_closed_days; ?>
                                                    <?php endif; ?>

                                                    <?php if (!$formatted_open_days && !$formatted_closed_days): ?>
                                                        <span class="text-muted">Business hours not set</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Facebook Link</strong></td>
                                                <td>
                                                    <?php if ($facebook_link): ?>
                                                        <a href="<?php echo $facebook_link; ?>"
                                                            target="_blank"><?php echo $facebook_link; ?></a>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Instagram Link</strong></td>
                                                <td>
                                                    <?php if ($instagram_link): ?>
                                                        <a href="<?php echo $instagram_link; ?>"
                                                            target="_blank"><?php echo $instagram_link; ?></a>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>LinkedIn Link</strong></td>
                                                <td>
                                                    <?php if ($linkedin_link): ?>
                                                        <a href="<?php echo $linkedin_link; ?>"
                                                            target="_blank"><?php echo $linkedin_link; ?></a>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Twitter Link</strong></td>
                                                <td>
                                                    <?php if ($twitter_link): ?>
                                                        <a href="<?php echo $twitter_link; ?>"
                                                            target="_blank"><?php echo $twitter_link; ?></a>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>YouTube Link</strong></td>
                                                <td>
                                                    <?php if ($youtube_link): ?>
                                                        <a href="<?php echo $youtube_link; ?>"
                                                            target="_blank"><?php echo $youtube_link; ?></a>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr class="d-none">
                                                <td><strong>YouTube Video Link</strong></td>
                                                <td>
                                                    <?php if ($video_link): ?>
                                                        <a href="<?php echo $video_link; ?>"
                                                            target="_blank"><?php echo $video_link; ?></a>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Favicon</strong></td>
                                                <td>
                                                    <?php if ($favicon_name): ?>

                                                        <img src="assets/images/favicons/<?php echo $favicon_name; ?>"
                                                            alt="Favicon" style="width: 60px; height: 60px;">
                                                        <br>
                                                        <?php echo $favicon_name; ?>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Logo</strong></td>
                                                <td>
                                                    <?php if ($logo_name): ?>

                                                        <img src="assets/images/favicons/<?php echo $logo_name; ?>"
                                                            alt="Logo" style="width: auto; max-height: 100px;">
                                                        <br>
                                                        <?php echo $logo_name; ?>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Logo Title</strong></td>
                                                <td><?php echo $logo_title; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Logo Alt Text</strong></td>
                                                <td><?php echo $logo_alt; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Map I frame Link</strong></td>
                                                <td>
                                                    <?php if ($map_iframe): ?>
                                                        <div
                                                            style="max-width: 40vw; word-break: break-all; font-family: monospace; font-size: 12px; background-color: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px;">
                                                            <?php echo htmlspecialchars($map_iframe); ?>
                                                        </div>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Map Frame View </strong></td>
                                                <td>
                                                    <?php if ($map_iframe): ?>
                                                        <div
                                                            style=" width: 100%; max-width: 600px; height: 300px; overflow: hidden;">
                                                            <?php echo htmlspecialchars_decode($map_iframe); ?>
                                                        </div>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>
                                            </tr>


                                            </tr>
                                            <tr>
                                                <td><strong>Map Link</strong></td>
                                                <td>
                                                    <?php if ($map_link): ?>
                                                        <a href="<?php echo $map_link; ?>"
                                                            target="_blank"><?php echo $map_link; ?></a>
                                                    <?php else: ?>
                                                        Not provided
                                                    <?php endif; ?>
                                                </td>

                                            </tr>

                                        </tbody>

                                    </table>



                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Site Details end -->

                </div>
            </div>
        </div>

        <?php
        require 'shared_components/footer.php';
        ?>
    </div>

    <?php require 'shared_components/scripts.php'; ?>
</body>

</html>