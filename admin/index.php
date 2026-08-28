<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if (!admin_is_logged_in()):
    $error = $_SESSION['login_error'] ?? null;
    unset($_SESSION['login_error']);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — TARUDEREO</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-login-body">
  <form method="post" class="admin-login-form">
    <h1>TARUDEREO Admin</h1>
    <?php if ($error): ?><p class="admin-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required autofocus>
    <input type="hidden" name="admin_login" value="1">
    <button type="submit" class="btn btn-primary">Log In</button>
  </form>
</body>
</html>
<?php
    exit;
endif;

require_once __DIR__ . '/functions.php';

$contentPath = dirname(__DIR__) . '/content.json';
$json = file_get_contents($contentPath);
$content = $json !== false ? json_decode($json, true) : null;
if (!is_array($content) || !isset($content['site'], $content['pages'])) {
    http_response_code(500);
    die('content.json failed to load or parse — fix it manually (or restore it from git) before using this page.');
}

$successMessage = isset($_GET['saved']) ? 'Changes saved.' : null;
$errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
$cacheBust = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Content Admin — TARUDEREO</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

<header class="admin-header">
  <h1>TARUDEREO Content Admin</h1>
  <a href="?logout=1" class="btn btn-outline btn-sm" style="color:#fff; border-color:rgba(255,255,255,0.5);">Log Out</a>
</header>

<?php if ($successMessage): ?><div class="admin-flash admin-flash-success"><?= htmlspecialchars($successMessage) ?></div><?php endif; ?>
<?php if ($errorMessage): ?><div class="admin-flash admin-flash-error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>

<form method="post" action="save.php" enctype="multipart/form-data" class="admin-form">
  <?= admin_csrf_field() ?>

  <section class="admin-section">
    <h2>Images</h2>
    <div class="field">
      <label for="logo_upload">Logo</label>
      <img src="../img/logo.png?<?= $cacheBust ?>" alt="Current logo" class="admin-preview">
      <input type="file" id="logo_upload" name="logo_upload" accept="image/png,image/jpeg,image/webp">
    </div>
    <div class="field">
      <label for="hero_upload">Homepage Hero Photo</label>
      <img src="../img/hero-maternal-health.jpg?<?= $cacheBust ?>" alt="Current hero photo" class="admin-preview admin-preview-wide">
      <input type="file" id="hero_upload" name="hero_upload" accept="image/png,image/jpeg,image/webp">
    </div>
    <p style="font-size:0.82rem; color:var(--stone); margin-bottom:20px;">
      Uploading a new photo replaces the existing one in place — no other changes needed anywhere else on the site.
    </p>
    <h3 style="font-size:0.95rem; margin-bottom:14px;">Focus Area Photos</h3>
    <p style="font-size:0.82rem; color:var(--stone);">
      Shown on the Home, Focus Areas, and Programmes pages. Environment and Micro-Economic
      Development don't have a photo yet — upload one any time to add it.
    </p>
    <?php
    $focusAreaSlots = [
        'focus_environment_upload' => ['img/focus-environment.jpg', 'Environment'],
        'focus_health_upload' => ['img/focus-health.jpg', 'Health'],
        'focus_agriculture_upload' => ['img/focus-agriculture.jpg', 'Agriculture'],
        'focus_education_upload' => ['img/focus-education.jpg', 'Education'],
        'focus_microeconomic_upload' => ['img/focus-microeconomic.jpg', 'Micro-Economic Development'],
    ];
    foreach ($focusAreaSlots as $fieldName => [$relPath, $label]):
        $absPath = dirname(__DIR__) . '/' . $relPath;
    ?>
    <div class="field">
      <label for="<?= htmlspecialchars($fieldName) ?>"><?= htmlspecialchars($label) ?></label>
      <?php if (file_exists($absPath)): ?>
        <img src="../<?= htmlspecialchars($relPath) ?>?<?= $cacheBust ?>" alt="Current <?= htmlspecialchars($label) ?> photo" class="admin-preview">
      <?php else: ?>
        <div class="admin-preview" style="display:flex; align-items:center; justify-content:center; font-size:0.7rem; color:var(--stone); text-align:center; border-radius:8px;">No photo yet</div>
      <?php endif; ?>
      <input type="file" id="<?= htmlspecialchars($fieldName) ?>" name="<?= htmlspecialchars($fieldName) ?>" accept="image/png,image/jpeg,image/webp">
    </div>
    <?php endforeach; ?>
  </section>

  <section class="admin-section">
    <h2>Site-wide (used on every page)</h2>
    <?php admin_render_fields($content['site'], 'site'); ?>
  </section>

  <section class="admin-section">
    <h2>Pages</h2>
    <?php foreach ($content['pages'] as $pageKey => $pageData): ?>
      <details class="admin-page">
        <summary><?= htmlspecialchars(admin_page_label((string) $pageKey)) ?></summary>
        <div>
          <?php admin_render_fields($pageData, "pages[$pageKey]"); ?>
        </div>
      </details>
    <?php endforeach; ?>
  </section>

  <div class="admin-save-bar">
    <button type="submit" class="btn btn-primary">Save All Changes</button>
  </div>
</form>

</body>
</html>
