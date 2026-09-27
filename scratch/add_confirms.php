<?php
$dir = new RecursiveDirectoryIterator('/Applications/XAMPP/xamppfiles/htdocs/eventDNA/presentation');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.php$/', RegexIterator::GET_MATCH);
$count = 0;

foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $original = $content;

    // 1. Add confirmation to <button>Remove</button> or <button>Delete</button> that are just dummy buttons
    // Only add if it doesn't already have an onclick
    $content = preg_replace_callback('/<button([^>]*)>(Remove|Delete)<\/button>/i', function($m) {
        $attrs = $m[1];
        $text = $m[2];
        if (stripos($attrs, 'onclick=') === false && stripos($attrs, 'type="submit"') === false) {
            return '<button' . $attrs . ' onclick="return confirm(\'Are you sure you want to ' . strtolower($text) . ' this item?\');">' . $text . '</button>';
        }
        return $m[0];
    }, $content);

    // 2. Add onsubmit to forms that contain a Remove or Delete submit button, or have an action="delete" / name="action" value="delete"
    // We will find all forms and inspect them
    if (preg_match_all('/<form[^>]*>.*?<\/form>/is', $content, $forms)) {
        foreach ($forms[0] as $form) {
            $hasDeleteBtn = preg_match('/<button[^>]*type="submit"[^>]*>(Remove|Delete)<\/button>/i', $form);
            $hasDeleteInput = preg_match('/<input[^>]*value="(delete|remove(_photo)?)"[^>]*>/i', $form);
            $hasConfirm = stripos($form, 'onsubmit=') !== false;
            
            if (($hasDeleteBtn || $hasDeleteInput) && !$hasConfirm) {
                // Add onsubmit to the form tag
                $newForm = preg_replace('/<form([^>]*)>/i', '<form$1 onsubmit="return confirm(\'Are you sure you want to perform this deletion?\');">', $form);
                $content = str_replace($form, $newForm, $content);
            }
        }
    }

    if ($original !== $content) {
        file_put_contents($path, $content);
        echo "Updated $path\n";
        $count++;
    }
}
echo "Total files updated: $count\n";
