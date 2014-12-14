<?php

	// create links to various page of this plugin
	$settings_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Settings");
	$spin_old_posts_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Spin_Old_Posts");
	$add_new_posts_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Add_New_Posts");
	$support_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Support");

	// create PAGE HTML

?>

	<div class="wrap">
		<h2><?php _e("About Spin Rewriter WP Plugin", "spin-rewriter-wp-plugin"); ?></h2>

		<p><b>Watch this short video to discover the true power of Spin Rewriter WP Plugin:</b></p>

		<iframe width="560" height="315" src="//www.youtube.com/embed/8I8FA2-vcOs?rel=0" frameborder="0" allowfullscreen></iframe>


		<br /><br /><br />
		<h2>2 Extremely Powerful Things Await:</h2>



		<h3>
			<span style="background-color:#FDED5D;"><b>
			1) Spin and re-publish your old posts to keep your website chock-full of fresh content at all times.
			</b>
		</h3>

		<p>
			<b>Spin Rewriter WP Plugin</b> will always keep an eye on your posts.
			As soon as one of your posts reaches a certain age (for example, 60 days),
			it will be spun automatically, using the powerful ENL Semantic Spinning technology
			built right into Spin Rewriter.
		</p>

		<p>
			This way brand new &amp; relevant posts will be created for you automatically &mdash;
			and this means your website will never run out of fresh content.
		</p>

		<p>
			If you prefer to have full control over everything on your website, you can also
			disable the automated functionality on the
			<a href="<?php echo $settings_page_url; ?>" title="Spin Rewriter Plugin - Settings">Settings page</a>,
			and choose to spin and re-publish old posts manually.
		</p>

		<p>
			Most importantly, the
			<a href="<?php echo $spin_old_posts_page_url; ?>" title="Spin Rewriter Plugin - Spin Old Posts">Spin Old Posts page</a>
			will always let you know exactly what's going on.
		</p>



		<h3>
			<span style="background-color:#FDED5D;"><b>
				2) Create brand new posts for your website, automatically &amp; in seconds.
			</b>
		</h3>

		<p><b>Spin Rewriter WP Plugin</b> has another ace up its sleeve, and it's quite the gamechanger.</p>

		<p>
			By entering your
			<a href="http://www.spinrewriter.com/big-content-search-exclusive" title="Big Content Search" target="_blank">Big Content Search</a>
			email address and API key, you will enable this plugin to automatically create brand new &amp; relevant posts for you.
		</p>

		<p>
			You can enter a list of primary keywords for your posts on the
			<a href="<?php echo $settings_page_url; ?>#bigContentSearchSettings" title="Spin Rewriter Plugin - Settings">Settings page</a>.
			Once you have entered your list of keywords, this plugin will automatically generate new posts
			based on your keywords for you. You can also choose how many brand new posts
			per day / week / month you wish to generate.
		</p>

		<p>
			If you prefer to have full control over the posts that are added to your website,
			you can always disable the automated functionality on the
			<a href="<?php echo $settings_page_url; ?>#bigContentSearchSettings" title="Spin Rewriter Plugin - Settings">Settings page</a>
			and choose to create relevant &amp; fresh content in seconds manually.
		</p>

		<p>
			Finally, you will always find the details about your freshly generated posts on the
			<a href="<?php echo $add_new_posts_page_url; ?>" title="Spin Rewriter Plugin - Add New Posts">Add New Posts page</a>.
		</p>
	</div>



