<?php
require 'config.php'; require_login();
if ($_SESSION['user']['role']==='admin') redirect('admin.php');
$election=$pdo->query("SELECT * FROM elections ORDER BY id DESC LIMIT 1")->fetch();
$positions=[];
if($election) {
  $s=$pdo->prepare("SELECT p.*, (SELECT COUNT(*) FROM votes v WHERE v.position_id=p.id AND v.voter_id=?) AS voted FROM positions p WHERE p.election_id=? ORDER BY p.id");
  $s->execute([$_SESSION['user']['id'],$election['id']]); $positions=$s->fetchAll();
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Voter dashboard</title><link rel="stylesheet" href="style.css"></head><body>
<header><div class="brand">🗳️ Online Voting System</div><nav><a href="results.php">Live results</a><a href="logout.php">Log out</a></nav></header>
<main><div class="welcome"><div><p class="eyebrow">VOTER DASHBOARD</p><h1>Welcome, <?=e($_SESSION['user']['name'])?></h1><p class="muted">Your vote is private. You can vote only once for each position.</p></div><span class="pill"><?=e($election['status']??'no election')?></span></div>
<?php if(!$election):?><div class="panel">No election has been created yet.</div>
<?php else: ?><section class="panel"><h2><?=e($election['title'])?></h2>
<?php if($election['status']!=='open'):?><p>Voting is currently <?=e($election['status'])?>. You can vote only while the election is open.</p><?php endif;?>
<div class="cards"><?php foreach($positions as $p):?><div class="mini-card"><h3><?=e($p['name'])?></h3><p><?= $p['voted']?'Your vote has been recorded.':'You have not voted for this position yet.' ?></p>
<?php if($election['status']==='open' && !$p['voted']):?><a class="button" href="vote.php?position=<?=$p['id']?>">Vote now</a><?php else:?><span class="pill"><?= $p['voted']?'Voted':'Unavailable' ?></span><?php endif;?></div><?php endforeach;?></div></section><?php endif;?></main></body></html>
