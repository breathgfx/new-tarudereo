<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
admin_require_login();
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
admin_check_csrf();

$contentPath = dirname(__DIR__) . '/content.json';
$json = file_get_contents($contentPath);
$content = $json !== false ? json_decode($json, true) : null;
if (!is_array($content) || !isset($content['site'], $content['pages'])) {
    header('Location: index.php?error=' . urlencode('content.json is currently invalid — fix it manually before saving from here.'));
    exit;
}

// --- Images first: fixed target filenames, so every existing HTML/CSS
// reference keeps working unchanged. ---
$imageSlots = [
    'logo_upload' => [dirname(__DIR__) . '/img/logo.png', 'png'],
    'hero_upload' => [dirname(__DIR__) . '/img/hero-maternal-health.jpg', 'jpg'],
];
foreach ($imageSlots as $fieldName => [$targetPath, $format]) {
    if (!empty($_FILES[$fieldName]['tmp_name']) && is_uploaded_file($_FILES[$fieldName]['tmp_name'])) {
        $result = admin_save_uploaded_image($_FILES[$fieldName], $targetPath, $format);
        if ($result !== true) {
            header('Location: index.php?error=' . urlencode($result));
            exit;
        }
    }
}

// --- Then the text fields. ---
$postSite = $_POST['site'] ?? null;
if (is_array($postSite)) {
    $content['site'] = admin_merge($content['site'], $postSite);
}
$postPages = $_POST['pages'] ?? null;
if (is_array($postPages)) {
    $content['pages'] = admin_merge($content['pages'], $postPages);
}

$newJson = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($newJson === false) {
    header('Location: index.php?error=' . urlencode('Could not encode the changes back to JSON — nothing was saved.'));
    exit;
}
if (file_put_contents($contentPath, $newJson . "\n") === false) {
    header('Location: index.php?error=' . urlencode('Could not write content.json — check that it and its folder are writable by the web server.'));
    exit;
}

header('Location: index.php?saved=1');
exit;
