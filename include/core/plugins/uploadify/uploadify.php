<?php
/*
Uploadify v2.1.0
Release Date: August 24, 2009

Copyright (c) 2009 Ronnie Garcia, Travis Nickels

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/


$post_cookie = explode(';', $_POST['cookie']);
$sid = '';

if (count($post_cookie) > 0) {
	foreach ($post_cookie as $value) {
		$keyvalue = explode('=', $value);
		if (trim($keyvalue[0]) == 'PHPSESSID') {
			$sid = trim($keyvalue[1]);
		}
	}
} else {
	$post_cookie = explode('=', $_POST['cookie']);
	if(trim($post_cookie[0]) == 'PHPSESSID')
		$sid = trim($post_cookie[1]);
}

session_id(filter_var($sid, FILTER_SANITIZE_SPECIAL_CHARS));

require_once '../../../critical.php';
require_once ROOT .'classes/simpleimage.php';

$image = new SimpleImage();

if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetFile = $user->getUploadFolder() . $_FILES['Filedata']['name'];
	
	$fileTypes  = 'jpg|jpeg|png|gif|pdf';
	$typesArray = explode('|',$fileTypes);
	$fileParts  = pathinfo($_FILES['Filedata']['name']);
	$fileExt	= strtolower($fileParts['extension']);
	
	if (in_array($fileExt,$typesArray)) {
		if(!is_dir($user->getUploadFolder())) {
			mkdir($user->getUploadFolder());
		}

		if(move_uploaded_file($tempFile,$targetFile))
			echo "1";
		else
			echo CRITICAL_ERROR;
	} else {
	 	echo 'Invalid file type';
	}
}
?>
