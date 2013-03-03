<?php
require_once '../critical.php';

if(!$util->isAjax())
	die('Unauthorized');

$p_id = (int)$_POST['person_id'];

$query = 'DELETE FROM '. TABLE_E_REGISTRANTS .' WHERE id='. $p_id .' AND user_id='. $user->getId() .
	' AND payment+fee=0';
$sql->q($query);

?>
