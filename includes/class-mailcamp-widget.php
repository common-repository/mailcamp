<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://mailcamp.nl
 * @since      1.0.0
 *
 * @package Mailcamp
 * @subpackage Mailcamp/includes
 */

/**
 * The widget-specific functionality of the plugin.
 *
 * Creates the form widget
 *
 * @package Mailcamp
 * @subpackage Mailcamp/includes
 * @author Silas de Rooy <silasderooy@gmail.com>
 */
class MailCamp_Widget extends WP_Widget {

    /**
     * MailCamp_Widget constructor.
     * sets up the widget
     *
     * @since 1.0.0
     */
	public function __construct() {


		$id    = 'mailcamp_widget';
		$title = __( 'MailCamp Widget', 'mailcamp' );

		$options = [
			'classname'   => 'mailcamp_widget',
			'description' => __( 'Small subscription form with only an email field, captcha and GDPR descriptionlink and check', 'mailcamp' ),
		];

		parent::__construct( $id, $title, $options );
	}

	/**
	 * output widget content
	 *
     * @since 1.0.0
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		// outputs the content of the widget
		$widget_shortcode = '';

		if ( isset( $instance['shortcode'] ) ) {
			$widget_shortcode = 'widget_' . $instance['shortcode'] . '';

			add_shortcode( $widget_shortcode, 'mailcamp_shortcode_formulier' );

			echo do_shortcode( '[' . $widget_shortcode . ']');

		}


	}

	/**
	 * output widget form fields
	 *
     * @since 1.0.0
	 * @param array $instance
	 */
	public function form( $instance ) {
		// outputs the widget form fields in the Admin Area
		$id = $this->get_field_id( 'shortcode' );

		$for = $this->get_field_id( 'shortcode' );

		$name = $this->get_field_name( 'shortcode' );

		$label = __( 'Form shortcode:', 'mailcamp' );

		$shortcode = '';

		if ( isset( $instance['shortcode'] ) && ! empty( $instance['shortcode'] ) ) {

			$shortcode = $instance['shortcode'];

		}; ?>

        <p>
            <label for="<?php echo esc_attr( $for ); ?>"><?php echo esc_html( $label ); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr( $id ); ?>"
                      name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( $shortcode ); ?></textarea>
        </p>

		<?php

	}

    /**
     * process widget options
     *
     * @since 1.0.0
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
	public function update( $new_instance, $old_instance ) {
		// process the widget options
		$instance = [];

		if ( isset( $new_instance['shortcode'] ) && ! empty( $new_instance['shortcode'] ) ) {

			$instance['shortcode'] = $new_instance['shortcode'];

		}

		$instance['shortcode'] = str_replace(['[',']'],'', $instance['shortcode']);

		return $instance;

	}

}

/*
 * registers the widget
 * @since 1.0.0
 */
function mailcamp_register_widget() {

	register_widget( 'Mailcamp_widget' );
}

add_action( 'widgets_init', 'mailcamp_register_widget' );