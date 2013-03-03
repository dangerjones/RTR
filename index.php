<?php
require_once 'include/critical.php';
require_once ROOT .'classes/event.php';

$month = isset($_REQUEST['month']) ? (int)$_REQUEST['month'] : 0;
$year = isset($_REQUEST['year']) ? (int)$_REQUEST['year'] : 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>Run That Race - Events</title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
	<!-- Events -->
    <link rel="stylesheet" type="text/css" href="/include/events/events.css" />
    <script type="text/javascript" src="/include/events/events.js"></script>
    <script type="text/javascript" src="/include/events/addevent.js"></script>

	<!-- Uploadify plugin -->
	<script type="text/javascript" src="/include/core/plugins/uploadify/swfobject.js"></script>
	<script type="text/javascript" src="/include/core/plugins/uploadify/jquery.uploadify.v2.1.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/include/core/plugins/uploadify/uploadify.css" />
	<script type="text/javascript">
		$(document).ready(function() {
			if(location.hash.length > 0) {
				var hash = location.hash.substr(1)*1;
				if(!isNaN(hash)) {
					$('#eventid-'+hash).closest('li').click();
				}
			}
		});
	</script>
	<script type="text/javascript" src="/include/base.js"></script>
</head>
<body><?php
	include ROOT .'include/core/navigation.php';
	?>
	<div id="wrapper">
		<?php
		include ROOT .'include/core/head.php';
		?>
		<div id="content">
			<?php include 'include/splash.php'; ?>
			<?php include 'include/events/events.php'; ?>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
		<img src="/img/ajax-loader.gif" alt="" class="no-show" />
	</div>
</body>
</html>

<?php

function printCalendar($year, $month) {
	if($year == 0)
		$year = date('Y');
	if($month == 0)
		$month = date('m');
		
	for($i = $month-1; $i < $month+11; $i++) {
		
		echo '<div class="month-container">'. "\n";
		printMonth($i > 12 ? $i-12:$i, $year, $month);
		echo '</div>'. "\n";
	}

}

function printMonth($month, $year, $cMonth, $day = 1) {
    global $eventList; // included: include/events/events.php

    $unix = mktime(0, 0, 0, $month, $day, $year);
    $monthName = date('F', $unix);
    $lastDay = (int)date('t', $unix);
    $date = getdate($unix);
    $fDayLocation = $date['wday'];
    $hide = '';

    if($month == $cMonth || $month == $cMonth-1 || $month == $cMonth+1);
    else
        $hide = ' no-show';
    
    echo <<<BLOCK
    <table class="$month-$year$hide" id="calendar-month-$month-$year">
        <caption>$monthName $year</caption>
        <thead>
            <tr>
                <th>Su</th>
                <th>Mo</th>
                <th>Tu</th>
                <th>We</th>
                <th>Th</th>
                <th>Fr</th>
                <th>Sa</th>
            </tr>
        </thead>
        <tbody>
            <tr>
BLOCK;
            for($i = 0; $i < $fDayLocation; $i++) // print padding before first day
                echo '<td class="prev-month">--</td>'. "\n";
                
            while($fDayLocation++ < 7) // finish first row
                echo calendarCell($month, $day, $eventList->isEvent($month, $day++, $year));
            
            echo '</tr>'. "\n";
            
            $row = ($lastDay-$day+1)/7; // calculate number of rows required  
			if(is_Float($row)) {
				$row += 1;
				$row = (int)$row;
			}
            for($i = 0; $i < $row; $i++) { // print rest of calendar
                echo '<tr>'. "\n";
                
                $j = 0;
                while($day < $lastDay+1 && $j++ < 7)
                    echo calendarCell($month, $day, $eventList->isEvent($month, $day++, $year));
                    
                if($i == $row-1) // add padding to last row
                    while($j++ < 7)
                        echo '<td class="prev-month">--</td>'. "\n";
                    
                echo '</tr>'. "\n";
            }
            
            echo '</tbody>
            </table>';
}

function calendarCell($month, $day, $isEvent) {
    $cell = '<td id="day-'. $month .'-'. $day .'" class="'. $month .'-'. $day;
    $cell .= $isEvent ? ' is-event' : '';
    $cell .= '">'. $day . '</td>'. "\n";
        
    return $cell;
}

?>
