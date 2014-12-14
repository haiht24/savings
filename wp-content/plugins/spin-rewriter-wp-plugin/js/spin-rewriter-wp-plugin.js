jQuery(document).ready(function() {

	// toggle advanced spinning settings (for Spin Rewriter) on click
	jQuery("a.linkSpinRewriterAdvancedSettings").click(function(event) {
		event.preventDefault();

		// find the current state of advanced ENL Spinning Settings (shown or hidden)
		if (jQuery("tr.trSpinRewriterAdvancedSettings").eq(0).is(":visible")) {
			// hide settings
			jQuery("a.linkSpinRewriterAdvancedSettings").html("Click here to show advanced ENL Spinning Settings...");

			jQuery("tr.trSpinRewriterAdvancedSettings").find("td, input, textarea, label, p").slideUp("slow", function() {
				jQuery("tr.trSpinRewriterAdvancedSettings").hide();
			});
		} else {
			// show setings
			jQuery("a.linkSpinRewriterAdvancedSettings").html("Click here to hide advanced ENL Spinning Settings...");

			jQuery("tr.trSpinRewriterAdvancedSettings").find("td, input, textarea, label, p").hide();
			jQuery("tr.trSpinRewriterAdvancedSettings").show();
			jQuery("tr.trSpinRewriterAdvancedSettings").find("td, input, textarea, label, p").slideDown("slow");
		}
	});



	// toggle the textarea for primary keywords (for Big Content Search)
	if (jQuery("select#bigcontentsearch_add_posts_every_x_days").val() != "0") {
		jQuery(".trBigContentSearchAdvancedSettings").show();
	}
	// listen for changes of the dropdown menu
	jQuery("select#bigcontentsearch_add_posts_every_x_days").on("change", function() {
		if (jQuery(this).val() != "0") {
			jQuery(".trBigContentSearchAdvancedSettings").show();
		} else {
			jQuery(".trBigContentSearchAdvancedSettings").hide();
		}
	});



	// delete the custom keyword if user picks one of the existing primary keywords
	jQuery("input[name='bigcontentsearch_primary_keyword']").on("change", function() {
		if (jQuery(this).val() != "PRIMARY_KEYWORD_CUSTOM") {
			jQuery("input#bigcontentsearch_keyword").val("");
		}
	});
	// select the custom keyword radio button if user enters a custom keyword
	jQuery("input#bigcontentsearch_keyword").focus(function() {
		if (jQuery("input[name='bigcontentsearch_primary_keyword']").size() > 0) {
			jQuery("input#bigcontentsearch_primary_keyword_custom").attr("checked", "checked");
		}
	});
});
