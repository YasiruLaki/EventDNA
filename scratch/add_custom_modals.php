<?php
$dir = new RecursiveDirectoryIterator('/Applications/XAMPP/xamppfiles/htdocs/eventDNA/presentation');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.php$/', RegexIterator::GET_MATCH);
$count = 0;

$modalCode = <<<HTML
<!-- Custom Confirm Modal Setup -->
<style>
.custom-confirm-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);
    display: none; align-items: center; justify-content: center; z-index: 999999;
}
.custom-confirm-modal {
    background: #fff; padding: 2rem; border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    max-width: 400px; width: 90%; text-align: center;
    animation: confirmPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes confirmPop { from { transform: scale(0.95) translateY(10px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
.custom-confirm-modal h3 { margin: 0 0 1rem; color: #0f172a; font-size: 1.25rem; font-weight: 800; font-family: 'Inter', sans-serif; }
.custom-confirm-modal p { color: #64748b; margin-bottom: 2rem; font-size: 0.95rem; line-height: 1.5; font-family: 'Inter', sans-serif; }
.custom-confirm-actions { display: flex; gap: 1rem; justify-content: center; }
.custom-confirm-btn {
    padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem; font-family: 'Inter', sans-serif; transition: all 0.2s;
}
.custom-confirm-cancel { background: #f1f5f9; color: #475569; }
.custom-confirm-cancel:hover { background: #e2e8f0; }
.custom-confirm-danger { background: #ef4444; color: #fff; }
.custom-confirm-danger:hover { background: #dc2626; transform: translateY(-1px); }
</style>
<div class="custom-confirm-overlay" id="customConfirmOverlay">
    <div class="custom-confirm-modal">
        <h3 id="customConfirmTitle">Are you sure?</h3>
        <p id="customConfirmMessage">This action cannot be undone.</p>
        <div class="custom-confirm-actions">
            <button class="custom-confirm-btn custom-confirm-cancel" id="customConfirmCancel">Cancel</button>
            <button class="custom-confirm-btn custom-confirm-danger" id="customConfirmOk">Yes, I'm sure</button>
        </div>
    </div>
</div>
<script>
let pendingConfirmAction = null;
function initCustomConfirm(el, message, e) {
    if (e) e.preventDefault();
    document.getElementById('customConfirmMessage').innerText = message;
    
    // Auto title based on message
    let title = "Are you sure?";
    if(message.toLowerCase().includes('delete')) title = "Confirm Deletion";
    else if(message.toLowerCase().includes('remove')) title = "Confirm Removal";
    document.getElementById('customConfirmTitle').innerText = title;

    document.getElementById('customConfirmOverlay').style.display = 'flex';
    
    pendingConfirmAction = () => {
        if (el.tagName === 'FORM') {
            // Remove the onsubmit handler so it doesn't trigger again, then submit
            el.onsubmit = null;
            el.submit();
        } else if (el.tagName === 'BUTTON' || el.tagName === 'A') {
            if(el.form) {
                el.form.onsubmit = null;
                el.form.submit();
            } else {
                // If there's an href, navigate
                if(el.href && el.href !== '#' && !el.href.startsWith('javascript:')) {
                    window.location.href = el.href;
                }
            }
        }
    };
    return false;
}
document.getElementById('customConfirmCancel').addEventListener('click', () => {
    document.getElementById('customConfirmOverlay').style.display = 'none';
    pendingConfirmAction = null;
});
document.getElementById('customConfirmOk').addEventListener('click', () => {
    document.getElementById('customConfirmOverlay').style.display = 'none';
    if(pendingConfirmAction) pendingConfirmAction();
});
</script>
HTML;

foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $original = $content;

    // Replace the confirm() calls
    $content = preg_replace('/return\s+confirm\(\s*[\'"]([^\'"]+)[\'"]\s*\)/i', 'return initCustomConfirm(this, \'$1\', event)', $content);

    // If we replaced something, we need to make sure the modal code is added at the end (before </body>)
    if ($original !== $content) {
        if (strpos($content, 'id="customConfirmOverlay"') === false) {
            $content = str_replace('</body>', $modalCode . "\n</body>", $content);
        }
        file_put_contents($path, $content);
        echo "Updated $path\n";
        $count++;
    }
}
echo "Total files updated with custom modal: $count\n";
