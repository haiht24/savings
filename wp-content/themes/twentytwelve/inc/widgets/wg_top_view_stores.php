<?php
add_action('widgets_init', 'register_top_view_stores_widget');
function register_top_view_stores_widget()
{
	register_widget('top_view_stores');
}
class top_view_stores extends WP_Widget // widget class
{
	function top_view_stores() // widget setting | class name must be same function name
	{
		$widget_ops = array('description' =>
				'Display Top View Stores');
		$control_ops = array(
			'width' => 250,
			'height' => 350,
			'id_base' => 'top_view_stores');
		$this->WP_Widget('top_view_stores', 'Top View Stores', $widget_ops, $control_ops);
	}
	function widget($args, $instance) // display widget
	{
		extract($args);
        $title = $instance['title'];
        $number = $instance['number_show'];
        if(!$number)
            $number = 50;
        $qr = "
        SELECT post_id AS store_id, meta_value AS c FROM wp_postmeta WHERE meta_key = 'store_view_count_metadata'
        AND post_id IN (SELECT ID FROM wp_posts WHERE post_type='store' AND post_status='publish')
        ORDER BY meta_value*1 DESC
        LIMIT 0, {$number}
        ";
        global $wpdb;
        $rs = $wpdb->get_results($qr);
        if(count($rs) > 0)
        { ?>
            <h4 class="widget-title"><?php echo $title; ?></h4>
        <?php
            foreach ($rs as $s) {
                $st_link = get_permalink($s->store_id);
                $st_title = get_post_field('post_title', $s->store_id);
        ?>
            <ul>
                <li class="ct_li">
                    <a href="<?php echo $st_link; ?>" title="<?php echo $st_title ?>"><?php echo str_replace('Coupon Codes', '', $st_title); ?></a>
                </li>
            </ul>
        <?php
            }
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
