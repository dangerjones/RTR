<?php
require_once 'include/critical.php';
require_once ROOT .'classes/event.php';
require_once 'Spreadsheet/Excel/Writer.php';

$e_id = (int)$_GET['e'];
$event = new Event($e_id);
$exists = $util->eventExists($e_id);
$can_edit = $event->hasAccess($user->getId());

if(!$exists || !$can_edit)
	die('Invalid request');


$regs = $event->getPaidRegistrants();
usort($regs, array('Registrant', 'cmp_byLName'));

$workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
$workbook->send($util->filenameSafe($event->getDecodedName()) .'_'. date('m.d.y_H-i') .'.xls');

// Make worksheet
$worksheet =& $workbook->addWorksheet('Event Registrants');

// Write header
$format_title =& $workbook->addFormat(array('Size' => 24));

$format_head =& $workbook->addFormat(array('Size' => 13,
                                      'Align' => 'center',
                                      'Color' => 'white',
                                      'FgColor' => 'green'));
$header = array(
	'Last name', 'First name', 'Email', 'Race', 'Address', 'Address 2', 'City', 'State', 'Zip',
	'Phone', 'Birthday', 'Age', 'Sex', 'Shirt size', 'No-shirt discount', 'Completed on', 'Total paid', 'Discounts',
	'Coupon', 'Answers'
		);

$worksheet->write(0, 0, $event->getDecodedName(), $format_title);
for($i = 0; $i < count($header); $i++) {
	$worksheet->write(1, $i, $header[$i], $format_head);
}

// Write data
for($i = 0; $i < count($regs); $i++) {
	$j = 0;
	$k = $i+2;
	$worksheet->write($k, $j++, $regs[$i]->getLName());
	$worksheet->write($k, $j++, $regs[$i]->getFName());
	$worksheet->write($k, $j++, $regs[$i]->getEmail());
	$worksheet->write($k, $j++, $regs[$i]->getRace()->getName());
	$worksheet->write($k, $j++, $regs[$i]->getAddr());
	$worksheet->write($k, $j++, $regs[$i]->getAddr2());
	$worksheet->write($k, $j++, $regs[$i]->getCity());
	$worksheet->write($k, $j++, $regs[$i]->getState());
	$worksheet->write($k, $j++, $regs[$i]->getZip());
	$worksheet->write($k, $j++, $regs[$i]->getfPhone());
	$worksheet->write($k, $j++, $regs[$i]->getfBday());
	$worksheet->write($k, $j++, $regs[$i]->getAge());
	$worksheet->write($k, $j++, $regs[$i]->getGender());
	$worksheet->write($k, $j++, $regs[$i]->hasShirt() ? (strtoupper($regs[$i]->getShirtSize()) . ($regs[$i]->isAdultSize() ? '':' (YOUTH)')):'');
	$worksheet->write($k, $j++, $regs[$i]->hasShirt() ? '':$util->money($regs[$i]->getDiscounts() - ($regs[$i]->hasCoupon() ? $regs[$i]->getCoupon()->getAmount():0)));
	$worksheet->write($k, $j++, $regs[$i]->getPaidDate('M jS, h:i a'));
	$worksheet->write($k, $j++, $util->money($regs[$i]->getTotalPaid()));
	$worksheet->write($k, $j++, $regs[$i]->hasDiscounts() ? $util->money($regs[$i]->getDiscounts()):'');
	$worksheet->write($k, $j++, $regs[$i]->hasCoupon() ? $regs[$i]->getCoupon()->getName() . ' ('. $util->money($regs[$i]->getCoupon()->getAmount()) .')':'');
	$worksheet->write($k, $j++, $regs[$i]->spreadsheetAnswers());
}

// Send the file
$workbook->close();
?>
