<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$db = Database::getConnection();

$urls = [
    ['loc' => BASE_URL . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => BASE_URL . '/public/about.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => BASE_URL . '/public/services.php', 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => BASE_URL . '/public/portfolio.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => BASE_URL . '/public/blog.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => BASE_URL . '/public/contact.php', 'priority' => '0.6', 'changefreq' => 'yearly'],
    ['loc' => BASE_URL . '/public/quote.php', 'priority' => '0.9', 'changefreq' => 'yearly'],
];

foreach ($db->query("SELECT slug, published_at FROM blog_posts WHERE status='published'") as $p) {
    $urls[] = [
        'loc' => BASE_URL . '/public/blog-post.php?slug=' . urlencode($p['slug']),
        'lastmod' => date('Y-m-d', strtotime($p['published_at'])),
        'priority' => '0.7',
        'changefreq' => 'monthly',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
    if (!empty($u['lastmod'])) echo "    <lastmod>{$u['lastmod']}</lastmod>\n";
    echo "    <changefreq>{$u['changefreq']}</changefreq>\n";
    echo "    <priority>{$u['priority']}</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';