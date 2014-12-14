<?php

	// create a link to the Settings page of this plugin
	$settings_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Settings");

	// get all custom options
	$spinrewriter_settings_array = $this->getOption("spinrewriter_settings");
	$bigcontentsearch_settings_array = $this->getOption("bigcontentsearch_settings");
	$action_log_array = $this->getOption("action_log");



	// handle POST DATA

	// UPDATE the custom options if there is POST data (if the Big Content Search create post form was submitted)
	if ($_POST['bigcontentsearch_create_post_submit_button']) {

		$wp_error_message = "";

		$bigcontentsearch_primary_keyword_radio = strtolower(trim(str_replace(array("\r", "\n"), null, $_POST['bigcontentsearch_primary_keyword'])));
		if (!$bigcontentsearch_primary_keyword_radio || $bigcontentsearch_primary_keyword_radio == strtolower("PRIMARY_KEYWORD_CUSTOM")) {
			$bigcontentsearch_keyword = strtolower(trim(str_replace(array("\r", "\n"), null, $_POST['bigcontentsearch_keyword'])));
		} else {
			$bigcontentsearch_keyword = $bigcontentsearch_primary_keyword_radio;
		}

		if (!$bigcontentsearch_keyword) {
			$wp_error_message = __("You need to enter a primary Keyword or Key Phrase for your new post.", "spin-rewriter-wp-plugin");
		} else if (strpos($bigcontentsearch_keyword, ",") !== false || strpos($bigcontentsearch_keyword, ".") !== false) {
			$wp_error_message = __("Your primary Keyword or Key Phrase must not include a comma (,) or a dot (.).", "spin-rewriter-wp-plugin");
		} else if (strlen($bigcontentsearch_keyword) < 3) {
			$wp_error_message = __("Your primary Keyword or Key Phrase seems too short, it should have at least 3 characters.", "spin-rewriter-wp-plugin");
		} else if (strlen($bigcontentsearch_keyword) > 50) {
			$wp_error_message = __("Your primary Keyword or Key Phrase seems to long, try with a shorter one.", "spin-rewriter-wp-plugin");
		}

		if (!$wp_error_message) {

			// should we spin the new post before publishing it?
			$spin = (intval($_POST['bigcontentsearch_spin_new_post']) == 1) ? true : false;

			// fetch content from Big Content Search and create a new post
			$adding_post_return_value = $this::addNewPost($bigcontentsearch_keyword, $spin, "manual");

			// update the action log so it shows the most recent changes on page load
			$action_log_array = $this->getOption("action_log");

			if ($adding_post_return_value['status'] == "OK") {
				$wp_success_message = $adding_post_return_value['message'];
			} else {
				$wp_error_message = $adding_post_return_value['message'];
			}
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
		<h2><?php _e("Create New Posts in Seconds", "spin-rewriter-wp-plugin"); ?></h2>

		<?php echo $wp_message_output; ?>

		<?php
		// has this user set up his Big Content Search API credentials yet?
		if (strpos($bigcontentsearch_settings_array['email_address'], "@") === false || !$bigcontentsearch_settings_array['api_key']) {
			$api_notification = '<b>IMPORTANT:</b>
						In order to automatically create brand new posts for your website in seconds, you will need to
						<b>enter your
						<a href="http://www.spinrewriter.com/big-content-search-exclusive" title="Big Content Search" target="_blank">Big Content Search</a>
						email address and API key</b>
						on the <a href="' . $settings_page_url . '#bigContentSearchSettings" title="Spin Rewriter Plugin - Settings">Settings page</a>.';
			echo "<div class='error' style='color:#DD3D36;'><p><strong>{$api_notification}</strong></p></div>";
		}



		// does this user have the "Automatically Add New Posts" feature turned OFF?
		if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "0") {
		?>

		<form action="" method="post">
		<table border="0" cellspacing="0" cellpadding="7" class="widefat" style="margin:17px 0px 7px 0px; max-width:659px;">
			<tr>
				<td><b>Enter a Primary Keyword or Key Phrase for Your New Post:</b></td>
			</tr>
			<tr class="alternate">
				<td>
				<?php
				// this user has already entered some primary keywords on the Settings page
				if (count($bigcontentsearch_settings_array['primary_keywords']) > 0) {
					$primary_keyword_counter = 0;
					if (is_array($bigcontentsearch_settings_array['primary_keywords']) && count($bigcontentsearch_settings_array['primary_keywords']) > 0) {
						foreach ($bigcontentsearch_settings_array['primary_keywords'] as $primary_keyword) {
							$primary_keyword_counter++;
							$checked = ($primary_keyword_counter == 1) ? 'checked="checked"' : '';
							echo '<p><input type="radio" name="bigcontentsearch_primary_keyword"
								id="bigcontentsearch_primary_keyword' . $primary_keyword_counter . '"
						        value="' . esc_attr(trim($primary_keyword)) . '" ' . $checked . ' />
						        <label for="bigcontentsearch_primary_keyword' . $primary_keyword_counter . '">' . trim($primary_keyword) . '</label></p>';
						}
					}
					?>
					<input type="radio" name="bigcontentsearch_primary_keyword"
					       id="bigcontentsearch_primary_keyword_custom"
					       value="PRIMARY_KEYWORD_CUSTOM" /> <label for="bigcontentsearch_primary_keyword_custom">Other:</label>
					<input type="text" name="bigcontentsearch_keyword" id="bigcontentsearch_keyword"
					       value="" size="30" />
				<?php
				} else {
					// this user has NOT entered any primary keywords on the Settings page yet
					?>
					<input type="text" name="bigcontentsearch_keyword" id="bigcontentsearch_keyword"
					       value="" size="50" />
				<?php
				}
				?>
				<p class="description" style="margin-top:10px; margin-bottom:5px; padding-bottom:5px;">&nbsp;Enter the topic of your new blog post, e.g: &raquo;guitar chords&laquo; or &raquo;guitar&laquo; or &raquo;music&laquo;.</p>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" name="bigcontentsearch_spin_new_post" id="bigcontentsearch_spin_new_post"
					       value="1" style="margin-left:2px;"
							<?php echo ((intval($bigcontentsearch_settings_array['spin_new_posts']) == 1) ? 'checked="checked"' : ''); ?>
					/>
					<b><label for="bigcontentsearch_spin_new_post">Spin the new post with Spin Rewriter before publishing it</label></b>
				</td>
			</tr>
			<tr>
				<td><input type="submit" name="bigcontentsearch_create_post_submit_button" id="bigcontentsearch_create_post_submit_button" class="button button-primary" value="<?php _e("Create a New Post", "spin-rewriter-wp-plugin") ?>"/></td>
			</tr>
		</table>
		</form>
		&nbsp;Visit the
		<a href="<?php echo $settings_page_url; ?>#bigContentSearchSettings" title="Spin Rewriter Plugin - Settings">Settings page</a>
		to make changes to these settings, or to
		<a href="<?php echo $settings_page_url; ?>#bigContentSearchSettings" title="Spin Rewriter Plugin - Settings">enable automated post creation</a>.



		<?php
		// END :: if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "0")
		} else {
			// this user has the "Automatically Add New Posts" feature turned ON
			$every_x_days_output = $bigcontentsearch_settings_array['add_posts_every_x_days'];
			if ($every_x_days_output < 0.49) {
				$every_x_days_output = "Every 6 Hours (4 Posts per Day)";
			} else if ($every_x_days_output < 0.99) {
				$every_x_days_output = "Every 12 Hours (2 Posts per Day)";
			} else if (abs(intval($bigcontentsearch_settings_array['add_posts_every_x_days'])) == 1) {
				$every_x_days_output = "Every Day";
			} else {
				$every_x_days_output = "Every " . abs(intval($bigcontentsearch_settings_array['add_posts_every_x_days'])) . " Day";
				$every_x_days_output .= (abs(intval($bigcontentsearch_settings_array['add_posts_every_x_days'])) != 1) ? "s" : "";
			}

			$keywords_key_phrases_output = "";
			if (is_array($bigcontentsearch_settings_array['primary_keywords']) && count($bigcontentsearch_settings_array['primary_keywords']) > 0) {
				foreach ($bigcontentsearch_settings_array['primary_keywords'] as $primary_keyword) {
					$keywords_key_phrases_output .= "&bull; <i>" . trim($primary_keyword) . "</i><br />";
				}
			}
			if (substr($keywords_key_phrases_output, -6) == "<br />") {
				$keywords_key_phrases_output = substr($keywords_key_phrases_output, 0, strlen($keywords_key_phrases_output) - 6);
			}
		?>

		<table border="0" cellspacing="0" cellpadding="7" class="widefat" style="margin:17px 0px 7px 0px; max-width:659px;">
			<tr>
				<td align="right"><b>Mode:</b></td>
				<td>Automated</td>
			</tr>
			<tr class="alternate">
				<td align="right" style="width:150px;"><b>Create New Posts:</b></td>
				<td style="max-width:509px;"><?php echo $every_x_days_output; ?></td>
			</tr>
			<tr>
				<td align="right"><b>List of Keywords:</b></td>
				<td><?php echo $keywords_key_phrases_output; ?></td>
			</tr>
			<tr class="alternate">
				<td>&nbsp;</td>
				<td><i>New posts will be created automatically at scheduled intervals.</i></td>
			</tr>
		</table>
		&nbsp;Visit the
		<a href="<?php echo $settings_page_url; ?>#bigContentSearchSettings" title="Spin Rewriter Plugin - Settings">Settings page</a>
		to make changes to these settings, or to
		<a href="<?php echo $settings_page_url; ?>#bigContentSearchSettings" title="Spin Rewriter Plugin - Settings">disable automated post creation</a>.

		<?php
			}   // END :: if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "0") ... ELSE ...
		?>



		<br /><br /><br />
		<h2><?php _e("Log of Recent Posting Activity", "spin-rewriter-wp-plugin"); ?></h2>

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
					if ($action_log_entry[1] == false) {
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
