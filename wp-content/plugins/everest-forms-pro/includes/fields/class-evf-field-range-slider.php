<?php
/**
 * Range Slider field
 *
 * @package EverestForms_Pro\Fields
 * @since   1.3.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Field_Range_Slider Class.
 */
class EVF_Field_Range_Slider extends EVF_Form_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name     = esc_html__( 'Range Slider', 'everest-forms-pro' );
		$this->type     = 'range-slider';
		$this->icon     = 'evf-icon evf-icon-range-slider';
		$this->order    = 140;
		$this->group    = 'advanced';
		$this->settings = array(
			'basic-options'    => array(
				'field_options' => array(
					'label',
					'meta',
					'description',
					'required',
					'required_field_message',
					'step',
					'min_max_values',
				),
			),
			'advanced-options' => array(
				'field_options' => array(
					'skin',
					'handle_color',
					'highlight_color',
					'track_color',
					'show_grid',
					'show_prefix_postfix',
					'use_text_prefix_postfix',
					'show_slider_input',
					'label_hide',
					'default_value',
					'css',
				),
			),
		);

		parent::__construct();
	}

	/**
	 * Hook in tabs.
	 */
	public function init_hooks() {
		add_filter( 'everest_forms_field_properties_' . $this->type, array( $this, 'field_properties' ), 5, 3 );
		add_action( 'everest_forms_shortcode_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * Queue frontend scripts.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function load_assets( $atts ) {
		$form_data = evf()->form->get( $atts['id'], array( 'content_only' => true ) );

		if ( ! empty( $form_data['form_fields'] ) ) {
			$is_enhanced_select = wp_list_filter(
				$form_data['form_fields'],
				array(
					'type' => 'range-slider',
				)
			);

			if ( ! empty( $is_enhanced_select ) ) {
				wp_enqueue_style( 'ion-range-slider' );
				wp_enqueue_script( 'ion-range-slider' );
			}
		}
	}

	/**
	 * Step field option.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function step( $field ) {
		$label       = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'step',
				'value'   => esc_html__( 'Step', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Allows users to enter specific legal number intervals.', 'everest-forms-pro' ),
			),
			false
		);
		$input_field = $this->field_element(
			'text',
			$field,
			array(
				'type'  => 'number',
				'slug'  => 'step',
				'class' => 'evf-input-number-step',
				'value' => ! empty( $field['step'] ) ? esc_attr( $field['step'] ) : 1,
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'step',
				'content' => $label . $input_field,
			)
		);
	}

	/**
	 * Minimum value field option.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function min_value( $field ) {
		$label       = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'min_value',
				'value'   => esc_html__( 'Min Value', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Minimum value user is allowed to enter.', 'everest-forms-pro' ),
			),
			false
		);
		$input_field = $this->field_element(
			'text',
			$field,
			array(
				'type'  => 'number',
				'slug'  => 'min_value',
				'class' => 'evf-input-number',
				'value' => isset( $field['min_value'] ) ? esc_attr( $field['min_value'] ) : '',
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'min_value',
				'content' => $label . $input_field,
			)
		);
	}

	/**
	 * Maximum value field option.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function max_value( $field ) {
		$label       = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'max_value',
				'value'   => esc_html__( 'Max Value', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Maximum value user is allowed to enter.', 'everest-forms-pro' ),
			),
			false
		);
		$input_field = $this->field_element(
			'text',
			$field,
			array(
				'type'  => 'number',
				'slug'  => 'max_value',
				'class' => 'evf-input-number',
				'value' => isset( $field['max_value'] ) ? esc_attr( $field['max_value'] ) : '',
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'max_value',
				'content' => $label . $input_field,
			)
		);
	}

	/**
	 * Minimum/Maximum values field option.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function min_max_values( $field ) {
		echo '<div class="everest-forms-border-container evf-range-slider-min-max">';
		echo '<h4 class="everest-forms-border-container-title">' . esc_html__( 'Min/Max', 'everest-forms-pro' ) . '</h4>';

		ob_start();
		$this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'min_max_values',
				'value'   => esc_html__( 'Minimum and Maximum values', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Enter minimum and maximum values for the range slider field.', 'everest-forms-pro' ),
			)
		);
		echo '<div class="input-group-col-2">';
		$this->field_element(
			'text',
			$field,
			array(
				'type'        => 'number',
				'slug'        => 'min_value',
				'class'       => 'evf-input-number',
				'value'       => isset( $field['min_value'] ) ? esc_attr( $field['min_value'] ) : '',
				'placeholder' => esc_html__( 'Min Value', 'everest-forms-pro' ),
			)
		);
		$this->field_element(
			'text',
			$field,
			array(
				'type'        => 'number',
				'slug'        => 'max_value',
				'class'       => 'evf-input-number',
				'value'       => isset( $field['max_value'] ) ? esc_attr( $field['max_value'] ) : '',
				'placeholder' => esc_html__( 'Max Value', 'everest-forms-pro' ),
			)
		);
		echo '</div>';
		$output = ob_get_clean();

		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'min_max_values',
				'content' => $output,
			)
		);

		echo '</div>';
	}

	/**
	 * Skin for the slider.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function skin( $field ) {
		$label       = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'skin',
				'value'   => esc_html__( 'Skin', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Skin to use for the range slider.', 'everest-forms-pro' ),
			),
			false
		);
		$input_field = $this->field_element(
			'select',
			$field,
			array(
				'type'    => 'select',
				'slug'    => 'skin',
				'class'   => 'evf-range-slider-skin',
				'options' => array(
					'flat'   => esc_html__( 'Flat', 'everest-forms-pro' ),
					'big'    => esc_html__( 'Big', 'everest-forms-pro' ),
					'modern' => esc_html__( 'Modern', 'everest-forms-pro' ),
					'sharp'  => esc_html__( 'Sharp', 'everest-forms-pro' ),
					'round'  => esc_html__( 'Round', 'everest-forms-pro' ),
					'square' => esc_html__( 'Square', 'everest-forms-pro' ),
				),
				'value'   => ! empty( $field['skin'] ) ? esc_attr( $field['skin'] ) : 'round',
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'skin',
				'content' => $label . $input_field,
			)
		);
	}

	/**
	 * Show or Hide grid for the slider.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function show_grid( $field ) {
		$input_field = $this->field_element(
			'checkbox',
			$field,
			array(
				'type'    => 'checkbox',
				'slug'    => 'show_grid',
				'class'   => 'evf-range-slider-show-grid',
				'value'   => isset( $field['show_grid'] ) ? esc_attr( $field['show_grid'] ) : false,
				'desc'    => esc_html__( 'Show Grid For The Slider', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Check this if you want to show grid for this Range Slider field.', 'everest-forms-pro' ),
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'show_grid',
				'content' => $input_field,
			)
		);
	}

	/**
	 * Show or Hide slider input.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function show_slider_input( $field ) {
		$input_field = $this->field_element(
			'checkbox',
			$field,
			array(
				'type'    => 'checkbox',
				'slug'    => 'show_slider_input',
				'class'   => 'evf-show-slider-input',
				'value'   => isset( $field['show_slider_input'] ) ? esc_attr( $field['show_slider_input'] ) : false,
				'desc'    => esc_html__( 'Show Slider Input', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Check this if you want to show input box of this Range Slider field.', 'everest-forms-pro' ),
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'show_slider_input',
				'content' => $input_field,
			)
		);
	}

	/**
	 * Show or Hide slider prefix/postfix.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function show_prefix_postfix( $field ) {
		$input_field = $this->field_element(
			'checkbox',
			$field,
			array(
				'type'    => 'checkbox',
				'slug'    => 'show_prefix_postfix',
				'class'   => 'evf-show-slider-prefix-postfix',
				'value'   => isset( $field['show_prefix_postfix'] ) ? esc_attr( $field['show_prefix_postfix'] ) : false,
				'desc'    => esc_html__( 'Show Slider Prefix/Postfix', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Check this if you want to show Prefix/Postfix of this Range Slider field.', 'everest-forms-pro' ),
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'show_prefix_postfix',
				'content' => $input_field,
			)
		);
	}

	/**
	 * Use texts in prefix and postfix.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function use_text_prefix_postfix( $field ) {
		$use_text_prefix_postfix = isset( $field['use_text_prefix_postfix'] ) && '1' === $field['use_text_prefix_postfix'] ? true : false;
		$show_prefix_postfix     = isset( $field['show_prefix_postfix'] ) && '1' === $field['show_prefix_postfix'] ? true : false;

		$input_field = $this->field_element(
			'checkbox',
			$field,
			array(
				'type'    => 'checkbox',
				'slug'    => 'use_text_prefix_postfix',
				'class'   => 'evf-use-text-prefix-postfix',
				'value'   => isset( $field['use_text_prefix_postfix'] ) ? esc_attr( $field['use_text_prefix_postfix'] ) : false,
				'desc'    => esc_html__( 'Use Texts for Prefix and Postfix', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Check this if you want to use texts in prefix and postfix for this Range Slider field.', 'everest-forms-pro' ),
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'use_text_prefix_postfix',
				'content' => $input_field,
			)
		);

		// Prefix/Postfix warning message.
		echo '<div class="notice notice-warning evf-prefix-postfix-warning-message" style="margin:0 0 12px;"><p>';
		printf(
			'<b>%s</b>: %s <b>%s</b> %s.',
			esc_html__( 'Warning', 'everest-forms-pro' ),
			esc_html__( 'If you don\'t check the option', 'everest-forms-pro' ),
			esc_html__( 'Use Texts for Prefix and Postfix', 'everest-forms-pro' ),
			esc_html__( 'then the min/max values will be used as prefix and postfix', 'everest-forms-pro' )
		);
		echo '</p></div>';

		echo '<div class="everest-forms-border-container evf-range-slider-prefix-postfix-texts">';
		echo '<h4 class="everest-forms-border-container-title">' . esc_html__( 'Prefix/Postfix Texts', 'everest-forms-pro' ) . '</h4>';
		ob_start();
		$this->field_element(
			'label',
			$field,
			array(
				'value'   => esc_html__( 'Texts to use for prefix and postfix', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Enter texts to show in prefix and postfix of this field.', 'everest-forms-pro' ),
			)
		);
		echo '<div class="input-group-col-2">';
		$this->field_element(
			'text',
			$field,
			array(
				'type'        => 'text',
				'slug'        => 'prefix_text',
				'class'       => 'evf-input-prefix-text',
				'value'       => isset( $field['prefix_text'] ) ? esc_attr( $field['prefix_text'] ) : '',
				'placeholder' => esc_html__( 'Prefix Text', 'everest-forms-pro' ),
			)
		);
		$this->field_element(
			'text',
			$field,
			array(
				'type'        => 'text',
				'slug'        => 'postfix_text',
				'class'       => 'evf-input-postfix-text',
				'value'       => isset( $field['postfix_text'] ) ? esc_attr( $field['postfix_text'] ) : '',
				'placeholder' => esc_html__( 'Postfix Text', 'everest-forms-pro' ),
			)
		);
		echo '</div>';
		$output = ob_get_clean();

		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'min_max_values',
				'content' => $output,
			)
		);

		echo '</div>';
	}

	/**
	 * Change handle color.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function handle_color( $field ) {
		$label       = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'handle_color',
				'value'   => esc_html__( 'Slider Handle Color', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Select color for the slider handle.', 'everest-forms-pro' ),
			),
			false
		);
		$input_field = $this->field_element(
			'text',
			$field,
			array(
				'type'  => 'text',
				'slug'  => 'handle_color',
				'class' => 'evf-range-slider-handle-color',
				'value' => isset( $field['handle_color'] ) ? esc_attr( $field['handle_color'] ) : '',
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'handle_color',
				'content' => $label . $input_field,
			)
		);
	}

	/**
	 * Change slider highlight color.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function highlight_color( $field ) {
		$label       = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'highlight_color',
				'value'   => esc_html__( 'Slider Highlight Color', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Select color for the slider highlight.', 'everest-forms-pro' ),
			),
			false
		);
		$input_field = $this->field_element(
			'text',
			$field,
			array(
				'type'  => 'text',
				'slug'  => 'highlight_color',
				'class' => 'evf-range-slider-highlight-color',
				'value' => isset( $field['highlight_color'] ) ? esc_attr( $field['highlight_color'] ) : '',
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'highlight_color',
				'content' => $label . $input_field,
			)
		);
	}

	/**
	 * Change slider track color.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 */
	public function track_color( $field ) {
		$label       = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'track_color',
				'value'   => esc_html__( 'Slider Track Color', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Select color for the slider track.', 'everest-forms-pro' ),
			),
			false
		);
		$input_field = $this->field_element(
			'text',
			$field,
			array(
				'type'  => 'text',
				'slug'  => 'track_color',
				'class' => 'evf-range-slider-track-color',
				'value' => isset( $field['track_color'] ) ? esc_attr( $field['track_color'] ) : '',
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'track_color',
				'content' => $label . $input_field,
			)
		);
	}

	/**
	 * Define additional field properties.
	 *
	 * @param  array $properties Field properties.
	 * @param  array $field      Field settings.
	 * @param  array $form_data  Form data and settings.
	 * @return array of additional field properties.
	 */
	public function field_properties( $properties, $field, $form_data ) {
		// Input primary: step interval.
		if ( ! empty( $field['step'] ) ) {
			$properties['inputs']['primary']['attr']['step'] = (float) $field['step'];
		}

		// Input primary: minimum value.
		if ( ! empty( $field['min_value'] ) ) {
			$properties['inputs']['primary']['attr']['min'] = (float) $field['min_value'];
		}

		// Input primary: maximum value.
		if ( ! empty( $field['max_value'] ) ) {
			$properties['inputs']['primary']['attr']['max'] = (float) $field['max_value'];
		}

		$min_value               = empty( $field['min_value'] ) ? 0 : esc_attr( $field['min_value'] );
		$max_value               = empty( $field['max_value'] ) ? 10 : esc_attr( $field['max_value'] );
		$step                    = empty( $field['step'] ) ? 1 : esc_attr( $field['step'] );
		$skin                    = empty( $field['skin'] ) ? 'round' : esc_attr( $field['skin'] );
		$default_value           = empty( $field['default_value'] ) ? 0 : esc_attr( $field['default_value'] );
		$show_grid               = isset( $field['show_grid'] ) && '1' === $field['show_grid'] ? 'true' : 'false';
		$handle_color            = empty( $field['handle_color'] ) ? '' : esc_attr( $field['handle_color'] );
		$highlight_color         = empty( $field['highlight_color'] ) ? '' : esc_attr( $field['highlight_color'] );
		$track_color             = empty( $field['track_color'] ) ? '' : esc_attr( $field['track_color'] );
		$show_slider_input       = empty( $field['show_slider_input'] ) ? '' : esc_attr( $field['show_slider_input'] );
		$show_prefix_postfix     = empty( $field['show_prefix_postfix'] ) ? '' : esc_attr( $field['show_prefix_postfix'] );
		$use_text_prefix_postfix = empty( $field['use_text_prefix_postfix'] ) ? '' : esc_attr( $field['use_text_prefix_postfix'] );
		$prefix_text             = empty( $field['prefix_text'] ) ? '' : esc_attr( $field['prefix_text'] );
		$postfix_text            = empty( $field['postfix_text'] ) ? '' : esc_attr( $field['postfix_text'] );

		$properties['container']['attr']['style']                           = 'display: none;';
		$properties['inputs']['primary']['class'][]                         = 'evf-field-primary-input';
		$properties['inputs']['primary']['data']['skin']                    = 'round';
		$properties['inputs']['primary']['data']['default']                 = $default_value;
		$properties['inputs']['primary']['data']['from']                    = $default_value;
		$properties['inputs']['primary']['data']['min']                     = $min_value;
		$properties['inputs']['primary']['data']['max']                     = $max_value;
		$properties['inputs']['primary']['data']['step']                    = $step;
		$properties['inputs']['primary']['data']['skin']                    = $skin;
		$properties['inputs']['primary']['data']['grid']                    = $show_grid;
		$properties['inputs']['primary']['data']['handle_color']            = $handle_color;
		$properties['inputs']['primary']['data']['highlight_color']         = $highlight_color;
		$properties['inputs']['primary']['data']['track_color']             = $track_color;
		$properties['inputs']['primary']['data']['show_slider_input']       = $show_slider_input;
		$properties['inputs']['primary']['data']['hide-min-max']            = ( '1' === $show_prefix_postfix ) ? 'false' : 'true';
		$properties['inputs']['primary']['data']['use-text-prefix-postfix'] = ( '1' === $use_text_prefix_postfix ) ? 'true' : 'false';
		$properties['inputs']['primary']['data']['prefix-text']             = $prefix_text;
		$properties['inputs']['primary']['data']['postfix-text']            = $postfix_text;
		$properties['inputs']['primary']['data']['grid-snap']               = 'true';

		return $properties;
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.3.3
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_preview( $field ) {
		// Label.
		$this->field_preview_option( 'label', $field );

		// Prepare Variables.
		$min_value                  = empty( $field['min_value'] ) ? 0 : esc_attr( $field['min_value'] );
		$max_value                  = empty( $field['max_value'] ) ? 10 : esc_attr( $field['max_value'] );
		$skin                       = empty( $field['skin'] ) ? 'round' : esc_attr( $field['skin'] );
		$default_value              = empty( $field['default_value'] ) ? 4 : esc_attr( $field['default_value'] );
		$show_grid                  = isset( $field['show_grid'] ) && '1' === $field['show_grid'] ? 'true' : 'false';
		$show_prefix_postfix        = empty( $field['show_prefix_postfix'] ) ? '' : $field['show_prefix_postfix'];
		$show_prefix_postfix        = ( '1' === $show_prefix_postfix ) ? 'false' : 'true';
		$show_slider_input          = isset( $field['show_slider_input'] ) && '1' === $field['show_slider_input'] ? true : false;
		$slider_input_wrapper_style = '';

		// Primary input.
		echo '<div class="everest-forms-range-slider">';
		echo '<div class="evf-slider-group">';
		echo '<div class="evf-slider">';
		printf(
			'<input type="text" class="evf-range-slider-preview" data-min="%s" data-max="%s" data-from="%s" data-disable="true" data-skin="%s" data-grid="%s" data-hide-min-max="%s" data-grid-snap="true" />',
			esc_attr( $min_value ),
			esc_attr( $max_value ),
			esc_attr( $default_value ),
			esc_attr( $skin ),
			esc_attr( $show_grid ),
			esc_attr( $show_prefix_postfix )
		);
		echo '</div>';

		// Slider Input.
		if ( true === $show_slider_input ) {
			$slider_input_wrapper_style .= 'display:block;';
		} else {
			$slider_input_wrapper_style .= 'display:none;';
		}
		printf( '<div class="evf-slider-input-wrapper" style="%s">', esc_attr( $slider_input_wrapper_style ) );
		printf( '<input type="number" class="evf-slider-input" value="%s" disabled />', esc_attr( $default_value ) );
		echo '</div>';

		// Slider Reset Icon.
		echo '<span class="evf-range-slider-reset-icon dashicons dashicons-image-rotate"></span>';
		echo '</div></div>';

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.3.3
	 * @param array $field Field Data.
	 * @param array $field_atts Field attributes.
	 * @param array $form_data All Form Data.
	 */
	public function field_display( $field, $field_atts, $form_data ) {
		// Define data.
		$primary           = $field['properties']['inputs']['primary'];
		$min_value         = empty( $field['min_value'] ) ? 0 : esc_attr( $field['min_value'] );
		$max_value         = empty( $field['max_value'] ) ? 10 : esc_attr( $field['max_value'] );
		$default_value     = empty( $field['default_value'] ) ? 0 : esc_attr( $field['default_value'] );
		$show_slider_input = isset( $field['show_slider_input'] ) && '1' === $field['show_slider_input'] ? true : false;

		// Range Slider UI.
		echo '<div class="evf-slider-group">';
		echo '<div class="evf-slider">';
		printf(
			'<input type="number" %s %s />',
			evf_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ),
			esc_attr( $primary['required'] )
		);
		echo '</div>';

		// Slider Input.
		if ( true === $show_slider_input ) {
			echo '<div class="evf-slider-input-wrapper">';
			printf(
				'<input type="number" class="evf-slider-input" id="evf-slider-input-%s" value="%s" min="%s" max="%s" />',
				esc_attr( $field['id'] ),
				esc_attr( $default_value ),
				esc_attr( $min_value ),
				esc_attr( $max_value )
			);
			echo '</div>';
		}

		// Slider Reset Icon.
		echo '<span class="evf-range-slider-reset-icon dashicons dashicons-image-rotate"></span>';
		echo '</div>';
	}
}
