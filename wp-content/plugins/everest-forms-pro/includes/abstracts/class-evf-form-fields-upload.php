<?php
/**
 * Abstract uploader helper class
 *
 * @package EverestForms_Pro\Abstracts
 * @version 1.3.0
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Form_Fields_Upload
 */
abstract class EVF_Form_Fields_Upload extends EVF_Form_Fields {

	/**
	 * Files that are not allowed.
	 *
	 * @var array
	 */
	protected $blacklist = array( 'ade', 'adp', 'app', 'asp', 'bas', 'bat', 'cer', 'cgi', 'chm', 'cmd', 'com', 'cpl', 'crt', 'csh', 'csr', 'dll', 'drv', 'exe', 'fxp', 'flv', 'hlp', 'hta', 'htaccess', 'htm', 'htpasswd', 'inf', 'ins', 'isp', 'jar', 'js', 'jse', 'jsp', 'ksh', 'lnk', 'mdb', 'mde', 'mdt', 'mdw', 'msc', 'msi', 'msp', 'mst', 'ops', 'pcd', 'php', 'pif', 'pl', 'prg', 'ps1', 'ps2', 'py', 'rb', 'reg', 'scr', 'sct', 'sh', 'shb', 'shs', 'sys', 'swf', 'tmp', 'torrent', 'url', 'vb', 'vbe', 'vbs', 'vbscript', 'wsc', 'wsf', 'wsf', 'wsh' );

