<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

/* fetch complete record + cohorts */
$sql = "
SELECT ss.*,
       c.name  AS category,
       sc.name AS subcategory,
       GROUP_CONCAT(ct.cohort_name ORDER BY ct.id SEPARATOR ', ') AS cohorts,
       MAX(ct.weighted_score) AS weighted_score
  FROM subcategory_settings ss
  JOIN categories    c  ON c.id  = ss.category_id
  JOIN subcategories sc ON sc.id = ss.subcategory_id
  LEFT JOIN cohorts  ct ON ct.category_id    = ss.category_id
                       AND ct.subcategory_id = ss.subcategory_id
 WHERE ss.id = ?
 GROUP BY ss.id";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$policy = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$policy) {
    die('<h4 class="text-danger m-4">Record not found.</h4>');
}
error_log("Cohorts fetched from DB: " . $policy['cohorts']);
/* Map DB keys → human-readable labels (one per table header in listing) */
$labels = [
    'category' => 'Category',
    'subcategory' => 'Sub-category',
    'cohorts' => 'Cohorts',
    'weighted_score' => 'Weighted Score',
    'sum_insured' => 'Sum Insured',
    'product_type' => 'Product Type',
    'adult_age_from' => 'Adult Age From',
    'adult_age_to' => 'Adult Age To',
    'child_age_from' => 'Child Age From (days)',
    'child_age_to' => 'Child Age To (yrs)',
    'plan_combination' => 'Plan Combination',
    'room_rent' => 'Room Rent',
    'domiciliary_hospitalization' => 'Domiciliary Hospitalization',
    'pre_hospitalization_days' => 'Pre-Hospitalization (days)',
    'post_hospitalization_days' => 'Post-Hospitalization (days)',
    'ayush_treatment' => 'AYUSH Treatment',
    'day_care_treatment_covered' => 'Day-care Treatment',
    'organ_donor_expenses' => 'Organ Donor Expenses',
    'hospital_daily_allowance' => 'Hospital Daily Allowance',
    'cumulative_bonus' => 'Cumulative Bonus',
    'restoration_benefit' => 'Restoration Benefit',
    'restoration_details' => 'Restoration Details',
    'modern_treatment' => 'Modern Treatment',
    'disease_sub_limits' => 'Disease Sub-limits',
    'copayment' => 'Co-payment',
    'copayment_detail' => 'Co-payment Detail',
    'consumable_cover' => 'Consumable Cover',
    'consumable_detail' => 'Consumable Detail',
    'road_ambulance_cover' => 'Road Ambulance Cover',
    'air_ambulance_cover' => 'Air Ambulance Cover',
    'worldwide_emergency' => 'Worldwide Emergency',
    'maternity_benefits' => 'Maternity Benefits',
    'maternity_waiting_period' => 'Maternity Waiting Period',
    'new_born_baby_covered' => 'New-born Covered',
    'ivf_cover' => 'IVF Cover',
    'health_checkup' => 'Health Check-up',
    'wellness_benefits' => 'Wellness Benefits',
    'opd_details' => 'OPD Details',
    'accidental_cover' => 'Accidental Cover',
    'critical_illness_cover' => 'Critical Illness Cover',
    'dental_cover' => 'Dental Cover',
    'diabetes_cover_from_day1' => 'Diabetes Cover (Day 1)',
    'portability_option' => 'Portability Option',
    'claim_settlement_ratio' => 'Claim Settlement Ratio (%)',
    'insurer_vintage_years' => 'Insurer Track Record (yrs)',
    'created_at' => 'Created At'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <title>View Policy Detail</title>
</head>

<body>
    <div id="layout-wrapper">
        <?php require './shared_components/header.php'; ?>
        <?php require './shared_components/navbar.php'; ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">

                                    <div class="d-flex justify-content-between mb-3">
                                        <h4 class="card-title mb-0">Policy Detail</h4>
                                        <div>
                                            <a href="policy-details.php?id=<?= $policy['id']; ?>"
                                                class="btn btn-primary btn-sm">Edit</a>

                                            <a href="policy-details.php" class="btn btn-secondary btn-sm">Back</a>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <tbody>
                                                <?php
                                                foreach ($labels as $key => $label) {
                                                    $val = $policy[$key] ?? '';
                                                    /* pretty-print dates & empty cells */
                                                    if ($key === 'created_at') {
                                                        $val = date('d-M-Y H:i', strtotime($val));
                                                    } elseif ($val === '') {
                                                        $val = '<em class="text-muted">—</em>';
                                                    } else {
                                                        $val = htmlspecialchars($val);
                                                    }
                                                    echo "<tr>
                                    <th style='width:30%;white-space:nowrap;'>{$label}</th>
                                    <td>{$val}</td>
                                  </tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div><!-- /.card-body -->
                            </div><!-- /.card -->
                        </div>
                    </div>

                </div><!-- /.container-fluid -->
            </div><!-- /.page-content -->
            <?php require './shared_components/footer.php'; ?>
        </div><!-- /.main-content -->
        <?php require './shared_components/scripts.php'; ?>
    </div><!-- /#layout-wrapper -->
</body>

</html>