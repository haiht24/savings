<?php
add_action('widgets_init', 'register_random_category_widget');
function register_random_category_widget()
{
	register_widget('random_category');
}
class random_category extends WP_Widget // widget class
{
	function random_category() // widget setting | class name must be same function name
	{
		$widget_ops = array('description' =>
				'Display Random Category');
		$control_ops = array(
			'width' => 250,
			'height' => 350,
			'id_base' => 'random_category');
		$this->WP_Widget('random_category', 'Random Category', $widget_ops, $control_ops);
	}
	function widget($args, $instance) // display widget
	{
		extract($args);
        $title = $instance['title'];
        $number = $instance['number_show'];
        if(!$number)
            $number = 50;
        //display random sorted list of terms in a given taxonomy
        $taxonomy = 'store_category';
        $args = array('hide_empty' => false, 'number' => $number);
        $terms = get_terms($taxonomy, $args);
        shuffle($terms);
        if($terms){
        ?>
        <div>
            <h4 class="widget-title"><?php echo $title; ?></h4>
            <?php  foreach($terms as $term):?>

            <ul>
    			<li class="ct_li"><a href="<?php echo get_term_link($term); ?>" title="<?php echo $term->name . ' Coupon Codes'; ?>"><?php echo $term->name; ?></a></li>
            </ul>
            <?php endforeach; ?>
        </div>
        <?php } ?>
 <?php

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
