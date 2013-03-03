<form action="" method="">
	<p>
		To use a file, you must first upload it and then select it through "my uploads". To start, click on "Upload a file".
		Once the upload completes, click on "Change" or "Add" under the area you would like to use the file. This will bring up
		"My uploads" which is a collection of all your uploaded files.
	</p>
	<input type="file" name="upload_file" id="edit-upload-new-file" />
</form>
<form action="" method="post" id="edit-event-edit-uploads">
	<ul>
		<li>
			<label for="edit-upload-banner" class="level-1">Banner:</label>
			<input type="text" name="banner" id="edit-upload-banner" readonly="readonly" value="<?php echo $event->getBannerFileName(); ?>" size="50" />
			<?php
			if($event->hasBanner()) {
				$no_show = '';
				$button_text = 'Change';
			} else {
				$no_show = ' no-show';
				$button_text = 'Add';
			}
			?>
			<img src="/img/round-cancel.png" alt="Remove" class="remove-file point<?php echo $no_show; ?>" title="Remove" />
			<a href="<?php echo $event->getBannerPath(); ?>" class="button fancybox view-uploaded-preview<?php echo $no_show; ?>">View</a>
			<a class="change-file button" rel="banner"><?php echo $button_text; ?></a>
		</li>
		<li>
			<label for="edit-upload-entry-form" class="level-1">Entry form:</label>
			<input type="text" name="form" id="edit-upload-entry-form" readonly="readonly" value="<?php echo $event->getEntryFormFileName(); ?>" size="50" />
			<?php
			if($event->hasEntryForm()) {
				$no_show = '';
				$button_text = 'Change';
			} else {
				$no_show = ' no-show';
				$button_text = 'Add';
			}
			?>
			<img src="/img/round-cancel.png" alt="Remove" class="remove-file point<?php echo $no_show; ?>" title="Remove" />
			<a href="<?php echo $event->getEntryFormPath(); ?>" target="_blank" class="button fancybox view-uploaded-preview<?php echo $no_show; ?>">View</a>
			<a class="change-file button" rel="entry-form"><?php echo $button_text; ?></a>
		</li>
		<li>
			<label for="edit-upload-course-map" class="level-1">Course map:</label>
			<input type="text" name="map" id="edit-upload-course-map" readonly="readonly" value="<?php echo $event->getCourseMapFileName(); ?>" size="50" />
			<?php
			if($event->hasCourseMap()) {
				$no_show = '';
				$button_text = 'Change';
			} else {
				$no_show = ' no-show';
				$button_text = 'Add';
			}
			?>
			<img src="/img/round-cancel.png" alt="Remove" class="remove-file point<?php echo $no_show; ?>" title="Remove" />
			<a href="<?php echo $event->getCourseMapPath(); ?>" class="button fancybox view-uploaded-preview<?php echo $no_show; ?>">View</a>
			<a class="change-file button" rel="course-map"><?php echo $button_text; ?></a>
		</li>
	</ul>
</form>