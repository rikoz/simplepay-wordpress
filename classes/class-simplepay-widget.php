<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

// Creating the widget 
class SimplePay_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
		// Base ID of your widget
		'SimplePay_Widget', 

		// Widget name will appear in UI
		__('SimplePay Widget', 'simplepay_widget_domain'), 

		// Widget description
		array( 'description' => __( 'SimplePay footer widget - Add our "Payments Powered by SimplePay" logo to your website.', 'simplepay_widget_domain' ), ) 
		);
	}

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		if ( isset( $instance[ 'color' ] ) ) {
			$color = $instance[ 'color' ];
		} else {
			$color = 'purple-vertical';
		}

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];

		// This is where you run the code and display the output
		echo '<div style="width: 100%; text-align: center"><a href="https://www.simplepay.ng"><img src="' . SP_DIR_URL . 'assets/img/widget/'. $color . '.png"></a></div>';
		echo $args['after_widget'];
	}

	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'color' ] ) ) {
			$color = $instance[ 'color' ];
		} else {
			$color = 'purple-vertical';
		}
		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'color' ); ?>"><?php _e( 'Logo Color:' ); ?></label> 
			<select class='widefat' id="<?php echo $this->get_field_id('color'); ?>"
					name="<?php echo $this->get_field_name('color'); ?>" type="text">
				<option value='purple-vertical'<?php echo ($color=='purple-vertical')?'selected':''; ?>>
					Purple Vertical
				</option>
				<option value='white-vertical'<?php echo ($color=='white-vertical')?'selected':''; ?>>
					White Vertical
				</option> 
				<option value='purple-horizontal'<?php echo ($color=='purple-horizontal')?'selected':''; ?>>
					Purple Horizontal
				</option>
				<option value='white-horizontal'<?php echo ($color=='white-horizontal')?'selected':''; ?>>
					White Horizontal
				</option> 
			</select>   
		</p>
		<?php 
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['color'] = ( ! empty( $new_instance['color'] ) ) ? strip_tags( $new_instance['color'] ) : '';
		return $instance;
	}
} // Class SimplePay_Widget ends here

// Register and load the widget
function simplepay_load_widget() {
	register_widget( 'SimplePay_Widget' );
}
add_action( 'widgets_init', 'simplepay_load_widget' );


