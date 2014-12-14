<?php

	// create a link to the Settings page of this plugin
	$settings_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Settings");

	// get all custom options
	$spinrewriter_settings_array = $this->getOption("spinrewriter_settings");
	$action_log_array = $this->getOption("action_log");



	// handle POST DATA



	// UPDATE the custom options if there is POST data (if the manual spinning form was submitted)
	if ($_POST['spinrewriter_manual_spinning_submit_button']) {

		$wp_error_message = "";

		// the highest allowed frequency of spinning old posts is one per 30 seconds
		$delay_required = false;
		if (is_array($action_log_array) && count($action_log_array) > 0) {
			$count_action_log_array = count($action_log_array) - 1;
			$last_action_log_entry = $action_log_array[$count_action_log_array];
			if ($last_action_log_entry[1] > (time() - 30)) {
				$delay_required = true;
			}
		}

		if (!$delay_required) {
			// find a suitable post that meets the criteria to be spun and re-published
			$suitable_post = $this::findSuitablePost();

			if ($suitable_post !== false) {
				// we found a suitable post, it's stored in $suitable_post

				// process this post in the requested way
				$processing_return_value = $this::processSuitablePost($suitable_post, "manual");

				// update the action log so it shows the most recent changes on page load
				$action_log_array = $this->getOption("action_log");

				if ($processing_return_value['status'] == "OK") {
					$wp_success_message = $processing_return_value['message'];
				} else {
					$wp_error_message = $processing_return_value['message'];
				}
			} else {
				// there are NO suitable posts at the moment
				$spin_after_x_days_readable_format = $this::convertNumberOfDaysIntoReadableFormat($spinrewriter_settings_array['spin_after_x_days']);
				$wp_error_message = __("There are currently no suitable posts, older than {$spin_after_x_days_readable_format}.", "spin-rewriter-wp-plugin");
			}
		} else {
			// a short delay is required
			$wp_error_message = __("Please wait at least 30 seconds before submitting your request.", "spin-rewriter-wp-plugin");
		}
	}










	// create PAGE HTML

	// show a SUCCESS or ERROR message
	$wp_message_output = "";
	if ($wp_success_message) {
		$wp_message_output = "<div class='updated'><p><strong>{$wp_success_message}</strong></p></div>";
	} else if ($wp_error_message) {
		$wp_message_output = "<div class='error' style='color:#DD3D36;'><p><strong>{$wp_error_message}</strong></p></div>";
	}

