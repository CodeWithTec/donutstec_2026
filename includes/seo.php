<?php
/**
 * SEO meta tag generator.
 * Reads default values from `settings` + per-page overrides.
 *
 * Usage: in a page, before including header.php, set:
 *   $page_title = '...';
 *   $page_description = '...';
 *   $page_image = 'https://...'; // optional
 *   $page_type  = 'website' | 'article';
 *   $noindex    = true; // optional
 */
require_once __DIR__ . '/functions.php';

function seo_meta(): string
{
    $db = Database::getConnection();
    $settings = [];
    foreach ($db->query("SELECT setting_key, setting_value FROM settings") as $r) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }

    global $page_title, $page_description, $page_image, $page_type, $noindex;

    $title = trim(($page_title ?? '') . ' — ' . ($settings['site_name'] ?? 'DonutsTecLiberia'));
    if (empty($page_title)) $title = $settings['seo_title'] ?? $settings['site_name'] ?? 'DonutsTecLiberia';

    $desc = $page_description ?? $settings['seo_description'] ?? '';
    $desc = mb_substr(strip_tags($desc), 0, 160);

    $ogImage = $page_image ?? ($settings['seo_og_image'] ?: '');
    $canonical = canonical_url();
    $type = $page_type ?? 'website';

    $out  = '<title>' . e($title) . '</title>' . "\n";
    $out .= '<meta name="description" content="' . e($desc) . '">' . "\n";
    if (!empty($settings['seo_keywords'])) {
        $out .= '<meta name="keywords" content="' . e($settings['seo_keywords']) . '">' . "\n";
    }
    if (!empty($noindex)) {
        $out .= '<meta name="robots" content="noindex,nofollow">' . "\n";
    }
    $out .= '<link rel="canonical" href="' . e($canonical) . '">' . "\n";

    // Open Graph
    $out .= '<meta property="og:title" content="' . e($title) . '">' . "\n";
    $out .= '<meta property="og:description" content="' . e($desc) . '">' . "\n";
    $out .= '<meta property="og:type" content="' . e($type) . '">' . "\n";
    $out .= '<meta property="og:url" content="' . e($canonical) . '">' . "\n";
    $out .= '<meta property="og:site_name" content="' . e($settings['site_name'] ?? 'DonutsTecLiberia') . '">' . "\n";
    if ($ogImage) {
        $out .= '<meta property="og:image" content="' . e($ogImage) . '">' . "\n";
    }

    // Twitter card
    $out .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $out .= '<meta name="twitter:title" content="' . e($title) . '">' . "\n";
    $out .= '<meta name="twitter:description" content="' . e($desc) . '">' . "\n";
    if ($ogImage) {
        $out .= '<meta name="twitter:image" content="' . e($ogImage) . '">' . "\n";
    }

    return $out;
}

/**
 * JSON-LD structured data for the organization (homepage only).
 */
function seo_jsonld(): string
{
    $db = Database::getConnection();
    $s = [];
    foreach ($db->query("SELECT setting_key, setting_value FROM settings") as $r) {
        $s[$r['setting_key']] = $r['setting_value'];
    }
    $data = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $s['site_name'] ?? 'DonutsTecLiberia',
        'url'      => BASE_URL,
        'email'    => $s['site_email'] ?? '',
        'telephone'=> $s['site_phone'] ?? '',
        'address'  => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Monrovia',
            'addressCountry'  => 'LR',
            'streetAddress'   => $s['site_address'] ?? '',
        ],
        'sameAs' => array_values(array_filter([
            $s['facebook_url'] ?? '', $s['twitter_url'] ?? '',
            $s['linkedin_url'] ?? '', $s['instagram_url'] ?? '',
        ])),
    ];
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES) . '</script>';
}