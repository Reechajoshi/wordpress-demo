<?php
/*
 * @package by Theme Record
 * @auther: MattMao
*/

class theme_widget_flickr extends WP_Widget 
{
	#
	#Constructor
	#
	function theme_widget_flickr()
	{
		$widget_ops = array(
			'classname' => 'widget-flickr', 
			'description' => __('This widget will display a flickr section.', 'TR')
		);
		$this->WP_Widget( THEME_SLUG. '_flickr', THEME_NAME.' &raquo; Flickr', $widget_ops );
	}


	#
	#Form
	#
	function form($instance) 
	{
		$instance = wp_parse_args((array) $instance, array( 
			'title' => 'Flickr',
			'id' => '',
			'limit' => '10'
		));
		$title = strip_tags($instance['title']);
		$id = strip_tags($instance['id']);
		$limit = strip_tags($instance['limit']);
		?>
		<div class="theme-widget-wrap">
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title:','TR'); ?></label>
			<input  id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</div>

		<div class="theme-widget-wrap">
			<label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php esc_html_e('ID:','TR'); ?></label>
			<input  id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" type="text" value="<?php echo esc_attr( $id ); ?>" />
			<p class="theme-description"><a href="http://idgettr.com/" target="_blank">Get your flickr id.</a></p>
		</div>

		<div class="theme-widget-wrap">
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php esc_html_e('Limit:','TR'); ?></label>
			<input  id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />
		</div>
		<?php
	}	


	#
	#Update & save the widget
	#
	function update($new_instance, $old_instance) 
	{
		$instance = $old_instance;	
		foreach($new_instance as $key=>$value)
		{
			$instance[$key]	= strip_tags($new_instance[$key]);
		}

		// Refresh Cache
		delete_transient('theme_latest_filckr_gallery');


		return $instance;
	}


	#
	#Prints the widget
	#
	function widget($args, $instance) 
	{
		extract($args, EXTR_SKIP);
		$title = $instance['title'];
		$flickr_id = $instance['id'];
		$image_count = $instance['limit'];
	?>
	<?php echo $before_widget; ?>
	<?php echo $before_title . $title . $after_title; ?>
	<?php
		echo theme_latest_filckr_gallery($flickr_id, $image_count);
	?>
	<?php echo $after_widget; ?>
	<?php
	}
}

register_widget( 'theme_widget_flickr' );
?>