<?php
// admin/cohorts.php

require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// ─── Gather POST + defaults ─────────────────────────────────────
$errors = [];
$success = $_GET['success'] ?? '';
$catId = $_POST['category_id'] ?? '';
$subcatId = $_POST['subcategory_id'] ?? '';
$selectedCohorts = $_POST['cohorts'] ?? [];
$weightedScore = $_POST['weighted_score'] ?? '';
$sumInsured = $_POST['sum_insured'] ?? '';
$productType = trim($_POST['product_type'] ?? '');
$adultFrom = $_POST['adult_age_from'] ?? '';
$adultTo = $_POST['adult_age_to'] ?? '';
$childFrom = $_POST['child_age_from'] ?? '';
$childTo = $_POST['child_age_to'] ?? '';
$planCombo = trim($_POST['plan_combination'] ?? '');
$roomRent = trim($_POST['room_rent'] ?? '');
$domiHosp = trim($_POST['domiciliary_hospitalization'] ?? '');
$preMonths = $_POST['pre_hospitalization_months'] ?? '';
$postMonths = $_POST['post_hospitalization_months'] ?? '';
$ayush = trim($_POST['ayush_treatment'] ?? '');
$daycare = trim($_POST['day_care_treatment_covered'] ?? '');
$organDonor = trim($_POST['organ_donor_expenses'] ?? '');
$hospitalDailyAllow = trim($_POST['hospital_daily_allowance'] ?? '');
$cumulativeBonus = trim($_POST['cumulative_bonus'] ?? '');
$restorationBenefit = $_POST['restoration_benefit'] ?? '';
$restorationDetails = trim($_POST['restoration_details'] ?? '');
$modernTreatment = trim($_POST['modern_treatment'] ?? '');
$diseaseSubLimits = trim($_POST['disease_sub_limits'] ?? '');
$coPayment = $_POST['copayment'] ?? '';
$coPaymentDetail = trim($_POST['copayment_detail'] ?? '');
$consumableCover = $_POST['consumable_cover'] ?? '';
$consumableDetail = trim($_POST['consumable_detail'] ?? '');
$roadAmbulanceCover = trim($_POST['road_ambulance_cover'] ?? '');
$airAmbulanceCover = trim($_POST['air_ambulance_cover'] ?? '');
$worldwideEmergency = $_POST['worldwide_emergency'] ?? '';
$maternityBenefits = $_POST['maternity_benefits'] ?? '';
$maternityBenefitsDetail = trim($_POST['maternity_benefits_detail'] ?? '');
$maternityWaiting = $_POST['maternity_waiting_period'] ?? '';
$maternityWaitingDetail = trim($_POST['maternity_waiting_period_detail'] ?? '');
$newBornCovered = $_POST['new_born_baby_covered'] ?? '';
$ivfCover = $_POST['ivf_cover'] ?? '';
$healthCheckup = trim($_POST['health_checkup'] ?? '');
$wellnessBenefits = trim($_POST['wellness_benefits'] ?? '');
$opdDetails = $_POST['opd_details'] ?? '';
$accidentalCover = trim($_POST['accidental_cover'] ?? '');
$criticalIllness = $_POST['critical_illness_cover'] ?? '';
$dentalCover = trim($_POST['dental_cover'] ?? '');
$diabetesCover = trim($_POST['diabetes_cover_from_day1'] ?? '');
$portabilityOption = $_POST['portability_option'] ?? '';
$claimSettlementRatio = $_POST['claim_settlement_ratio'] ?? '';
$insurerVintageYears = $_POST['insurer_vintage_years'] ?? '';

// ─── New fields ─────────────────────────────────────────────
$accidentalCover2 = trim($_POST['accidental_cover2'] ?? '');
$initialWaitingPeriod = trim($_POST['initial_waiting_period'] ?? '');
$slowGrowingSpecificDisease = trim($_POST['slow_growing_specific_disease'] ?? '');
$preExistingCoverage = trim($_POST['pre_existing_coverage'] ?? '');
$diabetesCoverFromDay1New = trim($_POST['diabetes_cover_from_day1_new'] ?? '');
$inhouseClaimsTeam = trim($_POST['inhouse_claims_team'] ?? '');
$abilityToPayInEmis = trim($_POST['ability_to_pay_in_emis'] ?? '');

