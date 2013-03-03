			<p>Store Hours: Mon 10-7, Tues-Fri 10-8, Sat 10-7; Closed Sunday</p>
			<p>91 East 100 North, American Fork, UT 84003<br />(801) 756-4747</p>
			<p>1270 North State St., Provo, UT 84604<br />(801) 822-5241</p>
			<?php

			if(TESTING) {
				$page_load_testing_ended = microtime(true);

				$page_load_total_time = $page_load_testing_ended - $page_load_testing_started;

				?>
				<p>Loaded: <?php echo number_format($page_load_total_time, 3); ?></p>
				<?php
			}
			?>