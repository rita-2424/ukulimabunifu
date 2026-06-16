<?php
require_once 'marketplace_common.php';

$db = marketplace_db();

$kiambu_areas = array(
    'Gatundu North', 'Gatundu South', 'Juja', 'Kabete', 'Kiambaa', 'Kiambu',
    'Kikuyu', 'Limuru', 'Lari', 'Ruiru', 'Thika East', 'Thika West', 'Other (Kiambu)'
);

$region = $_GET['region'] ?? '';
$where_clauses = array("l.status = 'active'", "l.approval_status = 'approved'");
$where_types = '';
$where_params = array();

if (!empty($_GET['search'])) {
    $where_clauses[] = "l.crop_name LIKE ?";
    $where_types .= 's';
    $where_params[] = '%' . trim((string) $_GET['search']) . '%';
}

if ($region === 'Kiambu') {
    $placeholders = implode(',', array_fill(0, count($kiambu_areas), '?'));
    $where_clauses[] = "l.subcounty IN ($placeholders)";
    $where_types .= str_repeat('s', count($kiambu_areas));
    $where_params = array_merge($where_params, $kiambu_areas);
}

if (!empty($_GET['county'])) {
    $where_clauses[] = "l.subcounty = ?";
    $where_types .= 's';
    $where_params[] = trim((string) $_GET['county']);
}

if (!empty($_GET['category'])) {
    $where_clauses[] = "l.category = ?";
    $where_types .= 's';
    $where_params[] = trim((string) $_GET['category']);
}

$where = 'WHERE ' . implode(' AND ', $where_clauses);

$listings_result = marketplace_query_result(
    $db,
    "SELECT l.listing_id, l.crop_name, l.category, l.price, l.approved_price, l.unit, l.quantity, l.subcounty, l.description
     FROM listings l
     $where
     ORDER BY l.created_at DESC",
    $where_types,
    $where_params
);

$counties_result = mysqli_query(
    $db,
    "SELECT DISTINCT subcounty
     FROM listings
     WHERE status = 'active' AND approval_status = 'approved'
     ORDER BY subcounty"
);
$categories_result = mysqli_query(
    $db,
    "SELECT DISTINCT category
     FROM listings
     WHERE status = 'active' AND approval_status = 'approved'
     ORDER BY category"
);

$all_counties = array();
if ($counties_result) {
    while ($row = mysqli_fetch_assoc($counties_result)) {
        $all_counties[] = $row['subcounty'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmerLink - Browse Produce</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="listing.css">
</head>
<body style="background: linear-gradient(rgba(255,255,255,0.74), rgba(255,255,255,0.68)), url('photos/bg.jpeg') center/cover fixed !important; color: #333;">

<?php include 'nav.php'; ?>

<div class="page-hero-fl">
  <h2>Browse Produce</h2>
  <p>Browse for whatever you'de like</p>
</div>

<div class="filter-bar">
  <form method="get" action="browse.php">
    <input type="text" name="search" placeholder="Search produce e.g. tomatoes" value="<?php echo marketplace_h($_GET['search'] ?? ''); ?>">

    <select name="region" id="region-select">
      <option value="">All areas</option>
      <option value="Kiambu" <?php echo (($_GET['region'] ?? '') === 'Kiambu') ? 'selected' : ''; ?>>Kiambu subcounties</option>
    </select>

    <select name="county" id="county-select">
      <option value="">All subcounties</option>
      <?php foreach ($all_counties as $county_option) : ?>
        <option value="<?php echo marketplace_h($county_option); ?>" <?php echo (($_GET['county'] ?? '') === $county_option) ? 'selected' : ''; ?>>
          <?php echo marketplace_h($county_option); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <select name="category">
      <option value="">All categories</option>
      <?php if ($categories_result) : ?>
        <?php while ($row = mysqli_fetch_assoc($categories_result)) : ?>
          <option value="<?php echo marketplace_h($row['category']); ?>" <?php echo (($_GET['category'] ?? '') === $row['category']) ? 'selected' : ''; ?>>
            <?php echo marketplace_h($row['category']); ?>
          </option>
        <?php endwhile; ?>
      <?php endif; ?>
    </select>

    <button type="submit" class="btn-fl">Search</button>
    <a href="browse.php" class="btn-fl-small">Clear</a>
  </form>
</div>

<div class="main">
  <div class="fl-section">
    <?php if (!$listings_result || mysqli_num_rows($listings_result) === 0) : ?>
      <p class="no-results">No produce matches your search right now.</p>
    <?php else : ?>
      <div class="listings-grid">
        <?php while ($row = mysqli_fetch_assoc($listings_result)) : ?>
          <div class="listing-card">
            <div class="listing-card-body">
              <span class="fl-tag"><?php echo marketplace_h($row['category']); ?></span>
              <h3><?php echo marketplace_h($row['crop_name']); ?></h3>
              <p class="listing-meta">
                Source: <?php echo marketplace_h($row['subcounty']); ?>
                <br>
                Available: <?php echo marketplace_quantity($row['quantity']); ?> <?php echo marketplace_h($row['unit']); ?>
              </p>
              <p class="listing-price">
                KSh <?php echo marketplace_money(marketplace_public_price($row)); ?> / <?php echo marketplace_h($row['unit']); ?>
              </p>
              <?php if (!empty($row['description'])) : ?>
                <p class="listing-desc"><?php echo marketplace_h($row['description']); ?></p>
              <?php endif; ?>
            </div>
            <div class="listing-card-footer">
              <span class="farmer-name">Available Listings</span>
              <a href="listing.php?id=<?php echo (int) $row['listing_id']; ?>" class="btn-fl">View and Order</a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  const allCounties = <?php echo json_encode($all_counties); ?>;
  const kiambuAreas = <?php echo json_encode($kiambu_areas); ?>;
  const initialCounty = <?php echo json_encode($_GET['county'] ?? ''); ?>;

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, character => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }[character]));
  }

  function createOptions(items) {
    return items.map(item => `<option value="${escapeHtml(item)}">${escapeHtml(item)}</option>`).join('');
  }

  function updateCountyOptions() {
    const regionSelect = document.getElementById('region-select');
    const countySelect = document.getElementById('county-select');
    const region = regionSelect ? regionSelect.value : '';
    let counties = allCounties.slice();

    if (region === 'Kiambu') {
      counties = allCounties.filter(county => kiambuAreas.includes(county));
    }

    countySelect.innerHTML = '<option value="">All subcounties</option>' + createOptions(counties);
    countySelect.value = counties.includes(initialCounty) ? initialCounty : '';
  }

  document.addEventListener('DOMContentLoaded', function() {
    updateCountyOptions();
    const regionSelect = document.getElementById('region-select');
    if (regionSelect) {
      regionSelect.addEventListener('change', updateCountyOptions);
    }
  });
</script>

</body>
</html>
