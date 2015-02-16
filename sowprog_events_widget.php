<?php

require_once (dirname( __FILE__ ) . '/sowprog_events_configuration.php');
require_once (dirname( __FILE__ ) . '/sowprog_events_output.php');

class SowprogEventsWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
				'sp_event_widget', // Base ID
				__( 'Evénements Sowprog', 'sowprog_events' ), // Name
				array( 'description' => __( 'Evénements Sowprog', 'sowprog_events' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		$sowprogEventsOutput = new SowprogEventsOutput();
		if ( ! empty( $instance['sowprog_widget_title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['sowprog_widget_title'] ). $args['after_title'];
		}
		
		if ($instance['sowprog_widget_use_javascript']) {
			echo $sowprogEventsOutput->output_widget_javascript($instance['sowprog_widget_events_count']);
		} else {
			echo $sowprogEventsOutput->output_widget($instance['sowprog_widget_events_count']);
		}
		
		$sowprogEventsConfiguration = new SowprogEventsConfiguration();
		echo '<a href="' . $sowprogEventsConfiguration->getAgendaPageFullURL() . '">'. $instance['sowprog_widget_more_events_text'] .'</a>';
		
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$sowprog_widget_title = ! empty( $instance['sowprog_widget_title'] ) ? $instance['sowprog_widget_title'] : __( 'Prochains événements', 'sowprog_events' );
		$sowprog_widget_events_count = ! empty( $instance['sowprog_widget_events_count'] ) ? $instance['sowprog_widget_events_count'] : __( '5', 'sowprog_events' );
		$sowprog_widget_more_events_text = ! empty( $instance['sowprog_widget_more_events_text'] ) ? $instance['sowprog_widget_more_events_text'] : __( 'Tous les événements', 'sowprog_events' );
		?>
<p>
	<label for="<?php echo $this->get_field_id( 'sowprog_widget_title' ); ?>">
		<?php _e( 'Titre : ' ); ?>
	</label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'sowprog_widget_title' ); ?>" name="<?php echo $this->get_field_name( 'sowprog_widget_title' ); ?>" type="text" value="<?php echo esc_attr( $sowprog_widget_title ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'sowprog_widget_events_count' ); ?>">
		<?php _e( 'Nombre à afficher : ' ); ?>
	</label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'sowprog_widget_events_count' ); ?>" name="<?php echo $this->get_field_name( 'sowprog_widget_events_count' ); ?>" type="text" value="<?php echo esc_attr( $sowprog_widget_events_count ); ?>">
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'sowprog_widget_more_events_text' ); ?>">
		<?php _e( 'Texte du lien page agenda : ' ); ?>
	</label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'sowprog_widget_more_events_text' ); ?>" name="<?php echo $this->get_field_name( 'sowprog_widget_more_events_text' ); ?>" type="text" value="<?php echo esc_attr( $sowprog_widget_more_events_text ); ?>">
</p>
<p>
    <input class="checkbox" type="checkbox" <?php checked($instance['sowprog_widget_use_javascript'], 'on'); ?> id="<?php echo $this->get_field_id('sowprog_widget_use_javascript'); ?>" name="<?php echo $this->get_field_name('sowprog_widget_use_javascript'); ?>" /> 
    <label for="<?php echo $this->get_field_id('sowprog_widget_use_javascript'); ?>">Utiliser la version javascript</label>
</p>
<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['sowprog_widget_title'] = ( ! empty( $new_instance['sowprog_widget_title'] ) ) ? strip_tags( $new_instance['sowprog_widget_title'] ) : 'Prochains événements';
		$instance['sowprog_widget_events_count'] = ( ! empty( $new_instance['sowprog_widget_events_count'] ) ) ? strip_tags( $new_instance['sowprog_widget_events_count'] ) : '5';
		$instance['sowprog_widget_more_events_text'] = ( ! empty( $new_instance['sowprog_widget_more_events_text'] ) ) ? strip_tags( $new_instance['sowprog_widget_more_events_text'] ) : 'Tous les événements';
		$instance['sowprog_widget_use_javascript'] = $new_instance['sowprog_widget_use_javascript'];
		return $instance;
	}
}

function register_sp_events_widget() {
	register_widget( 'SowprogEventsWidget' );
}
add_action( 'widgets_init', 'register_sp_events_widget' );


?>