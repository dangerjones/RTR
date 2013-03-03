<?php
if(defined('ROOT'))
	require_once ROOT. 'include/critical.php';
else
	require_once '../critical.php';

if(!$user->loggedIn())
	die('Unauthorized');

echo '<p>Use previously saved information: <span class="small-note">(optional)</span> ';
echo '<select name="register_friend" id="saved-info"><option disabled="disabled">Choose an individual</option>';

$friends		= $user->getFriends();
$num_friends	= count($friends);

if(!$user->hasPersonalInfo() && $num_friends == 0)
	echo '<option disabled="disabled">None available</option>';

if($user->hasPersonalInfo())
	echo '<option value="0">Myself</option>';

if($num_friends > 0) {
	foreach($friends as $f)
		echo '<option value="'. $f->getId() .'">'. $f->getFullName() .'</option>';
}

echo '</select> <span id="overwrite-text"></span></p>';
?>