	/**
	 * Hook in tabs.
	 */
	public function init_hooks() {
		add_filter( 'everest_forms_html_field_value', array( $this, 'html_field_value' ), 10, 4 );
		add_filter( 'everest_forms_plaintext_field_value', array( $this, 'plaintext_field_value' ), 10, 4 );
		add_filter( 'everest_forms_field_exporter_' . $this->type, array( $this, 'field_exporter' ) );

		if ( is_callable( array( $this, 'field_properties' ) ) ) {
			add_filter( 'everest_forms_field_properties_' . $this->type, array( $this, 'field_properties' ), 5, 3 );
		}

		// AJAX Events.
		$this->add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public function add_ajax_events() {
		$ajax_events = array(
			'upload_file',
			'remove_file',
		);

		foreach ( $ajax_events as $ajax_event ) {
			add_action( 'wp_ajax_everest_forms_' . $ajax_event, array( $this, $ajax_event ) );
			add_action( 'wp_ajax_nopriv_everest_forms_' . $ajax_event, array( $this, $ajax_event ) );
		}
	}

	/**
	 * Remove the file from the temporary directory.
	 *
	 * @since 1.3.0
	 */
	public function remove_file() {
		$default_error = esc_html__( 'Something went wrong while removing the file.', 'everest-forms-pro' );

		$validated_form_field = $this->ajax_validate_form_field();
		if ( empty( $validated_form_field ) ) {
			wp_send_json_error( $default_error );
		}

		if ( empty( $_POST['file'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_send_json_error( $default_error );
		}

		$file     = sanitize_file_name( wp_unslash( $_POST['file'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$tmp_path = wp_normalize_path( $this->get_tmp_dir() . '/' . $file );

		// Requested file does not exist, which is good.
		if ( ! is_file( $tmp_path ) ) {
			wp_send_json_success( $file );
		}

		if ( @unlink( $tmp_path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			wp_send_json_success( $file );
		}

		wp_send_json_error( $default_error );
	}

	/**
	 * Ajax handler for file upload.
	 *
	 * @since 1.3.0
	 */
	public function upload_file() {
		$default_error = esc_html__( 'Something went wrong, please try again.', 'everest-forms-pro' );

		$validated_form_field = $this->ajax_validate_form_field();
		if ( empty( $validated_form_field ) ) {
			wp_send_json_error( $default_error );
		}

		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_error( esc_html__( 'No file was uploaded', 'everest-forms-pro' ) );
		}

		if ( isset( $_FILES['file']['error'] ) && UPLOAD_ERR_OK !== $_FILES['file']['error'] ) {
			$error         = $_FILES['file']['error']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$error_message = $this->get_upload_validation_errors( $error );
			wp_send_json_error( $error_message );
		}

		// Make sure we have required values from $_FILES.
		if ( empty( $_FILES['file']['name'] ) ) {
			wp_send_json_error( $default_error );
		}
		if ( empty( $_FILES['file']['tmp_name'] ) ) {
			wp_send_json_error( $default_error );
		}

		// Make data available always.
		$this->form_data  = $validated_form_field['form_data'];
		$this->form_id    = $this->form_data['id'];
		$this->field_id   = $validated_form_field['field_id'];
		$this->field_data = $this->form_data['form_fields'][ $this->field_id ];

		$error     = empty( $_FILES['file']['error'] ) ? UPLOAD_ERR_OK : intval( $_FILES['file']['error'] );
		$path      = $_FILES['file']['tmp_name']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$name      = sanitize_file_name( wp_unslash( $_FILES['file']['name'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$extension = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		$errors    = $this->ajax_validate( $error, $extension, $path, $name );

		if ( count( $errors ) ) {
			wp_send_json_error( implode( ',', $errors ) );
		}

		$tmp_dir  = $this->get_tmp_dir();
		$tmp_name = $this->get_tmp_file_name( $extension );
		$tmp_path = wp_normalize_path( $tmp_dir . '/' . $tmp_name );
		$tmp      = $this->move_file( $path, $tmp_path );

		if ( ! $tmp ) {
			wp_send_json_error( $default_error );
		}

		$this->clean_tmp_files();

		wp_send_json_success(
			array(
				'file' => pathinfo( $tmp, PATHINFO_FILENAME ) . '.' . pathinfo( $tmp, PATHINFO_EXTENSION ),
				'name' => $name,
			)
		);
	}

	/**
	 * Clean up the tmp folder - remove all old files every day (filterable interval).
	 */
	protected function clean_tmp_files() {
		$files = glob( trailingslashit( $this->get_tmp_dir() ) . '*' );

		if ( ! is_array( $files ) || empty( $files ) ) {
			return;
		}

		$lifespan = (int) apply_filters( 'everest_forms_field_' . $this->type . '_clean_tmp_files_lifespan', DAY_IN_SECONDS );

		foreach ( $files as $file ) {
			if ( ! is_file( $file ) ) {
				continue;
			}

			// In some cases filemtime() can return false, in that case - pretend this is a new file and do nothing.
			$modified = (int) filemtime( $file );
			if ( empty( $modified ) ) {
				$modified = time();
			}

			if ( ( time() - $modified ) >= $lifespan ) {
				@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
		}
	}

	/**
	 * Ajax handler for errors.
	 *
	 * @param int    $error Errors.
	 * @param string $ext   Extension.
	 * @param string $path  Path to a newly uploaded file.
	 * @param string $name  Name of a newly uploaded file.
	 *
	 * @return array of errors.
	 */
	public function ajax_validate( $error, $ext, $path, $name ) {
		$errors = array();

		// Basic file upload validation.
		if ( UPLOAD_ERR_OK !== $error ) {
			$upload_error_message = $this->get_upload_validation_errors( $error );
			if ( is_string( $upload_error_message ) ) {
				/* translators: %s - error text. */
				$errors[] = sprintf( esc_html__( 'File upload error. %s', 'everest-forms-pro' ), $upload_error_message );
			}
		}

		// Validate file size.
		$max_size = min( wp_max_upload_size(), $this->max_file_size() );

		if ( ! empty( $_FILES ) ) {
			foreach ( $_FILES as $file ) {
				if ( $file['size'] > $max_size ) {
					$errors[] = sprintf(
						/* translators: %s: max upload size */
						esc_html__( 'File exceeds max size allowed (%s).', 'everest-forms-pro' ),
						evf_size_to_megabytes( $max_size )
					);
				}
			}
		}

		// Make sure file has an extension first.
		if ( empty( $ext ) ) {
			$errors[] = esc_html__( 'File must have an extension.', 'everest-forms-pro' );
		}

		// Validate extension against all allowed values.
		if ( ! in_array( $ext, $this->get_extensions(), true ) ) {
			$errors[] = esc_html__( 'File type is not allowed.', 'everest-forms-pro' );
		}

		/*
		 * Validate file against what WordPress is set to allow.
		 * At the end of the day, if you try to upload a file that WordPress
		 * doesn't allow, we won't allow it either. Users can use a plugin to
		 * filter the allowed mime types in WordPress if this is an issue.
		 */
		$wp_filetype = wp_check_filetype_and_ext( $path, $name );

		$ext             = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
		$type            = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];
		$proper_filename = empty( $wp_filetype['proper_filename'] ) ? '' : $wp_filetype['proper_filename'];

		if ( $proper_filename || ! $ext || ! $type ) {
			$errors[] = esc_html__( 'File type is not allowed.', 'everest-forms-pro' );
		}

		return $errors;
	}

	/**
	 * Get all allowed extensions.
	 * Check against user-entered extensions.
	 *
	 * @since 1.3.1
	 *
	 * @param mixed $ext_type Extension type.
	 *
	 * @return array of allowed extensions.
	 */
	protected function get_extensions( $ext_type = '' ) {
		$ext_types  = wp_get_ext_types();
		$mime_types = wp_get_mime_types();

		if ( ! empty( $this->field_data['extensions'] ) ) {
			// User provided specific extensions.
			$extensions = array_diff( explode( ',', strtolower( preg_replace( '/[^A-Za-z0-9,]/', '', $this->field_data['extensions'] ) ) ), $this->blacklist );
		} elseif ( '' !== $ext_type ) {
			$extensions = array_diff( $ext_types[ $ext_type ], $this->blacklist );
		} else {
			// Get default extensions supported by WordPress.
			$extensions = array_diff( explode( '|', implode( '|', array_keys( $mime_types ) ) ), $this->blacklist );
		}

		return $extensions;
	}

	/**
	 * Move file to a permanent location.
	 *
	 * @since 1.3.0
	 *
	 * @param string $filename    The filename of the uploaded file.
	 * @param string $destination The destination of the moved file.
	 *
	 * @return false|string False on error.
	 */
	protected function move_file( $filename, $destination ) {
		$this->create_dir( dirname( $destination ) );

		if ( false === move_uploaded_file( $filename, $destination ) ) {
			$logger = evf_get_logger();
			$logger->error(
				sprintf( 'Upload Error, could not upload file: %s', $filename ),
				array(
					'source' => 'file-upload',
				)
			);

			return false;
		}

		$this->set_file_fs_permissions( $destination );

		return $destination;
	}

	/**
	 * Create both the directory and index.html file in it if any of them doesn't exist.
	 *
	 * @since 1.3.0
	 *
	 * @param string $path Path to the directory.
	 *
	 * @return string Path to the newly created directory.
	 */
	protected function create_dir( $path ) {
		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
		}

		$index = wp_normalize_path( $path . '/index.html' );

		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}

		return $path;
	}

	/**
	 * Set correct file permissions in the file system.
	 *
	 * @since 1.3.0
	 *
	 * @param string $path File to set permissions for.
	 */
	protected function set_file_fs_permissions( $path ) {
		// Set correct file permissions.
		$stat = stat( dirname( $path ) );

		@chmod( $path, $stat['mode'] & 0000666 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Get tmp file name.
	 *
	 * @since 1.3.0
	 *
	 * @param string $extension File extension.
	 *
	 * @return string
	 */
	protected function get_tmp_file_name( $extension ) {
		return wp_hash( wp_rand() . microtime() . $this->form_id . $this->field_id ) . '.' . $extension;
	}

	/**
	 * Get tmp dir for files.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	protected function get_tmp_dir() {
		$uploads  = wp_upload_dir();
		$tmp_root = untrailingslashit( $uploads['basedir'] ) . '/everest_forms_uploads/tmp';

		if ( ! file_exists( $tmp_root ) || ! wp_is_writable( $tmp_root ) ) {
			wp_mkdir_p( $tmp_root );
		}

		$index = trailingslashit( $tmp_root ) . 'index.html';

		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}

		return $tmp_root;
	}

	/**
	 * Ajax validation for form fields.
	 *
	 * @since 1.3.0
	 */
	protected function ajax_validate_form_field() {
		if ( empty( $_POST['form_id'] ) || empty( $_POST['field_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return array();
		}

		$field_id  = sanitize_text_field( wp_unslash( $_POST['field_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$form_data = evf()->form->get(
			(int) $_POST['form_id'], // phpcs:ignore WordPress.Security.NonceVerification
			array(
				'content_only' => true,
			)
		);

		if ( empty( $form_data ) || ! is_array( $form_data ) ) {
			return array();
		}

		return array(
			'form_data' => $form_data,
			'field_id'  => $field_id,
		);
	}

	/**
	 * Allowed Extensions field option.
	 *
	 * @param array $field Field data.
	 */
	public function extensions( $field ) {
		$lbl  = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'extensions',
				'value'   => esc_html__( 'Allowed File Extensions', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Enter the extensions you would like to allow, comma separated.', 'everest-forms-pro' ),
			),
			false
		);
		$fld  = $this->field_element(
			'text',
			$field,
			array(
				'slug'  => 'extensions',
				'value' => ! empty( $field['extensions'] ) ? $field['extensions'] : '',
			),
			false
		);
		$args = array(
			'slug'    => 'extensions',
			'content' => $lbl . $fld,
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * Max file size field option.
	 *
	 * @param array $field Field data.
	 */
	public function max_size( $field ) {
		$lbl  = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'max_size',
				'value'   => esc_html__( 'Max File Size', 'everest-forms-pro' ),
				/* translators: %s - max upload size. */
				'tooltip' => sprintf( esc_html__( 'Enter the max file size, in megabytes, to allow. If left blank, the value defaults to the maximum size the server allows which is %s.', 'everest-forms-pro' ), evf_max_upload() ),
			),
			false
		);
		$fld  = $this->field_element(
			'text',
			$field,
			array(
				'slug'  => 'max_size',
				'value' => ! empty( $field['max_size'] ) ? $field['max_size'] : '',
			),
			false
		);
		$args = array(
			'slug'    => 'max_size',
			'content' => $lbl . $fld,
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * File upload limit option.
	 *
	 * @param array $field Field data.
	 */
	public function max_file_number( $field ) {
		$lbl  = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'max_file_number',
				'value'   => esc_html__( 'Maximum number limit on uploads', 'everest-forms-pro' ),
				'tooltip' => sprintf( esc_html__( 'Enter the number of files you wish the user to upload.', 'everest-forms-pro' ) ),
			),
			true
		);
		$fld  = $this->field_element(
			'text',
			$field,
			array(
				'slug'    => 'max_file_number',
				'type'    => 'number',
				'min'     => '1',
				'value'   => ! empty( $field['max_file_number'] ) ? $field['max_file_number'] : 1,
				'desc'    => esc_html__( 'Maximum number limit on uploads', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Enter the number of files you wish the user to upload.', 'everest-forms-pro' ),
			),
			false
		);
		$args = array(
			'slug'    => 'max_file_number',
			'content' => $fld,
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * Media Library field option.
	 *
	 * @param array $field Field data.
	 */
	public function media_library( $field ) {
		$fld  = $this->field_element(
			'checkbox',
			$field,
			array(
				'slug'    => 'media_library',
				'value'   => ! empty( $field['media_library'] ) ? 1 : '',
				'desc'    => esc_html__( 'Store file in WordPress Media Library', 'everest-forms-pro' ),
				'tooltip' => esc_html__( 'Check this option to store the final uploaded file in the WordPress Media Library', 'everest-forms-pro' ),
			),
			false
		);
		$args = array(
			'slug'    => 'media_library',
			'content' => $fld,
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * Customize format for HTML field value.
	 *
	 * @param  string $val       Field value.
	 * @param  array  $field_val Field settings.
	 * @param  array  $form_data Form data.
	 * @param  string $context   Value display context.
	 * @return string $val       Html Value.
	 */
	public function html_field_value( $val, $field_val, $form_data = array(), $context = '' ) {
		$meta_key = '';
		$entry_id = false;
		$uploads  = wp_upload_dir();

		if ( isset( $_GET['view-entry'] ) && 'entry-single' === $context ) { // phpcs:ignore WordPress.Security.NonceVerification
			$entry_id = absint( $_GET['view-entry'] ); // phpcs:ignore WordPress.Security.NonceVerification
			$meta_key = array_search( $val, $form_data, true );
		} elseif ( isset( $form_data, $field_val['meta_key'] ) && 'email-html' === $context ) {
			$entry_id = absint( $form_data['entry_id'] );
			$meta_key = evf_clean( $field_val['meta_key'] );
		} elseif ( is_object( $form_data ) ) {
			$entry_id = absint( $form_data->entry_id );
			$meta_key = array_search( $val, $form_data->meta, true );
		}

		$entry  = $entry_id ? evf_get_entry( $entry_id, true ) : false;
		$fields = isset( $entry->fields ) ? evf_decode( $entry->fields ) : array();

		if ( ! empty( $fields ) && ! is_serialized( $field_val ) ) {
			$output = array();

			foreach ( $fields as $field ) {
				if ( empty( $field['value'] ) || $field['type'] !== $this->type || $field['meta_key'] !== $meta_key ) {
					continue;
				}

				if ( ! empty( $field['value_raw'] ) ) {
					foreach ( $field['value_raw'] as $file ) {
						if ( empty( $file['value'] ) || empty( $file['file_original'] ) ) {
							$output[ $meta_key ] = '';
						}

						if ( 'export-csv' === $context ) {
							$output[ $meta_key ][] = esc_url( $file['value'] );
						} else {
							if ( 'image-upload' === $field['type'] ) {
								if ( 'export-pdf' === $context ) {
									$pdf_file              = $uploads['basedir'] . str_replace( '/uploads/', '/', str_replace( content_url(), '', esc_url( $file['value'] ) ) );
									$output[ $meta_key ][] = sprintf( '<img src="%s" style="width:200px;" />', $pdf_file );
								} elseif ( 'entry-single' === $context ) {
									$output[ $meta_key ][] = sprintf(
										'<a href="%1$s" rel="noopener noreferrer" target="_blank"><img src="%1$s" style="width:200px;" /></a>',
										esc_url( $file['value'] )
									);
								} else {
									$output[ $meta_key ][] = sprintf(
										'<a href="%s" rel="noopener noreferrer" target="_blank">%s</a>',
										esc_url( $file['value'] ),
										esc_html( $file['file_original'] )
									);
								}
							} else {
								$output[ $meta_key ][] = sprintf(
									'<a href="%s" rel="noopener noreferrer" target="_blank">%s</a>',
									esc_url( $file['value'] ),
									esc_html( $file['file_original'] )
								);
							}
						}
					}
				}
			}

			if ( ! empty( $output[ $meta_key ] ) ) {
				$val = implode( 'export-csv' !== $context ? '<br>' : '|', $output[ $meta_key ] );
			}
		} elseif ( is_serialized( $field_val ) ) {
			$value = maybe_unserialize( $field_val );

			if ( isset( $value['type'] ) && in_array( $value['type'], array( 'image-upload', 'file-upload' ), true ) ) {
				$val = empty( $value['file_url'] ) ? '<em>' . esc_html__( '(empty)', 'everest-forms-pro' ) . '</em>' : $val;
			}

			if ( isset( $value['type'], $value['file_url'] ) && $value['type'] === $this->type ) {
				$file = $uploads['basedir'] . str_replace( '/uploads/', '/', str_replace( content_url(), '', esc_url( $value['file_url'] ) ) );
				switch ( $this->type ) {
					case 'image-upload':
						if ( '' !== $value['file_url'] ) {
							if ( 'export-pdf' === $context ) {
								$val = sprintf( '<img src="%s" style="width:200px;height:100px;" />', $file );
							} elseif ( 'entry-single' === $context ) {
								$val = sprintf( '<a href="%1$s" rel="noopener noreferrer" target="_blank"><img src="%1$s" style="width:200px;" /></a>', esc_url( $value['file_url'] ) );
							} else {
								$val = sprintf( '<a href="%1$s" target="_blank" class="image">%2$s</a>', esc_url( $value['file_url'] ), sanitize_text_field( $value['file_original'] ) );
							}
						}
						break;
					default:
						if ( '' !== $value['file_url'] ) {
							$val = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( $value['file_url'] ), sanitize_text_field( $value['file_original'] ) );
						}
						break;
				}

				if ( in_array( $context, array( 'export-csv' ), true ) ) {
					$val = esc_url( $value['file_url'] );
				}
			}
		}

		return $val;
	}

	/**
	 * Customize format for Plain field value.
	 *
	 * @param  string $val       Field value.
	 * @param  array  $field_val Field settings.
	 * @param  array  $form_data Form data.
	 * @param  string $context   Value display context.
	 *
	 * @return string $val       Formatted file url.
	 */
	public function plaintext_field_value( $val, $field_val, $form_data = array(), $context = '' ) {
		if ( is_array( $field_val ) && 'email-plain' === $context ) {
			if ( isset( $field_val['type'], $field_val['file_url'] ) && $field_val['type'] === $this->type ) {
				return esc_url( $field_val['file_url'] ) . "\r\n\r\n";
			}
		}

		return $val;
	}

	/**
	 * Filter callback for outputting formatted data.
	 *
	 * @param array $field Field Data.
	 */
	public function field_exporter( $field ) {
		$value = array();

		if ( ! empty( $field['value_raw'] ) ) {
			if ( ! is_array( $field['value_raw'] ) ) {
				$field['value_raw'] = (array) $field['value_raw'];
			}

			array_walk(
				$field['value_raw'],
				function ( &$val, $key, $img ) {
					$img['data'][] = ! empty( $val['value'] ) ? sprintf(
						'<a href="%s" rel="noopener noreferrer" target="_blank">%s</a>',
						esc_url( $val['value'] ),
						esc_html( $val['name'] )
					) : '';
				},
				array(
					'data' => &$value,
				)
			);
		}

		return array(
			'label' => ! empty( $field['name'] ) ? $field['name'] : ucfirst( str_replace( '_', ' ', $field['type'] ) ) . " - {$field['id']}",
			'value' => is_array( $value ) ? count( $value ) ? implode( '<br>', $value ) : false : $value,
		);
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @param array $field Field data.
	 */
	public function field_preview( $field ) {
		// Label.
		$this->field_preview_option( 'label', $field );

		$max_file_number = ! empty( $field['max_file_number'] ) ? max( 1, absint( $field['max_file_number'] ) ) : 1;

		// Primary input.
		?>
		<div class="everest-forms-uploader">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32px" height="32px" fill="#868e96"><path class="cls-1" d="M18.12,17.52,17,16.4V25a1,1,0,0,1-2,0V16.4l-1.12,1.12a1,1,0,0,1-1.42,0,1,1,0,0,1,0-1.41l2.83-2.83a1,1,0,0,1,1.42,0l2.83,2.83a1,1,0,0,1-.71,1.7A1,1,0,0,1,18.12,17.52ZM22,22H20a1,1,0,0,1,0-2h2a4,4,0,0,0,.27-8,1,1,0,0,1-.84-.57,6,6,0,0,0-11.36,1.69,1,1,0,0,1-1,.86H9A3,3,0,0,0,9,20h3a1,1,0,0,1,0,2H9a5,5,0,0,1-.75-9.94A8,8,0,0,1,23,10.1,6,6,0,0,1,22,22Z"></path></svg>
			<span class="everest-forms-upload-title"><?php esc_html_e( 'Drop your file here or click here to upload.', 'everest-forms-pro' ); ?></span>
			<span class="everest-forms-upload-hint">
			<?php
			/* translators: %s: Max file number */
			printf( esc_html__( 'You can upload up to %d files.', 'everest-forms-pro' ), $max_file_number ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
			</span>
		</div>
		<input type="file" class="widefat" disabled>
		<?php

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field Data.
	 * @param array $field_atts Field attributes.
	 * @param array $form_data All Form Data.
	 */
	public function field_display( $field, $field_atts, $form_data ) {
		// Define data.
		$primary           = $field['properties']['inputs']['primary'];
		$conditional_rules = isset( $field['properties']['inputs']['primary']['attr']['conditional_rules'] ) ? $field['properties']['inputs']['primary']['attr']['conditional_rules'] : '';
		$conditional_id    = isset( $field['properties']['inputs']['primary']['attr']['conditional_id'] ) ? $field['properties']['inputs']['primary']['attr']['conditional_id'] : '';
		$field_id          = $field['id'];
		$form_id           = (int) $form_data['id'];
		$url               = admin_url( 'admin-ajax.php' );
		$input_name        = 'everest_forms_' . $form_data['id'] . '_' . $field['id'];
		$required          = $primary['required'];
		$extensions        = $primary['data']['rule-extension'];
		$max_size          = abs( $primary['data']['rule-maxsize'] );
		$max_file_number   = isset( $field['max_file_number'] ) ? $field['max_file_number'] : 1;
		$max_file_number   = max( 1, absint( $max_file_number ) );
		$post_max_size     = wp_max_upload_size();
		?>
		<div class="everest-forms-uploader"
			data-field-id="<?php echo esc_attr( $field_id ); ?>"
			data-form-id="<?php echo (int) $form_id; ?>"
			data-input-name="<?php echo esc_attr( $input_name ); ?>"
			data-extensions="<?php echo esc_attr( $extensions ); ?>"
			data-max-size="<?php echo (int) $max_size; ?>"
			data-max-file-number="<?php echo (int) $max_file_number; ?>"
			data-post-max-size="<?php echo (int) $post_max_size; ?>">
			<div class="dz-message">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32px" height="32px" fill="#868e96"><path class="cls-1" d="M18.12,17.52,17,16.4V25a1,1,0,0,1-2,0V16.4l-1.12,1.12a1,1,0,0,1-1.42,0,1,1,0,0,1,0-1.41l2.83-2.83a1,1,0,0,1,1.42,0l2.83,2.83a1,1,0,0,1-.71,1.7A1,1,0,0,1,18.12,17.52ZM22,22H20a1,1,0,0,1,0-2h2a4,4,0,0,0,.27-8,1,1,0,0,1-.84-.57,6,6,0,0,0-11.36,1.69,1,1,0,0,1-1,.86H9A3,3,0,0,0,9,20h3a1,1,0,0,1,0,2H9a5,5,0,0,1-.75-9.94A8,8,0,0,1,23,10.1,6,6,0,0,1,22,22Z"/></svg>
				<span class="everest-forms-upload-title">
					<?php echo esc_html( sprintf( _n( 'Drop your file here or click here to upload', 'Drop your files here or click here to upload', (int) $max_file_number, 'everest-forms-pro' ), (int) $max_file_number ) ); ?>
				</span>

				<?php if ( (int) $max_file_number > 1 ) : ?>
					<span class="everest-forms-upload-hint">
						<?php
						/* translators: %d - max number of files. */
						printf( esc_html__( 'You can upload up to %d files.', 'everest-forms-pro' ), (int) $max_file_number );
						?>
					</span>
				<?php endif; ?>
			</div>
		</div>
		<input type="text" class="dropzone-input input-text" id="everest-forms-<?php echo absint( $form_id ); ?>-field_<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $input_name ); ?>" <?php echo esc_attr( $required ); ?> conditional_id="<?php echo esc_attr( $conditional_id ); ?>" conditional_rules='<?php echo $conditional_rules; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'>
		<?php
	}

	/**
	 * Validates field on form submit.
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {
		$this->form_data  = (array) $form_data;
		$this->form_id    = absint( $this->form_data['id'] );
		$this->field_id   = $field_id;
		$this->field_data = $this->form_data['form_fields'][ $this->field_id ];

		$input_name = sprintf( 'everest_forms_%d_%s', $this->form_id, $this->field_id );

		if ( empty( $this->field_data['required'] ) ) {
			return;
		}

		$value = '';
		if ( ! empty( $_POST[ $input_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = json_decode( wp_unslash( $_POST[ $input_name ] ), true ); // phpcs:ignore WordPress.Security
		}

		if ( empty( $value ) ) {
			evf()->task->errors[ $this->form_id ][ $this->field_id ] = evf_get_required_label();
		}
	}

	/**
	 * Formats and sanitizes field.
	 *
	 * @param int    $field_id     Field ID.
	 * @param array  $field_submit Submitted field value.
	 * @param array  $form_data    Form data and settings.
	 * @param string $meta_key     Field Meta Key.
	 */
	public function format( $field_id, $field_submit, $form_data, $meta_key ) {
		// Setup class properties to reuse everywhere.
		$this->form_data  = (array) $form_data;
		$this->form_id    = absint( $this->form_data['id'] );
		$this->field_id   = $field_id;
		$this->field_data = $this->form_data['form_fields'][ $this->field_id ];

		$field_label = ! empty( $this->form_data['form_fields'][ $this->field_id ]['label'] ) ? $this->form_data['form_fields'][ $this->field_id ]['label'] : '';
		$input_name  = sprintf( 'everest_forms_%d_%s', $this->form_id, $this->field_id );

		$processed = array(
			'name'      => make_clickable( $field_label ),
			'value'     => '',
			'value_raw' => '',
			'id'        => $this->field_id,
			'type'      => $this->type,
			'meta_key'  => $meta_key,
		);

		// We should actually receive some files info.
		if ( empty( $_POST[ $input_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			evf()->task->form_fields[ $this->field_id ] = $processed;
			return;
		}

		// Make sure form fields are stored.
		if ( ! empty( evf()->task->form_fields[ $this->field_id ] ) ) { // @codingStandardsIgnoreLine
			return;
		}

		// Make sure json_decode() doesn't fail on newer PHP.
		try {
			$raw_files = json_decode( wp_unslash( $_POST[ $input_name ] ), true ); // phpcs:ignore WordPress.Security
		} catch ( Exception $e ) {
			evf()->task->form_fields[ $this->field_id ] = $processed;
			return;
		}

		// Make sure we process only submitted files with the expected structure and keys.
		$files = array_filter(
			$raw_files,
			static function ( $file ) {
				return count( $file ) === 2 && ! empty( $file['file'] ) && ! empty( $file['name'] );
			}
		);

		if ( empty( $files ) ) {
			evf()->task->form_fields[ $this->field_id ] = $processed;
			return;
		}

		$data = array();

		foreach ( $files as $file ) {
			$file = $this->generate_file_info( $file );

			if ( $this->is_media_integrated() ) {
				$file['path'] = $file['tmp_path'];

				$file = $this->generate_file_attachment( $file );
			} else {
				// Create form upload directory if needed.
				$this->create_dir( dirname( $file['path'] ) );
				@rename( $file['tmp_path'], $file['path'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$this->set_file_fs_permissions( $file['path'] );
			}

			$data[] = $this->generate_file_data( $file );
		}

		if ( ! empty( $data ) ) {
			$mapped_value = array_map(
				function( $file ) {
					return $file['value'];
				},
				$data
			);
			$mapped_value = implode( "\n", $mapped_value );
			$processed    = wp_parse_args(
				array(
					'value_raw' => $data,
					'value'     => $mapped_value,
				),
				$processed
			);
		}

		evf()->task->form_fields[ $this->field_id ] = $processed;
	}

	/**
	 * Generate a ready for DB data for each file.
	 *
	 * @since 1.3.0
	 *
	 * @param array $file File to generate data for.
	 *
	 * @return array
	 */
	protected function generate_file_data( $file ) {
		return array(
			'name'          => sanitize_text_field( $file['name'] ),
			'value'         => esc_url_raw( $file['file_url'] ),
			'file'          => $file['file_name_new'],
			'file_original' => $file['name'],
			'ext'           => pathinfo( $file['name'], PATHINFO_EXTENSION ),
			'attachment_id' => isset( $file['attachment_id'] ) ? absint( $file['attachment_id'] ) : 0,
			'id'            => $this->field_id,
			'type'          => $file['type'],
		);
	}

	/**
	 * Add additional information to the files array for each file.
	 *
	 * @since 1.3.0
	 *
	 * @param array $file Submitted file basic info.
	 */
	protected function generate_file_info( $file ) {
		$dir = $this->get_form_files_dir();

		$file['tmp_path'] = trailingslashit( $this->get_tmp_dir() ) . $file['file'];
		$file['type']     = 'application/octet-stream';
		if ( is_file( $file['tmp_path'] ) ) {
			$filetype     = wp_check_filetype( $file['tmp_path'] );
			$file['type'] = $filetype['type'];
		}

		// Data for no media case.
		$file_ext              = pathinfo( $file['name'], PATHINFO_EXTENSION );
		$file_base             = wp_basename( $file['name'], ".$file_ext" );
		$file['file_name_new'] = sprintf( '%s-%s.%s', $file_base, wp_hash( $dir['path'] . $this->form_data['id'] . $this->field_id ), strtolower( $file_ext ) );
		$file['file_name_new'] = wp_unique_filename( trailingslashit( $dir['path'] ), sanitize_file_name( $file['file_name_new'] ) );
		$file['file_url']      = trailingslashit( $dir['url'] ) . $file['file_name_new'];
		$file['path']          = trailingslashit( $dir['path'] ) . $file['file_name_new'];
		$file['attachment_id'] = 0;

		return $file;
	}

	/**
	 * Whether field is integrated with WordPress Media Library.
	 *
	 * @uses $this->field_data
	 *
	 * @since 1.3.0
	 */
	protected function is_media_integrated() {
		return ! empty( $this->field_data['media_library'] ) && '1' === $this->field_data['media_library'];
	}

	/**
	 * Get form-specific uploads directory path and URL.
	 *
	 * @since 1.3.0
	 */
	protected function get_form_files_dir() {
		$uploads = wp_upload_dir();
		$folder  = absint( $this->form_data['id'] ) . '-' . wp_hash( $this->form_data['id'] . $this->form_data['created'] );

		return array(
			'path' => "{$uploads['basedir']}/everest_forms_uploads/{$folder}",
			'url'  => "{$uploads['baseurl']}/everest_forms_uploads/{$folder}",
		);
	}

	/**
	 * Create a Media Library attachment.
	 *
	 * @since 1.3.0
	 *
	 * @param array $file File to create Media Library attachment for.
	 *
	 * @return array
	 */
	protected function generate_file_attachment( $file ) {
		// Include necessary code from core.
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$file_args = array(
			'error'    => '',
			'tmp_name' => $file['path'],
			'name'     => $file['file_name_new'],
			'type'     => $file['type'],
		);
		$upload    = wp_handle_sideload( $file_args, array( 'test_form' => false ) );

		if ( empty( $upload['file'] ) ) {
			return $file;
		}

		// Create a Media attachment for the file.
		$attachment_id = wp_insert_attachment(
			array(
				'post_title'     => $this->field_data['label'],
				'post_status'    => 'publish',
				'post_mime_type' => $file['type'],
			),
			$upload['file']
		);

		if ( empty( $attachment_id ) ) {
			return $file;
		}

		// Generate and update attachment meta.
		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
		);

		// Update file information.
		$file_url              = wp_get_attachment_url( $attachment_id );
		$file['path']          = $upload['file'];
		$file['file_url']      = $file_url;
		$file['file_name_new'] = wp_basename( $file_url );
		$file['attachment_id'] = $attachment_id;

		return $file;
	}

	/**
	 * Determine max file size allowed.
	 *
	 * @return int Number of bytes allowed.
	 */
	public function max_file_size() {
		if ( ! empty( $this->field_data['max_size'] ) ) {
			// Strip any suffix provided (eg M, MB etc), which leaves is wit the raw MB value.
			$max_size = preg_replace( '/[^0-9.]/', '', $this->field_data['max_size'] );
			$max_size = evf_size_to_bytes( $max_size . 'M' );
		} else {
			$max_size = evf_max_upload( true );
		}

		return $max_size;
	}

	/**
	 * Return upload validation errors messages.
	 *
	 * @since 1.3.1
	 *
	 * @param int|string $error PHP file upload error code.
	 *
	 * @return array|string|boolean Get validationr message
	 */
	protected function get_upload_validation_errors( $error = null ) {
		$errors = apply_filters(
			'evf_upload_validation_errors',
			array(
				UPLOAD_ERR_INI_SIZE   => esc_html__( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'everest-forms-pro' ),
				UPLOAD_ERR_FORM_SIZE  => esc_html__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'everest-forms-pro' ),
				UPLOAD_ERR_PARTIAL    => esc_html__( 'The uploaded file was only partially uploaded.', 'everest-forms-pro' ),
				UPLOAD_ERR_NO_FILE    => esc_html__( 'No file was uploaded.', 'everest-forms-pro' ),
				UPLOAD_ERR_NO_TMP_DIR => esc_html__( 'Missing a temporary folder.', 'everest-forms-pro' ),
				UPLOAD_ERR_CANT_WRITE => esc_html__( 'Failed to write file to disk.', 'everest-forms-pro' ),
				UPLOAD_ERR_EXTENSION  => esc_html__( 'File upload stopped by extension.', 'everest-forms-pro' ),
			)
		);

		if ( null === $error ) {
			return $errors;
		}

		if ( isset( $errors[ $error ] ) ) {
			return $errors[ $error ];
		}

		return true;
	}
}
