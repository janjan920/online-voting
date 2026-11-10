<?php
require 'config.php'; require_role('admin');
$msg=''; $err='';
if($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf(); $action=$_POST['action']??'';
 try {
  if($action==='status') {
    $status=$_POST['status']??'draft';
    if(!in_array($status,['draft','open','closed'],true)) throw new Exception('Invalid election status.');
    $pdo->prepare("UPDATE elections SET status=? WHERE id=?")->execute([$status,(int)$_POST['election_id']]);
    $msg='Election status updated.';
  } elseif($action==='approve') {
    $pdo->prepare("UPDATE users SET status='approved' WHERE id=? AND role='voter'")->execute([(int)$_POST['user_id']]); $msg='Voter account approved.';
  } elseif($action==='reject') {
    $pdo->prepare("UPDATE users SET status='rejected' WHERE id=? AND role='voter'")->execute([(int)$_POST['user_id']]); $msg='Voter account rejected.';
  } elseif($action==='election') {
    $title=trim($_POST['title']??'');
    if($title==='') throw new Exception('Enter an election title.');
    $pdo->prepare("INSERT INTO elections(title,status) VALUES(?,'draft')")->execute([$title]); $eid=(int)$pdo->lastInsertId();
    $defaults=['President','Vice President','Secretary'];
    foreach($defaults as $name) $pdo->prepare("INSERT INTO positions(election_id,name) VALUES(?,?)")->execute([$eid,$name]);
    $msg='Election created with President, Vice President, and Secretary positions. Add candidates below.';
  } elseif($action==='candidate') {
    $position=(int)$_POST['position_id']; $name=trim($_POST['candidate_name']??''); $platform=trim($_POST['platform']??'');
    if($name==='') throw new Exception('Enter candidate name.');
    $pdo->prepare("INSERT INTO candidates(position_id,full_name,platform) VALUES(?,?,?)")->execute([$position,$name,$platform]); $msg='Candidate added.';
  }
 } catch(Throwable $e) { $err=$e->getMessage(); }
}
$election=$pdo->query("SELECT * FROM elections ORDER BY id DESC LIMIT 1")->fetch();
$pending=$pdo->query("SELECT id,full_name,username,created_at FROM users WHERE role='voter' AND status='pending' ORDER BY created_at")->fetchAll();
$positions=[]; if($election){$s=$pdo->prepare("SELECT * FROM positions WHERE election_id=?");$s->execute([$election['id']]);$positions=$s->fetchAll();}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin dashboard</title><link rel="stylesheet" href="style.css"></head><body><header><div class="brand">🗳️ Online Voting System <span class="pill">ADMIN</span></div><nav><a href="results.php">Results report</a><a href="logout.php">Log out</a></nav></header><main><p class="eyebrow">ADMIN CONTROL PANEL</p><h1>Welcome, <?=e($_SESSION['user']['name'])?></h1>
<?php if($msg):?><div class="success"><?=e($msg)?></div><?php endif;?><?php if($err):?><div class="alert"><?=e($err)?></div><?php endif;?>
<div class="cards"><section class="panel"><h2>Create election</h2><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="election"><label>Election title</label><input name="title" placeholder="e.g. Student Council Election 2026" required><button>Create election</button></form><p class="tiny">Creating a new election adds three default positions.</p></section>
<section class="panel"><h2>Pending voter accounts</h2><?php if(!$pending):?><p>No pending registrations.</p><?php else: foreach($pending as $u):?><div class="pending"><span><strong><?=e($u['full_name'])?></strong><small>@<?=e($u['username'])?></small></span><form method="post" class="inline"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="user_id" value="<?=$u['id']?>"><button name="action" value="approve" class="small">Approve</button><button name="action" value="reject" class="small secondary">Reject</button></form></div><?php endforeach; endif;?></section></div>
<?php if($election):?><section class="panel"><div class="space"><div><h2><?=e($election['title'])?></h2><p class="muted">Current status: <strong><?=e($election['status'])?></strong></p></div><a href="results.php">View report →</a></div>
<form method="post" class="inline"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="status"><input type="hidden" name="election_id" value="<?=$election['id']?>"><select name="status"><option value="draft">Draft</option><option value="open">Open voting</option><option value="closed">Close voting</option></select><button>Update status</button></form>
<h3>Manage candidates</h3><div class="cards"><?php foreach($positions as $p):?><div class="mini-card"><strong><?=e($p['name'])?></strong><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="candidate"><input type="hidden" name="position_id" value="<?=$p['id']?>"><label>Candidate name</label><input name="candidate_name" required><label>Short platform (optional)</label><textarea name="platform" rows="2"></textarea><button>Add candidate</button></form><?php $cs=$pdo->prepare("SELECT full_name FROM candidates WHERE position_id=?");$cs->execute([$p['id']]);foreach($cs as $c):?><p class="tiny">• <?=e($c['full_name'])?></p><?php endforeach;?></div><?php endforeach;?></div></section><?php else:?><div class="panel"><p>No election created. Create one above to begin.</p></div><?php endif;?>
<section class="panel"><h2>System workflow</h2><ol><li>Voter registers → account is <b>Pending</b>.</li><li>Admin approves → account becomes <b>Approved</b>.</li><li>Admin creates election and candidates → election starts as <b>Draft</b>.</li><li>Admin opens voting → approved voters may cast one vote per position.</li><li>System blocks repeat votes automatically using a database unique rule.</li><li>Admin closes voting → ballots are locked; results report remains viewable.</li></ol></section></main></body></html>
