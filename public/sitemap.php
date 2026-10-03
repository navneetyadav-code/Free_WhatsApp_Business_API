<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$urls = [
    [
        'loc' => public_url('index.php?page=docs'),
        'priority' => '1.0',
        'changefreq' => 'weekly',
    ],
];

header('Content-Type: application/xml; charset=utf-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
    <url>
        <loc><?= e($url['loc']) ?></loc>
        <changefreq><?= e($url['changefreq']) ?></changefreq>
        <priority><?= e($url['priority']) ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
