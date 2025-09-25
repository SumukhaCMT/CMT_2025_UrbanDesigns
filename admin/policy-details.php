<?php
// admin/policy-details.php

require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';
// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // A date in the past

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// ─── Success flag ────────────────────────────────────────────
$success = $_GET['success'] ?? '';

// ─── Handle Delete ───────────────────────────────────────────
if (isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    $pdo->beginTransaction();
    // remove related cohorts
    $pdo->prepare("
      DELETE c
        FROM cohorts c
        JOIN subcategory_settings ss
          ON ss.category_id   = c.category_id
         AND ss.subcategory_id = c.subcategory_id
       WHERE ss.id = ?
    ")->execute([$id]);
    // remove the subcategory_settings row
    $pdo->prepare("DELETE FROM subcategory_settings WHERE id = ?")
        ->execute([$id]);
    $pdo->commit();
    echo "<script>
            alert('Policy detail deleted.');
            window.location='policy-details.php';
          </script>";
    exit;
}

// ─── Load dropdown & datalist sources ────────────────────────
$allCats = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$allSubcats = $pdo->query("
  SELECT s.id, c.id AS category_id, s.name
    FROM subcategories s
    JOIN categories c ON c.id=s.category_id
   ORDER BY c.name, s.name
")->fetchAll();
$subcatsByCat = [];
foreach ($allSubcats as $s) {
    $subcatsByCat[$s['category_id']][] = ['id' => $s['id'], 'name' => $s['name']];
}
$productList = $pdo->query("SELECT DISTINCT product_type                FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$planList = $pdo->query("SELECT DISTINCT plan_combination            FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$domiList = $pdo->query("SELECT DISTINCT domiciliary_hospitalization FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$ayushList = $pdo->query("SELECT DISTINCT ayush_treatment             FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$daycareList = $pdo->query("SELECT DISTINCT day_care_treatment_covered  FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$organList = $pdo->query("SELECT DISTINCT organ_donor_expenses        FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$cumBonusList = $pdo->query("SELECT DISTINCT cumulative_bonus            FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$modernList = $pdo->query("SELECT DISTINCT modern_treatment             FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$diseaseList = $pdo->query("SELECT DISTINCT disease_sub_limits           FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$roadAmbList = $pdo->query("SELECT DISTINCT road_ambulance_cover          FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$airAmbList = $pdo->query("SELECT DISTINCT air_ambulance_cover           FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$healthList = $pdo->query("SELECT DISTINCT health_checkup               FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$wellList = $pdo->query("SELECT DISTINCT wellness_benefits            FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$accidList = $pdo->query("SELECT DISTINCT accidental_cover             FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$dentalList = $pdo->query("SELECT DISTINCT dental_cover                 FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);
$diabetesList = $pdo->query("SELECT DISTINCT diabetes_cover_from_day1     FROM subcategory_settings")->fetchAll(PDO::FETCH_COLUMN);

// ─── Determine edit mode ─────────────────────────────────────
$editId = $_GET['id'] ?? '';
$isEdit = ctype_digit($editId);

// ─── Sticky form vars ────────────────────────────────────────
$category_id = $_POST['category_id'] ?? '';
$subcategory_id = $_POST['subcategory_id'] ?? '';
$selectedCohorts = $_POST['cohorts'] ?? [];
$weighted_score = $_POST['weighted_score'] ?? '';
$sum_insured = $_POST['sum_insured'] ?? '';
$product_type = trim($_POST['product_type'] ?? '');
$adult_age_from = $_POST['adult_age_from'] ?? '';
$adult_age_to = $_POST['adult_age_to'] ?? '';
$child_age_from = $_POST['child_age_from'] ?? '';
$child_age_to = $_POST['child_age_to'] ?? '';
$plan_combination = trim($_POST['plan_combination'] ?? '');
$room_rent = trim($_POST['room_rent'] ?? '');
$domiciliary_hospitalization = trim($_POST['domiciliary_hospitalization'] ?? '');
$pre_hospitalization_months = $_POST['pre_hospitalization_months'] ?? '';
$post_hospitalization_months = $_POST['post_hospitalization_months'] ?? '';
$ayush_treatment = trim($_POST['ayush_treatment'] ?? '');
$day_care_treatment_covered = trim($_POST['day_care_treatment_covered'] ?? '');
$organ_donor_expenses = trim($_POST['organ_donor_expenses'] ?? '');
$hospital_daily_allowance = trim($_POST['hospital_daily_allowance'] ?? '');
$cumulative_bonus = trim($_POST['cumulative_bonus'] ?? '');
$restoration_benefit = $_POST['restoration_benefit'] ?? '';
$restoration_details = trim($_POST['restoration_details'] ?? '');
$modern_treatment = trim($_POST['modern_treatment'] ?? '');
$disease_sub_limits = trim($_POST['disease_sub_limits'] ?? '');
$copayment = $_POST['copayment'] ?? '';
$copayment_detail = trim($_POST['copayment_detail'] ?? '');
$consumable_cover = $_POST['consumable_cover'] ?? '';
$consumable_detail = trim($_POST['consumable_detail'] ?? '');
$road_ambulance_cover = trim($_POST['road_ambulance_cover'] ?? '');
$air_ambulance_cover = trim($_POST['air_ambulance_cover'] ?? '');
$worldwide_emergency = $_POST['worldwide_emergency'] ?? '';
$maternity_benefits = $_POST['maternity_benefits'] ?? '';
$maternity_waiting_period = $_POST['maternity_waiting_period'] ?? '';
$new_born_baby_covered = $_POST['new_born_baby_covered'] ?? '';
$ivf_cover = $_POST['ivf_cover'] ?? '';
$health_checkup = trim($_POST['health_checkup'] ?? '');
$wellness_benefits = trim($_POST['wellness_benefits'] ?? '');
$opd_details = $_POST['opd_details'] ?? '';
$accidental_cover = trim($_POST['accidental_cover'] ?? '');
$critical_illness_cover = $_POST['critical_illness_cover'] ?? '';
$dental_cover = trim($_POST['dental_cover'] ?? '');
$diabetes_cover_from_day1 = trim($_POST['diabetes_cover_from_day1'] ?? '');
$portability_option = $_POST['portability_option'] ?? '';
$claim_settlement_ratio = $_POST['claim_settlement_ratio'] ?? '';
$insurer_vintage_years = $_POST['insurer_vintage_years'] ?? '';

// ─── If editing & GET, load record + cohorts ────────────────
if ($isEdit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM subcategory_settings WHERE id = ?");
    $stmt->execute([$editId]);
    $rec = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$rec) {
        echo "<div class='alert alert-danger'>Record not found.</div>";
        exit;
    }
    foreach ($rec as $k => $v) {
        $$k = $v;
    }
    // fetch its cohorts
    $coh = $pdo->prepare("
      SELECT cohort_name, weighted_score
        FROM cohorts
       WHERE category_id   = ?
         AND subcategory_id = ?
    ");
    $coh->execute([$rec['category_id'], $rec['subcategory_id']]);
    $selectedCohorts = [];
    $weighted_score = '';
    foreach ($coh->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $selectedCohorts[] = $r['cohort_name'];
        $weighted_score = $r['weighted_score'];
    }
}

/* ─── Handle Update POST ─────────────────────────────────────────── */
if ($isEdit && $_SERVER['REQUEST_METHOD'] === 'POST') {

    /* 1️⃣  ──   VALIDATION  ─────────────────────────────────────── */
    $errors = [];

    if (!ctype_digit($category_id))
        $errors[] = 'Select a category.';
    if (!ctype_digit($subcategory_id))
        $errors[] = 'Select a subcategory.';
    if (empty($selectedCohorts))
        $errors[] = 'Select at least one cohort.';
    if (!is_numeric($weighted_score) || $weighted_score < 0)
        $errors[] = 'Enter valid weighted score.';
    if (!is_numeric($sum_insured) || $sum_insured < 0)
        $errors[] = 'Enter valid sum insured.';
    if ($product_type === '')
        $errors[] = 'Enter product type.';
    if (
        !ctype_digit($adult_age_from) ||
        !ctype_digit($adult_age_to) ||
        $adult_age_from > $adult_age_to
    )
        $errors[] = 'Adult “from” must be ≤ “to”.';
    if (
        !ctype_digit($child_age_from) ||
        $child_age_from < 0 ||
        $child_age_from > 365
    )
        $errors[] = 'Children “from” days 0–365.';
    if (!ctype_digit($child_age_to))
        $errors[] = 'Children “to” in years.';
    if (empty($errors) && $child_age_to >= $adult_age_from)
        $errors[] = 'Children “to” < Adult “from”.';

    /* Fetch the original category / sub-category pair ------------- */
    $origStmt = $pdo->prepare("
        SELECT category_id AS oldCat, subcategory_id AS oldSub
          FROM subcategory_settings
         WHERE id = ?
    ");
    $origStmt->execute([$editId]);
    $orig = $origStmt->fetch(PDO::FETCH_ASSOC);   // $orig = ['oldCat'=>…, 'oldSub'=>…]

    /* 2️⃣  ──  ONLY PROCEED IF NO ERRORS  ────────────────────────── */
    if (empty($errors)) {

        $pdo->beginTransaction();
        try {

            /* 2a. UPDATE the parent record in subcategory_settings */
            $upd = $pdo->prepare("
              UPDATE subcategory_settings SET
                  category_id                 = :cid,
                  subcategory_id              = :scid,
                  sum_insured                 = :si,
                  product_type                = :pt,
                  adult_age_from              = :af,
                  adult_age_to                = :at,
                  child_age_from              = :cf,
                  child_age_to                = :ct,
                  plan_combination            = :pc,
                  room_rent                   = :rr,
                  domiciliary_hospitalization = :dh,
                  pre_hospitalization_days    = :ph,
                  post_hospitalization_days   = :po,
                  ayush_treatment             = :ay,
                  day_care_treatment_covered  = :dc,
                  organ_donor_expenses        = :od,
                  hospital_daily_allowance    = :hd,
                  cumulative_bonus            = :cb,
                  restoration_benefit         = :rb,
                  restoration_details         = :rd,
                  modern_treatment            = :mt,
                  disease_sub_limits          = :ds,
                  copayment                   = :cp,
                  copayment_detail            = :cpd,
                  consumable_cover            = :cc,
                  consumable_detail           = :ccd,
                  road_ambulance_cover        = :rac,
                  air_ambulance_cover         = :aac,
                  worldwide_emergency         = :we,
                  maternity_benefits          = :mb,
                  maternity_waiting_period    = :mw,
                  new_born_baby_covered       = :nb,
                  ivf_cover                   = :ivf,
                  health_checkup              = :hc,
                  wellness_benefits           = :wb,
                  opd_details                 = :odl,
                  accidental_cover            = :ac,
                  critical_illness_cover      = :ci,
                  dental_cover                = :dcv,
                  diabetes_cover_from_day1    = :dc1,
                  portability_option          = :poo,
                  claim_settlement_ratio      = :csr,
                  insurer_vintage_years       = :ivy
              WHERE id = :id
            ");
            $upd->execute([
                ':cid' => $category_id,
                ':scid' => $subcategory_id,
                ':si' => $sum_insured,
                ':pt' => $product_type,
                ':af' => $adult_age_from,
                ':at' => $adult_age_to,
                ':cf' => $child_age_from,
                ':ct' => $child_age_to,
                ':pc' => $plan_combination,
                ':rr' => $room_rent,
                ':dh' => $domiciliary_hospitalization,
                ':ph' => $pre_hospitalization_months,
                ':po' => $post_hospitalization_months,
                ':ay' => $ayush_treatment,
                ':dc' => $day_care_treatment_covered,
                ':od' => $organ_donor_expenses,
                ':hd' => $hospital_daily_allowance,
                ':cb' => $cumulative_bonus,
                ':rb' => $restoration_benefit,
                ':rd' => $restoration_details,
                ':mt' => $modern_treatment,
                ':ds' => $disease_sub_limits,
                ':cp' => $copayment,
                ':cpd' => $copayment_detail,
                ':cc' => $consumable_cover,
                ':ccd' => $consumable_detail,
                ':rac' => $road_ambulance_cover,
                ':aac' => $air_ambulance_cover,
                ':we' => $worldwide_emergency,
                ':mb' => $maternity_benefits,
                ':mw' => $maternity_waiting_period,
                ':nb' => $new_born_baby_covered,
                ':ivf' => $ivf_cover,
                ':hc' => $health_checkup,
                ':wb' => $wellness_benefits,
                ':odl' => $opd_details,
                ':ac' => $accidental_cover,
                ':ci' => $critical_illness_cover,
                ':dcv' => $dental_cover,
                ':dc1' => $diabetes_cover_from_day1,
                ':poo' => $portability_option,
                ':csr' => $claim_settlement_ratio,
                ':ivy' => $insurer_vintage_years,
                ':id' => $editId
            ]);

            /* 2b. DELETE all existing cohorts for CURRENT Cat/Sub */
            $pdo->prepare("
                DELETE FROM cohorts
                WHERE category_id    = :cid
                  AND subcategory_id = :scid
            ")->execute([
                        ':cid' => $category_id,
                        ':scid' => $subcategory_id
                    ]);

            /* 2c. Optional clean-up of OLD Cat/Sub if it changed   */
            if ($category_id != $orig['oldCat'] || $subcategory_id != $orig['oldSub']) {
                $pdo->prepare("
                    DELETE FROM cohorts
                    WHERE category_id    = :ocid
                      AND subcategory_id = :oscid
                ")->execute([
                            ':ocid' => $orig['oldCat'],
                            ':oscid' => $orig['oldSub']
                        ]);
            }


            /* 2d. INSERT the newly-selected cohorts (deduped)      */
            $ins = $pdo->prepare("
                INSERT INTO cohorts
                  (category_id, subcategory_id, cohort_name, weighted_score)
                VALUES
                  (:cid, :scid, :name, :ws)
            ");
            foreach (array_unique($selectedCohorts) as $cName) {
                $ins->execute([
                    ':cid' => $category_id,
                    ':scid' => $subcategory_id,
                    ':name' => $cName,
                    ':ws' => $weighted_score
                ]);
            }

            /* 2e. COMMIT everything */
            $pdo->commit();
            header("Location: view-policy-detail.php?id={$editId}&success=1");
            exit;

        } catch (Throwable $e) {
            /* Keep your existing error handling */
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;         // or use your own error handler
        }
    }
}
error_log("Cohorts received from form: " . print_r($selectedCohorts, true));

// ─── Fetch Listing WITH combined Cohorts ──────────────────────
$list = $pdo->query("
  SELECT
    ss.id,
    c.name  AS category,
    sc.name AS subcategory,
    GROUP_CONCAT(ct.cohort_name ORDER BY ct.id SEPARATOR ', ') AS cohorts,
    MAX(ct.weighted_score)               AS weighted_score,
    ss.sum_insured,
    ss.product_type,
    ss.adult_age_from,
    ss.adult_age_to,
    ss.child_age_from,
    ss.child_age_to,
    ss.plan_combination,
    ss.room_rent,
    ss.domiciliary_hospitalization,
    ss.pre_hospitalization_days,
    ss.post_hospitalization_days,
    ss.ayush_treatment,
    ss.day_care_treatment_covered,
    ss.organ_donor_expenses,
    ss.hospital_daily_allowance,
    ss.cumulative_bonus,
    ss.restoration_benefit,
    ss.restoration_details,
    ss.modern_treatment,
    ss.disease_sub_limits,
    ss.copayment,
    ss.copayment_detail,
    ss.consumable_cover,
    ss.consumable_detail,
    ss.road_ambulance_cover,
    ss.air_ambulance_cover,
    ss.worldwide_emergency,
    ss.maternity_benefits,
    ss.maternity_waiting_period,
    ss.new_born_baby_covered,
    ss.ivf_cover,
    ss.health_checkup,
    ss.wellness_benefits,
    ss.opd_details,
    ss.accidental_cover,
    ss.critical_illness_cover,
    ss.dental_cover,
    ss.diabetes_cover_from_day1,
    ss.portability_option,
    ss.claim_settlement_ratio,
    ss.insurer_vintage_years,
    ss.created_at
  FROM subcategory_settings ss
  JOIN categories c     ON c.id  = ss.category_id
  JOIN subcategories sc ON sc.id = ss.subcategory_id
  LEFT JOIN cohorts ct
    ON ct.category_id    = ss.category_id
   AND ct.subcategory_id = ss.subcategory_id
  GROUP BY ss.id
")->fetchAll(PDO::FETCH_ASSOC);

// ─── CSV Export Handler ────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $export = $pdo->query("
      SELECT
        c.name  AS category,
        sc.name AS subcategory,
        GROUP_CONCAT(ct.cohort_name ORDER BY ct.id SEPARATOR ', ') AS cohorts,
        MAX(ct.weighted_score)               AS weighted_score,
        ss.sum_insured,
        ss.product_type,
        ss.adult_age_from,
        ss.adult_age_to,
        ss.child_age_from,
        ss.child_age_to,
        ss.plan_combination,
        ss.room_rent,
        ss.domiciliary_hospitalization,
        ss.pre_hospitalization_days,
        ss.post_hospitalization_days,
        ss.ayush_treatment,
        ss.day_care_treatment_covered,
        ss.organ_donor_expenses,
        ss.hospital_daily_allowance,
        ss.cumulative_bonus,
        ss.restoration_benefit,
        ss.restoration_details,
        ss.modern_treatment,
        ss.disease_sub_limits,
        ss.copayment,
        ss.copayment_detail,
        ss.consumable_cover,
        ss.consumable_detail,
        ss.road_ambulance_cover,
        ss.air_ambulance_cover,
        ss.worldwide_emergency,
        ss.maternity_benefits,
        ss.maternity_waiting_period,
        ss.new_born_baby_covered,
        ss.ivf_cover,
        ss.health_checkup,
        ss.wellness_benefits,
        ss.opd_details,
        ss.accidental_cover,
        ss.critical_illness_cover,
        ss.dental_cover,
        ss.diabetes_cover_from_day1,
        ss.portability_option,
        ss.claim_settlement_ratio,
        ss.insurer_vintage_years,
        ss.created_at
      FROM subcategory_settings ss
      JOIN categories c     ON c.id  = ss.category_id
      JOIN subcategories sc ON sc.id = ss.subcategory_id
      LEFT JOIN cohorts ct
        ON ct.category_id    = ss.category_id
       AND ct.subcategory_id = ss.subcategory_id
      GROUP BY ss.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=policy-details.csv');
    $out = fopen('php://output', 'w');
    if (!empty($export)) {
        fputcsv($out, array_keys($export[0]));
        foreach ($export as $row) {
            fputcsv($out, $row);
        }
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet">
    <title>Policy Details</title>
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

                    <!-- EDIT FORM -->
                    <?php if ($isEdit): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h4 class="mb-4">
                                    <?= !empty($errors) ? 'Please fix errors' : 'Edit Policy Detail' ?>
                                </h4>
                                <?php if ($success): ?>
                                    <div class="alert alert-success">Policy details updated successfully.</div>
                                <?php endif; ?>
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <?php foreach ($errors as $e): ?>
                                            <div><?= htmlspecialchars($e) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <form id="settingsForm" method="post" class="custom-validation">

                                    <!-- Row 1 -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Insurance *</label>
                                            <select id="categorySelect" name="category_id" class="form-control" required>
                                                <option value="">Select…</option>
                                                <?php foreach ($allCats as $c): ?>
                                                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $category_id ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($c['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Product *</label>
                                            <select id="subcategorySelect" name="subcategory_id" class="form-control"
                                                required>
                                                <option value="">Select…</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Cohorts *</label>
                                            <div>
                                                <?php foreach (['cohort1', 'cohort2', 'cohort3', 'cohort4', 'cohort5', 'cohort6'] as $n): ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" id="<?= $n ?>"
                                                            name="cohorts[]" value="<?= $n ?>" <?= in_array($n, $selectedCohorts) ? 'checked' : '' ?>>
                                                        <label class="form-check-label"
                                                            for="<?= $n ?>"><?= ucfirst($n) ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Weighted Score *</label>
                                            <input type="number" name="weighted_score" class="form-control" min="0"
                                                step="0.01" required value="<?= htmlspecialchars($weighted_score) ?>">
                                        </div>
                                    </div>

                                    <!-- Row 2 -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Sum Insured *</label>
                                            <input type="text" name="sum_insured" class="form-control" min="0" step="0.01"
                                                required value="<?= htmlspecialchars($sum_insured) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Product Type *</label>
                                            <input type="text" name="product_type" class="form-control" list="productList"
                                                required value="<?= htmlspecialchars($product_type) ?>">
                                            <datalist id="productList">
                                                <?php foreach ($productList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Adult Age (yrs) *</label>
                                            <div class="d-flex">
                                                <input type="number" id="adultFrom" name="adult_age_from"
                                                    class="form-control me-1" min="0" required placeholder="From"
                                                    value="<?= htmlspecialchars($adult_age_from) ?>">
                                                <input type="number" id="adultTo" name="adult_age_to" class="form-control"
                                                    min="0" required placeholder="To"
                                                    value="<?= htmlspecialchars($adult_age_to) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Children Age: days→yrs *</label>
                                            <div class="d-flex">
                                                <input type="number" name="child_age_from" class="form-control me-1" min="0"
                                                    max="365" required placeholder="Days"
                                                    value="<?= htmlspecialchars($child_age_from) ?>">
                                                <input type="number" name="child_age_to" class="form-control" min="0"
                                                    required placeholder="Years"
                                                    value="<?= htmlspecialchars($child_age_to) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 3 -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Plan Combination *</label>
                                            <input type="text" name="plan_combination" class="form-control" list="planList"
                                                required value="<?= htmlspecialchars($plan_combination) ?>">
                                            <datalist id="planList">
                                                <?php foreach ($planList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Room Rent *</label>
                                            <input type="text" name="room_rent" class="form-control" required
                                                value="<?= htmlspecialchars($room_rent) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Domiciliary Hosp. *</label>
                                            <input type="text" name="domiciliary_hospitalization" class="form-control"
                                                list="domiList" required
                                                value="<?= htmlspecialchars($domiciliary_hospitalization) ?>">
                                            <datalist id="domiList">
                                                <?php foreach ($domiList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Pre-hosp (m) *</label>
                                            <select name="pre_hospitalization_months" class="form-control" required>
                                                <?php for ($m = 0; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= ($pre_hospitalization_months === "{$m}") ? 'selected' : '' ?>><?= $m ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Row 4 -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Post-hosp (m) *</label>
                                            <select name="post_hospitalization_months" class="form-control" required>
                                                <?php for ($m = 0; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= ($post_hospitalization_months === "{$m}") ? 'selected' : '' ?>><?= $m ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Ayush *</label>
                                            <input type="text" name="ayush_treatment" class="form-control" list="ayushList"
                                                required value="<?= htmlspecialchars($ayush_treatment) ?>">
                                            <datalist id="ayushList">
                                                <?php foreach ($ayushList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Day Care *</label>
                                            <input type="text" name="day_care_treatment_covered" class="form-control"
                                                list="daycareList" required
                                                value="<?= htmlspecialchars($day_care_treatment_covered) ?>">
                                            <datalist id="daycareList">
                                                <?php foreach ($daycareList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Organ Donor *</label>
                                            <input type="text" name="organ_donor_expenses" class="form-control"
                                                list="organList" required
                                                value="<?= htmlspecialchars($organ_donor_expenses) ?>">
                                            <datalist id="organList">
                                                <?php foreach ($organList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>

                                    <!-- Row 5 -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Daily Allowance *</label>
                                            <textarea name="hospital_daily_allowance" class="form-control" rows="2"
                                                required><?= htmlspecialchars($hospital_daily_allowance) ?></textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Cumulative Bonus *</label>
                                            <input type="text" name="cumulative_bonus" class="form-control"
                                                list="cumBonusList" required
                                                value="<?= htmlspecialchars($cumulative_bonus) ?>">
                                            <datalist id="cumBonusList">
                                                <?php foreach ($cumBonusList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Restoration Benefit *</label>
                                            <select id="restorationBenefit" name="restoration_benefit" class="form-control"
                                                required>
                                                <option value="">Select…</option>
                                                <option value="Yes" <?= $restoration_benefit === 'Yes' ? 'selected' : '' ?>>Yes
                                                </option>
                                                <option value="No" <?= $restoration_benefit === 'No' ? 'selected' : '' ?>>No
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-3" id="restorationDetailsWrap">
                                            <label>Restoration Details *</label>
                                            <textarea name="restoration_details" class="form-control"
                                                rows="2"><?= htmlspecialchars($restoration_details) ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Row 6 -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Modern Treatment *</label>
                                            <input type="text" name="modern_treatment" class="form-control"
                                                list="modernList" required
                                                value="<?= htmlspecialchars($modern_treatment) ?>">
                                            <datalist id="modernList">
                                                <?php foreach ($modernList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Disease Sub-limits *</label>
                                            <input type="text" name="disease_sub_limits" class="form-control"
                                                list="diseaseList" required
                                                value="<?= htmlspecialchars($disease_sub_limits) ?>">
                                            <datalist id="diseaseList">
                                                <?php foreach ($diseaseList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Co-payment *</label>
                                            <div class="d-flex">
                                                <select id="coPayment" name="copayment" class="form-control me-1" required>
                                                    <option value="">Select…</option>
                                                    <option value="Yes" <?= $copayment === 'Yes' ? 'selected' : '' ?>>Yes
                                                    </option>
                                                    <option value="No" <?= $copayment === 'No' ? 'selected' : '' ?>>No</option>
                                                </select>
                                                <input id="coPaymentDetail" type="text" name="copayment_detail"
                                                    class="form-control" placeholder="If Yes, enter value"
                                                    value="<?= htmlspecialchars($copayment_detail) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Consumable Cover *</label>
                                            <div class="d-flex">
                                                <select id="consumableCover" name="consumable_cover"
                                                    class="form-control me-1" required>
                                                    <option value="">Select…</option>
                                                    <option value="Yes" <?= $consumable_cover === 'Yes' ? 'selected' : '' ?>>
                                                        Yes
                                                    </option>
                                                    <option value="No" <?= $consumable_cover === 'No' ? 'selected' : '' ?>>No
                                                    </option>
                                                </select>
                                                <input id="consumableDetail" type="text" name="consumable_detail"
                                                    class="form-control" placeholder="If Yes, enter value"
                                                    value="<?= htmlspecialchars($consumable_detail) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 7 -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Road Ambulance *</label>
                                            <input type="text" name="road_ambulance_cover" class="form-control"
                                                list="roadAmbList" required
                                                value="<?= htmlspecialchars($road_ambulance_cover) ?>">
                                            <datalist id="roadAmbList">
                                                <?php foreach ($roadAmbList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Air Ambulance *</label>
                                            <input type="text" name="air_ambulance_cover" class="form-control"
                                                list="airAmbList" required
                                                value="<?= htmlspecialchars($air_ambulance_cover) ?>">
                                            <datalist id="airAmbList">
                                                <?php foreach ($airAmbList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Worldwide Emergency *</label>
                                            <select name="worldwide_emergency" class="form-control" required>
                                                <option value="">Select…</option>
                                                <option value="Covered" <?= $worldwide_emergency === 'Covered' ? 'selected' : '' ?>>
                                                    Covered</option>
                                                <option value="Not Covered" <?= $worldwide_emergency === 'Not Covered' ? 'selected' : '' ?>>Not Covered</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Maternity Benefits *</label>
                                            <select name="maternity_benefits" class="form-control" required>
                                                <option value="">Select…</option>
                                                <option value="Covered" <?= $maternity_benefits === 'Covered' ? 'selected' : '' ?>>
                                                    Covered</option>
                                                <option value="Not Covered" <?= $maternity_benefits === 'Not Covered' ? 'selected' : '' ?>>Not Covered</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Row 8 -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Maternity Waiting *</label>
                                            <select name="maternity_waiting_period" class="form-control" required>
                                                <option value="">Select…</option>
                                                <option value="Covered" <?= $maternity_waiting_period === 'Covered' ? 'selected' : '' ?>>Covered
                                                </option>
                                                <option value="Not Covered" <?= $maternity_waiting_period === 'Not Covered' ? 'selected' : '' ?>>Not Covered</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>New Born Covered *</label>
                                            <select name="new_born_baby_covered" class="form-control" required>
                                                <option value="">Select…</option>
                                                <option value="Covered" <?= $new_born_baby_covered === 'Covered' ? 'selected' : '' ?>>Covered</option>
                                                <option value="Not Covered" <?= $new_born_baby_covered === 'Not Covered' ? 'selected' : '' ?>>Not Covered</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>IVF Cover *</label>
                                            <select name="ivf_cover" class="form-control" required>
                                                <option value="">Select…</option>
                                                <option value="Covered" <?= $ivf_cover === 'Covered' ? 'selected' : '' ?>>
                                                    Covered
                                                </option>
                                                <option value="Not Covered" <?= $ivf_cover === 'Not Covered' ? 'selected' : '' ?>>
                                                    Not Covered</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Health Checkup *</label>
                                            <input type="text" name="health_checkup" class="form-control" list="healthList"
                                                required value="<?= htmlspecialchars($health_checkup) ?>">
                                            <datalist id="healthList">
                                                <?php foreach ($healthList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>

                                    <!-- Row 9 -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Wellness *</label>
                                            <input type="text" name="wellness_benefits" class="form-control" list="wellList"
                                                required value="<?= htmlspecialchars($wellness_benefits) ?>">
                                            <datalist id="wellList">
                                                <?php foreach ($wellList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>OPD Details *</label>
                                            <select name="opd_details" class="form-control" required>
                                                <option value="">Select…</option>
                                                <option value="Available" <?= $opd_details === 'Available' ? 'selected' : '' ?>>
                                                    Available</option>
                                                <option value="Not Available" <?= $opd_details === 'Not Available' ? 'selected' : '' ?>>Not Available</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Accidental Cover *</label>
                                            <input type="text" name="accidental_cover" class="form-control" list="accidList"
                                                required value="<?= htmlspecialchars($accidental_cover) ?>">
                                            <datalist id="accidList">
                                                <?php foreach ($accidList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Critical Illness *</label>
                                            <select name="critical_illness_cover" class="form-control" required>
                                                <option value="">Select…</option>
                                                <option value="Covered" <?= $critical_illness_cover === 'Covered' ? 'selected' : '' ?>>Covered</option>
                                                <option value="Not Covered" <?= $critical_illness_cover === 'Not Covered' ? 'selected' : '' ?>>Not Covered</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Row 10 -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Dental *</label>
                                            <input type="text" name="dental_cover" class="form-control" list="dentalList"
                                                required value="<?= htmlspecialchars($dental_cover) ?>">
                                            <datalist id="dentalList">
                                                <?php foreach ($dentalList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Diabetes (Day 1) *</label>
                                            <input type="text" name="diabetes_cover_from_day1" class="form-control"
                                                list="diabetesList" required
                                                value="<?= htmlspecialchars($diabetes_cover_from_day1) ?>">
                                            <datalist id="diabetesList">
                                                <?php foreach ($diabetesList as $v): ?>
                                                    <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Portability *</label>
                                            <select name="portability_option" class="form-control" required>
                                                <option value="">Select…</option>
                                                <option value="Yes" <?= $portability_option === 'Yes' ? 'selected' : '' ?>>Yes
                                                </option>
                                                <option value="No" <?= $portability_option === 'No' ? 'selected' : '' ?>>No
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Claim % *</label>
                                            <input type="number" name="claim_settlement_ratio" class="form-control" min="0"
                                                max="100" step="0.01" required
                                                value="<?= htmlspecialchars($claim_settlement_ratio) ?>">
                                        </div>
                                    </div>

                                    <!-- Row 11 -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <label>Track Record If Insurer (yrs) *</label>
                                            <input type="number" name="insurer_vintage_years" class="form-control" min="0"
                                                step="1" required value="<?= htmlspecialchars($insurer_vintage_years) ?>">
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Update Policy</button>
                                        <a href="policy-details.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- LISTING TABLE -->
                    <!--  LISTING  --------------------------------------------------------->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">

                                    <div class="table-rep-plugin">
                                        <div class="table-responsive mb-0" data-pattern="priority-columns">
                                            <table id="datatable" class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Sl. No</th>
                                                        <th>Policy</th>
                                                        <th>Sub Policy</th>
                                                        <th>View&nbsp;Details</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $sl = 1;
                                                    foreach ($list as $row): ?>
                                                    <tr>
                                                        <td>
                                                            <?= $sl++; ?>
                                                        </td>

                                                        <!-- category name -->
                                                            <td><?= htmlspecialchars($row['category']); ?></td>
                                                            <td><?= htmlspecialchars($row['subcategory']); ?></td>

                                                            <!-- view-details button -->
                                                            <td>
                                                                <a href="view-policy-detail.php?id=<?= $row['id']; ?>">
                                                                    <button type="button" class="btn btn-info btn-sm">
                                                                        View&nbsp;Details
                                                                    </button>
                                                                </a>
                                                            </td>

                                                            <!-- action buttons (edit / delete) -->
                                                            <td>
                                                                <a href="policy-details.php?id=<?= $row['id']; ?>"
                                                                    class="btn btn-primary btn-sm" data-bs-toggle="tooltip"
                                                                    title="Edit Details">
                                                                    <i class="bx bxs-pencil"></i>
                                                                </a>

                                                                <form method="post" class="d-inline"
                                                                    onsubmit="return confirm('Delete this record?');">
                                                                    <input type="hidden" name="delete_id"
                                                                        value="<?= $row['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                                        data-bs-toggle="tooltip" title="Delete">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div><!-- /.table-responsive -->
                                    </div><!-- /.table-rep-plugin -->

                                </div><!-- /.card-body -->
                            </div><!-- /.card -->
                        </div>
                    </div><!-- /.row -->


                </div> <!-- /.container-fluid -->
            </div> <!-- /.page-content -->
        </div> <!-- /.main-content -->

        <?php
        require './shared_components/footer.php';
        require './shared_components/scripts.php';
        require './shared_components/datatable_scripts.php';
        ?>
        <script>
            /* ───────────────── dependent sub-cat dropdown ─────────────── */
            const subcatsByCat = <?= json_encode($subcatsByCat) ?>;
            const catSel = document.getElementById('categorySelect'),
                subSel = document.getElementById('subcategorySelect');

            function loadSubcats() {
                if (!catSel || !subSel) return;        // listing page ⇒ form not present
                const id = catSel.value;
                subSel.innerHTML = '<option value="">Select…</option>';
                subSel.disabled = !id;
                if (id && subcatsByCat[id])
                    subcatsByCat[id].forEach(s => subSel.add(new Option(s.name, s.id)));
    <?php if ($isEdit): ?>subSel.value = "<?= $subcategory_id ?>"; <?php endif; ?>
            }
            if (catSel) {
                catSel.addEventListener('change', loadSubcats);
                loadSubcats();
            }

            /* ───────────────── restoration details toggle ─────────────── */
            const rb = document.getElementById('restorationBenefit'),
                rd = document.getElementById('restorationDetailsWrap');
            if (rb && rd) {
                rb.addEventListener('change',
                    () => rd.style.display = rb.value === 'Yes' ? 'block' : 'none');
                rb.dispatchEvent(new Event('change'));
            }

            /* ───────────────── co-pay & consumable detail toggles ─────── */
            const cp = document.getElementById('coPayment'),
                cpd = document.getElementById('coPaymentDetail'),
                cc = document.getElementById('consumableCover'),
                ccd = document.getElementById('consumableDetail');
            if (cp && cp.addEventListener) {
                cp.addEventListener('change',
                    () => cpd.style.display = cp.value === 'Yes' ? 'block' : 'none');
                cp.dispatchEvent(new Event('change'));
            }
            if (cc && cc.addEventListener) {
                cc.addEventListener('change',
                    () => ccd.style.display = cc.value === 'Yes' ? 'block' : 'none');
                cc.dispatchEvent(new Event('change'));
            }

            /* ───────────────── adult age range validation ─────────────── */
            const af = document.getElementById('adultFrom'),
                at = document.getElementById('adultTo');
            function validateAdult() {
                const f = +af.value, t = +at.value;
                if (!isNaN(f) && !isNaN(t) && f > t) {
                    af.setCustomValidity("'From' ≤ 'To'");
                    at.setCustomValidity("'To' ≥ 'From'");
                } else {
                    af.setCustomValidity('');
                    at.setCustomValidity('');
                }
            }
            if (af && at) {
                af.addEventListener('input', validateAdult);
                at.addEventListener('input', validateAdult);
            }


            /* ───────────────── localStorage form persistence ──────────── */
            const form = document.getElementById('settingsForm');
            if (form) {
                const fields = Array.from(form.elements).filter(el => el.name);
                fields.forEach(el =>
                    el.addEventListener('input',
                        () => localStorage.setItem('policyForm_' + el.name, el.value)));

                window.addEventListener('DOMContentLoaded', () => {
                    // Check if this is a GET request to edit form (not a POST submission result)
                    const isEditGet = <?= json_encode($isEdit && $_SERVER['REQUEST_METHOD'] !== 'POST') ?>;
                    const hasSuccess = <?= json_encode(!empty($success)) ?>;

                    if (isEditGet) {
                        // This is an edit form being loaded - database values are already populated
                        // Just clear any old localStorage data and load subcategories
                        fields.forEach(el => localStorage.removeItem('policyForm_' + el.name));
                        loadSubcats();
                    } else if (!hasSuccess) {
                        // This is a form with validation errors - restore from localStorage
                        fields.forEach(el => {
                            const v = localStorage.getItem('policyForm_' + el.name);
                            if (v !== null) el.value = v;
                        });
                        loadSubcats();
                    } else {
                        // This is after successful submission - clear localStorage
                        fields.forEach(el => localStorage.removeItem('policyForm_' + el.name));
                    }
                });
            }

            if (window.location.search.includes('success=1')) {
                // Remove success parameter from URL without page reload
                const url = new URL(window.location);
                url.searchParams.delete('success');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }
        </script>

    </div> <!-- /#layout-wrapper -->
</body>

</html>