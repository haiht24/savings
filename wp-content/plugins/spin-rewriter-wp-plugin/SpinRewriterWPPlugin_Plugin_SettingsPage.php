<?php

	// create a link to the Support page of this plugin
	$support_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Support");



	// handle POST DATA

	// UPDATE the custom options if there is POST data (if the Spin Rewriter settings form was submitted)
	if ($_POST['spinrewriter_settings_submit_button']) {

		$protected_keywords_explode = explode("\n", str_replace("\r", null, $_POST['spinrewriter_protected_keywords']));
		$protected_keywords = array();
		if (is_array($protected_keywords_explode) && count($protected_keywords_explode) > 0) {
			foreach ($protected_keywords_explode as $protected_keyword) {
				if (trim($protected_keyword)) {
					$protected_keywords[] = trim($protected_keyword);
				}
			}
		}

		$skip_posts = array();
		if (is_array($_POST['spinrewriter_skip_posts']) && count($_POST['spinrewriter_skip_posts']) > 0) {
			foreach ($_POST['spinrewriter_skip_posts'] as $skip_post) {
				if (trim($skip_post)) {
					$skip_posts[] = trim($skip_post);
				}
			}
		}

		if (strtolower(trim($_POST['spinrewriter_email_address'])) && trim($_POST['spinrewriter_api_key'])) {
			// validate data, check if these Spin Rewriter API credentials work
			require_once("SpinRewriterWPPlugin_SpinRewriterAPI.php");

			$spinrewriter_api = new SpinRewriterWPPlugin_SpinRewriterAPI(strtolower(trim($_POST['spinrewriter_email_address'])), trim($_POST['spinrewriter_api_key']));

			$response = $spinrewriter_api->getQuota();
		}

		if ($response['status'] == "ERROR") {
			// authentication error
			$wp_error_message = "Your Spin Rewriter email address and API key seem to be wrong.<br />";
			$wp_error_message .= "Spin Rewriter returned an error message: " . trim($response['response']);

			$wp_error_message = __($wp_error_message, "spin-rewriter-wp-plugin");
		} else {
			$spinrewriter_settings_array = array(
				"email_address" => strtolower(trim($_POST['spinrewriter_email_address'])),
				"api_key" => trim($_POST['spinrewriter_api_key']),
				"spin_after_x_days" => abs(intval(trim($_POST['spinrewriter_spin_after_x_days']))),
				"spin_titles" => abs(intval(trim($_POST['spinrewriter_spin_titles']))),
				"publish_as_new" => abs(intval(trim($_POST['spinrewriter_publish_as_new']))),
				"confidence_level" => strtolower(trim($_POST['spinrewriter_confidence_level'])),
				"protected_keywords" => $protected_keywords,
				"auto_protected_keywords" => abs(intval(trim($_POST['spinrewriter_auto_protected_keywords']))),
				"auto_sentences" => abs(intval(trim($_POST['spinrewriter_auto_sentences']))),
				"auto_paragraphs" => abs(intval(trim($_POST['spinrewriter_auto_paragraphs']))),
				"auto_new_paragraphs" => abs(intval(trim($_POST['spinrewriter_auto_new_paragraphs']))),
				"auto_sentence_trees" => abs(intval(trim($_POST['spinrewriter_auto_sentence_trees']))),
				"use_only_synonyms" => abs(intval(trim($_POST['spinrewriter_use_only_synonyms']))),
				"skip_posts" => $skip_posts,
				"run_in_background" => abs(intval(trim($_POST['spinrewriter_run_in_background'])))
			);

			// save these updated values into the actual option
			$this->updateOption("spinrewriter_settings", $spinrewriter_settings_array);

			$wp_success_message = __("Your settings have been successfully updated.", "spin-rewriter-wp-plugin");
		}
	}



	// UPDATE the custom options if there is POST data (if the Big Content Search settings form was submitted)
	if ($_POST['bigcontentsearch_settings_submit_button']) {

		$primary_keywords_explode = explode("\n", str_replace("\r", null, $_POST['bigcontentsearch_primary_keywords']));
		$primary_keywords = array();
		if (is_array($primary_keywords_explode) && count($primary_keywords_explode) > 0) {
			foreach ($primary_keywords_explode as $primary_keyword) {
				if (trim($primary_keyword)) {
					$primary_keywords[] = trim($primary_keyword);
				}
			}
		}

		$add_posts_every_x_days = abs(doubleval(trim($_POST['bigcontentsearch_add_posts_every_x_days'])));
		if ($add_posts_every_x_days < 0.24) {
			$add_posts_every_x_days = 0;
		} else if ($add_posts_every_x_days < 0.49) {
			$add_posts_every_x_days = 0.25;
		} else if ($add_posts_every_x_days < 0.99) {
			$add_posts_every_x_days = 0.50;
		} else {
			$add_posts_every_x_days = abs(intval($add_posts_every_x_days));
		}



		// validate data, check if these Big Content Search API credentials work
		require_once("SpinRewriterWPPlugin_BigContentSearchAPI.php");

		$bcs_api = new SpinRewriterWPPlugin_BigContentSearchAPI(strtolower(trim($_POST['bigcontentsearch_email_address'])), trim($_POST['bigcontentsearch_api_key']));

		// try to fetch an article with these API credentials
		$response = $bcs_api->search("marketing");

		if (intval($response['status_code']) == 8) {
			// authentication error
			$wp_error_message = __("Your Big Content Search email address and API key seem to be wrong, please try again.", "spin-rewriter-wp-plugin");
		} else if ($add_posts_every_x_days != 0 && count($primary_keywords) < 1) {
			// user wants automatically created content but they have provided no primary keywords or key phrases
			$wp_error_message = __("You need to provide a list of Keywords or Key Phrases if you want to generate new posts automatically.", "spin-rewriter-wp-plugin");
		} else {
			$bigcontentsearch_settings_array = array(
				"email_address" => strtolower(trim($_POST['bigcontentsearch_email_address'])),
				"api_key" => trim($_POST['bigcontentsearch_api_key']),
				"add_posts_every_x_days" => $add_posts_every_x_days,
				"primary_keywords" => $primary_keywords,
				"spin_new_posts" => abs(intval(trim($_POST['bigcontentsearch_spin_new_posts'])))
			);

			// save these updated values into the actual option
			$this->updateOption("bigcontentsearch_settings", $bigcontentsearch_settings_array);

			$wp_success_message = __("Your Big Content Search settings have been successfully updated.", "spin-rewriter-wp-plugin");
		}
	}










	// create PAGE HTML

	// get all custom options
	$spinrewriter_settings_array = $this->getOption("spinrewriter_settings");
	$bigcontentsearch_settings_array = $this->getOption("bigcontentsearch_settings");



	// format the list of protected keywords (for Spin Rewriter) for the textarea
	$textarea_formatted_protected_keywords = "";
	if (is_array($spinrewriter_settings_array['protected_keywords']) && count($spinrewriter_settings_array['protected_keywords']) > 0) {
		foreach ($spinrewriter_settings_array['protected_keywords'] as $protected_keyword) {
			$textarea_formatted_protected_keywords .= trim($protected_keyword) . "\n";
		}
	}
	$textarea_formatted_protected_keywords = trim($textarea_formatted_protected_keywords);



	// format the list of primary keywords (for Big Content Search) for the textarea
	$textarea_formatted_primary_keywords = "";
	if (is_array($bigcontentsearch_settings_array['primary_keywords']) && count($bigcontentsearch_settings_array['primary_keywords']) > 0) {
		foreach ($bigcontentsearch_settings_array['primary_keywords'] as $primary_keyword) {
			$textarea_formatted_primary_keywords .= trim($primary_keyword) . "\n";
		}
	}
	$textarea_formatted_primary_keywords = trim($textarea_formatted_primary_keywords);



	// create a list of existing blog posts (so user can decide to EXCLUDE them for getting spun)
	$wp_get_posts_arguments = array(
		'posts_per_page'   => 1000,
		'offset'           => 0,
		'category'         => '',
		'orderby'          => 'post_date',
		'order'            => 'DESC',
		'include'          => '',
		'exclude'          => '',
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'post',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'post_status'      => 'publish',
		'suppress_filters' => true );
	$wp_posts_array = get_posts($wp_get_posts_arguments);



	// show a SUCCESS or ERROR message
	$wp_message_output = "";
	if ($wp_success_message) {
		$wp_message_output = "<div class='updated'><p><strong>{$wp_success_message}</strong></p></div>";
	} else if ($wp_error_message) {
		$wp_message_output = "<div class='error' style='color:#DD3D36;'><p><strong>{$wp_error_message}</strong></p></div>";
	}

