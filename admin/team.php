<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Database connection
include 'shared_components/db.php';

// Handle form submission for updating title
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title_only_update'])) { // Changed check to differentiate
    $title = trim($_POST['title']);

    // Validate and update title
    if (!empty($title) && strlen($title) >= 2 && strlen($title) <= 50) {
        try {
            // Only update the 'title' column
            $update_stmt = $pdo->prepare("UPDATE team_title SET title = :title WHERE id = :id");
            $update_stmt->execute([
                ':title' => $title,
                ':id' => 1
            ]);

            // Redirect to prevent form resubmission
            echo "<script>alert('Title Updated Successfully!');
                window.location.href = 'team.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Invalid title! Title must be between 2 and 50 characters.');</script>";
    }
}

// Fetch all team members
$stmt = $pdo->prepare("SELECT * FROM team ORDER BY created_at DESC");
$stmt->execute();
$team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Fetch team member to get the image name
    $stmt = $pdo->prepare("SELECT img_name FROM team WHERE id = ?");
    $stmt->execute([$delete_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member && $member['img_name']) {
        $image_path = "assets/images/team/" . $member['img_name'];
        if (file_exists($image_path)) {
            unlink($image_path); // Delete the image from the server
        }
    }

    // Delete the team member from the database
    $stmt = $pdo->prepare("DELETE FROM team WHERE id = ?");
    $stmt->execute([$delete_id]);

    echo "<script>alert('Team member deleted successfully!');
    window.location.href = 'team.php';</script>";
    exit();
}

// Fetch existing title for the form
$team_title_stmt = $pdo->prepare("SELECT * FROM team_title WHERE id = 1");
$team_title_stmt->execute();
$result = $team_title_stmt->fetch(PDO::FETCH_ASSOC);
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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">
                                    Team Members
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Team Members
                                        </li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="" class="custom-validation">
                                    <input type="hidden" name="title_only_update" value="1">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label" for="title">Heading (required)</label>
                                            <input type="text" name="title" id="title" class="form-control"
                                                value="<?php echo htmlspecialchars($result['title']); ?>" minlength="2"
                                                maxlength="50" required placeholder="Minimum 2 characters">
                                        </div>

                                    </div>
                                    <div class="m-3 text-center">
                                        <button type="submit"
                                            class="btn btn-primary waves-effect waves-light">Update</button>
                                    </div>
                                </form>
                                <br>
                                <hr>
                                <div class="team-members">
                                    <div class="m-3 text-center">
                                        <a href="add-team-member.php" class="btn btn-primary mt-4">Add New Team
                                            Member</a>
                                    </div>

                                    <div class="table-rep-plugin">
                                        <div class="table-responsive mb-0" data-pattern="priority-columns">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Sl. No</th>
                                                        <th>Name</th>
                                                        <th>Designation</th>
                                                        <th>Image</th>
                                                        <th>Img Name</th>
                                                        <th>Img Alt</th>
                                                        <th>Img Title</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sl_no = 1;
                                                    foreach ($team_members as $member): ?>
                                                        <tr>
                                                            <td><?php echo $sl_no; ?></td>
                                                            <td><?php echo htmlspecialchars($member['name']); ?></td>
                                                            <td><?php echo htmlspecialchars($member['designation']); ?></td>
                                                            <td>
                                                                <?php
                                                                // Fixed image path logic
                                                                $image_path = '';
                                                                if (!empty($member['img_name']) && file_exists('assets/images/team/' . $member['img_name'])) {
                                                                    $image_path = 'assets/images/team/' . htmlspecialchars($member['img_name']);
                                                                } else {
                                                                    // Use JPG fallback instead of WebP
                                                                    $image_path = 'assets/images/team/user.webp';
                                                                }
                                                                ?>
                                                                <img src="<?php echo $image_path; ?>"
                                                                    alt="<?php echo htmlspecialchars($member['img_alt']); ?>"
                                                                    width="50" height="50" class="img-thumbnail"
                                                                    onerror="this.src='assets/images/team/user.webp';">
                                                            </td>
                                                            <td><?php echo htmlspecialchars($member['img_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($member['img_alt']); ?></td>
                                                            <td><?php echo htmlspecialchars($member['img_title']); ?></td>
                                                            <td>
                                                                <div class="button-container">
                                                                    <a href="edit-team-member.php?id=<?php echo $member['id']; ?>"
                                                                        class="btn btn-primary" data-bs-toggle="tooltip"
                                                                        title="Edit">
                                                                        <i class="bx bxs-pencil"></i>
                                                                    </a>
                                                                    <form method="POST" class="d-inline"
                                                                        onsubmit="return confirmDelete();">
                                                                        <input type="hidden" name="delete_id"
                                                                            value="<?php echo $member['id']; ?>">
                                                                        <button type="submit" class="btn btn-danger"
                                                                            data-bs-toggle="tooltip" title="Delete">
                                                                            <i class="bx bx-trash"></i>
                                                                        </button>
                                                                    </form>

                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        $sl_no++;
                                                    endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function confirmDelete() {
                return confirm('Are you sure you want to delete this item?');
            }
        </script>
        <?php
        require './shared_components/footer.php';
        ?>
    </div>
    <?php require './shared_components/scripts.php'; ?>
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
</body>

</html>