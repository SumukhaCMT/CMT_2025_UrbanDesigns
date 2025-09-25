<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];
$email = $phone_number = $alternate_email = $alternate_phone = $whatsapp_number = $address = $facebook_link = $instagram_link = $linkedin_link = $twitter_link = $video_link = $youtube_link = $logo_title = $logo_alt = $open_time = $open_days = $closed_days = $map_iframe = $map_link = "";
$logo_name = $favicon_name = "";

// Variables for time components
$start_hour = $start_period = $end_hour = $end_period = "";
$selected_days = [];
$selected_closed_days = [];

// Fetch existing data for ID = 1 (Edit mode)
$stmt = $pdo->prepare("SELECT * FROM site_details WHERE id = 1");
$stmt->execute();
$site_details = $stmt->fetch(PDO::FETCH_ASSOC);

if ($site_details) {
    $email = $site_details['email'];
    $phone_number = $site_details['phone_number'];
    $alternate_email = $site_details['alternate_email'] ?? '';
    $alternate_phone = $site_details['alternate_phone'] ?? '';
     $whatsapp_number = $site_details['whatsapp_number'] ?? ''; 
    $address = $site_details['address'];
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

    // Parse existing time format (e.g., "9 AM - 6 PM")
    if ($open_time) {
        $time_parts = explode(' - ', $open_time);
        if (count($time_parts) == 2) {
            $start_time_parts = explode(' ', trim($time_parts[0]));
            $end_time_parts = explode(' ', trim($time_parts[1]));
            
            if (count($start_time_parts) == 2) {
                $start_hour = $start_time_parts[0];
                $start_period = $start_time_parts[1];
            }
            
            if (count($end_time_parts) == 2) {
                $end_hour = $end_time_parts[0];
                $end_period = $end_time_parts[1];
            }
        }
    }

    // Parse existing days format
    if ($open_days) {
        $selected_days = explode(',', str_replace(' ', '', $open_days));
    }
    
    // Parse existing closed days format
    if ($closed_days) {
        $selected_closed_days = explode(',', str_replace(' ', '', $closed_days));
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate fields
    if (empty($_POST['email'])) {
        $errors['email'] = "Please enter a valid email.";
    } else {
        $email = htmlspecialchars($_POST['email']);
    }

    if (empty($_POST['phone_number'])) {
        $errors['phone_number'] = "Please enter a valid phone number.";
    } else {
        $phone_number = htmlspecialchars($_POST['phone_number']);
    }

    if (empty($_POST['address'])) {
        $errors['address'] = "Address is required.";
    } else {
        $address = htmlspecialchars($_POST['address']);
    }

    // Optional fields
    $facebook_link = isset($_POST['facebook_link']) ? htmlspecialchars($_POST['facebook_link']) : '';
    $instagram_link = isset($_POST['instagram_link']) ? htmlspecialchars($_POST['instagram_link']) : '';
    $linkedin_link = isset($_POST['linkedin_link']) ? htmlspecialchars($_POST['linkedin_link']) : '';
    $twitter_link = isset($_POST['twitter_link']) ? htmlspecialchars($_POST['twitter_link']) : '';
    $youtube_link = isset($_POST['youtube_link']) ? htmlspecialchars($_POST['youtube_link']) : '';
    $video_link = isset($_POST['video_link']) ? htmlspecialchars($_POST['video_link']) : '';
    $logo_title = isset($_POST['logo_title']) ? htmlspecialchars($_POST['logo_title']) : '';
    $logo_alt = isset($_POST['logo_alt']) ? htmlspecialchars($_POST['logo_alt']) : '';
    $map_iframe = isset($_POST['map_iframe']) ? $_POST['map_iframe'] : ''; // REMOVED htmlspecialchars()
    $map_link = isset($_POST['map_link']) ? htmlspecialchars($_POST['map_link']) : '';
    
    // Process alternate contact fields
    $alternate_email = isset($_POST['alternate_email']) ? htmlspecialchars(trim($_POST['alternate_email'])) : '';
    $alternate_phone = isset($_POST['alternate_phone']) ? htmlspecialchars(trim($_POST['alternate_phone'])) : '';
    
    // Validate alternate email if provided
    if (!empty($alternate_email) && !filter_var($alternate_email, FILTER_VALIDATE_EMAIL)) {
        $errors['alternate_email'] = "Please enter a valid alternate email address.";
    }
    
    // Validate alternate phone if provided
    if (!empty($alternate_phone) && !preg_match('/^\+?[0-9]{10,15}$/', $alternate_phone)) {
        $errors['alternate_phone'] = "Please enter a valid alternate phone number.";
    }

$whatsapp_number = isset($_POST['whatsapp_number']) ? htmlspecialchars(trim($_POST['whatsapp_number'])) : '';

// Validate WhatsApp number if provided
if (!empty($whatsapp_number)) {
    // Remove any non-numeric characters
    $whatsapp_clean = preg_replace('/\D/', '', $whatsapp_number);
    
    // Validate 10-digit number
    if (strlen($whatsapp_clean) !== 10 || !ctype_digit($whatsapp_clean)) {
        $errors['whatsapp_number'] = "Please enter a valid 10-digit WhatsApp number.";
    } else {
        $whatsapp_number = $whatsapp_clean; // Store clean number
    }
}
    // Process time fields
    $start_hour = isset($_POST['start_hour']) ? $_POST['start_hour'] : '';
    $start_period = isset($_POST['start_period']) ? $_POST['start_period'] : '';
    $end_hour = isset($_POST['end_hour']) ? $_POST['end_hour'] : '';
    $end_period = isset($_POST['end_period']) ? $_POST['end_period'] : '';

    // Combine time components
    if ($start_hour && $start_period && $end_hour && $end_period) {
        $open_time = $start_hour . ' ' . $start_period . ' - ' . $end_hour . ' ' . $end_period;
    } else {
        $open_time = '';
    }

    // Process days selection
    $selected_days = isset($_POST['days']) ? $_POST['days'] : [];
    if (!empty($selected_days)) {
        $open_days = implode(', ', $selected_days);
    } else {
        $open_days = '';
    }
    
    // Process closed days selection
    $selected_closed_days = isset($_POST['closed_days']) ? $_POST['closed_days'] : [];
    if (!empty($selected_closed_days)) {
        $closed_days = implode(', ', $selected_closed_days);
    } else {
        $closed_days = '';
    }

    // Handle favicon file upload
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == UPLOAD_ERR_OK) {
        // Validate file
        if ($_FILES['favicon']['size'] > 2 * 1024 * 1024) {
            $errors['favicon'] = "Favicon image size exceeds the 2MB limit.";
        } elseif (!in_array($_FILES['favicon']['type'], ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])) {
            $errors['favicon'] = "Only JPG, PNG, and WebP formats are allowed for the favicon.";
        } else {
            try {
                // Delete old favicon if it exists
                if (!empty($favicon_name) && file_exists("assets/images/favicons/$favicon_name")) {
                    unlink("assets/images/favicons/$favicon_name");
                }

                // Generate unique filename
                $extension = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
                $new_favicon_filename = 'favicon_' . time() . '.' . $extension;
                $targetPath = "assets/images/favicons/" . $new_favicon_filename;

                if (move_uploaded_file($_FILES['favicon']['tmp_name'], $targetPath)) {
                    $favicon_name = $new_favicon_filename;
                } else {
                    $errors['favicon'] = "Failed to upload favicon.";
                }
            } catch (Exception $e) {
                $errors['favicon'] = "Error processing favicon: " . $e->getMessage();
            }
        }
    }

    // Handle logo file upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        // Validate file
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
            $errors['logo'] = "Logo image size exceeds the 2MB limit.";
        } elseif (!in_array($_FILES['logo']['type'], ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])) {
            $errors['logo'] = "Only JPG, PNG, and WebP formats are allowed for the logo.";
        } else {
            try {
                // Delete old logo if it exists
                if (!empty($logo_name) && file_exists("assets/images/favicons/$logo_name")) {
                    unlink("assets/images/favicons/$logo_name");
                }

                // Generate unique filename
                $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $new_logo_filename = 'logo_' . time() . '.' . $extension;
                $targetPath = "assets/images/favicons/" . $new_logo_filename;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                    $logo_name = $new_logo_filename;
                } else {
                    $errors['logo'] = "Failed to upload logo.";
                }
            } catch (Exception $e) {
                $errors['logo'] = "Error processing logo: " . $e->getMessage();
            }
        }
    }

    // Update database if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE site_details SET 
                email = ?, 
                alternate_email = ?,
                phone_number = ?, 
                alternate_phone = ?,
                 whatsapp_number = ?, 
                address = ?, 
                facebook_link = ?, 
                instagram_link = ?, 
                linkedin_link = ?, 
                twitter_link = ?, 
                youtube_link = ?, 
                video_link = ?, 
                logo_title = ?, 
                logo_alt = ?, 
                open_time = ?, 
                open_days = ?, 
                closed_days = ?,
                map_iframe = ?, 
                map_link = ?, 
                logo_name = ?, 
                favicon_name = ? 
                WHERE id = 1");

            $result = $stmt->execute([
                $email,
                $alternate_email,
                $phone_number,
                $alternate_phone,
                $whatsapp_number,
                $address,
                $facebook_link,
                $instagram_link,
                $linkedin_link,
                $twitter_link,
                $youtube_link,
                $video_link,
                $logo_title,
                $logo_alt,
                $open_time,
                $open_days,
                $closed_days,
                $map_iframe,
                $map_link,
                $logo_name,
                $favicon_name
            ]);

            if ($result) {
                echo "<script>alert('Site settings updated successfully!'); window.location.href = 'site_settings.php';</script>";
                exit();
            } else {
                $errors['database'] = "Failed to update database.";
            }
        } catch (PDOException $e) {
            $errors['database'] = "Database error: " . $e->getMessage();
        }
    }
}
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
                    <!-- Breadcrumb -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Edit Site Settings</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li class="breadcrumb-item"><a href="site_settings.php">Site Settings</a> &nbsp;
                                            >&nbsp; &nbsp;</li>
                                        <li class="breadcrumb-item active">Edit Site Settings</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Site Settings</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                <?php foreach ($errors as $error): ?>
                                                    <li><?php echo htmlspecialchars($error); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <form action="" method="post" enctype="multipart/form-data">
                                        <!-- Basic Information -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    value="<?php echo htmlspecialchars($email); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="alternate_email" class="form-label">Alternate Email</label>
                                                <input type="email" class="form-control" id="alternate_email" name="alternate_email"
                                                    value="<?php echo htmlspecialchars($alternate_email); ?>">
                                                <small class="text-muted">Optional secondary email address</small>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="phone_number" class="form-label">Phone Number *</label>
                                                <input type="text" class="form-control" id="phone_number"
                                                    name="phone_number"
                                                    value="<?php echo htmlspecialchars($phone_number); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="alternate_phone" class="form-label">Alternate Phone</label>
                                                <input type="text" class="form-control" id="alternate_phone"
                                                    name="alternate_phone"
                                                    value="<?php echo htmlspecialchars($alternate_phone); ?>">
                                                <small class="text-muted">Optional secondary phone number</small>
                                            </div>
                                        </div>

                                        <div class="row">
