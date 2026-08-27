<?php
declare(strict_types=1);

// array_is_list() is PHP 8.1+; this server (and possibly whatever cPanel
// host this ends up on) may run 8.0, so polyfill it rather than assume.
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool
    {
        $i = 0;
        foreach ($array as $key => $_) {
            if ($key !== $i++) {
                return false;
            }
        }
        return true;
    }
}

/** Page keys -> friendly labels shown in the admin UI. */
function admin_page_label(string $key): string
{
    $labels = [
        'index' => 'Home',
        'about' => 'About',
        'focusAreas' => 'Focus Areas',
        'programmes' => 'Programmes',
        'impact' => 'Impact',
        'whyKigoma' => 'Why Kigoma?',
        'partnership' => 'Partnership',
        'transparency' => 'Transparency & Accountability',
        'resources' => 'Resources',
        'communityStories' => 'Community Stories',
        'support' => 'Support Us',
        'contact' => 'Contact',
    ];
    return $labels[$key] ?? admin_label($key);
}

/** camelCase / snake_case key -> "Title Case Words" */
function admin_label(string $key): string
{
    $spaced = preg_replace('/(?<!^)([A-Z])/', ' $1', $key) ?? $key;
    $spaced = str_replace('_', ' ', $spaced);
    $words = preg_split('/\s+/', trim($spaced)) ?: [$key];
    return implode(' ', array_map('ucfirst', $words));
}

/** Longer/free-text fields get a textarea; everything else gets a single-line input. */
function admin_is_textarea(string $key): bool
{
    $lk = strtolower($key);
    foreach (['body', 'lead', 'blurb', 'description'] as $hint) {
        if (str_contains($lk, $hint)) {
            return true;
        }
    }
    return false;
}

/** Best-effort short label for a list item (used as the <summary> for card-like entries). */
function admin_item_summary(array $item, int $index): string
{
    foreach (['name', 'title', 'headline', 'amount', 'label', 'year', 'value'] as $key) {
        if (!empty($item[$key]) && is_string($item[$key])) {
            return $item[$key];
        }
    }
    return 'Item ' . ($index + 1);
}

/**
 * Recursively render form fields for $data. $namePrefix is the PHP
 * bracket-array name built up so far (e.g. "site", "pages[about][hero]"),
 * so the submitted $_POST naturally nests back into the same shape.
 */
function admin_render_fields(array $data, string $namePrefix): void
{
    foreach ($data as $key => $value) {
        $name = $namePrefix === '' ? (string) $key : $namePrefix . '[' . $key . ']';
        $id = 'f_' . preg_replace('/[^a-zA-Z0-9]+/', '_', $name);

        if (is_string($value)) {
            $label = is_int($key) ? 'Item ' . ($key + 1) : admin_label((string) $key);
            echo '<div class="field">';
            echo '<label for="' . htmlspecialchars($id) . '">' . htmlspecialchars($label) . '</label>';
            if (admin_is_textarea((string) $key)) {
                echo '<textarea id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($name) . '" rows="3">'
                    . htmlspecialchars($value) . '</textarea>';
            } else {
                echo '<input type="text" id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($name)
                    . '" value="' . htmlspecialchars($value) . '">';
            }
            echo '</div>';
        } elseif (is_array($value)) {
            $label = admin_label((string) $key);
            $isList = array_is_list($value);
            echo '<fieldset class="admin-group">';
            echo '<legend>' . htmlspecialchars($label) . '</legend>';
            if ($isList) {
                foreach ($value as $i => $item) {
                    if (is_array($item)) {
                        echo '<div class="admin-list-item">';
                        echo '<div class="admin-list-item-label">' . htmlspecialchars(admin_item_summary($item, (int) $i)) . '</div>';
                        admin_render_fields($item, $name . '[' . $i . ']');
                        echo '</div>';
                    } else {
                        admin_render_fields([$i => (string) $item], $name);
                    }
                }
            } else {
                admin_render_fields($value, $name);
            }
            echo '</fieldset>';
        }
    }
}

/**
 * Merge $submitted (already nested via PHP's bracket-array POST parsing)
 * into $original, preserving $original's shape: only overwrites string
 * leaves that already exist, and only recurses into arrays that already
 * exist at that key. This means a missing/renamed field in $submitted is
 * silently ignored rather than corrupting the structure, and no new
 * top-level keys can be introduced by a form field name typo.
 */
function admin_merge(array $original, array $submitted): array
{
    foreach ($original as $key => $value) {
        if (!array_key_exists($key, $submitted)) {
            continue;
        }
        if (is_string($value)) {
            if (is_string($submitted[$key])) {
                $original[$key] = str_replace("\r\n", "\n", trim($submitted[$key]));
            }
        } elseif (is_array($value) && is_array($submitted[$key])) {
            if (array_is_list($value)) {
                foreach ($value as $i => $item) {
                    if (!array_key_exists($i, $submitted[$key])) {
                        continue;
                    }
                    if (is_array($item) && is_array($submitted[$key][$i])) {
                        $original[$key][$i] = admin_merge($item, $submitted[$key][$i]);
                    } elseif (is_string($item) && is_string($submitted[$key][$i])) {
                        $original[$key][$i] = trim($submitted[$key][$i]);
                    }
                }
            } else {
                $original[$key] = admin_merge($value, $submitted[$key]);
            }
        }
    }
    return $original;
}

/**
 * Validate and save an uploaded image to a fixed target path, re-encoding
 * it through GD (this also strips anything non-image smuggled into the
 * file). Re-encoding to the slot's existing format/filename means every
 * HTML/CSS reference to that file keeps working with zero code changes.
 * Returns true on success, or a human-readable error string on failure.
 */
function admin_save_uploaded_image(array $file, string $targetPath, string $format)
{
    $maxBytes = 8 * 1024 * 1024;
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload failed (error code ' . $file['error'] . ').';
    }
    if ($file['size'] > $maxBytes) {
        return 'Image is too large (max 8MB).';
    }
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return 'That file is not a valid image.';
    }

    $image = match ($imageInfo[2]) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($file['tmp_name']),
        IMAGETYPE_PNG => imagecreatefrompng($file['tmp_name']),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($file['tmp_name']) : false,
        IMAGETYPE_GIF => imagecreatefromgif($file['tmp_name']),
        default => false,
    };
    if ($image === false) {
        return 'Unsupported image format — use JPG, PNG, or WEBP.';
    }

    if ($format === 'png') {
        imagesavealpha($image, true);
    }
    $ok = match ($format) {
        'png' => imagepng($image, $targetPath, 6),
        'jpg', 'jpeg' => imagejpeg($image, $targetPath, 88),
        default => false,
    };
    imagedestroy($image);

    if (!$ok) {
        return 'Could not save the image to disk — check that the img/ folder is writable by the web server.';
    }
    return true;
}
