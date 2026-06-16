<?php
function admin_sidebar_links()
{
    return array(
        'overview' => array(
            'label' => 'Overview',
            'href' => 'admin-dashboard.php',
            'caption' => 'Summary and shortcuts'
        ),
        'listings' => array(
            'label' => 'Listing Reviews',
            'href' => 'admin-listings.php',
            'caption' => 'Approve and price produce'
        ),
        'orders' => array(
            'label' => 'Orders & Payouts',
            'href' => 'admin-orders.php',
            'caption' => 'Payments, routing, disbursement'
        ),
        'catalog' => array(
            'label' => 'Approved Catalog',
            'href' => 'admin-catalog.php',
            'caption' => 'Buyer-visible produce'
        ),
        'stock' => array(
            'label' => 'Stock Updates',
            'href' => 'admin-stock.php',
            'caption' => 'Farmer inventory feedback'
        ),
        'transport' => array(
            'label' => 'Transport',
            'href' => 'admin-transport.php',
            'caption' => 'Delivery options and routes'
        )
    );
}

function admin_sidebar_badge($page_key, $summary)
{
    if ($page_key === 'listings' && isset($summary['pending_listings'])) {
        return number_format((float) $summary['pending_listings']);
    }

    if ($page_key === 'catalog' && isset($summary['approved_listings'])) {
        return number_format((float) $summary['approved_listings']);
    }

    return '';
}

function admin_page_start($title, $active_page, $description, $summary = array())
{
    $links = admin_sidebar_links();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo marketplace_h($title); ?></title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="admin-dashboard.css">
</head>
<body class="admin-page-body">

<?php include 'nav.php'; ?>

<div class="admin-shell" data-admin-shell>
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-card admin-sidebar-intro">
      <span class="admin-kicker">Admin Workspace</span>
      <h1><?php echo marketplace_h($title); ?></h1>
      <p><?php echo marketplace_h($description); ?></p>
    </div>

    <nav class="admin-sidebar-card admin-sidebar-nav">
      <?php foreach ($links as $key => $link) : ?>
        <?php $is_active = $key === $active_page; ?>
        <?php $badge = admin_sidebar_badge($key, $summary); ?>
        <a href="<?php echo marketplace_h($link['href']); ?>" class="admin-sidebar-link<?php echo $is_active ? ' is-active' : ''; ?>">
          <span>
            <strong><?php echo marketplace_h($link['label']); ?></strong>
            <small><?php echo marketplace_h($link['caption']); ?></small>
          </span>
          <?php if ($badge !== '') : ?>
            <span class="admin-link-badge"><?php echo marketplace_h($badge); ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="admin-sidebar-card admin-sidebar-stats">
      <h3>Quick Totals</h3>
      <div class="admin-sidebar-stat">
        <span>Buyer payments</span>
        <strong>KSh <?php echo marketplace_money($summary['buyer_payments'] ?? 0); ?></strong>
      </div>
      <div class="admin-sidebar-stat">
        <span>Admin revenue</span>
        <strong>KSh <?php echo marketplace_money($summary['admin_revenue'] ?? 0); ?></strong>
      </div>
      <div class="admin-sidebar-stat">
        <span>Farmer payouts pending</span>
        <strong>KSh <?php echo marketplace_money($summary['pending_farmer_payouts'] ?? 0); ?></strong>
      </div>
      <div class="admin-sidebar-stat">
        <span>Transport payouts pending</span>
        <strong>KSh <?php echo marketplace_money($summary['pending_transport_payouts'] ?? 0); ?></strong>
      </div>
    </div>
  </aside>

  <main class="admin-content">
    <div class="admin-page-actions">
      <button
        type="button"
        class="admin-sidebar-toggle"
        data-admin-sidebar-toggle
        aria-controls="adminSidebar"
        aria-expanded="true"
      >

      </button>
    </div>
    <div class="admin-page-head">
      <span class="admin-kicker">FarmerLink Control Center</span>
      <h2 class="page-title admin-page-title"><?php echo marketplace_h($title); ?></h2>
      <p class="muted-note admin-page-copy"><?php echo marketplace_h($description); ?></p>
    </div>
<?php
}

function admin_page_end()
{
    ?>
  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var shell = document.querySelector('[data-admin-shell]');
  var sidebar = document.getElementById('adminSidebar');
  var toggleButtons = document.querySelectorAll('[data-admin-sidebar-toggle]');
  var storageKey = 'adminSidebarCollapsed';

  if (!shell || !sidebar || !toggleButtons.length) {
    return;
  }

  function setCollapsed(collapsed) {
    shell.classList.toggle('sidebar-collapsed', collapsed);
    sidebar.setAttribute('aria-hidden', collapsed ? 'true' : 'false');

    toggleButtons.forEach(function (button) {
      button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
      button.textContent = collapsed ? 'Show Menu' : 'Hide Menu';
    });

    try {
      window.localStorage.setItem(storageKey, collapsed ? '1' : '0');
    } catch (error) {
    }
  }

  var collapsed = false;

  try {
    collapsed = window.localStorage.getItem(storageKey) === '1';
  } catch (error) {
    collapsed = false;
  }

  setCollapsed(collapsed);

  toggleButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      setCollapsed(!shell.classList.contains('sidebar-collapsed'));
    });
  });
});
</script>

</body>
</html>
<?php
}
?>
