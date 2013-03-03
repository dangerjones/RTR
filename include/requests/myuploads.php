<?php
require_once '../critical.php';

if(!$util->isAjax() || !$user->loggedIn())
	die('Unauthorized');

$dir = $user->getUploadFolder();
$filter = array('.', '..');

$files = array();
if(is_dir($dir)) {
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
		if(!in_array($file, $filter)) {
			$file_info = pathinfo($dir . $file);
			$files[] = array('filename' => $file, 'type' => $file_info['extension'], 'path' => UPLOAD_FOLDER . $user->makeUploadedFilePath($file));
		}
	}
		closedir($dh);
	}
}

echo json_encode($files);
?>