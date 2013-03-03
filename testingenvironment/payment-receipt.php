<?php
require_once 'include/critical.php';

$user_payments	= $user->getPayments();
$num_payments	= count($user_payments);


if(!isset($_GET['id']) && $num_payments > 0) {
	
	$id = $user_payments[0]->getId();
	header("location: ". $_SERVER['PHP_SELF'] . '?id='. $id);
	die();
}
$payment_id = (int)$_GET['id'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title><?php echo TITLE_PREFIX; ?>Payment Receipt</title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
</head>
<body><?php
	include ROOT .'include/core/navigation.php';
	?>
	<div id="wrapper">
		<?php
		include ROOT .'include/core/head.php';
		?>
		<div id="content">
			<?php
			if($user->loggedIn() && $num_payments > 0) {
				if(!array_key_exists($payment_id, $user_payments))
					echo '<h2>Payments</h2><p>Invalid payment ID</p>';
				else {
					$payment = new Payment($payment_id);
					?>
					<h2 class="float-l">Payment Complete!</h2>
					<?php
					
					if($payment->isRecent(900)) { ?>
						<div class="success">
							<p>Thank You! You have successfully completed a payment of
							<strong><?php echo $util->money($payment->getAmt()); ?></strong>!
							Your registration is now complete!</p>
							<p>You can find additional details on your payment below.</p>
						</div><?php
					}

					echo '<h3>[ '.  date('M n, Y', strtotime($payment->getDate()))
						.' ] Transaction #: '. $payment->getTransaction() .'</h3>';
					echo '<p class="indent-1"><strong>Time:</strong> '. date('g:i a', strtotime($payment->getTime())) .'</p>';
					echo '<p class="indent-1"><strong>Total:</strong> '. $util->money($payment->getAmt()) .'</p>';
					echo '<p class="indent-1"><strong>Registrant(s):</strong> '. implode(', ', $payment->getRegistrants()) .'</p>';
					echo '<p class="indent-1"><strong>Event(s):</strong> ';

					$events = array();
					foreach($payment->getRegistrants() as $reg) {
						$event_obj = $reg->getEvent();
						$e_id_list = $event_obj->getId();

						if(!array_key_exists($e_id_list, $events))
							$events[$e_id_list] = $event_obj;
					}

					$num = count($events);
					$idx = 0;
					foreach($events as $e) {
						$idx++;
						echo '<a href="/events/'. $e->getPermalink() .'">'. $e->getName() .'</a>';
						if($idx < $num)
							echo ', ';

					}

					echo '</p>';
				}
			}
			else if(!$user->loggedIn())
				include ROOT .'include/unauthorized.php';
			else if($num_payments == 0)
				echo '<h2>Payments</h2><p>You have not made any payments.</p>';
			?>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
	</div>
</body>
</html>