?>
	<div class="wrap">
		<h2><?php _e($this::getPluginDisplayName() . " &mdash; Settings", "spin-rewriter-wp-plugin"); ?></h2>

		<?php echo $wp_message_output; ?>

		<form action="" method="post">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="spinrewriter_email_address">Spin Rewriter Email Address</label>
						</th>
						<td>
							<input type="text" name="spinrewriter_email_address" id="spinrewriter_email_address"
							          value="<?php echo esc_attr($spinrewriter_settings_array['email_address']) ?>" size="50"/>
							<p class="description">The email address you are using to log into your Spin Rewriter account.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="spinrewriter_api_key">Spin Rewriter API Key</label>
						</th>
						<td>
							<input type="text" name="spinrewriter_api_key" id="spinrewriter_api_key"
							          value="<?php echo esc_attr($spinrewriter_settings_array['api_key']) ?>" size="50"/>
							<p class="description">You will find your API Key in your
							<a href="http://www.spinrewriter.com/cp-api" title="Your Spin Rewriter API Key" target="_blank">Spin Rewriter account,
							under &raquo;API&laquo;</a>.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="spinrewriter_spin_after_x_days">Spin Posts After This Long</label>
						</th>
						<td>
							<select id="spinrewriter_spin_after_x_days" name="spinrewriter_spin_after_x_days">
								<optgroup label="Very Frequently">
									<option value="1" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "1") echo "selected='selected'"; ?>>1 Day</option>
									<option value="2" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "2") echo "selected='selected'"; ?>>2 Days</option>
									<option value="3" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "3") echo "selected='selected'"; ?>>3 Days</option>
									<option value="4" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "4") echo "selected='selected'"; ?>>4 Days</option>
									<option value="5" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "5") echo "selected='selected'"; ?>>5 Days</option>
									<option value="6" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "6") echo "selected='selected'"; ?>>6 Days</option>
								</optgroup>
								<optgroup label="Frequently">
									<option value="7" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "7") echo "selected='selected'"; ?>>1 Week</option>
									<option value="14" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "14") echo "selected='selected'"; ?>>2 Weeks</option>
									<option value="21" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "21") echo "selected='selected'"; ?>>3 Weeks</option>
									<option value="28" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "28") echo "selected='selected'"; ?>>4 Weeks</option>
								</optgroup>
								<optgroup label="Moderately">
									<option value="30" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "30") echo "selected='selected'"; ?>>1 Month</option>
									<option value="45" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "45") echo "selected='selected'"; ?>>1.5 Months</option>
									<option value="60" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "60") echo "selected='selected'"; ?>>2 Months</option>
									<option value="75" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "75") echo "selected='selected'"; ?>>2.5 Months</option>
									<option value="90" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "90") echo "selected='selected'"; ?>>3 Months</option>
									<option value="120" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "120") echo "selected='selected'"; ?>>4 Months</option>
								</optgroup>
								<optgroup label="Rarely">
									<option value="182" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "182") echo "selected='selected'"; ?>>Half a Year (6 Months)</option>
									<option value="365" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "365") echo "selected='selected'"; ?>>1 Year (12 Months)</option>
									<option value="547" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "547") echo "selected='selected'"; ?>>1.5 Years (18 Months)</option>
									<option value="730" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "730") echo "selected='selected'"; ?>>2 Years (24 Months)</option>
									<option value="1095" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "1095") echo "selected='selected'"; ?>>3 Years (36 Months)</option>
									<option value="1460" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "1460") echo "selected='selected'"; ?>>4 Years (48 Months)</option>
									<option value="1825" <?php if ($spinrewriter_settings_array['spin_after_x_days'] == "1825") echo "selected='selected'"; ?>>5 Years (60 Months)</option>
								</optgroup>
							</select>
							<p class="description">Once a post reaches the specified age, it is automatically spun and re-published.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="spinrewriter_spin_titles">Spin Post Titles</label>
						</th>
						<td>
							<select id="spinrewriter_spin_titles" name="spinrewriter_spin_titles">
								<option value="1" <?php if ($spinrewriter_settings_array['spin_titles'] == "1") echo "selected='selected'"; ?>>YES, spin post titles as well</option>
								<option value="0" <?php if ($spinrewriter_settings_array['spin_titles'] != "1") echo "selected='selected'"; ?>>NO, use the same post title with the re-published post</option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="spinrewriter_publish_as_new">Publish as New Posts</label>
						</th>
						<td>
							<select id="spinrewriter_publish_as_new" name="spinrewriter_publish_as_new">
								<option value="1" <?php if ($spinrewriter_settings_array['publish_as_new'] == "1") echo "selected='selected'"; ?>>YES, publish the old posts as NEW posts when we spin them</option>
								<option value="0" <?php if ($spinrewriter_settings_array['publish_as_new'] != "1") echo "selected='selected'"; ?>>NO, just spin and update old posts (don't create new posts)</option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="spinrewriter_skip_posts">Do NOT Spin These Posts</label>
							<p class="description">Selected posts will NOT get spun and re-published.</p>
						</th>
						<td style="vertical-align:top;">
							<div style="overflow:hidden; max-height:140px;">
								<div style="overflow-y:scroll; max-height:140px;">
								<?php
									if (is_array($wp_posts_array) && count($wp_posts_array) > 0) {
										foreach ($wp_posts_array as $wp_post) {
											$wp_post_id = $wp_post->ID;
											$wp_post_title = (strlen($wp_post->post_title) > 70) ? substr($wp_post->post_title, 0, 69) . "..." : $wp_post->post_title;
											$wp_post_date_local = date_i18n(get_option("date_format"), strtotime($wp_post->post_date_gmt));

											$wp_post_checked = (in_array(trim($wp_post->ID), $spinrewriter_settings_array['skip_posts'])) ? "checked='checked'" : "";

											echo "<label for='spinrewriter_skip_posts'>
												<input type='checkbox' name='spinrewriter_skip_posts[]' id='spinrewriter_skip_posts_{$wp_post_id}' value='{$wp_post_id}' {$wp_post_checked} />
												&raquo;<i>{$wp_post_title}</i>&laquo;, posted on {$wp_post_date_local}</label><br />";
										}
									}
								?>
								</div>
							</div>
						</td>
					</tr>

					<!-- toggle for advanced ENL Spinning Settings -->
					<tr valign="top">
						<th scope="row">
							ENL Spinning Settings
						</th>
						<td>
							<a href="#" title="Click here to toggle advanced ENL Spinning Settings" class="linkSpinRewriterAdvancedSettings">Click here to show advanced ENL Spinning Settings...</a>
						</td>
					</tr>

					<!-- advanced ENL Spinning Settings that can be toggled, one per <tr> -->
					<tr valign="top" style="display:none;" class="trSpinRewriterAdvancedSettings">
						<th scope="row">
							<label for="spinrewriter_confidence_level">Confidence Level</label>
						</th>
						<td>
							<select id="spinrewriter_confidence_level" name="spinrewriter_confidence_level">
								<option value="high" <?php if ($spinrewriter_settings_array['confidence_level'] == "high") echo "selected='selected'"; ?>>HIGH: create extremely readable and somewhat unique posts</option>
								<option value="medium" <?php if ($spinrewriter_settings_array['confidence_level'] == "medium") echo "selected='selected'"; ?>>MEDIUM: create very readable and very unique posts </option>
								<option value="low" <?php if ($spinrewriter_settings_array['confidence_level'] == "low") echo "selected='selected'"; ?>>LOW: create less readable and extremely unique posts</option>
							</select>
						</td>
					</tr>
					<tr valign="top" style="display:none;" class="trSpinRewriterAdvancedSettings">
						<th scope="row">
							<label for="spinrewriter_protected_keywords">Protected Keywords</label>
							<p class="description">One keyword or phrase per line.</p>
						</th>
						<td>
							<textarea name="spinrewriter_protected_keywords" id="spinrewriter_protected_keywords"
							          class="large-text code" rows="7" cols="23"><?php echo esc_attr($textarea_formatted_protected_keywords) ?></textarea>
							<p class="description">List one protected keyword or one protected phrase per line.</p>
						</td>
					</tr>
					<tr valign="top" style="display:none;" class="trSpinRewriterAdvancedSettings">
						<th scope="row">
							<label for="spinrewriter_auto_protected_keywords">Automatic Protection</label>
						</th>
						<td>
							<select id="spinrewriter_auto_protected_keywords" name="spinrewriter_auto_protected_keywords">
								<option value="1" <?php if ($spinrewriter_settings_array['auto_protected_keywords'] == "1") echo "selected='selected'"; ?>>YES, automatically protect all Capitalized Words except in the title</option>
								<option value="0" <?php if ($spinrewriter_settings_array['auto_protected_keywords'] != "1") echo "selected='selected'"; ?>>NO, do not automatically protect Capitalized Words</option>
							</select>
						</td>
					</tr>
					<tr valign="top" style="display:none;" class="trSpinRewriterAdvancedSettings">
						<th scope="row">
							<label for="spinrewriter_auto_sentences">Spin Sentences</label>
						</th>
						<td>
							<select id="spinrewriter_auto_sentences" name="spinrewriter_auto_sentences">
								<option value="1" <?php if ($spinrewriter_settings_array['auto_sentences'] == "1") echo "selected='selected'"; ?>>YES, automatically spin entire sentences</option>
								<option value="0" <?php if ($spinrewriter_settings_array['auto_sentences'] != "1") echo "selected='selected'"; ?>>NO, do not spin on sentence-level</option>
							</select>
						</td>
					</tr>
					<tr valign="top" style="display:none;" class="trSpinRewriterAdvancedSettings">
						<th scope="row">
							<label for="spinrewriter_auto_paragraphs">Spin Paragraphs</label>
						</th>
						<td>
							<select id="spinrewriter_auto_paragraphs" name="spinrewriter_auto_paragraphs">
								<option value="1" <?php if ($spinrewriter_settings_array['auto_paragraphs'] == "1") echo "selected='selected'"; ?>>YES, automatically spin entire paragraphs</option>
								<option value="0" <?php if ($spinrewriter_settings_array['auto_paragraphs'] != "1") echo "selected='selected'"; ?>>NO, do not spin on paragraph-level</option>
							</select>
						</td>
					</tr>
					<tr valign="top" style="display:none;" class="trSpinRewriterAdvancedSettings">
						<th scope="row">
							<label for="spinrewriter_auto_new_paragraphs">Add Paragraphs</label>
						</th>
						<td>
							<select id="spinrewriter_auto_new_paragraphs" name="spinrewriter_auto_new_paragraphs">
								<option value="1" <?php if ($spinrewriter_settings_array['auto_new_paragraphs'] == "1") echo "selected='selected'"; ?>>YES, automatically create new paragraphs (summary) at the end</option>
								<option value="0" <?php if ($spinrewriter_settings_array['auto_new_paragraphs'] != "1") echo "selected='selected'"; ?>>NO, do not create new paragraphs on your own</option>
							</select>
						</td>
					</tr>
					<tr valign="top" style="display:none;" class="trSpinRewriterAdvancedSettings">
						<th scope="row">
							<label for="spinrewriter_auto_sentence_trees">Change Sentences</label>
						</th>
						<td>
							<select id="spinrewriter_auto_sentence_trees" name="spinrewriter_auto_sentence_trees">
								<option value="1" <?php if ($spinrewriter_settings_array['auto_sentence_trees'] == "1") echo "selected='selected'"; ?>>YES, change structure of sentences (If A, B. &rarr; B because A.)</option>
								<option value="0" <?php if ($spinrewriter_settings_array['auto_sentence_trees'] != "1") echo "selected='selected'"; ?>>NO, do not change structure of sentences.</option>
							</select>
						</td>
					</tr>
					<tr valign="top" style="display:none;" class="trSpinRewriterAdvancedSettings">
						<th scope="row">
							<label for="spinrewriter_use_only_synonyms">Use Only Synonyms</label>
						</th>
						<td>
							<select id="spinrewriter_use_only_synonyms" name="spinrewriter_use_only_synonyms">
								<option value="1" <?php if ($spinrewriter_settings_array['use_only_synonyms'] == "1") echo "selected='selected'"; ?>>YES, always use synonyms when available (max. uniqueness)</option>
								<option value="0" <?php if ($spinrewriter_settings_array['use_only_synonyms'] != "1") echo "selected='selected'"; ?>>NO, use some of the original words as well</option>
							</select>
						</td>
					</tr>
					<tr valign="top" style="display:none;" class="trSpinRewriterAdvancedSettings">
						<th scope="row">
							<label for="spinrewriter_use_only_synonyms">PLEASE NOTE</label>
						</th>
						<td>
							<p>You can find out more about these ENL Spinning Settings on the <a href="<?php echo $support_page_url; ?>" title="Spin Rewriter Plugin - Support">Support page</a>.</p>
							<p><a href="#" title="Click here to toggle advanced ENL Spinning Settings" class="linkSpinRewriterAdvancedSettings">Click here to show advanced ENL Spinning Settings...</a></p>
							<br /><br />
						</td>
					</tr>
					<!-- END :: advanced ENL Spinning Settings that can be toggled, one per <tr> -->

					<tr valign="top">
						<th scope="row">
							<label for="spinrewriter_run_in_background">Run 24/7 Automatically</label>
						</th>
						<td>
							<select id="spinrewriter_run_in_background" name="spinrewriter_run_in_background">
								<option value="1" <?php if ($spinrewriter_settings_array['run_in_background'] == "1") echo "selected='selected'"; ?>>YES, do everything automatically</option>
								<option value="0" <?php if ($spinrewriter_settings_array['run_in_background'] != "1") echo "selected='selected'"; ?>>NO, I want to run the process manually from time to time</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" name="spinrewriter_settings_submit_button" id="spinrewriter_settings_submit_button" class="button button-primary" value="<?php _e("Save Changes", "spin-rewriter-wp-plugin") ?>"/>
			</p>
		</form>

		<br /><br />
		<h2 id="bigContentSearchSettings"><?php _e("Big Content Search &mdash; Settings", "spin-rewriter-wp-plugin"); ?></h2>

		<p>
			<a href="http://www.spinrewriter.com/big-content-search-exclusive" title="Big Content Search" target="_blank">Big Content Search</a>
			is an incredible collection of 126,700 high quality PLR articles with a powerful
			search engine built right into it.
		</p>

		<p>
			By using the Big Content Search API, you can
			instantly create relevant &amp; high quality posts for your website. What's more,
			you can use Spin Rewriter to spin these new posts and make them unique, automatically.
			<a href="http://www.spinrewriter.com/big-content-search-exclusive" title="Big Content Search" target="_blank">Find out more about Big Content Search here...</a>
		</p>

		<p>Enter the Big Content Search API details below if you want to <b>automatically generate new quality posts in seconds</b>:</p>

		<form action="" method="post">
			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="bigcontentsearch_email_address">Big Content Search Email</label>
					</th>
					<td>
						<input type="text" name="bigcontentsearch_email_address" id="bigcontentsearch_email_address"
						       value="<?php echo esc_attr($bigcontentsearch_settings_array['email_address']) ?>" size="50"/>
						<p class="description">The email address you are using to log into your <a href="http://www.spinrewriter.com/big-content-search-exclusive" title="Big Content Search" target="_blank">Big Content Search</a> account.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="bigcontentsearch_api_key">Big Content Search API Key</label>
					</th>
					<td>
						<input type="text" name="bigcontentsearch_api_key" id="bigcontentsearch_api_key"
						       value="<?php echo esc_attr($bigcontentsearch_settings_array['api_key']) ?>" size="50"/>
						<p class="description">You will find your API Key in your
							<a href="http://www.spinrewriter.com/big-content-search-exclusive" title="Your Big Content Search API Key" target="_blank">Big Content Search account,
								under &raquo;Preferences&laquo;</a>.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="bigcontentsearch_add_posts_every_x_days">Automatically Add New Posts</label>
					</th>
					<td>
						<select id="bigcontentsearch_add_posts_every_x_days" name="bigcontentsearch_add_posts_every_x_days">
							<optgroup label="Never">
								<option value="0" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "0") echo "selected='selected'"; ?>>Never. I will add all posts manually.</option>
							</optgroup>
							<optgroup label="Very Frequently">
								<option value="0.25" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "0.25") echo "selected='selected'"; ?>>New Post Every 6 Hours</option>
								<option value="0.5" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "0.5") echo "selected='selected'"; ?>>New Post Every 12 Hours</option>
								<option value="1" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "1") echo "selected='selected'"; ?>>New Post Every Day</option>
							</optgroup>
							<optgroup label="Frequently">
								<option value="2" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "2") echo "selected='selected'"; ?>>New Post Every Other Day</option>
								<option value="3" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "3") echo "selected='selected'"; ?>>New Post Every 3 Days</option>
								<option value="4" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "4") echo "selected='selected'"; ?>>New Post Every 4 Days</option>
								<option value="5" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "5") echo "selected='selected'"; ?>>New Post Every 5 Days</option>
								<option value="6" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "6") echo "selected='selected'"; ?>>New Post Every 6 Days</option>
							</optgroup>
							<optgroup label="Moderately">
								<option value="7" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "7") echo "selected='selected'"; ?>>New Post Every Week</option>
								<option value="14" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "14") echo "selected='selected'"; ?>>New Post Every Other Week</option>
								<option value="21" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "21") echo "selected='selected'"; ?>>New Post Every 3 Weeks</option>
								<option value="28" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "28") echo "selected='selected'"; ?>>New Post Every 4 Weeks</option>
							</optgroup>
							<optgroup label="Rarely">
								<option value="30" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "30") echo "selected='selected'"; ?>>New Post Every Month</option>
								<option value="45" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "45") echo "selected='selected'"; ?>>New Post Every 1.5 Months</option>
								<option value="60" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "60") echo "selected='selected'"; ?>>New Post Every 2 Months</option>
								<option value="75" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "75") echo "selected='selected'"; ?>>New Post Every 2.5 Months</option>
								<option value="90" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "90") echo "selected='selected'"; ?>>New Post Every 3 Months</option>
								<option value="120" <?php if ($bigcontentsearch_settings_array['add_posts_every_x_days'] == "120") echo "selected='selected'"; ?>>New Post Every 4 Months</option>
							</optgroup>
						</select>
						<p class="description trBigContentSearchAdvancedSettings" style="display:none;">New posts will be automatically created from the list of primary keywords below.</p>
					</td>
				</tr>
				<tr valign="top" style="display:none;" class="trBigContentSearchAdvancedSettings">
					<th scope="row">
						<label for="bigcontentsearch_primary_keywords">Primary Keywords</label>
						<p class="description">One keyword or phrase per line.</p>
					</th>
					<td>
						<textarea name="bigcontentsearch_primary_keywords" id="bigcontentsearch_primary_keywords"
						          class="large-text code" rows="10" cols="23"><?php echo esc_attr($textarea_formatted_primary_keywords) ?></textarea>
						<p class="description">List one primary keyword or one primary phrase for your new posts per line.
						Every time we are creating a new post for you, one of these keywords will be randomly selected for the main theme of your new post.</p>
					</td>
				</tr>
				<tr valign="top" style="display:none;" class="trBigContentSearchAdvancedSettings">
					<th scope="row">
						<label for="bigcontentsearch_spin_new_posts">Spin New Posts</label>
					</th>
					<td>
						<select id="bigcontentsearch_spin_new_posts" name="bigcontentsearch_spin_new_posts">
							<option value="1" <?php if ($bigcontentsearch_settings_array['spin_new_posts'] == "1") echo "selected='selected'"; ?>>YES, spin new posts before publishing them</option>
							<option value="0" <?php if ($bigcontentsearch_settings_array['spin_new_posts'] != "1") echo "selected='selected'"; ?>>NO, don't spin new posts before publishing them</option>
						</select>
						<p class="description">New posts will be fetched from the Big Content Search collection of over 126,700 quality PLR articles.
						You can choose to spin your new posts before publishing them to maximize their uniqueness.
						Spinning will be done automatically with Spin Rewriter.</p>
					</td>
				</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" name="bigcontentsearch_settings_submit_button" id="bigcontentsearch_settings_submit_button" class="button button-primary" value="<?php _e("Save Changes", "spin-rewriter-wp-plugin") ?>"/>
			</p>
		</form>
	</div>