<?php
require_once 'marketplace_common.php';

marketplace_require_role('farmer');

$db = marketplace_db();
$farmer_id = (int) $_SESSION['user_id'];

$errors = array();
$crop_name = '';
$category = '';
$price = '';
$unit = 'kg';
$quantity = '';
$selected_subcounty = '';
$description = '';
$delivery = 0;

$kiambu_subcounties = array(
    'Gatundu North', 'Gatundu South', 'Juja', 'Kabete', 'Kiambaa', 'Kiambu',
    'Kikuyu', 'Limuru', 'Lari', 'Ruiru', 'Thika East', 'Thika West', 'Other (Kiambu)'
);

if (isset($_POST['post_listing'])) {
    $crop_name = trim((string) ($_POST['crop_name'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? ''));
    $price = (float) ($_POST['price'] ?? 0);
    $unit = trim((string) ($_POST['unit'] ?? 'kg'));
    $quantity = (float) ($_POST['quantity'] ?? 0);
    $selected_subcounty = trim((string) ($_POST['subcounty'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $delivery = isset($_POST['delivery_available']) ? 1 : 0;

    if ($crop_name === '') {
        $errors[] = 'Crop name is required.';
    }
    if ($category === '') {
        $errors[] = 'Please select a category.';
    }
    if ($price <= 0) {
        $errors[] = 'Please enter a valid farmer price.';
    }
    if ($quantity <= 0) {
        $errors[] = 'Please enter a valid quantity.';
    }
    if ($selected_subcounty === '') {
        $errors[] = 'Please select your subcounty.';
    }

    if (count($errors) === 0) {
        $stmt = mysqli_prepare(
            $db,
            "INSERT INTO listings (
                farmer_id, crop_name, category, price, approved_price, unit, quantity,
                subcounty, description, delivery_available, status, approval_status
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 'pending')"
        );

        if ($stmt) {
            $approved_price = $price;
            mysqli_stmt_bind_param(
                $stmt,
                "issddsdssi",
                $farmer_id,
                $crop_name,
                $category,
                $price,
                $approved_price,
                $unit,
                $quantity,
                $selected_subcounty,
                $description,
                $delivery
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: dashboard-farmer.php?posted=1');
                exit();
            }

            $errors[] = 'Database error: ' . mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = 'Prepare failed: ' . mysqli_error($db);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmerLink - Post Produce</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body {
      background: linear-gradient(rgba(255,255,255,0.92), rgba(255,255,255,0.84)), url('photos/bg2.jpeg') center/cover fixed !important;
      color: #333;s
    }
    .helper-copy {
      max-width: 760px;
      margin: 0 auto 1.25rem;
      color: #5f6b63;
    }
    .section-title-center {
      text-align: center;
      margin-bottom: 1rem;
}
  </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="main">
  <div class="fl-section">
        <h2 class="section-title-center">Post Your Produce</h2>
    <?php if (count($errors) > 0) : ?>
      <div class="error">
        <?php foreach ($errors as $error) : ?>
          <p><?php echo marketplace_h($error); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="post-listing.php" class="form-card">
      <div class="input-group">
        <label>Crop Name *</label>
        <input type="text" name="crop_name" placeholder="e.g. Fresh Tomatoes" value="<?php echo marketplace_h($crop_name); ?>">
      </div>

      <div class="input-group">
        <label>Category *</label>
        <select name="category">
          <option value="">-- Select Category --</option>
          <?php foreach (array('Vegetables', 'Fruits', 'Grains', 'Leafy Greens', 'Root Veg', 'Legumes') as $cat) : ?>
            <option value="<?php echo marketplace_h($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
              <?php echo marketplace_h($cat); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row">
        <div class="input-group">
          <label>Farmer Price (KSh) *</label>
          <input type="number" name="price" min="1" step="0.01" placeholder="e.g. 80" value="<?php echo marketplace_h($price); ?>">
        </div>
        <div class="input-group">
          <label>Unit *</label>
          <select name="unit">
            <?php foreach (array('kg', 'bunch', 'piece', 'crate', 'bag') as $unit_option) : ?>
              <option value="<?php echo marketplace_h($unit_option); ?>" <?php echo $unit === $unit_option ? 'selected' : ''; ?>>
                Per <?php echo marketplace_h($unit_option); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="input-group">
        <label>Available Quantity *</label>
        <input type="number" name="quantity" min="1" step="0.01" placeholder="e.g. 200" value="<?php echo marketplace_h($quantity); ?>">
      </div>

      <div class="input-group">
        <label>Subcounty (Kiambu) *</label>
        <select name="subcounty">
          <option value="">-- Select Your Kiambu Subcounty --</option>
          <?php foreach ($kiambu_subcounties as $subcounty_option) : ?>
            <option value="<?php echo marketplace_h($subcounty_option); ?>" <?php echo $selected_subcounty === $subcounty_option ? 'selected' : ''; ?>>
              <?php echo marketplace_h($subcounty_option); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="input-group">
        <label>Description (optional)</label>
        <textarea name="description" rows="3" placeholder="e.g. Grade A tomatoes harvested this morning."><?php echo marketplace_h($description); ?></textarea>
      </div>

      <div class="input-group">
        <button type="submit" name="post_listing" class="btn-fl">Submit for Review</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