<div class="col-md-6 mb-3">
        <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
        <div class="input-group">
            <span class="input-group-text">+91</span>
            <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" 
                   value="<?php echo htmlspecialchars($whatsapp_number); ?>" placeholder="9731378655" 
                   maxlength="10" pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number">
        </div>
        <div class="form-text">Enter 10-digit mobile number without country code</div>
    </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="address" class="form-label">Address *</label>
                                                <textarea class="form-control" id="address" name="address" rows="3"
                                                    required><?php echo htmlspecialchars($address); ?></textarea>
                                            </div>
                                        </div>

                                        <!-- Visit Between Section -->
                                        <div class="row">
                                            <div class="col-md-12 mb-4">
                                                <h5 class="card-title">Visit Between</h5>
                                                
                                                <!-- Open Days Selection -->
                                                <div class="mb-3">
                                                    <label class="form-label">Open Days:</label>
                                                    <div class="row">
                                                        <?php 
                                                        $days_options = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                                        foreach ($days_options as $day): 
                                                        ?>
                                                        <div class="col-md-3 col-sm-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                    name="days[]" value="<?php echo $day; ?>" 
                                                                    id="open_day_<?php echo $day; ?>"
                                                                    <?php echo in_array($day, $selected_days) ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="open_day_<?php echo $day; ?>">
                                                                    <?php echo $day; ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                                <!-- Time Selection -->
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Opening Time:</label>
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <select class="form-select" name="start_hour">
                                                                    <option value="">Hour</option>
                                                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                                                        <option value="<?php echo $i; ?>" 
                                                                            <?php echo ($start_hour == $i) ? 'selected' : ''; ?>>
                                                                            <?php echo $i; ?>
                                                                        </option>
                                                                    <?php endfor; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-6">
                                                                <select class="form-select" name="start_period">
                                                                    <option value="">AM/PM</option>
                                                                    <option value="AM" <?php echo ($start_period == 'AM') ? 'selected' : ''; ?>>AM</option>
                                                                    <option value="PM" <?php echo ($start_period == 'PM') ? 'selected' : ''; ?>>PM</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Closing Time:</label>
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <select class="form-select" name="end_hour">
                                                                    <option value="">Hour</option>
                                                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                                                        <option value="<?php echo $i; ?>" 
                                                                            <?php echo ($end_hour == $i) ? 'selected' : ''; ?>>
                                                                            <?php echo $i; ?>
                                                                        </option>
                                                                    <?php endfor; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-6">
                                                                <select class="form-select" name="end_period">
                                                                    <option value="">AM/PM</option>
                                                                    <option value="AM" <?php echo ($end_period == 'AM') ? 'selected' : ''; ?>>AM</option>
                                                                    <option value="PM" <?php echo ($end_period == 'PM') ? 'selected' : ''; ?>>PM</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Closed Days Selection -->
                                                <div class="mb-3">
                                                    <label class="form-label">Closed Days:</label>
                                                    <div class="row">
                                                        <?php 
                                                        foreach ($days_options as $day): 
                                                        ?>
                                                        <div class="col-md-3 col-sm-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                    name="closed_days[]" value="<?php echo $day; ?>" 
                                                                    id="closed_day_<?php echo $day; ?>"
                                                                    <?php echo in_array($day, $selected_closed_days) ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="closed_day_<?php echo $day; ?>">
                                                                    <?php echo $day; ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Social Media -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="facebook_link" class="form-label">Facebook Link</label>
                                                <input type="url" class="form-control" id="facebook_link"
                                                    name="facebook_link"
                                                    value="<?php echo htmlspecialchars($facebook_link); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="instagram_link" class="form-label">Instagram Link</label>
                                                <input type="url" class="form-control" id="instagram_link"
                                                    name="instagram_link"
                                                    value="<?php echo htmlspecialchars($instagram_link); ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="linkedin_link" class="form-label">LinkedIn Link</label>
                                                <input type="url" class="form-control" id="linkedin_link"
                                                    name="linkedin_link"
                                                    value="<?php echo htmlspecialchars($linkedin_link); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="twitter_link" class="form-label">Twitter Link</label>
                                                <input type="url" class="form-control" id="twitter_link"
                                                    name="twitter_link"
                                                    value="<?php echo htmlspecialchars($twitter_link); ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="youtube_link" class="form-label">YouTube Link</label>
                                                <input type="url" class="form-control" id="youtube_link"
                                                    name="youtube_link"
                                                    value="<?php echo htmlspecialchars($youtube_link); ?>">
                                            </div>
                                        </div>

                                        <!-- Logo Information -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="logo_title" class="form-label">Logo Title</label>
                                                <input type="text" class="form-control" id="logo_title"
                                                    name="logo_title"
                                                    value="<?php echo htmlspecialchars($logo_title); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="logo_alt" class="form-label">Logo Alt Text</label>
                                                <input type="text" class="form-control" id="logo_alt" name="logo_alt"
                                                    value="<?php echo htmlspecialchars($logo_alt); ?>">
                                            </div>
                                        </div>

                                        <!-- Map Section -->
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="map_iframe" class="form-label">Map Embed Code</label>
                                                <textarea class="form-control" id="map_iframe" name="map_iframe"
                                                    rows="4"
                                                    placeholder="Paste your Google Maps embed iframe code here..."><?php echo $map_iframe; ?></textarea>
                                                <small class="text-muted">
                                                    <strong>Instructions:</strong>
                                                    Go to Google Maps → Search location → Share → Embed a map → Copy the
                                                    iframe code and paste here
                                                </small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="map_link" class="form-label">Map Link</label>
                                                <input type="url" class="form-control" id="map_link" name="map_link"
                                                    value="<?php echo htmlspecialchars($map_link); ?>">
                                            </div>
                                        </div>

                                        <!-- File Uploads -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="favicon" class="form-label">Favicon</label>
                                                <?php if (!empty($favicon_name)): ?>
                                                    <div class="mb-2">
                                                        <img src="assets/images/favicons/<?php echo htmlspecialchars($favicon_name); ?>"
                                                            alt="Current Favicon" width="32" height="32" class="border">
                                                        <small class="d-block text-muted">Current:
                                                            <?php echo htmlspecialchars($favicon_name); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="favicon" name="favicon"
                                                    accept="image/*">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="logo" class="form-label">Logo</label>
                                                <?php if (!empty($logo_name)): ?>
                                                    <div class="mb-2">
                                                        <img src="assets/images/favicons/<?php echo htmlspecialchars($logo_name); ?>"
                                                            alt="Current Logo" style="max-height: 100px;" class="border">
                                                        <small class="d-block text-muted">Current:
                                                            <?php echo htmlspecialchars($logo_name); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="logo" name="logo"
                                                    accept="image/*">
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary me-2">Update Settings</button>
                                            <a href="site_settings.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require './shared_components/footer.php'; ?>
    </div>
    <?php require './shared_components/scripts.php'; ?>
    <script>
document.getElementById('whatsapp_number').addEventListener('input', function(e) {
    // Remove any non-numeric characters
    this.value = this.value.replace(/\D/g, '');
    
    // Limit to 10 digits
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }
});
</script>

</body>

</html>