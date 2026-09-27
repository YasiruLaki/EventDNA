<?php
$dir = '/Applications/XAMPP/xamppfiles/htdocs/eventDNA/presentation/admin';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    if (basename($file) === 'logout.php') continue;
    
    $content = file_get_contents($file);
    
    // Replace the logout link
    $content = str_replace('href="../auth/logout/index.php"', 'href="logout.php"', $content);
    
    file_put_contents($file, $content);
}
echo "Done replacing Log Out links to point to admin logout.php.\n";
