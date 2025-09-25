<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// Fetch the current stats from the table
$stmt = $pdo->prepare("SELECT * FROM stats WHERE id = :id LIMIT 1");
$stmt->execute(['id' => 1]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the form is submitted to update the values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve the four new POST variables
    $projects_completed = (int) trim($_POST['projects_completed']);
    $client_satisfaction_rate = (int) trim($_POST['client_satisfaction_rate']);
    $sq_ft_transformed = (int) trim($_POST['sq_ft_transformed']);
    $years_of_combined_team_experience = (int) trim($_POST['years_of_combined_team_experience']);

    // Prepare the UPDATE statement with the correct column names
    $update_stmt = $pdo->prepare("UPDATE stats SET 
        projects_completed = :projects_completed,
        client_satisfaction_rate = :client_satisfaction_rate,
        sq_ft_transformed = :sq_ft_transformed,
        years_of_combined_team_experience = :years_of_combined_team_experience
        WHERE id = :id");

    $update_result = $update_stmt->execute([
        'projects_completed' => $projects_completed,
        'client_satisfaction_rate' => $client_satisfaction_rate,
        'sq_ft_transformed' => $sq_ft_transformed,
        'years_of_combined_team_experience' => $years_of_combined_team_experience,
        'id' => 1
    ]);

    if ($update_result) {
        echo "<script>alert('Stats Updated Successfully!'); 
        window.location.href = 'stats.php';</script>";
        exit();
    } else {
        $error = "Failed to update database.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <title>Fun Factor Stats Management</title>
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
                                <h4 class="mb-sm-0 font-size-18">Fun Factor Stats</h4>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <p><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Update Site Statistics</h4>
                                    <form method="post">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Projects Completed</label>
                                                <input type="number" name="projects_completed" class="form-control"
                                                    value="<?php echo htmlspecialchars($stats['projects_completed'] ?? '0'); ?>"
                                                    required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Client Satisfaction Rate (%)</label>
                                                <input type="number" name="client_satisfaction_rate"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($stats['client_satisfaction_rate'] ?? '0'); ?>"
                                                    required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Square Feet Transformed</label>
                                                <input type="number" name="sq_ft_transformed" class="form-control"
                                                    value="<?php echo htmlspecialchars($stats['sq_ft_transformed'] ?? '0'); ?>"
                                                    required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Years of Combined Team Experience</label>
                                                <input type="number" name="years_of_combined_team_experience"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($stats['years_of_combined_team_experience'] ?? '0'); ?>"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="mt-4 text-center">
                                            <button type="submit"
                                                class="btn btn-primary waves-effect waves-light">Update Stats</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php require './shared_components/footer.php'; ?>
        </div>
        <?php require './shared_components/scripts.php'; ?>
    </div>
</body>

</html>