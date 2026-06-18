<?php

$pageTitle = 'Messages';
require_once 'includes/db.php';
require_once 'includes/session.php';

if (!isLoggedIn()) redirect('login.php?required=1');

$db  = getDB();
$uid = (int)$_SESSION['user_id'];

$db->query("CREATE TABLE IF NOT EXISTS tblMessages (
    message_id  INT AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT NOT NULL,
    receiver_id INT NOT NULL,
    clothes_id  INT DEFAULT NULL,
    subject     VARCHAR(255) NOT NULL,
    body        TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    sent_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_msg_s2 FOREIGN KEY (sender_id)   REFERENCES tblUser(user_id)    ON DELETE CASCADE,
    CONSTRAINT fk_msg_r2 FOREIGN KEY (receiver_id) REFERENCES tblUser(user_id)    ON DELETE CASCADE,
    CONSTRAINT fk_msg_c2 FOREIGN KEY (clothes_id)  REFERENCES tblClothes(clothes_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$tab     = $_GET['tab']    ?? 'inbox';
$viewId  = (int)($_GET['view']  ?? 0);
$toId    = (int)($_GET['to']    ?? 0);
$itemId  = (int)($_GET['item']  ?? 0);
$flash   = '';
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $clothesId  = (int)($_POST['clothes_id']  ?? 0) ?: null;
    $subject    = trim($_POST['subject'] ?? '');
    $body       = trim($_POST['body']    ?? '');

    if (!$receiverId || !$subject || !$body) {
        $flash = 'All fields are required.'; $flashType = 'error';
    } elseif ($receiverId === $uid) {
        $flash = 'You cannot message yourself.'; $flashType = 'error';
    } else {
        $st = $db->prepare("INSERT INTO tblMessages (sender_id, receiver_id, clothes_id, subject, body) VALUES (?,?,?,?,?)");
        $st->bind_param('iiiss', $uid, $receiverId, $clothesId, $subject, $body);
        if ($st->execute()) {
            $st->close();
            $db->close();
            header('Location: messages.php?tab=sent&flash=sent');
            exit;
        }
        $flash = 'Failed to send message. Please try again.'; $flashType = 'error';
        $st->close();
    }
}

if (isset($_GET['flash']) && $_GET['flash'] === 'sent') {
    $flash = 'Message sent successfully.';
}

if ($tab === 'inbox') {
    $db->query("UPDATE tblMessages SET is_read=1 WHERE receiver_id=$uid AND is_read=0");
}

$thread      = [];
$threadOther = null;
$threadItem  = null;
if ($viewId > 0) {
    
    $st = $db->prepare("SELECT * FROM tblMessages WHERE message_id=? AND (sender_id=? OR receiver_id=?)");
    $st->bind_param('iii', $viewId, $uid, $uid);
    $st->execute();
    $root = $st->get_result()->fetch_assoc();
    $st->close();

    if ($root) {
        $otherId = ($root['sender_id'] === $uid) ? $root['receiver_id'] : $root['sender_id'];
        $cid     = $root['clothes_id'];

        
        $sql = "SELECT m.*, s.first_name as sfn, s.last_name as sln,
                       r.first_name as rfn, r.last_name as rln,
                       c.brand, c.item_name, c.image_file
                FROM tblMessages m
                JOIN tblUser s ON m.sender_id   = s.user_id
                JOIN tblUser r ON m.receiver_id = r.user_id
                LEFT JOIN tblClothes c ON m.clothes_id = c.clothes_id
                WHERE ((m.sender_id=? AND m.receiver_id=?) OR (m.sender_id=? AND m.receiver_id=?))";
        if ($cid) {
            $sql .= " AND m.clothes_id=?";
            $st2 = $db->prepare($sql . " ORDER BY m.sent_at ASC");
            $st2->bind_param('iiiii', $uid, $otherId, $otherId, $uid, $cid);
        } else {
            $st2 = $db->prepare($sql . " ORDER BY m.sent_at ASC");
            $st2->bind_param('iiii', $uid, $otherId, $otherId, $uid);
        }
        $st2->execute();
        $res = $st2->get_result();
        while ($r = $res->fetch_assoc()) $thread[] = $r;
        $st2->close();

        
        $db->query("UPDATE tblMessages SET is_read=1 WHERE receiver_id=$uid AND sender_id=$otherId");

        
        $st3 = $db->prepare("SELECT user_id, first_name, last_name, username FROM tblUser WHERE user_id=?");
        $st3->bind_param('i', $otherId); $st3->execute();
        $threadOther = $st3->get_result()->fetch_assoc(); $st3->close();

        if ($cid) {
            $st4 = $db->prepare("SELECT clothes_id, brand, item_name, image_file FROM tblClothes WHERE clothes_id=?");
            $st4->bind_param('i', $cid); $st4->execute();
            $threadItem = $st4->get_result()->fetch_assoc(); $st4->close();
        }
    }
}

$inbox = [];
$r = $db->query("
    SELECT m.message_id, m.subject, m.body, m.is_read, m.sent_at, m.clothes_id,
           u.first_name, u.last_name, u.username,
           c.brand, c.item_name, c.image_file
    FROM tblMessages m
    JOIN tblUser u ON m.sender_id = u.user_id
    LEFT JOIN tblClothes c ON m.clothes_id = c.clothes_id
    WHERE m.receiver_id = $uid
    ORDER BY m.sent_at DESC
");
if ($r) while ($row = $r->fetch_assoc()) $inbox[] = $row;

$sent = [];
$r = $db->query("
    SELECT m.message_id, m.subject, m.body, m.sent_at, m.is_read, m.clothes_id,
           u.first_name, u.last_name, u.username,
           c.brand, c.item_name, c.image_file
    FROM tblMessages m
    JOIN tblUser u ON m.receiver_id = u.user_id
    LEFT JOIN tblClothes c ON m.clothes_id = c.clothes_id
    WHERE m.sender_id = $uid
    ORDER BY m.sent_at DESC
");
if ($r) while ($row = $r->fetch_assoc()) $sent[] = $row;

$composeTo   = null;
$composeItem = null;
if ($toId > 0) {
    $tab = 'compose';
    $st = $db->prepare("SELECT user_id, first_name, last_name, username FROM tblUser WHERE user_id=?");
    $st->bind_param('i', $toId); $st->execute();
    $composeTo = $st->get_result()->fetch_assoc(); $st->close();
}
if ($itemId > 0) {
    $st = $db->prepare("SELECT clothes_id, brand, item_name, image_file, sell_price FROM tblClothes WHERE clothes_id=?");
    $st->bind_param('i', $itemId); $st->execute();
    $composeItem = $st->get_result()->fetch_assoc(); $st->close();
}

$unread = count(array_filter($inbox, fn($m) => !$m['is_read']));
$db->close();

include 'includes/header.php';
?>

<div class="messages-layout">

  
  <aside class="messages-sidebar">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
      <h2 style="font-size:1.1rem;font-weight:700;">Messages</h2>
      <a href="messages.php?tab=compose" class="btn btn-dark btn-sm">
        <i data-lucide="pencil" class="icon icon-sm"></i> Compose
      </a>
    </div>

    <nav class="msg-tabs">
      <a href="messages.php?tab=inbox"
         class="msg-tab <?= $tab==='inbox'?'active':'' ?>">
        <i data-lucide="inbox" class="icon icon-sm"></i> Inbox
        <?php if ($unread > 0): ?>
          <span class="msg-badge"><?= $unread ?></span>
        <?php endif ?>
      </a>
      <a href="messages.php?tab=sent"
         class="msg-tab <?= $tab==='sent'?'active':'' ?>">
        <i data-lucide="send" class="icon icon-sm"></i> Sent
      </a>
      <a href="messages.php?tab=compose"
         class="msg-tab <?= $tab==='compose'?'active':'' ?>">
        <i data-lucide="plus" class="icon icon-sm"></i> New
      </a>
    </nav>

    
    <div class="msg-list">
      <?php
      $listItems = $tab === 'sent' ? $sent : $inbox;
      if ($tab === 'compose'): ?>
        <p class="text-muted text-sm" style="padding:1rem;text-align:center;">Composing a new message.</p>
      <?php elseif (empty($listItems)): ?>
        <p class="text-muted text-sm" style="padding:1rem;text-align:center;">
          <?= $tab==='inbox' ? 'No messages received yet.' : 'No messages sent yet.' ?>
        </p>
      <?php else: foreach ($listItems as $m):
        $isActive = $viewId === $m['message_id'];
        $unreadDot = ($tab==='inbox' && !$m['is_read']);
      ?>
      <a href="messages.php?tab=<?= $tab ?>&view=<?= $m['message_id'] ?>"
         class="msg-item <?= $isActive?'active':'' ?> <?= $unreadDot?'unread':'' ?>">
        <div class="msg-item-top">
          <span class="msg-item-name">
            <?= $unreadDot ? '<span class="msg-dot"></span>' : '' ?>
            <?= htmlspecialchars($m['first_name'].' '.$m['last_name']) ?>
          </span>
          <span class="msg-item-time"><?= date('d M', strtotime($m['sent_at'])) ?></span>
        </div>
        <p class="msg-item-subject"><?= htmlspecialchars($m['subject']) ?></p>
        <?php if ($m['brand']): ?>
        <p class="msg-item-meta"><i data-lucide="shirt" class="icon" style="width:.75rem;height:.75rem;"></i> <?= htmlspecialchars($m['brand'].' '.$m['item_name']) ?></p>
        <?php endif ?>
      </a>
      <?php endforeach; endif ?>
    </div>
  </aside>

  
  <main class="messages-main">

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flashType==='error'?'error':'success' ?>" style="margin-bottom:1rem;">
      <i data-lucide="<?= $flashType==='error'?'alert-circle':'check-circle' ?>" class="icon"></i>
      <?= htmlspecialchars($flash) ?>
    </div>
    <?php endif ?>

    <?php if ($tab === 'compose' || ($tab !== 'inbox' && $tab !== 'sent' && !$viewId)): ?>
    
    <div class="msg-compose-header">
      <i data-lucide="pencil" class="icon"></i>
      <h3>New Message</h3>
    </div>

    <?php if ($composeItem): ?>
    <div class="msg-item-context">
      <?php if ($composeItem['image_file']): ?>
      <img src="images/<?= htmlspecialchars($composeItem['image_file']) ?>"
           alt="" style="width:48px;height:48px;object-fit:cover;border-radius:.4rem;">
      <?php endif ?>
      <div>
        <p style="font-size:.75rem;font-weight:600;"><?= htmlspecialchars($composeItem['brand'].' — '.$composeItem['item_name']) ?></p>
        <p style="font-size:.72rem;color:var(--muted);">R<?= number_format($composeItem['sell_price'],2) ?></p>
      </div>
    </div>
    <?php endif ?>

    <form method="POST" action="messages.php" class="msg-form">
      <input type="hidden" name="receiver_id" value="<?= $composeTo ? $composeTo['user_id'] : '' ?>">
      <input type="hidden" name="clothes_id"  value="<?= $composeItem ? $composeItem['clothes_id'] : '' ?>">

      <?php if (!$composeTo): ?>
      <div class="form-group">
        <label><i data-lucide="user" class="icon icon-sm"></i> To (username)</label>
        <input type="text" name="to_username" placeholder="e.g. sarah01" required>
        <p class="form-hint">Ask admin for a seller's username or check a listing.</p>
      </div>
      <?php else: ?>
      <div class="msg-to-chip">
        <i data-lucide="user-check" class="icon icon-sm"></i>
        To: <strong><?= htmlspecialchars($composeTo['first_name'].' '.$composeTo['last_name']) ?></strong>
        <span style="opacity:.5;">@<?= htmlspecialchars($composeTo['username']) ?></span>
      </div>
      <?php endif ?>

      <div class="form-group">
        <label><i data-lucide="type" class="icon icon-sm"></i> Subject</label>
        <input type="text" name="subject" required
               value="<?= $composeItem ? htmlspecialchars('Enquiry: '.$composeItem['brand'].' '.$composeItem['item_name']) : '' ?>"
               placeholder="e.g. Is this item still available?">
      </div>
      <div class="form-group">
        <label><i data-lucide="message-square" class="icon icon-sm"></i> Message</label>
        <textarea name="body" rows="6" required
                  placeholder="Write your message here…"></textarea>
      </div>
      <button type="submit" class="btn btn-dark">
        <i data-lucide="send" class="icon icon-sm"></i> Send Message
      </button>
    </form>

    <?php elseif ($viewId && !empty($thread)): ?>
    
    <div class="msg-thread-header">
      <?php if ($threadItem && $threadItem['image_file']): ?>
      <img src="images/<?= htmlspecialchars($threadItem['image_file']) ?>"
           alt="" style="width:44px;height:44px;object-fit:cover;border-radius:.4rem;flex-shrink:0;">
      <?php endif ?>
      <div>
        <h3 style="font-size:1rem;font-weight:700;"><?= htmlspecialchars($thread[0]['subject']) ?></h3>
        <?php if ($threadItem): ?>
        <p style="font-size:.78rem;color:var(--muted);"><?= htmlspecialchars($threadItem['brand'].' — '.$threadItem['item_name']) ?></p>
        <?php endif ?>
        <p style="font-size:.72rem;color:var(--muted);margin-top:.2rem;">
          Conversation with <strong><?= htmlspecialchars($threadOther['first_name'].' '.$threadOther['last_name']) ?></strong>
        </p>
      </div>
      <a href="messages.php?tab=<?= $tab ?>" class="btn btn-outline btn-sm" style="margin-left:auto;">
        <i data-lucide="arrow-left" class="icon icon-sm"></i> Back
      </a>
    </div>

    <div class="msg-thread">
      <?php foreach ($thread as $msg):
        $mine = ((int)$msg['sender_id'] === $uid);
        $name = $mine ? 'You' : htmlspecialchars($msg['sfn'].' '.$msg['sln']);
      ?>
      <div class="msg-bubble-wrap <?= $mine ? 'mine' : 'theirs' ?>">
        <div class="msg-bubble">
          <div class="msg-bubble-meta">
            <strong><?= $name ?></strong>
            <span><?= date('d M Y H:i', strtotime($msg['sent_at'])) ?></span>
          </div>
          <p><?= nl2br(htmlspecialchars($msg['body'])) ?></p>
        </div>
      </div>
      <?php endforeach ?>
    </div>

    
    <div class="msg-reply-box">
      <h4 style="font-size:.85rem;font-weight:600;margin-bottom:.75rem;">
        <i data-lucide="corner-down-right" class="icon icon-sm"></i> Reply
      </h4>
      <form method="POST" action="messages.php?tab=<?= $tab ?>&view=<?= $viewId ?>">
        <input type="hidden" name="receiver_id" value="<?= $threadOther['user_id'] ?>">
        <input type="hidden" name="clothes_id"  value="<?= $threadItem ? $threadItem['clothes_id'] : '' ?>">
        <input type="hidden" name="subject"     value="Re: <?= htmlspecialchars($thread[0]['subject']) ?>">
        <textarea name="body" rows="3" required
                  placeholder="Write your reply…"
                  style="width:100%;border-radius:var(--radius-sm);border:1.5px solid var(--border);padding:.65rem .875rem;font-size:.875rem;resize:vertical;margin-bottom:.75rem;"></textarea>
        <button type="submit" class="btn btn-dark btn-sm">
          <i data-lucide="send" class="icon icon-sm"></i> Send Reply
        </button>
      </form>
    </div>

    <?php else: ?>
    
    <div class="text-center" style="padding:4rem 0;">
      <i data-lucide="message-square" class="icon" style="width:3rem;height:3rem;opacity:.2;margin-bottom:1rem;"></i>
      <p class="text-muted" style="margin-bottom:1.25rem;">
        <?= $tab==='inbox' ? 'Your inbox is empty.' : 'No messages sent yet.' ?>
      </p>
      <a href="shop.php" class="btn btn-dark">
        <i data-lucide="shopping-bag" class="icon icon-sm"></i> Browse the shop to contact a seller
      </a>
    </div>
    <?php endif ?>

  </main>
</div>

<style>
.messages-layout {
  display: grid;
  grid-template-columns: 300px 1fr;
  min-height: 70vh;
  gap: 0;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  margin: 1.5rem;
}
.messages-sidebar {
  border-right: 1px solid var(--border);
  background: var(--surface);
  display: flex; flex-direction: column;
  padding: 1.25rem;
}
.msg-tabs { display: flex; gap: .25rem; margin-bottom: 1rem; }
.msg-tab {
  display: flex; align-items: center; gap: .35rem;
  padding: .4rem .75rem; border-radius: 9999px;
  font-size: .78rem; font-weight: 500; color: var(--muted);
  text-decoration: none; transition: background .15s, color .15s;
}
.msg-tab:hover { background: var(--bg); color: var(--text); }
.msg-tab.active { background: var(--accent); color: var(--accent-fg); }
.msg-badge {
  background: #dc2626; color: #fff;
  font-size: .65rem; font-weight: 700;
  padding: .1rem .35rem; border-radius: 9999px; margin-left: .2rem;
}
.msg-dot {
  display: inline-block; width: .45rem; height: .45rem;
  background: #2563eb; border-radius: 9999px; margin-right: .3rem;
  vertical-align: middle;
}
.msg-list { flex: 1; overflow-y: auto; margin: 0 -.5rem; }
.msg-item {
  display: block; padding: .75rem .875rem;
  border-radius: var(--radius-sm); text-decoration: none;
  color: var(--text); transition: background .12s;
  border-bottom: 1px solid var(--border);
}
.msg-item:hover { background: var(--bg); }
.msg-item.active { background: var(--bg); border-left: 3px solid var(--accent); }
.msg-item.unread .msg-item-subject { font-weight: 600; color: var(--text); }
.msg-item-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: .2rem; }
.msg-item-name { font-size: .82rem; font-weight: 600; }
.msg-item-time { font-size: .7rem; color: var(--muted); }
.msg-item-subject { font-size: .8rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-item-meta { font-size: .7rem; color: var(--muted); margin-top: .2rem; display: flex; align-items: center; gap: .25rem; }

.messages-main { padding: 1.5rem; overflow-y: auto; background: var(--bg); }
.msg-compose-header { display: flex; align-items: center; gap: .5rem; margin-bottom: 1.25rem; }
.msg-compose-header h3 { font-size: 1rem; font-weight: 700; }
.msg-item-context {
  display: flex; align-items: center; gap: .75rem;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius-sm); padding: .75rem 1rem; margin-bottom: 1.25rem;
}
.msg-to-chip {
  display: flex; align-items: center; gap: .4rem;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 9999px; padding: .4rem .875rem;
  font-size: .83rem; margin-bottom: 1rem;
}
.msg-form { display: flex; flex-direction: column; gap: .875rem; max-width: 600px; }
.msg-form .form-group { margin: 0; }

.msg-thread-header {
  display: flex; align-items: center; gap: .875rem;
  padding-bottom: 1rem; margin-bottom: 1rem;
  border-bottom: 1px solid var(--border);
}
.msg-thread { display: flex; flex-direction: column; gap: .875rem; margin-bottom: 1.5rem; }
.msg-bubble-wrap { display: flex; }
.msg-bubble-wrap.mine { justify-content: flex-end; }
.msg-bubble-wrap.theirs { justify-content: flex-start; }
.msg-bubble {
  max-width: 75%; padding: .875rem 1rem;
  border-radius: 1rem; line-height: 1.6; font-size: .875rem;
}
.mine .msg-bubble {
  background: var(--accent); color: var(--accent-fg);
  border-bottom-right-radius: .25rem;
}
.theirs .msg-bubble {
  background: var(--surface); border: 1px solid var(--border);
  border-bottom-left-radius: .25rem;
}
.msg-bubble-meta {
  display: flex; justify-content: space-between; gap: 1rem;
  font-size: .7rem; opacity: .7; margin-bottom: .4rem;
}
.msg-reply-box {
  border-top: 1px solid var(--border); padding-top: 1.25rem;
}

@media (max-width: 700px) {
  .messages-layout { grid-template-columns: 1fr; }
  .messages-sidebar { border-right: none; border-bottom: 1px solid var(--border); max-height: 280px; }
}
</style>

<?php include 'includes/footer.php'; ?>