// ─── Load datalist source values ─────────────────────────────────
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

// ─── New datalists ─────────────────────────────────────────────
$accidentalCover2List = $pdo->query("SELECT DISTINCT accidental_cover2                FROM subcategory_settings WHERE accidental_cover2 != ''")->fetchAll(PDO::FETCH_COLUMN);
$initialWaitingPeriodList = $pdo->query("SELECT DISTINCT initial_waiting_period          FROM subcategory_settings WHERE initial_waiting_period != ''")->fetchAll(PDO::FETCH_COLUMN);
$slowGrowingSpecificDiseaseList = $pdo->query("SELECT DISTINCT slow_growing_specific_disease  FROM subcategory_settings WHERE slow_growing_specific_disease != ''")->fetchAll(PDO::FETCH_COLUMN);
$preExistingCoverageList = $pdo->query("SELECT DISTINCT pre_existing_coverage           FROM subcategory_settings WHERE pre_existing_coverage != ''")->fetchAll(PDO::FETCH_COLUMN);
$diabetesCoverFromDay1NewList = $pdo->query("SELECT DISTINCT diabetes_cover_from_day1_new    FROM subcategory_settings WHERE diabetes_cover_from_day1_new != ''")->fetchAll(PDO::FETCH_COLUMN);
$inhouseClaimsTeamList = $pdo->query("SELECT DISTINCT inhouse_claims_team              FROM subcategory_settings WHERE inhouse_claims_team != ''")->fetchAll(PDO::FETCH_COLUMN);
$abilityToPayInEmisList = $pdo->query("SELECT DISTINCT ability_to_pay_in_emis           FROM subcategory_settings WHERE ability_to_pay_in_emis != ''")->fetchAll(PDO::FETCH_COLUMN);

