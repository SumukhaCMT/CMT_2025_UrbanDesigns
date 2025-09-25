<?php
session_start();
require './shared_components/session.php';
require './shared_components/error.php';

// Database connection
include 'shared_components/db.php';

$errors = [];
$name = $email = $phone_number = $subject = $message = "";
$enquiry_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch contact enquiry data based on ID
$stmt = $pdo->prepare("SELECT * FROM contact_enquiry WHERE id = ?");
$stmt->execute([$enquiry_id]);
$enquiry_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enquiry_data) {
    echo "<script>alert('Contact enquiry not found!');
          window.location.href = 'contact-enquiries.php';</script>";
    exit();
}

// Prepopulate enquiry data
$name = $enquiry_data['name'];
$email = $enquiry_data['email'];
$phone_number = $enquiry_data['phone_number'];
$subject = $enquiry_data['subject'];
$message = $enquiry_data['message'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    $name = !empty($_POST['name']) ? htmlspecialchars($_POST['name']) : $errors['name'] = "Name is required.";

    // Validate email
    $email = !empty($_POST['email']) ? (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : $errors['email'] = "Invalid email.") : $errors['email'] = "Email is required.";

    // Validate phone number
    $phone_number = !empty($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : $errors['phone_number'] = "Phone number is required.";
    if (!preg_match('/^\+?[0-9]{10,14}$/', $phone_number)) {
        $errors['phone_number'] = "Invalid phone number format.";
    }

    // Validate subject
    $subject = !empty($_POST['subject']) ? htmlspecialchars($_POST['subject']) : $errors['subject'] = "Subject is required.";

    // Validate message
    $message = !empty($_POST['message']) ? htmlspecialchars($_POST['message']) : $errors['message'] = "Message is required.";

    // Update enquiry data if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE contact_enquiry SET name = ?, email = ?, phone_number = ?, subject = ?, message = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone_number, $subject, $message, $enquiry_id]);

        echo "<script>alert('Contact enquiry updated successfully!');
              window.location.href = 'contact-enquiries.php';</script>";
        exit();
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
        <?php
        require './shared_components/header.php';
        require './shared_components/navbar.php';
        ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- Breadcrumb -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">
                                    Edit Contact Enquiries
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>

                                            <a href="contact-enquiries.php">Contact Enquiries</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        <li>
                                            Edit Contact Enquiries
                                        </li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- Breadcrumb end -->
                </div>

                <!-- Main content -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <form action="" method="POST" class="custom-validation">
                                    <div class="row">
                                        <!-- Name -->
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Name:</label>
                                            <input type="text" id="name" name="name" class="form-control"
                                                value="<?php echo htmlspecialchars($name); ?>" minlength="2" required
                                                placeholder="Enter name (min 2 characters)" required>
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email:</label>
                                            <input type="email" id="email" name="email" class="form-control"
                                                value="<?php echo htmlspecialchars($email); ?>" required
                                                placeholder="Enter email" required>
                                        </div>

                                        <!-- Phone Number -->
                                        <div class="col-md-6 mb-3">
                                            <label for="phone_number" class="form-label">Phone Number:</label>
                                            <input type="number" id="phone_number" name="phone_number"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($phone_number); ?>" required
                                                placeholder="Enter phone number (10 digits)">
                                        </div>

                                        <!-- Subject -->
                                        <div class="col-md-6 mb-3">
                                            <label for="subject" class="form-label">Subject:</label>
                                            <input type="text" id="subject" name="subject" class="form-control"
                                                value="<?php echo htmlspecialchars($subject); ?>" required
                                                placeholder="Enter subject">
                                        </div>

                                        <!-- Message -->
                                        <div class="mb-3">
                                            <label for="message" class="form-label">Message:</label>
                                            <textarea id="message" name="message" class="form-control" rows="5" required
                                                placeholder="Enter message"><?php echo htmlspecialchars($message); ?></textarea>
                                        </div>
                                    </div>
                                    <!-- Submit Button -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                        <a href="contact-enquiries.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>



                <!--  -->

            </div>
        </div>


        <?php
        require './shared_components/footer.php';
        ?>
    </div>
    <?php require './shared_components/scripts.php'; ?>
    <!-- validation -->
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
</body>

</html>