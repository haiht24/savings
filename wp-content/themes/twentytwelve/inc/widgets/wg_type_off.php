<?php
add_action('widgets_init', 'register_type_off_widget');
function register_type_off_widget()
{
	register_widget('type_off');
}
class type_off extends WP_Widget // widget class
{
	function type_off() // widget setting | class name must be same function name
	{
		$widget_ops = array('description' =>
				'Display Coupon Type Off');
		$control_ops = array(
			'width' => 250,
			'height' => 350,
			'id_base' => 'type_off');
		$this->WP_Widget('type_off', 'Type Off', $widget_ops, $control_ops);
	}
	function widget($args, $instance) // display widget
	{
		extract($args);
        $title = $instance['title'];
        global $post;
        $stTitle = get_post_field('post_title', $post->ID);

        $arrTypeOff = get_post_meta($post->ID, 'store_type_off_metadata', true);
        $arrTypeOff = explode(',', $arrTypeOff);
        // remove empty element
        $arrTypeOff = array_filter($arrTypeOff);
        // remove duplicate element
        $arrTypeOff = array_unique($arrTypeOff);
        // trim element in array
        $arrTypeOff = array_map('trim', $arrTypeOff);
        if(count($arrTypeOff) > 0)
        {
        ?>
        <div>
            <h4 class="widget-title"><?php echo $title; ?></h4>
            <?php
                $stPermalink = get_permalink($post->ID);
                foreach($arrTypeOff as $a):
                $slug = $a;
                $slug = strtolower($slug);
                $slug = str_replace('%', ' percent', $slug);
                $slug = str_replace('$', ' dolar', $slug);
                $slug = str_replace(' ', '-', $slug);
            ?>
            <ul>
    			<li class="ct_li"><a href="<?php echo $stPermalink . '/' .$slug ; ?>" title="<?php echo $stTitle .' '. $a; ?>"><?php echo $stTitle .' '. $a; ?></a></li>
            </ul>
        <?php
                endforeach; ?>
            </div>
        <?php
        }
        ?>

        <?php
	}

	function update($new_instance, $old_instance) // update widget
	{
		$instance = $old_instance;
        $instance['title'] = $new_instance['title'];
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
        </div>
<?php
	}
}
?>
