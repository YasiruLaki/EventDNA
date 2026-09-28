<?php
require_once __DIR__ . '/../includes/guard.php';
require_once "../../../data/database.php";
require_once "../../../application/controllers/GroupController.php";

$groupController = new GroupController($conn);
$groupId = $_GET['id'] ?? 0;
$group = $groupController->getGroupById($groupId);

if (!$group) { die("Group not found or archived."); }

$userId = $_SESSION['user_id'];
$membership = $groupController->isMember($groupId, $userId);
$isOrganizer = (int)($_SESSION['role_id'] ?? 0) === 2;
$isOwner = $membership && $membership['role'] === 'OWNER';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($group['name']) ?> - EventDNA</title>
    <link rel="stylesheet" href="../dashboard/styles.css" />
    <link rel="stylesheet" href="../../organizer/organizer.css" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        
        .group-hero { background: #fff; border-bottom: 1px solid var(--border-color); padding: 3rem 0 2rem 0; color: #0f172a; margin-bottom: 2rem; }
        
        .hero-content { position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 2rem; }
        .hero-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.2; color: #0f172a; }
        .hero-meta { display: flex; gap: 1.5rem; color: var(--text-secondary); font-size: 0.95rem; align-items: center; }
        
        .layout-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 2rem; align-items: start; }
        @media(max-width: 900px) { .layout-grid { grid-template-columns: 1fr; } }
        
        .panel { background: #fff; border-radius: 4px; padding: 2rem; border: 1px solid #d1d5db; margin-bottom: 2rem; }
        .panel-title { font-size: 1.15rem; font-weight: 700; color: var(--secondary); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9; }
        
        .g-tag { background: transparent; color: var(--text-primary); border: 1px solid var(--border-color); padding: 0.35rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500; display: inline-block; margin: 0.25rem 0.25rem 0.25rem 0; }
        
        /* Thread Styles */
        .post-input-wrap { display: flex; gap: 1rem; margin-bottom: 2.5rem; }
        .avatar { width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; overflow: hidden; background: #e2e8f0; } .avatar img { width: 100%; height: 100%; object-fit: cover; }
        
        
        
        .thread { border-bottom: 1px solid #f1f5f9; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
        .thread:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .thread-header { display: flex; gap: 1rem; margin-bottom: 0.75rem; }
        .thread-author { font-weight: 600; color: var(--secondary); font-size: 0.95rem; }
        .thread-time { font-size: 0.8rem; color: var(--text-secondary); margin-left: 0.5rem; font-weight: 400; }
        .thread-role { background: #fee2e2; color: #ef4444; font-size: 0.7rem; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 700; margin-left: 0.5rem; }
        .thread-content { color: var(--text-primary); line-height: 1.6; font-size: 0.95rem; margin-left: 60px; margin-bottom: 1rem; }
        
        .thread-actions { margin-left: 60px; display: flex; gap: 1rem; }
        .btn-action { background: none; border: none; color: var(--text-secondary); font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.3rem; padding: 0; transition: color 0.2s; }
        .btn-action:hover { color: var(--primary); }
        
        .reply-block { margin-left: 60px; margin-top: 1rem; background: #f8fafc; border-radius: 12px; padding: 1.25rem; display: flex; gap: 1rem; }
        .reply-avatar { width: 32px; height: 32px; font-size: 0.85rem; }
    </style>
</head>
<body class="org-page-bg">
    <?php include '../includes/nav.php'; ?>
    <div class="dashboard-shell">
        
        <div class="group-hero">
            <div class="hero-content">
                <div>
                    <h1 class="hero-title"><?= htmlspecialchars($group['name']) ?></h1>
                    <div class="hero-meta">
                        <span style="display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="users" style="width: 18px;"></i> <?= $group['member_count'] ?> Members</span>
                        <span style="display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="calendar" style="width: 18px;"></i> Est. <?= date('Y', strtotime($group['created_at'])) ?></span>
                    </div>
                </div>
                <div>
                    <?php if (false): // Attendees cannot edit ?>
                    <?php elseif ($membership): ?>
                        <a href="leave.php?id=<?= $groupId ?>" class="btn-secondary" style="border-radius: 4px; padding: 0.6rem 1.5rem;"><i data-lucide="log-out" style="width: 18px;"></i> Leave Group</a>
                    <?php else: ?>
                        <a href="join.php?id=<?= $groupId ?>" class="btn-primary" style="border-radius: 4px; padding: 0.6rem 2rem;">Join Group</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="layout-grid">
            <!-- Left Column: Discussions -->
            <div>
                <div class="panel">
                    <h2 class="panel-title"><i data-lucide="message-square" style="width: 20px;"></i> Discussion Thread</h2>
                    
                    <?php if ($membership || $isOwner): ?>
                        <div class="post-input-wrap" style="display: flex; gap: 1rem; margin-bottom: 2.5rem; align-items: flex-start;">
                            <div class="avatar"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80" alt="Y"></div>
                            <div style="flex: 1; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 0.5rem; display: flex; flex-direction: column; transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.02);" onfocusin="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 3px var(--primary-tint)';" onfocusout="this.style.borderColor='var(--border-color)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.02)';">
                                <textarea rows="2" placeholder="Share something with the community..." style="border: none; background: transparent; padding: 0.5rem; outline: none; width: 100%; resize: none; font-family: inherit; font-size: 0.95rem;" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.5rem 0;">
                                    <button type="button" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 0.4rem; border-radius: 6px; display: flex; align-items: center; gap: 0.3rem; font-size: 0.85rem; font-weight: 500; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.color='var(--primary)'" onmouseout="this.style.background='none'; this.style.color='var(--text-secondary)'">
                                        <i data-lucide="paperclip" style="width: 16px;"></i> Attach
                                    </button>
                                    <button class="btn-primary" style="padding: 0.5rem 1.5rem; border-radius: 8px; display: flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                                        Post <i data-lucide="send" style="width: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="background: #f8fafc; padding: 2rem; border-radius: 12px; text-align: center; border: 1px dashed var(--border-color); margin-bottom: 2.5rem;">
                            <i data-lucide="lock" style="width: 32px; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                            <p style="font-weight: 600; color: var(--secondary);">Join this community to participate in discussions.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Mocked Threads UI -->
                    <div class="thread">
                        <div class="thread-header">
                            <div class="avatar"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80" alt="Admin"></div>
                            <div>
                                <div class="thread-author">Admin User <span class="thread-role">ORGANIZER</span><span class="thread-time">2 hours ago</span></div>
                            </div>
                        </div>
                        <div class="thread-content">
                            Welcome everyone to our new community hub! We will be posting updates about the upcoming Summit here. Feel free to ask any questions.
                        </div>
                        <div class="thread-actions">
                            <button class="btn-action"><i data-lucide="heart" style="width: 16px;"></i> 12</button>
                            <button class="btn-action"><i data-lucide="message-circle" style="width: 16px;"></i> Reply</button>
                            <button class="btn-action" style="color: #ef4444; margin-left: auto;" onclick="openReportModal('POST', 1)"><i data-lucide="flag" style="width: 16px;"></i> Report</button>
                        </div>
                        
                        <!-- Reply block -->
                        <div class="reply-block">
                            <div class="avatar reply-avatar"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" alt="Sarah"></div>
                            <div style="flex: 1;">
                                <div class="thread-author">Sarah Doe <span class="thread-time">1 hour ago</span></div>
                                <div style="color: var(--text-primary); font-size: 0.9rem; margin-top: 0.4rem; line-height: 1.5;">Super excited! Are the workshop schedules finalized yet?</div>
                            </div>
                            <button class="btn-action" style="color: #ef4444; margin-left: auto; align-self: flex-start;" onclick="openReportModal('COMMENT', 1)"><i data-lucide="flag" style="width: 14px;"></i></button>
                        </div>
                    </div>

                    <div class="thread">
                        <div class="thread-header">
                            <div class="avatar"><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80" alt="John"></div>
                            <div>
                                <div class="thread-author">John Smith <span class="thread-time">Yesterday</span></div>
                            </div>
                        </div>
                        <div class="thread-content">
                            Is anyone looking to form a team for the hackathon? I have experience in React and Node.js.
                        </div>
                        <div class="thread-actions">
                            <button class="btn-action"><i data-lucide="heart" style="width: 16px;"></i> 5</button>
                            <button class="btn-action"><i data-lucide="message-circle" style="width: 16px;"></i> 0 Replies</button>
                            <button class="btn-action" style="color: #ef4444; margin-left: auto;" onclick="openReportModal('POST', 2)"><i data-lucide="flag" style="width: 16px;"></i> Report</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: About -->
            <div>
                <div class="panel">
                    <h2 class="panel-title"><i data-lucide="info" style="width: 20px;"></i> About Community</h2>
                    <p style="color: var(--text-primary); line-height: 1.6; white-space: pre-wrap; word-break: break-word; margin-bottom: 2rem; font-size: 0.95rem;"><?= htmlspecialchars($group['description']) ?></p>
                    
                    <h3 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-bottom: 1rem; font-weight: 700;">Topics & Interests</h3>
                    <div style="margin-bottom: 2rem;">
                        <?php foreach ($group['interests'] as $tag): ?>
                            <span class="g-tag"><?= htmlspecialchars($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <h3 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-bottom: 1rem; font-weight: 700;">Recent Members</h3>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <div class="avatar" style="width: 36px; height: 36px;" title="Member"><img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80"></div>
                        <div class="avatar" style="width: 36px; height: 36px;" title="Member"><img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=150&q=80"></div>
                        <div class="avatar" style="width: 36px; height: 36px;" title="Member"><img src="https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&w=150&q=80"></div>
                        <div class="avatar" style="width: 36px; height: 36px;" title="Member"><img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=150&q=80"></div>
                        <div class="avatar" style="width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: #475569; font-size: 0.8rem; font-weight: 600;">+<?= max(0, $group['member_count'] - 4) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal-overlay" id="reportModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.4); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center;">
        <div class="modal" style="background:#fff; border-radius:12px; width:90%; max-width:400px; padding:2rem; position:relative; box-shadow:0 20px 40px rgba(0,0,0,0.15);">
            <button class="modal-close" id="closeReportModal" style="position:absolute; top:1rem; right:1rem; background:none; border:none; color:#64748b; cursor:pointer;"><i data-lucide="x" style="width: 20px;"></i></button>
            <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:1rem; color:var(--secondary);">Report Content</h3>
            <p style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:1.5rem;">Why are you reporting this? Your report will be kept anonymous.</p>
            
            <form id="reportForm">
                <input type="hidden" id="reportType" name="type" value="">
                <input type="hidden" id="reportContentId" name="content_id" value="">
                
                <div style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom: 2rem;">
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.95rem; cursor:pointer;">
                        <input type="radio" name="reason" value="Spam or promotional" required> Spam or promotional
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.95rem; cursor:pointer;">
                        <input type="radio" name="reason" value="Harassment or bullying"> Harassment or bullying
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.95rem; cursor:pointer;">
                        <input type="radio" name="reason" value="Inappropriate content"> Inappropriate content
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.95rem; cursor:pointer;">
                        <input type="radio" name="reason" value="Off-topic"> Off-topic
                    </label>
                </div>
                
                <div style="display:flex; gap:1rem;">
                    <button type="button" class="btn-secondary" style="flex:1; justify-content:center; border-radius:4px;" onclick="closeReport()">Cancel</button>
                    <button type="submit" class="btn-primary" style="flex:1; justify-content:center; border-radius:4px; background:#ef4444; border-color:#ef4444;">Submit Report</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    lucide.createIcons();
    
    function openReportModal(type, contentId) {
        document.getElementById('reportType').value = type;
        document.getElementById('reportContentId').value = contentId;
        document.getElementById('reportModal').style.display = 'flex';
        document.getElementById('reportForm').reset();
    }
    
    function closeReport() {
        document.getElementById('reportModal').style.display = 'none';
    }
    
    document.getElementById('closeReportModal').addEventListener('click', closeReport);
    
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const data = {
            type: document.getElementById('reportType').value,
            content_id: document.getElementById('reportContentId').value,
            reason: document.querySelector('input[name="reason"]:checked').value
        };
        
        fetch('report.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                alert('Thank you! Your report has been submitted for review.');
                closeReport();
            } else {
                alert(res.message || 'Error submitting report.');
            }
        })
        .catch(() => alert('Error submitting report.'));
    });
    </script>
</body>
</html>
