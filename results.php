<?php
require 'config.php'; require_login();
$election=$pdo->query("SELECT * FROM elections ORDER BY id DESC LIMIT 1")->fetch();
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta http-equiv="refresh" content="15"><title>Live results</title><link rel="stylesheet" href="style.css"></head><body><header><div class="brand">🗳️ Online Voting System</div><nav><a href="dashboard.php">Dashboard</a><a href="logout.php">Log out</a></nav></header><main><p class="eyebrow">RESULTS REPORT</p><h1>Live results by position</h1><p class="muted">This page refreshes every 15 seconds. Results are visible to logged-in users.</p>
<?php if(!$election):?><div class="panel">No election yet.</div><?php else:?><h2><?=e($election['title'])?> <span class="pill"><?=e($election['status'])?></span></h2>
<?php
$ps=$pdo->prepare("SELECT * FROM positions WHERE election_id=? ORDER BY id");$ps->execute([$election['id']]);
foreach($ps as $p):
$q=$pdo->prepare("SELECT c.full_name, COUNT(v.id) AS total FROM candidates c LEFT JOIN votes v ON v.candidate_id=c.id WHERE c.position_id=? GROUP BY c.id ORDER BY total DESC,c.full_name");$q->execute([$p['id']]);$rows=$q->fetchAll();$total=array_sum(array_column($rows,'total'));
?>
<section class="panel"><h2><?=e($p['name'])?></h2><?php if(!$rows):?><p>No candidates.</p><?php endif;?>
<?php foreach($rows as $r):$pct=$total?round($r['total']/$total*100,1):0;?>
<div class="result-row"><div class="result-label"><strong><?=e($r['full_name'])?></strong><span><?= (int)$r['total']?> votes · <?=$pct?>%</span></div><div class="bar"><span style="width:<?=$pct?>%"></span></div></div><?php endforeach;?>
<p class="muted">Total votes cast for this position: <?=$total?></p></section><?php endforeach; endif;?></main></body></html>
