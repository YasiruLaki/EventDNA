<?php
$dir = '/Applications/XAMPP/xamppfiles/htdocs/eventDNA/presentation/admin';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Remove the settings tab line
    $content = preg_replace('/<a href="#" class="admin-nav-item">.*?Settings<\/a>\s*/s', '', $content);
    
    // Fix logout link and make sure it has the red styling
    // The previous link might look like: <a href="../auth/login/index.php" class="admin-nav-item" style="color: var(--danger);"><svg ...> Log Out</a>
    $content = preg_replace('/<a href="[^"]*login[^"]*" class="admin-nav-item"[^>]*>(.*?)Log Out<\/a>/s', '<a href="../auth/logout/index.php" class="admin-nav-item" style="color: var(--danger);">$1Log Out</a>', $content);
    
    // Also catch if it already had logout but no danger style
    $content = preg_replace('/<a href="[^"]*logout[^"]*" class="admin-nav-item">(.*?)Log Out<\/a>/s', '<a href="../auth/logout/index.php" class="admin-nav-item" style="color: var(--danger);">$1Log Out</a>', $content);

    file_put_contents($file, $content);
}
echo "Done replacing Settings and fixing Log Out.\n";
