<?php
add_action('widgets_init', 'register_related_stores_widget');
function register_related_stores_widget()
{
	register_widget('related_stores');
}
class related_stores extends WP_Widget // widget class
{
	function related_stores() // widget setting | class name must be same function name
	{
		$widget_ops = array('description' =>
				'Display Related Stores');
		$control_ops = array(
			'width' => 250,
			'height' => 350,
			'id_base' => 'related_stores');
		$this->WP_Widget('related_stores', 'Related Stores', $widget_ops, $control_ops);
	}
	function widget($args, $instance) // display widget
	{
		extract($args);
        $title = $instance['title'];
        $number = $instance['number_show'];
        if(!$number)
            $number = 50;
        global $post;
        $store_id = $post->ID;
		// get the custom post type's taxonomy terms
		$custom_taxterms = wp_get_object_terms($store_id, 'store_category', array('fields' => 'ids'));
		// arguments
		$args = array(
			'post_type' => 'store',
			'post_status' => 'publish',
			'posts_per_page' => $number,
			'orderby' => 'rand',
			'tax_query' => array(array(
					'taxonomy' => 'store_category',
					'field' => 'id',
					'terms' => $custom_taxterms)));
		$related_items = new WP_Query($args);
		$arr_st_ids = array();
		if ($related_items->have_posts())
		{ ?>
            <div>
                <h4 class="widget-title"><?php echo $title; ?></h4>
        <?php
			foreach ($related_items->posts as $p)
			{
                if($p->ID != $store_id):
        ?>
                <ul>
        			<li class="ct_li"><a href="<?php echo get_permalink($p->ID); ?>" title="<?php echo get_post_field('post_title', $p->ID); ?>"><?php echo get_post_field('post_title', $p->ID); ?></a></li>
                </ul>

 <?php
                endif;
            } ?>
            </div>
 <?php
        }
	}

	function update($new_instance, $old_instance) // update widget
	{
		$instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['number_show'] = $new_instance['number_show'];
		return $instance;
	}

	function form($instance) // form for the widget options
	{
?>
        <div style="color: #333;">
    		<p>
    			<label for="<?php echo $this->get_field_id('title');?>"><?php echo 'Title'; ?></label>
    			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php	echo $this->get_field_name('title');?>" value="<?php echo $instance['title'];?>" style="width:100%;" />
    		</p>
            <p>
    			<label for="<?php echo $this->get_field_id('number_show');?>"><?php echo 'Number show (default 50)'; ?></label>
    			<input type="text" id="<?php echo $this->get_field_id('number_show'); ?>" name="<?php	echo $this->get_field_name('number_show');?>" value="<?php echo $instance['number_show'];?>" style="width:100%;" />
    		</p>
        </div>
<?php
	}
}
?>