// ─── Handle form submit ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // ── (validation for all fields identical to prior examples) ──

  if (empty($errors)) {
    // 1) Upsert subcategory_settings
    $chk = $pdo->prepare("
          SELECT id FROM subcategory_settings
           WHERE category_id    = :cid
             AND subcategory_id = :scid
        ");
    $chk->execute([':cid' => $catId, ':scid' => $subcatId]);

    if ($chk->fetch()) {
      // UPDATE
      $stmt = $pdo->prepare("
              UPDATE subcategory_settings SET
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
                maternity_benefits_detail   = :mbd,
                maternity_waiting_period    = :mw,
                maternity_waiting_period_detail = :mwd,
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
                insurer_vintage_years       = :ivy,
                accidental_cover2           = :ac2,
                initial_waiting_period      = :iwp,
                slow_growing_specific_disease = :sgsd,
                pre_existing_coverage       = :pec,
                diabetes_cover_from_day1_new = :dc1n,
                inhouse_claims_team         = :ict,
                ability_to_pay_in_emis      = :atpie
              WHERE category_id    = :cid
                AND subcategory_id = :scid
            ");
    } else {
      // INSERT
      $stmt = $pdo->prepare("
              INSERT INTO subcategory_settings (
                category_id, subcategory_id,
                sum_insured, product_type,
                adult_age_from, adult_age_to,
                child_age_from, child_age_to,
                plan_combination, room_rent,
                domiciliary_hospitalization,
                pre_hospitalization_days,
                post_hospitalization_days,
                ayush_treatment,
                day_care_treatment_covered,
                organ_donor_expenses,
                hospital_daily_allowance,
                cumulative_bonus,
                restoration_benefit,
                restoration_details,
                modern_treatment,
                disease_sub_limits,
                copayment,
                copayment_detail,
                consumable_cover,
                consumable_detail,
                road_ambulance_cover,
                air_ambulance_cover,
                worldwide_emergency,
                maternity_benefits,
                maternity_benefits_detail,
                maternity_waiting_period,
                maternity_waiting_period_detail,
                new_born_baby_covered,
                ivf_cover,
                health_checkup,
                wellness_benefits,
                opd_details,
                accidental_cover,
                critical_illness_cover,
                dental_cover,
                diabetes_cover_from_day1,
                portability_option,
                claim_settlement_ratio,
                insurer_vintage_years,
                accidental_cover2,
                initial_waiting_period,
                slow_growing_specific_disease,
                pre_existing_coverage,
                diabetes_cover_from_day1_new,
                inhouse_claims_team,
                ability_to_pay_in_emis
              ) VALUES (
                :cid, :scid,
                :si, :pt,
                :af, :at,
                :cf, :ct,
                :pc, :rr,
                :dh, :ph,
                :po, :ay,
                :dc, :od,
                :hd, :cb,
                :rb, :rd,
                :mt, :ds,
                :cp, :cpd,
                :cc, :ccd,
                :rac, :aac,
                :we, :mb,
                :mbd, :mw,
                :mwd, :nb,
                :ivf, :hc,
                :wb, :odl,
                :ac, :ci,
                :dcv, :dc1,
                :poo, :csr,
                :ivy, :ac2,
                :iwp, :sgsd,
                :pec, :dc1n,
                :ict, :atpie
              )
            ");
    }

    // Bind & execute subcategory_settings
    $stmt->execute([
      ':cid' => $catId,
      ':scid' => $subcatId,
      ':si' => $sumInsured,
      ':pt' => $productType,
      ':af' => $adultFrom,
      ':at' => $adultTo,
      ':cf' => $childFrom,
      ':ct' => $childTo,
      ':pc' => $planCombo,
      ':rr' => $roomRent,
      ':dh' => $domiHosp,
      ':ph' => $preMonths,
      ':po' => $postMonths,
      ':ay' => $ayush,
      ':dc' => $daycare,
      ':od' => $organDonor,
      ':hd' => $hospitalDailyAllow,
      ':cb' => $cumulativeBonus,
      ':rb' => $restorationBenefit,
      ':rd' => $restorationDetails,
      ':mt' => $modernTreatment,
      ':ds' => $diseaseSubLimits,
      ':cp' => $coPayment,
      ':cpd' => $coPaymentDetail,
      ':cc' => $consumableCover,
      ':ccd' => $consumableDetail,
      ':rac' => $roadAmbulanceCover,
      ':aac' => $airAmbulanceCover,
      ':we' => $worldwideEmergency,
      ':mb' => $maternityBenefits,
      ':mbd' => $maternityBenefitsDetail,
      ':mw' => $maternityWaiting,
      ':mwd' => $maternityWaitingDetail,
      ':nb' => $newBornCovered,
      ':ivf' => $ivfCover,
      ':hc' => $healthCheckup,
      ':wb' => $wellnessBenefits,
      ':odl' => $opdDetails,
      ':ac' => $accidentalCover,
      ':ci' => $criticalIllness,
      ':dcv' => $dentalCover,
      ':dc1' => $diabetesCover,
      ':poo' => $portabilityOption,
      ':csr' => $claimSettlementRatio,
      ':ivy' => $insurerVintageYears,
      ':ac2' => $accidentalCover2,
      ':iwp' => $initialWaitingPeriod,
      ':sgsd' => $slowGrowingSpecificDisease,
      ':pec' => $preExistingCoverage,
      ':dc1n' => $diabetesCoverFromDay1New,
      ':ict' => $inhouseClaimsTeam,
      ':atpie' => $abilityToPayInEmis
    ]);

    // 2) Replace cohorts entries
    $pdo->prepare("
          DELETE FROM cohorts
           WHERE category_id    = :cid
             AND subcategory_id = :scid
        ")->execute([':cid' => $catId, ':scid' => $subcatId]);

    $insCoh = $pdo->prepare("
          INSERT INTO cohorts
            (category_id, subcategory_id, cohort_name, weighted_score)
          VALUES
            (:cid, :scid, :cname, :ws)
        ");
    foreach ($selectedCohorts as $cname) {
      $insCoh->execute([
        ':cid' => $catId,
        ':scid' => $subcatId,
        ':cname' => $cname,
        ':ws' => $weightedScore
      ]);
    }

    header("Location: cohorts.php?success=updated");
    exit;
  }
}

// ─── Fetch dropdowns ─────────────────────────────────────────────
$allCats = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$allSubcats = $pdo->query("
  SELECT s.id, c.id AS category_id, s.name AS subcategory
    FROM subcategories s
    JOIN categories c ON c.id=s.category_id
   ORDER BY c.name, s.name
")->fetchAll();
$subcatsByCat = [];
foreach ($allSubcats as $s) {
  $subcatsByCat[$s['category_id']][] = ['id' => $s['id'], 'name' => $s['subcategory']];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php require './shared_components/head.php'; ?>
  <title>Add New Policy </title>
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

          <!-- Page Title -->
          <div class="row mb-4">
            <div class="col-12">
              <h4 class="font-size-18">Add New Policy</h4>
            </div>
          </div>

          <div class="card">
            <div class="card-body">

              <!-- Alerts -->
              <?php if ($success === 'updated'): ?>
                <div class="alert alert-success">Policy Added.</div>
              <?php endif; ?>
              <?php if ($errors): ?>
                <div class="alert alert-danger">
                  <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <!-- Settings Form -->
              <form id="settingsForm" method="post" class="custom-validation">

                <!-- Row 1 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Category *</label>
                    <select id="categorySelect" name="category_id" class="form-control" required>
                      <option value="">Select…</option>
                      <?php foreach ($allCats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $catId ? 'selected' : '' ?>>
                          <?= htmlspecialchars($c['name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Subcategory *</label>
                    <select id="subcategorySelect" name="subcategory_id" class="form-control" required disabled>
                      <option value="">Select…</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Cohorts *</label>
                    <div>
                      <?php foreach (['cohort1', 'cohort2', 'cohort3', 'cohort4', 'cohort5', 'cohort6'] as $n): ?>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="checkbox" id="<?= $n ?>" name="cohorts[]" value="<?= $n ?>"
                            <?= in_array($n, $selectedCohorts) ? 'checked' : '' ?>>
                          <label class="form-check-label" for="<?= $n ?>"><?= ucfirst($n) ?></label>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Weighted Score *</label>
                    <input type="number" name="weighted_score" class="form-control" min="0" step="0.01" required
                      value="<?= htmlspecialchars($weightedScore) ?>">
                  </div>
                </div>

                <!-- Row 2 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Sum Insured *</label>
                    <input type="text" name="sum_insured" class="form-control" required
                      value="<?= htmlspecialchars($sumInsured) ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Product Type *</label>
                    <input type="text" name="product_type" class="form-control" list="productList" required
                      value="<?= htmlspecialchars($productType) ?>">
                    <datalist id="productList">
                      <?php foreach ($productList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Adult Age (yrs) *</label>
                    <div class="d-flex">
                      <input type="number" id="adultFrom" name="adult_age_from" class="form-control me-1" min="0"
                        required placeholder="From" value="<?= htmlspecialchars($adultFrom) ?>">
                      <input type="number" id="adultTo" name="adult_age_to" class="form-control" min="0" required
                        placeholder="To" value="<?= htmlspecialchars($adultTo) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Children Age: days→yrs *</label>
                    <div class="d-flex">
                      <input type="number" name="child_age_from" class="form-control me-1" min="0" max="365" required
                        placeholder="Days" value="<?= htmlspecialchars($childFrom) ?>">
                      <input type="number" name="child_age_to" class="form-control" min="0" required placeholder="Years"
                        value="<?= htmlspecialchars($childTo) ?>">
                    </div>
                  </div>
                </div>

                <!-- Row 3 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Plan Combination *</label>
                    <input type="text" name="plan_combination" class="form-control" list="planList" required
                      value="<?= htmlspecialchars($planCombo) ?>">
                    <datalist id="planList">
                      <?php foreach ($planList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Room Rent *</label>
                    <input type="text" name="room_rent" class="form-control" required
                      value="<?= htmlspecialchars($roomRent) ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Domiciliary Hosp. *</label>
                    <input type="text" name="domiciliary_hospitalization" class="form-control" list="domiList" required
                      value="<?= htmlspecialchars($domiHosp) ?>">
                    <datalist id="domiList">
                      <?php foreach ($domiList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Pre-hospitalization *</label>
                    <input type="text" name="pre_hospitalization_months" class="form-control" required
                      value="<?= htmlspecialchars($preMonths) ?>">
                  </div>
                </div>

                <!-- Row 4 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Post-hospitalization *</label>
                    <input type="text" name="post_hospitalization_months" class="form-control" required
                      value="<?= htmlspecialchars($postMonths) ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Ayush Treatment *</label>
                    <input type="text" name="ayush_treatment" class="form-control" list="ayushList" required
                      value="<?= htmlspecialchars($ayush) ?>">
                    <datalist id="ayushList">
                      <?php foreach ($ayushList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Day Care Covered *</label>
                    <input type="text" name="day_care_treatment_covered" class="form-control" list="daycareList"
                      required value="<?= htmlspecialchars($daycare) ?>">
                    <datalist id="daycareList">
                      <?php foreach ($daycareList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Organ Donor Expenses *</label>
                    <input type="text" name="organ_donor_expenses" class="form-control" list="organList" required
                      value="<?= htmlspecialchars($organDonor) ?>">
                    <datalist id="organList">
                      <?php foreach ($organList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                </div>

                <!-- Row 5 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Hospital Daily Allowance *</label>
                    <textarea name="hospital_daily_allowance" class="form-control" rows="2"
                      required><?= htmlspecialchars($hospitalDailyAllow) ?></textarea>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Cumulative Bonus *</label>
                    <input type="text" name="cumulative_bonus" class="form-control" list="cumBonusList" required
                      value="<?= htmlspecialchars($cumulativeBonus) ?>">
                    <datalist id="cumBonusList">
                      <?php foreach ($cumBonusList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Restoration Benefit *</label>
                    <select id="restorationBenefit" name="restoration_benefit" class="form-control" required>
                      <option value="">Select…</option>
                      <option value="Yes" <?= $restorationBenefit === 'Yes' ? 'selected' : '' ?>>Yes</option>
                      <option value="No" <?= $restorationBenefit === 'No' ? 'selected' : '' ?>>No</option>
                    </select>
                  </div>
                  <div class="col-md-3" id="restorationDetailsWrap">
                    <label class="form-label">Restoration Details *</label>
                    <textarea name="restoration_details" class="form-control"
                      rows="2"><?= htmlspecialchars($restorationDetails) ?></textarea>
                  </div>
                </div>

                <!-- Row 6 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Modern Treatment *</label>
                    <input type="text" name="modern_treatment" class="form-control" list="modernList" required
                      value="<?= htmlspecialchars($modernTreatment) ?>">
                    <datalist id="modernList">
                      <?php foreach ($modernList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Disease Sub-limits *</label>
                    <input type="text" name="disease_sub_limits" class="form-control" list="diseaseList" required
                      value="<?= htmlspecialchars($diseaseSubLimits) ?>">
                    <datalist id="diseaseList">
                      <?php foreach ($diseaseList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Co-payment *</label>
                    <div class="d-flex">
                      <select id="coPayment" name="copayment" class="form-control me-1" required>
                        <option value="">Select…</option>
                        <option value="Yes" <?= $coPayment === 'Yes' ? 'selected' : '' ?>>Yes</option>
                        <option value="No" <?= $coPayment === 'No' ? 'selected' : '' ?>>No</option>
                      </select>
                      <input id="coPaymentDetail" type="text" name="copayment_detail" class="form-control"
                        placeholder="If Yes, enter value" value="<?= htmlspecialchars($coPaymentDetail) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Consumable Cover *</label>
                    <div class="d-flex">
                      <select id="consumableCover" name="consumable_cover" class="form-control me-1" required>
                        <option value="">Select…</option>
                        <option value="Yes" <?= $consumableCover === 'Yes' ? 'selected' : '' ?>>Yes</option>
                        <option value="No" <?= $consumableCover === 'No' ? 'selected' : '' ?>>No</option>
                      </select>
                      <input id="consumableDetail" type="text" name="consumable_detail" class="form-control"
                        placeholder="If Yes, enter value" value="<?= htmlspecialchars($consumableDetail) ?>">
                    </div>
                  </div>
                </div>

                <!-- Row 7 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Road Ambulance Cover *</label>
                    <input type="text" name="road_ambulance_cover" class="form-control" list="roadAmbList" required
                      value="<?= htmlspecialchars($roadAmbulanceCover) ?>">
                    <datalist id="roadAmbList">
                      <?php foreach ($roadAmbList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Air Ambulance Cover *</label>
                    <input type="text" name="air_ambulance_cover" class="form-control" list="airAmbList" required
                      value="<?= htmlspecialchars($airAmbulanceCover) ?>">
                    <datalist id="airAmbList">
                      <?php foreach ($airAmbList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Worldwide Emergency *</label>
                    <select name="worldwide_emergency" class="form-control" required>
                      <option value="">Select…</option>
                      <option value="Covered" <?= $worldwideEmergency === 'Covered' ? 'selected' : '' ?>>Covered</option>
                      <option value="Not Covered" <?= $worldwideEmergency === 'Not Covered' ? 'selected' : '' ?>>Not Covered
                      </option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Maternity Benefits *</label>
                    <div class="d-flex">
                      <select id="maternityBenefits" name="maternity_benefits" class="form-control me-1" required>
                        <option value="">Select…</option>
                        <option value="Covered" <?= $maternityBenefits === 'Covered' ? 'selected' : '' ?>>Covered</option>
                        <option value="Not Covered" <?= $maternityBenefits === 'Not Covered' ? 'selected' : '' ?>>Not Covered</option>
                      </select>
                      <input id="maternityBenefitsDetail" type="text" name="maternity_benefits_detail" class="form-control"
                        placeholder="If Covered, enter details" value="<?= htmlspecialchars($maternityBenefitsDetail) ?>">
                    </div>
                  </div>
                </div>

                <!-- Row 8 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Maternity Waiting Period *</label>
                    <div class="d-flex">
                      <select id="maternityWaiting" name="maternity_waiting_period" class="form-control me-1" required>
                        <option value="">Select…</option>
                        <option value="Covered" <?= $maternityWaiting === 'Covered' ? 'selected' : '' ?>>Covered</option>
                        <option value="Not Covered" <?= $maternityWaiting === 'Not Covered' ? 'selected' : '' ?>>Not Covered</option>
                      </select>
                      <input id="maternityWaitingDetail" type="text" name="maternity_waiting_period_detail" class="form-control"
                        placeholder="If Covered, enter details" value="<?= htmlspecialchars($maternityWaitingDetail) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">New Born Baby Covered *</label>
                    <select name="new_born_baby_covered" class="form-control" required>
                      <option value="">Select…</option>
                      <option value="Covered" <?= $newBornCovered === 'Covered' ? 'selected' : '' ?>>Covered</option>
                      <option value="Not Covered" <?= $newBornCovered === 'Not Covered' ? 'selected' : '' ?>>Not Covered
                      </option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">IVF Cover *</label>
                    <select name="ivf_cover" class="form-control" required>
                      <option value="">Select…</option>
                      <option value="Covered" <?= $ivfCover === 'Covered' ? 'selected' : '' ?>>Covered</option>
                      <option value="Not Covered" <?= $ivfCover === 'Not Covered' ? 'selected' : '' ?>>Not Covered</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Health Checkup *</label>
                    <input type="text" name="health_checkup" class="form-control" list="healthList" required
                      value="<?= htmlspecialchars($healthCheckup) ?>">
                    <datalist id="healthList">
                      <?php foreach ($healthList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                </div>

                <!-- Row 9 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Wellness Benefits *</label>
                    <input type="text" name="wellness_benefits" class="form-control" list="wellList" required
                      value="<?= htmlspecialchars($wellnessBenefits) ?>">
                    <datalist id="wellList">
                      <?php foreach ($wellList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">OPD Details *</label>
                    <select name="opd_details" class="form-control" required>
                      <option value="">Select…</option>
                      <option value="Available" <?= $opdDetails === 'Available' ? 'selected' : '' ?>>Available</option>
                      <option value="Not Available" <?= $opdDetails === 'Not Available' ? 'selected' : '' ?>>Not Available
                      </option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Accidental Cover *</label>
                    <input type="text" name="accidental_cover" class="form-control" list="accidList" required
                      value="<?= htmlspecialchars($accidentalCover) ?>">
                    <datalist id="accidList">
                      <?php foreach ($accidList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Critical Illness Cover *</label>
                    <select name="critical_illness_cover" class="form-control" required>
                      <option value="">Select…</option>
                      <option value="Covered" <?= $criticalIllness === 'Covered' ? 'selected' : '' ?>>Covered</option>
                      <option value="Not Covered" <?= $criticalIllness === 'Not Covered' ? 'selected' : '' ?>>Not Covered
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Row 10 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Dental Cover *</label>
                    <input type="text" name="dental_cover" class="form-control" list="dentalList" required
                      value="<?= htmlspecialchars($dentalCover) ?>">
                    <datalist id="dentalList">
                      <?php foreach ($dentalList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Diabetes Cover (Day 1) *</label>
                    <input type="text" name="diabetes_cover_from_day1" class="form-control" list="diabetesList" required
                      value="<?= htmlspecialchars($diabetesCover) ?>">
                    <datalist id="diabetesList">
                      <?php foreach ($diabetesList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Portability Option *</label>
                    <select name="portability_option" class="form-control" required>
                      <option value="">Select…</option>
                      <option value="Yes" <?= $portabilityOption === 'Yes' ? 'selected' : '' ?>>Yes</option>
                      <option value="No" <?= $portabilityOption === 'No' ? 'selected' : '' ?>>No</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Claim Sett. Ratio (%) *</label>
                    <input type="number" name="claim_settlement_ratio" class="form-control" min="0" max="100"
                      step="0.01" required value="<?= htmlspecialchars($claimSettlementRatio) ?>">
                  </div>
                </div>

                <!-- Row 11 -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Track record if Insurer (yrs) *</label>
                    <input type="number" name="insurer_vintage_years" class="form-control" min="0" step="1" required
                      value="<?= htmlspecialchars($insurerVintageYears) ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Accidental Cover2 *</label>
                    <input type="text" name="accidental_cover2" class="form-control" list="accidentalCover2List" required
                      value="<?= htmlspecialchars($accidentalCover2) ?>">
                    <datalist id="accidentalCover2List">
                      <?php foreach ($accidentalCover2List as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Initial Waiting Period *</label>
                    <input type="text" name="initial_waiting_period" class="form-control" list="initialWaitingPeriodList" required
                      value="<?= htmlspecialchars($initialWaitingPeriod) ?>">
                    <datalist id="initialWaitingPeriodList">
                      <?php foreach ($initialWaitingPeriodList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Slow Growing/ Specific Disease *</label>
                    <input type="text" name="slow_growing_specific_disease" class="form-control" list="slowGrowingSpecificDiseaseList" required
                      value="<?= htmlspecialchars($slowGrowingSpecificDisease) ?>">
                    <datalist id="slowGrowingSpecificDiseaseList">
                      <?php foreach ($slowGrowingSpecificDiseaseList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                </div>

                <!-- Row 12 -->
                <div class="row mb-4">
                  <div class="col-md-3">
                    <label class="form-label">Pre-existing Coverage *</label>
                    <input type="text" name="pre_existing_coverage" class="form-control" list="preExistingCoverageList" required
                      value="<?= htmlspecialchars($preExistingCoverage) ?>">
                    <datalist id="preExistingCoverageList">
                      <?php foreach ($preExistingCoverageList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Diabetes Cover from Day 1 *</label>
                    <input type="text" name="diabetes_cover_from_day1_new" class="form-control" list="diabetesCoverFromDay1NewList" required
                      value="<?= htmlspecialchars($diabetesCoverFromDay1New) ?>">
                    <datalist id="diabetesCoverFromDay1NewList">
                      <?php foreach ($diabetesCoverFromDay1NewList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Inhouse Claims Team *</label>
                    <input type="text" name="inhouse_claims_team" class="form-control" list="inhouseClaimsTeamList" required
                      value="<?= htmlspecialchars($inhouseClaimsTeam) ?>">
                    <datalist id="inhouseClaimsTeamList">
                      <?php foreach ($inhouseClaimsTeamList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Ability to pay in EMIs *</label>
                    <input type="text" name="ability_to_pay_in_emis" class="form-control" list="abilityToPayInEmisList" required
                      value="<?= htmlspecialchars($abilityToPayInEmis) ?>">
                    <datalist id="abilityToPayInEmisList">
                      <?php foreach ($abilityToPayInEmisList as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                    </datalist>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary waves-effect waves-light">
                  Add Policy
                </button>
                <button type="button" id="clearForm" class="btn btn-secondary waves-effect waves-light ms-2">
                  Clear Form
                </button>
              </form>

            </div>
          </div>

        </div>
      </div>
    </div>

    <?php
    require './shared_components/footer.php';
    require './shared_components/scripts.php';
    ?>
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
    <script>
      // Dependent Subcategory dropdown
      const subcatsByCat = <?= json_encode($subcatsByCat) ?>;
      const catSel = document.getElementById('categorySelect');
      const subSel = document.getElementById('subcategorySelect');
      catSel.addEventListener('change', () => {
        const id = catSel.value;
        subSel.innerHTML = '<option value="">Select…</option>';
        subSel.disabled = !id;
        if (id && subcatsByCat[id]) {
          subcatsByCat[id].forEach(s => subSel.add(new Option(s.name, s.id)));
        }
      });
      if (catSel.value) catSel.dispatchEvent(new Event('change'));

      // Show/hide Restoration Details
      const rb = document.getElementById('restorationBenefit'),
        rdWrap = document.getElementById('restorationDetailsWrap');
      rb.addEventListener('change', () => {
        rdWrap.style.display = rb.value === 'Yes' ? 'block' : 'none';
      });
      rb.dispatchEvent(new Event('change'));

      // Show/hide Co-pay & Consumable detail fields
      const cp = document.getElementById('coPayment'),
        cpd = document.getElementById('coPaymentDetail'),
        cc = document.getElementById('consumableCover'),
        ccd = document.getElementById('consumableDetail');
      cp.addEventListener('change', () => cpd.style.display = cp.value === 'Yes' ? 'block' : 'none');
      cc.addEventListener('change', () => ccd.style.display = cc.value === 'Yes' ? 'block' : 'none');
      cp.dispatchEvent(new Event('change'));
      cc.dispatchEvent(new Event('change'));

      // Show/hide Maternity Benefits and Waiting Period detail fields
      const mb = document.getElementById('maternityBenefits'),
        mbd = document.getElementById('maternityBenefitsDetail'),
        mw = document.getElementById('maternityWaiting'),
        mwd = document.getElementById('maternityWaitingDetail');
      mb.addEventListener('change', () => mbd.style.display = mb.value === 'Covered' ? 'block' : 'none');
      mw.addEventListener('change', () => mwd.style.display = mw.value === 'Covered' ? 'block' : 'none');
      mb.dispatchEvent(new Event('change'));
      mw.dispatchEvent(new Event('change'));

      // Client-side validation: Adult From ≤ To
      const af = document.getElementById('adultFrom'), at = document.getElementById('adultTo');
      function validateAdult() {
        const f = parseInt(af.value, 10), t = parseInt(at.value, 10);
        if (f > t) { af.setCustomValidity("'From' must be ≤ 'To'"); at.setCustomValidity("'To' must be ≥ 'From'"); }
        else { af.setCustomValidity(''); at.setCustomValidity(''); }
      }
      af.addEventListener('input', validateAdult);
      at.addEventListener('input', validateAdult);

      // Persist form data in localStorage
      const form = document.getElementById('settingsForm');
      const fields = Array.from(form.elements).filter(el => el.name);
      fields.forEach(el => el.addEventListener('input', () => {
        localStorage.setItem('cohortForm_' + el.name, el.value);
      }));
      window.addEventListener('DOMContentLoaded', () => {
        if (!'<?= $success ?>') {
          fields.forEach(el => {
            const v = localStorage.getItem('cohortForm_' + el.name);
            if (v !== null) el.value = v;
          });
          if (catSel.value) catSel.dispatchEvent(new Event('change'));
        } else {
          fields.forEach(el => localStorage.removeItem('cohortForm_' + el.name));
        }
      });
       document.getElementById('clearForm').addEventListener('click', () => {
        const form = document.getElementById('settingsForm');
        form.reset();

        // Re‐fire dependent UI toggles:
        catSel.dispatchEvent(new Event('change'));
        rb.dispatchEvent(new Event('change'));
        cp.dispatchEvent(new Event('change'));
        cc.dispatchEvent(new Event('change'));
        mb.dispatchEvent(new Event('change'));
        mw.dispatchEvent(new Event('change'));
      });
    </script>
  </div>
</body>

</html>