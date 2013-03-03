<?php

			if(TESTING) {
				$page_load_testing_ended = microtime(true);

				$page_load_total_time = $page_load_testing_ended - $page_load_testing_started;

				?>
				<p>Loaded: <?php echo number_format($page_load_total_time, 3); ?></p>
				<?php
			}
			?>
