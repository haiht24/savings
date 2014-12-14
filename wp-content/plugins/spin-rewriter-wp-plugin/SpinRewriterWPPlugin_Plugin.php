<?php

include_once('SpinRewriterWPPlugin_LifeCycle.php');

class SpinRewriterWPPlugin_Plugin extends SpinRewriterWPPlugin_LifeCycle {

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        /*
        //  http://plugin.michael-simpson.com/?page_id=31
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'ATextInput' => array(__('Enter in some text', 'my-awesome-plugin')),
            'Donated' => array(__('I have donated to this plugin', 'my-awesome-plugin'), 'false', 'true'),
            'CanSeeSubmitData' => array(__('Can See Submission data', 'my-awesome-plugin'),
                                        'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
        */
	    return array("spinrewriter_settings", "bigcontentsearch_settings", "spun_posts", "used_bcs_articles", "action_log");
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        /*
	    $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
        */
    }

    public function getPluginDisplayName() {
        return 'Spin Rewriter';
    }

    protected function getMainPluginFileName() {
        return 'spin-rewriter-wp-plugin.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }

    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
    }

	/**
	 * Add actions and filters
	 * @return void
	 */
	public function addActionsAndFilters() {

		// add pages to the left-hand side Admin Menu
	    add_action("admin_menu", array(&$this, "addMenuPages"));

		// enqueue custom JavaScript code
		wp_enqueue_script("spin-rewriter-wp-plugin-js", plugins_url("spin-rewriter-wp-plugin/js/spin-rewriter-wp-plugin.js"));

		// make sure the hourly cron job triggers our code
		add_action("spinrewriterhourlyeventauto", array(&$this, "hourlyCronJob"));

	    /*
	     * SAMPLE CODE:

		    // Add options administration page
	        // http://plugin.michael-simpson.com/?page_id=47
	        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

	        // Example adding a script & style just for the options administration page
	        // http://plugin.michael-simpson.com/?page_id=47
	        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
	        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
	        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
	        //        }


	        // Add Actions & Filters
	        // http://plugin.michael-simpson.com/?page_id=37


	        // Adding scripts & styles to all pages
	        // Examples:
	        //        wp_enqueue_script('jquery');
	        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
	        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));


	        // Register short codes
	        // http://plugin.michael-simpson.com/?page_id=39


	        // Register AJAX hooks
	        // http://plugin.michael-simpson.com/?page_id=41
	     *
	     */
    }





	/*
	 *
	 * CUSTOM CODE BEGINS HERE...
	 *
	 */

	/**
	 * This code is run when the plugin is activated
	 * @return void
	 */
	public function activate() {
		// set the default values of our options
		$spinrewriter_settings_array = array(
			"email_address" => "",
			"api_key" => "",
			"spin_after_x_days" => "60",
			"spin_titles" => "1",
			"publish_as_new" => "1",
			"confidence_level" => "medium",
			"protected_keywords" => array(),
			"auto_protected_keywords" => 1,
			"auto_sentences" => 1,
			"auto_paragraphs" => 1,
			"auto_new_paragraphs" => 1,
			"auto_sentence_trees" => 1,
			"use_only_synonyms" => 0,
			"skip_posts" => array(),
			"run_in_background" => "0"
		);

		$bigcontentsearch_settings_array = array(
			"email_address" => "",
			"api_key" => "",
			"add_posts_every_x_days" => 0,
			"primary_keywords" => array(),
			"spin_new_posts" => 0
		);

		$spun_posts = array();

		$used_bcs_articles = array();

		$action_log = array();

		// save these default values as actual options
		$this->updateOption("spinrewriter_settings", $spinrewriter_settings_array);
		$this->updateOption("bigcontentsearch_settings", $bigcontentsearch_settings_array);
		$this->updateOption("spun_posts", $spun_posts);
		$this->updateOption("used_bcs_articles", $used_bcs_articles);
		$this->updateOption("action_log", $action_log);

		// add an hourly cron job that can be used to automatically process old posts
		if (!wp_next_scheduled("spinrewriterhourlyeventauto")) {
			/*
			 * Note:
			 * For some reason there seems to be a problem on some systems where the hook must not contain underscores or uppercase characters.
			 * http://codex.wordpress.org/Function_Reference/wp_schedule_event
			 */
			wp_schedule_event(time() + 60, "hourly", "spinrewriterhourlyeventauto");
		}
	}

	/**
	 * This code is run when the plugin is deactivated,
	 * clean everything up and leave it as it was.
	 * @return void
	 */
	public function deactivate() {
		// do NOT delete any custom options in case this user reactivates our plugin later on

		/*
		$this->deleteOption("settings");
		$this->deleteOption("spinrewriter_settings");
		$this->deleteOption("bigcontentsearch_settings");
		$this->deleteOption("spun_posts");
		$this->deleteOption("used_bcs_articles");
		$this->deleteOption("action_log");
		*/

		// remove (disable) the hourly cron job that we were using to automatically process old posts
		wp_clear_scheduled_hook("spinrewriterhourlyeventauto");
	}

	/**
	 * Create a new top-level menu for the Spin Rewriter WP Plugin,
	 * with three subpages: Plugin Settings, Content, Support
	 * @return void
	 */
	public function addMenuPages() {
		// create a new top-level menu for the Spin Rewriter WP Plugin
		add_menu_page($this::getPluginDisplayName() . " Plugin: About",
			$this::getPluginDisplayName(),
			"activate_plugins",
			get_class($this) . "_About",
			array(&$this, "aboutPage"));

		// create a submenu for the Settings page
		add_submenu_page(get_class($this) . "_About",
			$this::getPluginDisplayName() . " Plugin: About",
			"About",
			"activate_plugins",
			get_class($this) . "_About",
			array(&$this, "aboutPage"));

		// create a submenu for the Settings page
		add_submenu_page(get_class($this) . "_About",
			$this::getPluginDisplayName() . " Plugin: Settings",
			"Settings",
			"activate_plugins",
			get_class($this) . "_Submenu_Settings",
			array(&$this, "settingsPage"));

		// create a submenu for the Spin Old Posts page
		add_submenu_page(get_class($this) . "_About",
			$this::getPluginDisplayName() . " Plugin: Spin Old Posts",
			"Spin Old Posts",
			"activate_plugins",
			get_class($this) . "_Submenu_Spin_Old_Posts",
			array(&$this, "spinOldPostsPage"));

		// create a submenu for the Add New Posts page
		add_submenu_page(get_class($this) . "_About",
			$this::getPluginDisplayName() . " Plugin: Add New Posts",
			"Add New Posts",
			"activate_plugins",
			get_class($this) . "_Submenu_Add_New_Posts",
			array(&$this, "addNewPostsPage"));

		// create a submenu for the Support page
		add_submenu_page(get_class($this) . "_About",
			$this::getPluginDisplayName() . " Plugin: Support",
			"Support",
			"activate_plugins",
			get_class($this) . "_Submenu_Support",
			array(&$this, "supportPage"));
	}

	/**
	 * Create HTML for the About page that introduces this plugin to its users.
	 * @return void
	 */
	public function aboutPage() {
		if (!current_user_can("activate_plugins")) {
			wp_die(__("You do not have sufficient permissions to access this page.", "spin-rewriter-wp-plugin"));
		}

		require_once("SpinRewriterWPPlugin_Plugin_AboutPage.php");
	}

	/**
	 * Create HTML for the Settings page to set options for this plugin.
	 * @return void
	 */
	public function settingsPage() {
		if (!current_user_can("activate_plugins")) {
			wp_die(__("You do not have sufficient permissions to access this page.", "spin-rewriter-wp-plugin"));
		}

		require_once("SpinRewriterWPPlugin_Plugin_SettingsPage.php");
	}

	/**
	 * Create HTML for the Spin Old Posts page of this plugin.
	 * @return void
	 */
	public function spinOldPostsPage() {
		if (!current_user_can("activate_plugins")) {
			wp_die(__("You do not have sufficient permissions to access this page.", "spin-rewriter-wp-plugin"));
		}

		require_once("SpinRewriterWPPlugin_Plugin_SpinOldPostsPage.php");
	}

	/**
	 * Create HTML for the Add New Posts page of this plugin.
	 * @return void
	 */
	public function addNewPostsPage() {
		if (!current_user_can("activate_plugins")) {
			wp_die(__("You do not have sufficient permissions to access this page.", "spin-rewriter-wp-plugin"));
		}

		require_once("SpinRewriterWPPlugin_Plugin_AddNewPostsPage.php");
	}

	/**
	 * Create HTML for the Support page of this plugin.
	 * @return void
	 */
	public function supportPage() {
		if (!current_user_can("activate_plugins")) {
			wp_die(__("You do not have sufficient permissions to access this page.", "spin-rewriter-wp-plugin"));
		}

		require_once("SpinRewriterWPPlugin_Plugin_SupportPage.php");
	}

	/**
	 * Create a number of days (e.g. 60) into a readable format (e.g. 2 months).
	 * @param $number_of_days
	 * @return string
	 */
	public function convertNumberOfDaysIntoReadableFormat($number_of_days) {
		$number_of_days = intval($number_of_days);
		if ($number_of_days == 1) $spin_after_x_days_readable_format = "1 Day";
			else if ($number_of_days == 2) $spin_after_x_days_readable_format = "2 Days";
			else if ($number_of_days == 3) $spin_after_x_days_readable_format = "3 Days";
			else if ($number_of_days == 4) $spin_after_x_days_readable_format = "4 Days";
			else if ($number_of_days == 5) $spin_after_x_days_readable_format = "5 Days";
			else if ($number_of_days == 6) $spin_after_x_days_readable_format = "6 Days";
			else if ($number_of_days == 7) $spin_after_x_days_readable_format = "1 Week";
			else if ($number_of_days == 14) $spin_after_x_days_readable_format = "2 Weeks";
			else if ($number_of_days == 21) $spin_after_x_days_readable_format = "3 Weeks";
			else if ($number_of_days == 28) $spin_after_x_days_readable_format = "4 Weeks";
			else if ($number_of_days == 30) $spin_after_x_days_readable_format = "1 Month";
			else if ($number_of_days == 45) $spin_after_x_days_readable_format = "1.5 Months";
			else if ($number_of_days == 60) $spin_after_x_days_readable_format = "2 Months";
			else if ($number_of_days == 75) $spin_after_x_days_readable_format = "2.5 Months";
			else if ($number_of_days == 90) $spin_after_x_days_readable_format = "3 Months";
			else if ($number_of_days == 120) $spin_after_x_days_readable_format = "4 Months";
			else if ($number_of_days == 182) $spin_after_x_days_readable_format = "Half a Year (6 Months)";
			else if ($number_of_days == 365) $spin_after_x_days_readable_format = "1 Year (12 Months)";
			else if ($number_of_days == 547) $spin_after_x_days_readable_format = "1.5 Years (18 Months)";
			else if ($number_of_days == 730) $spin_after_x_days_readable_format = "2 Years (24 Months)";
			else if ($number_of_days == 1095) $spin_after_x_days_readable_format = "3 Years (36 Months)";
			else if ($number_of_days == 1460) $spin_after_x_days_readable_format = "4 Years (48 Months)";
			else if ($number_of_days == 1825) $spin_after_x_days_readable_format = "5 Years (60 Months)";
			else $spin_after_x_days_readable_format = $number_of_days . "Days";
		$spin_after_x_days_readable_format = strtolower($spin_after_x_days_readable_format);
		return $spin_after_x_days_readable_format;
	}

	/**
	 * Store this activity into the actual option
	 * @param $description
	 * @param $spinning_or_adding
	 * @return void
	 */
	public function storeActivityIntoOption($description, $spinning_or_adding) {
		$action_log = $this->getOption("action_log");
		$new_action_log = array();
		$number_of_existing_actions = count($action_log);
		$max_number_of_actions = 500;
		$min_counter_index_to_keep = $number_of_existing_actions - $max_number_of_actions;
		$counter = 0;
		if (is_array($action_log) && count($action_log) > 0) {
			foreach ($action_log as $action_log_entry) {
				$counter++;
				if ($counter > $min_counter_index_to_keep) {
					// we should never keep more than the most recent X actions in the action log
					$new_action_log[] = $action_log_entry;
				}
			}
		}

		// add the new action log entry
		$new_action_log[] = array($description, ($spinning_or_adding == "spinning") ? true : false, time());
		$this->updateOption("action_log", $new_action_log);
	}

	/**
	 * Run this code when the hourly cron job is triggered
	 * @return void
	 */
	public function hourlyCronJob() {

		// get all custom options
		$spinrewriter_settings_array = $this->getOption("spinrewriter_settings");
		$bigcontentsearch_settings_array = $this->getOption("bigcontentsearch_settings");
		$action_log_array = $this->getOption("action_log");

		// is this user actually using the automated option for spinning and re-publishing?
		if (intval($spinrewriter_settings_array['run_in_background']) == 1) {

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
					$this::processSuitablePost($suitable_post, "auto");
				}
			}
		}

		// is this user actually using the automated option for adding new content from the BCS collection?
		if ($bigcontentsearch_settings_array['add_posts_every_x_days'] != "0") {

			// has it been long enough since the last automatically added post?
			$required_time_difference = abs(doubleval($bigcontentsearch_settings_array['add_posts_every_x_days'])) * 24 * 60 * 60;
			$min_time = time() - $required_time_difference;

			// find the most recent activity that has to do with adding new posts
			$most_recent_suitable_action_log_entry = false;
			$action_log = $this->getOption("action_log");
			$reversed_action_log = array_reverse($action_log);
			if (is_array($reversed_action_log) && count($reversed_action_log) > 0) {
				foreach ($reversed_action_log as $action_log_entry) {
					if ($action_log_entry[1] == false) {
						$most_recent_suitable_action_log_entry = $action_log_entry;
						break;
					}
				}
			}

			$found_no_existing_fetched_posts = ($most_recent_suitable_action_log_entry === false) ? true : false;
			$found_old_enough_fetched_post = (is_array($most_recent_suitable_action_log_entry) && $most_recent_suitable_action_log_entry[2] < $min_time) ? true : false;

			if ($found_no_existing_fetched_posts || $found_old_enough_fetched_post) {

				// most recent suitable activity is old enough, it's time to fetch new content and create a new post
				$primary_keywords_count = count($bigcontentsearch_settings_array['primary_keywords']);
				if ($primary_keywords_count < 1) {
					// store this activity into an actual option
					$this::storeActivityIntoOption("Error: You haven't provided any primary Keywords or Key Phrases for Big Content Search on the Settings page. Unable to create a brand new post, based on PLR content.", "adding");
				} else {
					$primary_keyword_random_index = rand(0, $primary_keywords_count - 1);
					$bigcontentsearch_keyword = trim($bigcontentsearch_settings_array['primary_keywords'][$primary_keyword_random_index]);
					if ($bigcontentsearch_keyword) {
						$spinning_requested = (intval($bigcontentsearch_settings_array['spin_new_posts']) == 1) ? true : false;
						$this::addNewPost($bigcontentsearch_keyword, $spinning_requested, "auto");
					}
				}
			}
		}
	}

	/**
	 * Fetch content from Big Content Search and create a new post
	 * @param $bigcontentsearch_keyword
	 * @param $spinning_requested
	 * @param $manual_or_auto
	 * @return Array
	 */
	public function addNewPost($bigcontentsearch_keyword, $spinning_requested, $manual_or_auto) {
		// create a link to the Settings page of this plugin
		$settings_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Settings");

		// get all custom options
		$spinrewriter_settings_array = $this->getOption("spinrewriter_settings");
		$bigcontentsearch_settings_array = $this->getOption("bigcontentsearch_settings");

		// get a list of already fetched Big Content Search articles
		$list_of_used_bcs_article_ids = $this->getOption("used_bcs_articles");

		require_once("SpinRewriterWPPlugin_BigContentSearchAPI.php");

		$bcs_api = new SpinRewriterWPPlugin_BigContentSearchAPI($bigcontentsearch_settings_array['email_address'], $bigcontentsearch_settings_array['api_key']);

		$response = $bcs_api->searchWithoutDuplicates("\"{$bigcontentsearch_keyword}\"", $list_of_used_bcs_article_ids);

		if (intval($response['status_code']) == 8) {
			// authentication error
			$wp_error_message = __("Your Big Content Search email address and API key seem to be wrong. Please update them on the <a href='{$settings_page_url}#bigContentSearchSettings' title='Spin Rewriter Plugin - Settings'>Settings page</a>.", "spin-rewriter-wp-plugin");

			// store this activity into an actual option
			$this::storeActivityIntoOption("Error: Your Big Content Search email address and API key seem to be wrong. Unable to create a brand new post, based on PLR content.", "adding");

			// something went wrong
			$processing_return_value = array(
				"status" => "ERROR",
				"message" => $wp_error_message
			);

			return $processing_return_value;
		} else if (intval($response['status_code']) == 100) {
			// no articles found
			$wp_error_message = __("Big Content Search couldn't find any articles for your primary Keyword or Key Phrase. Please try with another keyword.", "spin-rewriter-wp-plugin");

			// store this activity into an actual option
			$this::storeActivityIntoOption("Error: Big Content Search couldn't find any articles for your primary Keyword or Key Phrase: &raquo;{$bigcontentsearch_keyword}&laquo;. Unable to create a brand new post, based on PLR content.", "adding");

			// something went wrong
			$processing_return_value = array(
				"status" => "ERROR",
				"message" => $wp_error_message
			);

			return $processing_return_value;
		} else if (intval($response['status_code']) == 110) {
			// daily download limit reached
			$wp_error_message = __("You have reached the daily download limit for Big Content Search. Please try again tomorrow.", "spin-rewriter-wp-plugin");

			// store this activity into an actual option
			$this::storeActivityIntoOption("Error: You have reached the daily download limit for Big Content Search. Unable to create a brand new post, based on PLR content.", "adding");

			// something went wrong
			$processing_return_value = array(
				"status" => "ERROR",
				"message" => $wp_error_message
			);

			return $processing_return_value;
		} else if ($response['error_msg']) {
			$wp_error_message = __("Big Content Search returned an error message: " . trim($response['error_msg']), "spin-rewriter-wp-plugin");

			// store this activity into an actual option
			$this::storeActivityIntoOption("Error: Big Content Search returned an error message: &raquo;" . trim($response['error_msg']) . "&laquo;. Unable to create a brand new post, based on PLR content.", "adding");

			// something went wrong
			$processing_return_value = array(
				"status" => "ERROR",
				"message" => $wp_error_message
			);

			return $processing_return_value;
		} else if (!$response['response']['uid']) {
			$wp_error_message = __("Big Content Search couldn't find any articles for your primary Keyword or Key Phrase. Please try with another keyword.", "spin-rewriter-wp-plugin");

			// store this activity into an actual option
			$this::storeActivityIntoOption("Error: Big Content Search couldn't find any articles for your primary Keyword or Key Phrase: &raquo;{$bigcontentsearch_keyword}&laquo;. Unable to create a brand new post, based on PLR content.", "adding");

			// something went wrong
			$processing_return_value = array(
				"status" => "ERROR",
				"message" => $wp_error_message
			);

			return $processing_return_value;
		} else {

			$article_title = trim($response['response']['title']);
			$article_body = trim($response['response']['text']);
			$article_uid = trim($response['response']['uid']);

			// "clean" the article title
			$replace_array = array("SUBJECT: ", "TITLE: ", "[SUBJECT]", "[TITLE]", "[FIRSTNAME], ", "[FIRSTNAME]",
				"[YOUR NAME HERE]", "YOUR NAME HERE", "[YOUR NAME]", "YOUR NAME", "[NAME]", "NAME",
				"[YOUR SIGNATURE HERE]", "YOUR SIGNATURE HERE", "[YOUR SIGNATURE]", "YOUR SIGNATURE", "[SIGNATURE]", "SIGNATURE");
			$article_title = str_replace($replace_array, null, $article_title);

			// "clean" the article body
			$article_lines = explode("\n", $article_body);
			if (is_array($article_lines) && count($article_lines) > 0) {
				foreach ($article_lines as $key => $article_line) {
					$article_lines[$key] = str_replace($replace_array, null, $article_line);
				}
			}
			$article_body = implode("\n", $article_lines);
			$article_body = trim($article_body);

			// remove the title from the body (otherwise it would be duplicate)
			if (substr($article_body, 0, strlen($article_title)) == $article_title) {
				$article_body = trim(substr($article_body, strlen($article_title)));
			}

			// remember that we have already fetched this article for this user
			$list_of_used_bcs_article_ids[] = $article_uid;
			$this->updateOption("used_bcs_articles", $list_of_used_bcs_article_ids);



			// do we need to spin this article before we publish it?
			if ($spinning_requested) {

				require_once("SpinRewriterWPPlugin_SpinRewriterAPI.php");

				$spinrewriter_api = new SpinRewriterWPPlugin_SpinRewriterAPI($spinrewriter_settings_array['email_address'], $spinrewriter_settings_array['api_key']);

				$spinrewriter_api->setAutoProtectedTerms($spinrewriter_settings_array['auto_protected_keywords']);

				if (count($spinrewriter_settings_array['protected_keywords']) > 0) {
					$spinrewriter_api->setProtectedTerms($spinrewriter_settings_array['protected_keywords']);
				}

				$spinrewriter_api->setConfidenceLevel($spinrewriter_settings_array['confidence_level']);

				$spinrewriter_api->setAutoSentences($spinrewriter_settings_array['auto_sentences']);
				$spinrewriter_api->setAutoParagraphs($spinrewriter_settings_array['auto_paragraphs']);
				$spinrewriter_api->setAutoNewParagraphs($spinrewriter_settings_array['auto_new_paragraphs']);

				$spinrewriter_api->setAutoSentenceTrees($spinrewriter_settings_array['auto_sentence_trees']);

				$spinrewriter_api->setUseOnlySynonyms($spinrewriter_settings_array['use_only_synonyms']);

				$article_full = $article_title . "\nSPINREWRITER_WP_PLUGIN_POST_TITLE_BODY_SEPARATOR\n" . $article_body;
				$response = $spinrewriter_api->getUniqueVariation($article_full);

				if ($response['status'] == "ERROR") {
					$spinrewriter_error_message = __("Spin Rewriter returned an error message: " . trim($response['response']), "spin-rewriter-wp-plugin");
				} else {
					$spun_article = trim($response['response']);
					$spun_article_explode = explode("SPINREWRITER_WP_PLUGIN_POST_TITLE_BODY_SEPARATOR", $spun_article);
					$article_title = trim($spun_article_explode[0]);
					$article_body = trim($spun_article_explode[1]);
				}
			}

			// manually added posts should be "DRAFT", automatically added posts should be "PUBLISH"
			$draft_or_publish = ($manual_or_auto == "manual") ? "draft" : "publish";

			// create a new post object
			$new_post_object = array(
				'post_title'    => $article_title,
				'post_content'  => $article_body,
				'post_status'   => $draft_or_publish
			);

			// insert the post into the database and fetch its ID
			$new_post_id = wp_insert_post($new_post_object);

			// create a link to this post for our user
			$edit_post_url = admin_url("post.php?post={$new_post_id}&action=edit");

			if ($manual_or_auto == "manual") {
				// this adding of a new post was triggered by hand (manually)

				// store this activity into an actual option
				$this::storeActivityIntoOption("Created <a href=\"{$edit_post_url}\">a brand new post &raquo;{$article_title}&laquo;</a>, based on PLR content, manually.", "adding");

				if ($spinrewriter_error_message) {
					// we successfully fetched an article, BUT we were unable to spin it
					$wp_success_message = "Your <a href=\"{$edit_post_url}\">new post &raquo;{$article_title}&laquo;</a> was successfully created, but it wasn't spun.<br />";
					$wp_success_message .= "{$spinrewriter_error_message}<br /><br />";
					$wp_success_message .= "<a href=\"{$edit_post_url}\" title=\"Edit and publish your new post\">You can EDIT and PUBLISH your new post by clicking here.</a>";
				} else {
					// everything worked correctly
					$wp_success_message = "Your <a href=\"{$edit_post_url}\">new post &raquo;{$article_title}&laquo;</a> was successfully created and is ready to be previewed.<br /><br />";
					$wp_success_message .= "<a href=\"{$edit_post_url}\" title=\"Edit and publish your new post\">You can EDIT and PUBLISH your new post by clicking here.</a>";
				}

				$processing_return_value = array(
					"status" => "OK",
					"message" => $wp_success_message
				);

				return $processing_return_value;
			} else {
				// this adding of a new post was triggered by a cron job (automatically)

				// store this activity into an actual option
				$this::storeActivityIntoOption("Created <a href=\"{$edit_post_url}\">a brand new post &raquo;{$article_title}&laquo;</a>, based on PLR content, automatically.", "adding");

				$processing_return_value = array(
					"status" => "OK",
					"message" => "OK"
				);

				return $processing_return_value;
			}
		}
	}

	/**
	 * Find a suitable post that meets the criteria to be spun and re-published
	 * @return Object
	 */
	public function findSuitablePost() {
		// get all custom options
		$spinrewriter_settings_array = $this->getOption("spinrewriter_settings");

		// find the post that will get spun if the user proceeds right now
		$skip_posts_because_the_user_said_so = $spinrewriter_settings_array['skip_posts'];
		$skip_posts_because_they_were_already_spun = $this->getOption("spun_posts");
		$suitable_post = null;
		$all_suitable_posts = 0;
		$date_limit = time() - (intval($spinrewriter_settings_array['spin_after_x_days']) * 24 * 60 * 60);
		$wp_get_posts_arguments = array(
			'posts_per_page'   => 100000,
			'offset'           => 0,
			'category'         => '',
			'orderby'          => 'post_date',
			'order'            => 'ASC',
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
		if (is_array($wp_posts_array) && count($wp_posts_array) > 0) {
			foreach ($wp_posts_array as $wp_post) {
				if (!in_array($wp_post->ID, $skip_posts_because_the_user_said_so) && !in_array($wp_post->ID, $skip_posts_because_they_were_already_spun)) {
					if (strtotime($wp_post->post_date_gmt) < $date_limit) {
						// we found a post that is OLD enough
						$all_suitable_posts++;
						if ($suitable_post === null) {
							// this is the FIRST (and oldest) post that meets our criteria
							$suitable_post = $wp_post;
						}
					}
				}
			}
		}

		if ($all_suitable_posts > 0) {
			// there is at least one suitable post, it's stored in $suitable_post
			return $suitable_post;
		} else {
			return false;
		}
	}

	/**
	 * Process a suitable post in the requested way
	 * @param $suitable_post
	 * @param $manual_or_auto
	 * @return Array
	 */
	public function processSuitablePost($suitable_post, $manual_or_auto) {
		// get all custom options
		$spinrewriter_settings_array = $this->getOption("spinrewriter_settings");

		// we were given a suitable post, it's stored in $suitable_post
		$suitable_post_title = (strlen($suitable_post->post_title) > 70) ? substr($suitable_post->post_title, 0, 69) . "..." : $suitable_post->post_title;
		$suitable_post_date_local = date_i18n(get_option("date_format"), strtotime($suitable_post->post_date_gmt));

		// spin it
		require_once("SpinRewriterWPPlugin_SpinRewriterAPI.php");

		$spinrewriter_api = new SpinRewriterWPPlugin_SpinRewriterAPI($spinrewriter_settings_array['email_address'], $spinrewriter_settings_array['api_key']);

		$spinrewriter_api->setAutoProtectedTerms($spinrewriter_settings_array['auto_protected_keywords']);

		if (count($spinrewriter_settings_array['protected_keywords']) > 0) {
			$spinrewriter_api->setProtectedTerms($spinrewriter_settings_array['protected_keywords']);
		}

		$spinrewriter_api->setConfidenceLevel($spinrewriter_settings_array['confidence_level']);

		$spinrewriter_api->setAutoSentences($spinrewriter_settings_array['auto_sentences']);
		$spinrewriter_api->setAutoParagraphs($spinrewriter_settings_array['auto_paragraphs']);
		$spinrewriter_api->setAutoNewParagraphs($spinrewriter_settings_array['auto_new_paragraphs']);

		$spinrewriter_api->setAutoSentenceTrees($spinrewriter_settings_array['auto_sentence_trees']);

		$spinrewriter_api->setUseOnlySynonyms($spinrewriter_settings_array['use_only_synonyms']);

		// do we need to spin the title as well?
		if (intval($spinrewriter_settings_array['spin_titles']) == 1) {
			$content_to_spin = trim($suitable_post->post_title) . "\nSPINREWRITER_WP_PLUGIN_POST_TITLE_BODY_SEPARATOR\n" . trim($suitable_post->post_content);
		} else {
			$content_to_spin = trim($suitable_post->post_content);
		}
		$response = $spinrewriter_api->getUniqueVariation($content_to_spin);

		if ($response['status'] == "ERROR") {
			// store this activity into an actual option
			$this::storeActivityIntoOption("Error: Spin Rewriter returned an error message: &raquo;" . trim($response['response']) . "&laquo;. Unable to spin and re-publish a new post.", "spinning");

			// there was en error with spinning
			$processing_return_value = array(
				"status" => "ERROR",
				"message" => __("This operation did NOT succeed.<br /><br />Spin Rewriter returned an error message: " . trim($response['response']), "spin-rewriter-wp-plugin")
			);
			return $processing_return_value;
		} else {
			// did we spin the title as well?
			if (intval($spinrewriter_settings_array['spin_titles']) == 1) {
				$spun_post = trim($response['response']);
				$spun_post_explode = explode("SPINREWRITER_WP_PLUGIN_POST_TITLE_BODY_SEPARATOR", $spun_post);
				$post_title = trim($spun_post_explode[0]);
				$post_content = trim($spun_post_explode[1]);
			} else {
				$post_title = trim($suitable_post->post_title);
				$post_content = trim($response['response']);
			}

			// publish it as a new post, OR update the existing post
			if (intval($spinrewriter_settings_array['publish_as_new']) == 1) {
				// publish it as a new post using the freshly spun content

				// create a new post array
				$new_post_object = array(
					'post_title'    => $post_title,
					'post_content'  => $post_content,
					'post_status'   => "publish"
				);

				// insert the post into the database and fetch its ID
				$new_post_id = wp_insert_post($new_post_object);

				// create a link to this post for our user
				$edit_post_url = admin_url("post.php?post={$new_post_id}&action=edit");

				// create a link to the old post for our user
				$edit_old_post_url = admin_url("post.php?post={$suitable_post->ID}&action=edit");

				// mark the old post as "spun" so it doesn't get used again in the future
				$spun_posts = $this->getOption("spun_posts");
				if (!is_array($spun_posts)) {
					$spun_posts = array();
				}
				$spun_posts[] = $suitable_post->ID;
				$this->updateOption("spun_posts", $spun_posts);

				$new_post_title = (strlen($post_title) > 70) ? substr($post_title, 0, 69) . "..." : $post_title;

				if ($manual_or_auto == "manual") {
					// this processing was triggered by hand (manually)

					// store this activity into an actual option
					$this::storeActivityIntoOption("Published <a href=\"{$edit_post_url}\">a new post &raquo;{$new_post_title}&laquo;</a>, based on <a href=\"{$edit_old_post_url}\">the old post &raquo;{$suitable_post_title}&laquo;</a>, manually.", "spinning");

					// output the success message
					$wp_success_message = "A brand new post entitled &raquo;<a href=\"{$edit_post_url}\" title=\"Edit your new post\"><i>{$new_post_title}</i></a>&laquo; was successfully created.<br /><br />";
					$wp_success_message .= "This new post is based on the old post &raquo;<a href=\"{$edit_old_post_url}\" title=\"Edit the old post\"><i>{$suitable_post_title}</i></a>&laquo; from {$suitable_post_date_local}.";

					$processing_return_value = array(
						"status" => "OK",
						"message" => $wp_success_message
					);

					return $processing_return_value;
				} else {
					// this processing was triggered by a cron job (automatically)

					// store this activity into an actual option
					$this::storeActivityIntoOption("Published <a href=\"{$edit_post_url}\">a new post &raquo;{$new_post_title}&laquo;</a>, based on <a href=\"{$edit_old_post_url}\">the old post &raquo;{$suitable_post_title}&laquo;</a>, automatically.", "spinning");

					$processing_return_value = array(
						"status" => "OK",
						"message" => "OK"
					);

					return $processing_return_value;
				}
			} else {
				// update the existing post with the freshly spun content

				// create an array that will update the existing post
				$updated_post_object = array(
					'ID'            => $suitable_post->ID,
					'post_title'    => $post_title,
					'post_content'  => $post_content,
					'post_status'   => "publish"
				);

				// update the post
				wp_update_post($updated_post_object);

				// create a link to the post for our user
				$edit_post_url = admin_url("post.php?post={$suitable_post->ID}&action=edit");

				// mark this post as "spun" so it doesn't get used again in the future
				$spun_posts = $this->getOption("spun_posts");
				if (!is_array($spun_posts)) {
					$spun_posts = array();
				}
				$spun_posts[] = $suitable_post->ID;
				$this->updateOption("spun_posts", $spun_posts);

				$updated_post_title = (strlen($post_title) > 70) ? substr($post_title, 0, 69) . "..." : $post_title;

				if ($manual_or_auto == "manual") {
					// this processing was triggered by hand (manually)

					// store this activity into an actual option
					$this::storeActivityIntoOption("Spun <a href=\"{$edit_post_url}\">the existing post &raquo;{$updated_post_title}&laquo;</a> and updated it in-place without publishing a new post, manually.", "spinning");

					// output the success message
					$wp_success_message = "The existing post &raquo;<a href=\"{$edit_post_url}\" title=\"Edit your post\"><i>{$updated_post_title}</i></a>&laquo; was successfully spun and updated in-place without publishing a new post.";

					$processing_return_value = array(
						"status" => "OK",
						"message" => $wp_success_message
					);

					return $processing_return_value;
				} else {
					// this processing was triggered by a cron job (automatically)

					// store this activity into an actual option
					$this::storeActivityIntoOption("Spun <a href=\"{$edit_post_url}\">the existing post &raquo;{$updated_post_title}&laquo;</a> and updated it in-place without publishing a new post, automatically.", "spinning");

					$processing_return_value = array(
						"status" => "OK",
						"message" => "OK"
					);

					return $processing_return_value;
				}
			}
		}
	}
}