<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /api/\n";
echo "Disallow: /session.php\n";
echo "Disallow: /test-page.php\n\n";
echo 'Sitemap: ' . public_url('sitemap.php') . "\n";
