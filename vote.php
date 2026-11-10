<?php
require 'config.php'; require_role('voter');
$election=$pdo->query("SELECT * FROM elections ORDER BY id DESC LIMIT 1")->fetch();
$pid=(int)($_GET['position']??$_POST['position_id']??0);
$s=$pdo->prepare("SELECT * FROM positions WHERE id=? AND election_id=?"); $s->execute([$pid,$election['id']??0]); $position=$s->fetch();
if(!$election || $election['status']!=='open' || !$position) exit('Voting is not available for this position.');
$s=$pdo->prepare("SELECT id FROM votes WHERE voter_id=? AND election_id=? AND position_id=?");
$s->execute([$_SESSION['user']['id'],$election['id'],$pid]);
if($s->fetch()) exit('A vote for this position has already been recorded. <a href=\"dashboard.php\">Return</a>');
$c=$pdo->prepare("SELECT * FROM candidates WHERE position_id=? ORDER BY full_name"); $c->execute([$pid]); $candidates=$c->fetchAll();
$error='';
if($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf(); $cid=(int)($_POST['candidate_id']??0);
  $valid=$pdo->prepare("SELECT id FROM candidates WHERE id=? AND position_id=?"); $valid->execute([$cid,$pid]);
  if(!$valid->fetch()) $error='Please choose a valid candidate.';
  else {
    try {
      $ins=$pdo->prepare("INSERT INTO votes(voter_id,election_id,position_id,candidate_id) VALUES(?,?,?,?)");
      $ins->execute([$_SESSION['user']['id'],$election['id'],$pid,$cid]);
      redirect('dashboard.php?voted=1');
    } catch(PDOException $ex) { $error='Your vote may already have been submitted. Each voter can vote only once per position.'; }
  }
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Cast vote</title><link rel="stylesheet" href="style.css"></head><body><main class="narrow"><a href="dashboard.php">← Dashboard</a><div class="panel"><p class="eyebrow">BALLOT</p><h1><?=e($position['name'])?></h1><p class="muted">Select one candidate. Your selection cannot be changed after submission.</p>
<?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?>
<form method="post" onsubmit="return confirm('Submit your vote? You cannot vote again for this position.');"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="position_id" value="<?=$pid?>">
<?php foreach($candidates as $candidate):?><label class="candidate"><input type="radio" name="candidate_id" value="<?=$candidate['id']?>" required><span><strong><?=e($candidate['full_name'])?></strong><small><?=e($candidate['platform'])?></small></span></label><?php endforeach;?>
<?php if($candidates):?><button class="full">Submit vote</button><?php else:?><p>No candidates added for this position.</p><?php endif;?></form></div></main></body></html>