?>
	<div class="wrap">
		<h2><?php _e("Spin and Re-Publish Old Posts", "spin-rewriter-wp-plugin"); ?></h2>

		<?php echo $wp_message_output; ?>

		<?php
		// has this user set up his Spin Rewriter API credentials yet?
		if (strpos($spinrewriter_settings_array['email_address'], "@") === false || !$spinrewriter_settings_array['api_key']) {
			$api_notification = '<b>IMPORTANT:</b>
				In order to automatically spin and re-publish your old posts, you will need to
				<b>enter your Spin Rewriter email address and API key</b>
				on the <a href="' . $settings_page_url . '" title="Spin Rewriter Plugin - Settings">Settings page</a>.';
			echo "<div class='error' style='color:#DD3D36;'><p><strong>{$api_notification}</strong></p></div>";
		}



		// output an overview of the current settings and queued posts
		$spin_after_x_days_readable_format = $this::convertNumberOfDaysIntoReadableFormat($spinrewriter_settings_array['spin_after_x_days']);

		// find the next suitable post that meets the criteria to be spun and re-published
		$suitable_post = $this::findSuitablePost();

		$spin_posts_when = ucwords($spin_after_x_days_readable_format) . " Old";
		$republish_type = (intval($spinrewriter_settings_array['publish_as_new']) == 1) ? "As a New Post" : "Replace the Old Post";

		$next_post_in_queue = "No posts are {$spin_after_x_days_readable_format} old at the moment.";
		if ($suitable_post !== false) {
			$wp_post_id = $suitable_post->ID;
			$wp_post_title = (strlen($suitable_post->post_title) > 120) ? substr($suitable_post->post_title, 0, 117) . "..." : $suitable_post->post_title;
			$wp_post_date_local = date_i18n(get_option("date_format"), strtotime($suitable_post->post_date_gmt));
			$wp_post_edit_url = admin_url("post.php?post={$wp_post_id}&action=edit");
			$next_post_in_queue = "<a href='{$wp_post_edit_url}'><i>{$wp_post_title}</i></a>";
			$next_post_in_queue .= " <span style='font-size:80%; color:#343434;'>from {$wp_post_date_local}</span>";
		}

		$mode_output = (intval($spinrewriter_settings_array['run_in_background']) == 1) ? "Automated" : "Manual";

		$action_tr_contents = "";
		if ($suitable_post !== false) {
			if (intval($spinrewriter_settings_array['run_in_background']) == 1) {
				// explain that the next post will be processed within an hour
				$action_tr_contents = "<i>Your queued post will be processed automatically within an hour.</i>";
			} else {
				// show the button for manual processing
				$action_tr_contents = '<form action="" method="post">';
				$action_tr_contents .= '<input type="submit" name="spinrewriter_manual_spinning_submit_button" id="spinrewriter_manual_spinning_submit_button" class="button button-primary" ';
				$action_tr_contents .= 'value="' . __("Spin and Re-Publish This Old Post", "spin-rewriter-wp-plugin") . '" /></form>';
			}
		}
		?>

		<table border="0" cellspacing="0" cellpadding="7" class="widefat" style="margin:17px 0px 7px 0px; max-width:659px;">
			<tr>
				<td align="right" style="width:150px;"><b>Mode:</b></td>
				<td style="max-width:509px;"><?php echo $mode_output; ?></td>
			</tr>
			<tr class="alternate">
				<td align="right"><b>Spin Posts When:</b></td>
				<td><?php echo $spin_posts_when; ?></td>
			</tr>
			<tr>
				<td align="right"><b>Re-Publish:</b></td>
				<td><?php echo $republish_type; ?></td>
			</tr>
			<tr class="alternate">
				<td align="right"><b>Next Post in Queue:</b></td>
				<td><?php echo $next_post_in_queue; ?></td>
			</tr>
			<?php
			if ($action_tr_contents) {
			?>
			<tr>
				<td>&nbsp;</td>
				<td><?php echo $action_tr_contents; ?></td>
			</tr>
			<?php
			} // END :: if ($action_tr_contents)
			?>
		</table>
		&nbsp;Visit the
		<a href="<?php echo $settings_page_url; ?>" title="Spin Rewriter Plugin - Settings">Settings page</a>
		to make changes to these settings.



		<br /><br /><br />
		<h2><?php _e("Log of Recent Spinning Activity", "spin-rewriter-wp-plugin"); ?></h2>

		<br />
		<table class="widefat fixed" cellspacing="0">
			<thead>
			<tr>
				<th id="columnname" class="manage-column column-columnname" scope="col">Event Description</th>
				<th id="columnname" class="manage-column column-columnname" scope="col" width="190" style="width:190px;">Time of Event</th>

			</tr>
			</thead>
			<tbody>

		<?php
			$counter = 0;
			$counter_max_display = 30;
			$action_log_array = array_reverse($action_log_array);
			if (is_array($action_log_array) && count($action_log_array) > 0) {
				foreach ($action_log_array as $action_log_entry) {
					if ($action_log_entry[1] == true) {
						$counter++;
						if ($counter < $counter_max_display) {
							$tr_class = ($counter % 2 == 1) ? ' class="alternate"' : '';
							$action_log_entry_date_local = date_i18n(get_option("date_format"), $action_log_entry[2]);
							$action_log_entry_date_local .= " " . __("at", "spin-rewriter-wp-plugin") . " ";
							$action_log_entry_date_local .= date_i18n("H:i", $action_log_entry[2]);
							echo '<tr' . $tr_class . '>
								<td class="column-columnname">' . $action_log_entry[0] . '</td>
								<td class="column-columnname">' . $action_log_entry_date_local . '</td>
							</tr>';
						}
					}
				}
			}
		?>

			</tbody>
		</table>

		<br />
	</div>