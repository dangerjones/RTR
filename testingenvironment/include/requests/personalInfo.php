<?php
require_once '../critical.php';

$person_id = (int)$_POST['person_id'];

if($person_id > 0) {
	$query = 'SELECT * FROM '. TABLE_FRIENDS_FAMILY .' WHERE user_id='. $user->getId() .
			' AND id='. $person_id;
	$info = $sql->getAssocRow($query);
} else {
	$query = 'SELECT * FROM '. TABLE_PERSONAL .' WHERE user_id='. $user->getId();
	$info = $sql->getAssocRow($query);
}

if(isset($info['birthday'])) {
	$bday = explode('-', $info['birthday']);
	$info['birthday'] = $bday[1] .'/'. $bday[2] .'/'. $bday[0];
}

if(isset($info['phone'])) {
	$phone = $info['phone'];
	$info['phone'] = substr($phone, 0, 3);
	$info['phone2'] = substr($phone, 3, 3);
	$info['phone3'] = substr($phone, 6, 4);
}

echo json_encode($info);

?>