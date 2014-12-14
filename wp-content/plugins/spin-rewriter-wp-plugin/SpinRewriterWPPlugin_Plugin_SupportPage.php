<?php

	$about_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_About");
	$settings_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Settings");
	$spin_old_posts_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Spin_Old_Posts");
	$add_new_posts_page_url = admin_url("admin.php?page=SpinRewriterWPPlugin_Plugin_Submenu_Add_New_Posts");

	// create PAGE HTML
	$faq_counter = 1;

?>

	<div class="wrap">
		<h2><?php _e("Frequently Asked Questions", "spin-rewriter-wp-plugin"); ?></h2>



		<h3><?php echo $faq_counter++; ?>) <span style="background-color:#FDED5D;"><b>
			What will Spin Rewriter WP Plugin do for me?
		</b></span></h3>
		<p><b>Answer:</b>
			That's a great question &mdash; and I'm happy to say that
			Spin Rewriter WP Plugin is going to help you in 2 different &amp; very powerful ways.
			You will find
			<a href="<?php echo $about_page_url; ?>" title="Spin Rewriter Plugin - About">a short video
			that reveals the true power of Spin Rewriter WP Plugin on the About page</a>.
		</p>



		<h3 style="margin-top:27px;"><?php echo $faq_counter++; ?>) <span style="background-color:#FDED5D;"><b>
			Can you guide me through all of the settings on the
			<a href="<?php echo $settings_page_url; ?>" title="Spin Rewriter Plugin - Settings">Settings page</a>?
		</b></span></h3>
		<p><b>Answer:</b>
			Absolutely, happy to! Check out this Tutorial Video right here:<br />
			<iframe width="560" height="315" src="//www.youtube.com/embed/r5gqDYw6fMg?rel=0" frameborder="0" allowfullscreen></iframe>
		</p>



		<h3 style="margin-top:27px;"><?php echo $faq_counter++; ?>) <span style="background-color:#FDED5D;"><b>
			Can I spin and re-publish old posts manually?
		</b></span></h3>
		<p><b>Answer:</b>
			Sure. On the
			<a href="<?php echo $settings_page_url; ?>" title="Spin Rewriter Plugin - Settings">Settings page</a>
			you can decide whether you want old posts spun automatically or manually (by clicking a button).
		</p>



		<h3 style="margin-top:27px;"><?php echo $faq_counter++; ?>) <span style="background-color:#FDED5D;"><b>
			What happens when an old post is spun and re-published?
		</b></span></h3>
		<p><b>Answer:</b>
			First off, you can pick a specific age (X days, X weeks, X months, ...) that your posts need to reach
			before they get spun. When a post reaches this specified age, it will get spun and then 2 different things
			can happen. The newly created post can either be published as a brand new post, OR it can replace the
			existing post. You can decide which of these 2 actions you prefer on the
			<a href="<?php echo $settings_page_url; ?>" title="Spin Rewriter Plugin - Settings">Settings page</a>.
		</p>



		<h3 style="margin-top:27px;"><?php echo $faq_counter++; ?>) <span style="background-color:#FDED5D;"><b>
			Can I fine-tune the spinning process?
		</b></span></h3>
		<p><b>Answer:</b>
			Yup. On the <a href="<?php echo $settings_page_url; ?>" title="Spin Rewriter Plugin - Settings">Settings page</a>
			you'll see a blue &raquo;Click here to show advanced ENL Spinning Settings...&laquo; next to where it says
			&raquo;ENL Spinning Settings&laquo;. Clicking this link will give you access to the detailed settings for
			the Spin Rewriter spinning process: Confidence Level, Auto-Protected Keywords, Your own list of Protected Keywords,
			Spinning on Sentence-Level, Spinning on Paragraph-Level, Changing of Sentence Structure, etc.
		</p>



		<h3 style="margin-top:27px;"><?php echo $faq_counter++; ?>) <span style="background-color:#FDED5D;"><b>
			How can I create brand new posts, based on PLR articles, in seconds?
		</b></span></h3>
		<p><b>Answer:</b>
			You will need to enter your
			<a href="http://www.spinrewriter.com/big-content-search-exclusive" title="Big Content Search" target="_blank">Big Content Search</a>
			email address and API key on the
			<a href="<?php echo $settings_page_url; ?>#bigContentSearchSettings" title="Spin Rewriter Plugin - Settings">Settings page</a>
			in order to use this feature. Big Content Search comes with a huge collection of 126,700 high quality PLR articles,
			and these articles can be used to create new posts for your website automatically. Of course you can choose to
			have these articles spun before they are published as posts on your website, to make sure they are unique.
		</p>



		<h3 style="margin-top:27px;"><?php echo $faq_counter++; ?>) <span style="background-color:#FDED5D;"><b>
			Do all these brand new posts get published automatically?
		</b></span></h3>
		<p><b>Answer:</b>
			It depends. If you create a new post based on
			<a href="http://www.spinrewriter.com/big-content-search-exclusive" title="Big Content Search" target="_blank">Big Content Search</a>'s
			collection of 126,700 PLR articles manually (by clicking a button on the
			<a href="<?php echo $add_new_posts_page_url; ?>" title="Spin Rewriter Plugin - Add New Posts">Add New Posts page</a>),
			your new post will be saved as &raquo;Draft&laquo; and you will be given a chance to preview it before
			publishing it. On the other hand, if new posts are created for you automatically
			(every X hours, or every X days, or every X weeks, ...), then these new posts will be published automatically
			so your website never runs out of fresh content even if you don't touch anything at all.
		</p>



		<h3 style="margin-top:27px;"><?php echo $faq_counter++; ?>) <span style="background-color:#FDED5D;"><b>
			Can you tell me more about the automated spinning and re-publishing feature?
		</b></span></h3>
		<p><b>Answer:</b>
			Sure! If you decide to go with the automated spinning and re-publishing option,
			you can pick a specific age (X days, X weeks, X months, ...) that your posts need to reach
			before they get spun. Spin Rewriter WP Plugin will then perform a check every hour to see
			if any posts are old enough to meet the criteria. If it finds posts that need to be spun and re-published,
			it will spin and re-publish one post every hour until there are no more old posts in the queue.
		</p>



		<h3 style="margin-top:27px;"><?php echo $faq_counter++; ?>) <span style="background-color:#FDED5D;"><b>
			Can you tell me more about the automated posting feature?
		</b></span></h3>
		<p><b>Answer:</b>
			Absolutely. If you pick the automated posting feature,
			you will first have to decide how often you want to have brand new posts published automatically.
			The frequency can range from a couple of brand new posts per day, to one post a week,
			to one post every couple of months... whichever way you like it.
			Spin Rewriter WP Plugin will then perform a check every hour to see
			if it needs to create a new post for you. If it does, it will fetch a high quality PLR article from
			<a href="http://www.spinrewriter.com/big-content-search-exclusive" title="Big Content Search" target="_blank">Big Content Search</a>'s
			collection of 126,700 PLR articles, it will spin it for maximum uniqueness, and then it will publish it for you.
		</p>



		<h3 style="margin-top:27px;"><?php echo $faq_counter++; ?>) <span style="background-color:#FDED5D;"><b>
			Sometimes the automated features don't work. What's up with that?!
		</b></span></h3>
		<p><b>Answer:</b>
			Due to the way WordPress works, you should understand what's happening in the background.
			Spin Rewriter WP Plugin wants to spin and re-publish your old posts and fetch brand new posts from the
			PLR database for you once every hour. However (and this is important!) WordPress handles scheduled hourly tasks
			in a somewhat peculiar manner. Every time someone visits a page on your website, WordPress checks to see if
			any of the scheduled hourly tasks need to be executed. This is based on the interval since the last
			execution of the hourly task &mdash; WordPress checks if one hour has already passed since the previous time.
			For example, our task is scheduled to run hourly. Let's say that it was last run at 4:23 PM.
			When someone visits a page on your website at 5:21 PM, our task will be skipped because it was
			last run less than an hour prior. A few minutes later, at 5:25 PM, when another person visits
			a page on your website, our task will be run. This means that if your website gets very few visitors
			(for example, 5 per day), the hourly task will be run at most 5 times per day instead of 24 times.
			You can resolve this by making sure someone visits your website at least once per hour. Because your
			website will always be full of fresh content, it's almost guaranteed that Google &amp; Bing &amp; Yahoo
			indexing robots will visit your website very often and this problem will quickly solve itself.
		</p>



		<br /><br />
		<h2><?php _e("Our Support Department", "spin-rewriter-wp-plugin"); ?></h2>

		<p>
			The entire <a href="http://www.spinrewriter.com/">Spin Rewriter</a>
			Customer Support Department is always more than happy to help you.
		</p>

		<p>
			If you have any questions whatsoever, feel absolutely free to shoot us an email at
			<a href="mailto:info@spinrewriter.com">info@spinrewriter.com</a>, or open a
			Support Ticket from within your Spin Rewriter user account.
		</p>

		<p>
			Our only goal is to make sure you are <b>100% satisfied with our services!</b>
		</p>

		<p>
			Thank you for using <a href="http://www.spinrewriter.com/">Spin Rewriter</a> and
			<a href="http://www.spinrewriter.com/wordpress-plugin">Spin Rewriter WP Plugin</a>,
			and we'll definitely keep on doing our very best to make sure YOU succeed.
		</p>

		<p>
			<b>I wish you all the best,<br />
			to your success,</b><br /><br />
			- Aaron Sustar<br />
			<img src="http://www.spinrewriter.com/images/Aaron-signature-transparent.png" title="Aaron's Signature" alt="Aaron's Signature" />
		</p>
	</div>



