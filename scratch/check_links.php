<?php
$baseDir = '/Applications/XAMPP/xamppfiles/htdocs/eventDNA';

$dir = new RecursiveDirectoryIterator($baseDir);
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.(php|html)$/', RegexIterator::GET_MATCH);

$brokenLinks = [];

function resolvePath($base, $rel) {
    if (strpos($rel, '/') === 0) {
        // Absolute from document root, hard to know where doc root is exactly, 
        // assuming eventDNA is root for this purpose
        global $baseDir;
        return $baseDir . $rel;
    }
    
    $baseParts = explode('/', dirname($base));
    $relParts = explode('/', $rel);
    
    foreach ($relParts as $part) {
        if ($part === '.') continue;
        if ($part === '..') {
            array_pop($baseParts);
        } else {
            $baseParts[] = $part;
        }
    }
    return implode('/', $baseParts);
}

foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    
    // Check href="..."
    preg_match_all('/href=["\']([^"\']+)["\']/i', $content, $hrefs);
    // Check header("Location: ...")
    preg_match_all('/header\s*\(\s*["\']Location:\s*([^"\']+)["\']\s*\)/i', $content, $locations);
    
    $links = array_merge($hrefs[1], $locations[1]);
    
    foreach ($links as $link) {
        // Ignore external links, anchor links, JS, mailto, etc.
        if (strpos($link, 'http') === 0 || strpos($link, '#') === 0 || strpos($link, 'javascript:') === 0 || strpos($link, 'mailto:') === 0 || $link === '') {
            continue;
        }
        
        // Strip query params and hashes for file existence checking
        $cleanLink = preg_replace('/[?#].*$/', '', $link);
        if ($cleanLink === '') continue; // Link was just query params
        
        $resolved = resolvePath($path, $cleanLink);
        
        // Check if file exists. If it's a directory, check if index.php or index.html exists inside
        $exists = file_exists($resolved);
        if ($exists && is_dir($resolved)) {
            $exists = file_exists($resolved . '/index.php') || file_exists($resolved . '/index.html') || file_exists($resolved . '/view.php'); 
        }
        
        // some specific ignore rules
        if (strpos($cleanLink, 'fonts.googleapis.com') !== false) continue;
        if (strpos($cleanLink, 'cdn.jsdelivr.net') !== false) continue;
        
        if (!$exists) {
            $brokenLinks[] = [
                'file' => str_replace($baseDir, '', $path),
                'link' => $link,
                'resolved' => str_replace($baseDir, '', $resolved)
            ];
        }
    }
}

if (empty($brokenLinks)) {
    echo "No broken links found!\n";
} else {
    echo "Found " . count($brokenLinks) . " broken links:\n";
    foreach ($brokenLinks as $bl) {
        echo "- In {$bl['file']}:\n  Broken link: {$bl['link']}\n  Expected at: {$bl['resolved']}\n\n";
    }
}
