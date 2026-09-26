<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../../auth/login.php"); exit; }
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
        
        .group-hero { background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%); border-radius: 20px; padding: 4rem 3rem; color: #fff; position: relative; overflow: hidden; margin-bottom: 2rem; box-shadow: 0 10px 30px -10px rgba(79, 70, 229, 0.3); }
        
        .hero-content { position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 2rem; }
        .hero-title { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; line-height: 1.2; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .hero-meta { display: flex; gap: 1.5rem; opacity: 0.9; font-size: 0.95rem; align-items: center; }
        
        .layout-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 2rem; align-items: start; }
        @media(max-width: 900px) { .layout-grid { grid-template-columns: 1fr; } }
        
        .panel { background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 24px -4px rgba(0,0,0,0.03), 0 2px 8px -2px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; margin-bottom: 2rem; }
        .panel-title { font-size: 1.15rem; font-weight: 700; color: var(--secondary); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9; }
        
        .g-tag { background: var(--primary-tint); color: var(--primary); padding: 0.4rem 0.8rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; display: inline-block; margin: 0.25rem; }
        
        /* Thread Styles */
        .post-input-wrap { display: flex; gap: 1rem; margin-bottom: 2.5rem; }
        .avatar { width: 44px; height: 44px; border-radius: 50%; background: var(--primary-tint); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; font-size: 1.1rem; }
        
        
        
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
                        <a href="edit.php?id=<?= $groupId ?>" class="btn-primary" style="background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.4); text-decoration: none; padding: 0.8rem 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="settings" style="width: 18px;"></i> Manage Group</a>
                    <?php elseif ($membership): ?>
                        <a href="leave.php?id=<?= $groupId ?>" style="background: #fff; color: var(--primary); font-weight: 700; border-radius: 8px; text-decoration: none; padding: 0.8rem 1.5rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"><i data-lucide="log-out" style="width: 18px;"></i> Leave Group</a>
                    <?php else: ?>
                        <a href="join.php?id=<?= $groupId ?>" style="background: #fff; color: var(--primary); font-weight: 700; border-radius: 8px; text-decoration: none; padding: 0.8rem 2rem; font-size: 1.05rem; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">Join Group</a>
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
                            <div class="avatar">Y</div>
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
                            <div class="avatar">A</div>
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
                            <?php if (false): // Attendees cannot edit ?><button class="btn-action" style="color: #ef4444; margin-left: auto;"><i data-lucide="trash-2" style="width: 16px;"></i></button><?php endif; ?>
                        </div>
                        
                        <!-- Reply block -->
                        <div class="reply-block">
                            <div class="avatar reply-avatar">S</div>
                            <div>
                                <div class="thread-author">Sarah Doe <span class="thread-time">1 hour ago</span></div>
                                <div style="color: var(--text-primary); font-size: 0.9rem; margin-top: 0.4rem; line-height: 1.5;">Super excited! Are the workshop schedules finalized yet?</div>
                            </div>
                        </div>
                    </div>

                    <div class="thread">
                        <div class="thread-header">
                            <div class="avatar" style="background: #e0e7ff; color: #4338ca;">J</div>
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
                            <?php if (false): // Attendees cannot edit ?><button class="btn-action" style="color: #ef4444; margin-left: auto;"><i data-lucide="trash-2" style="width: 16px;"></i></button><?php endif; ?>
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
                        <div class="avatar" style="width: 36px; height: 36px; font-size: 0.9rem;" title="Member">JD</div>
                        <div class="avatar" style="width: 36px; height: 36px; font-size: 0.9rem; background: #e0e7ff; color: #4338ca;" title="Member">AS</div>
                        <div class="avatar" style="width: 36px; height: 36px; font-size: 0.9rem; background: #dcfce7; color: #166534;" title="Member">MK</div>
                        <div class="avatar" style="width: 36px; height: 36px; font-size: 0.9rem; background: #fef9c3; color: #854d0e;" title="Member">TR</div>
                        <div class="avatar" style="width: 36px; height: 36px; font-size: 0.9rem; background: #f1f5f9; color: #475569;">+<?= max(0, $group['member_count'] - 4) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
