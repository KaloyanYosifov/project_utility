<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

if ( class_exists( '\Carbon_Fields\Carbon_Fields' ) ) {
	global $crb_socials;

	function crb_get_searched_page() {
		if ( is_front_page() ) {
			return '';
		}

		$requested_uri = explode( '/', $_SERVER['REQUEST_URI'] );

		$requested_page = '';

		$requested_page = $requested_uri[ count ( $requested_uri ) - 1 ];

		if ( empty( $requested_page ) ) {
			$requested_page = $requested_uri[ count( $requested_uri ) - 2 ];
		} 

		return $requested_page;
	}

	function crb_is_array_associative( $arr ) {
		if ( empty( $arr ) ) {
			return false;
		}

		foreach ( array_keys( $arr ) as $key ) {
			if ( ! is_int( $key ) ) {
				return true;
			} else {
				return false;
			}
		}
	}

	function crb_add_social( $social_name, $arr ) {
		if ( empty( $arr ) || empty( $social_name ) ) {
			return;
		}

		global $crb_socials;

		$crb_socials[ $social_name ] = $arr;
	}

	function crb_add_socials( $arr ) {
		if ( empty( $arr ) || ! crb_is_array_associative( $arr ) ) {
			return;
		}

		global $crb_socials;

		foreach ( $arr as $social_name => $social_data ) {
			$crb_socials[ $social_name ] = $social_data;
		}
	}

	function crb_get_socials( $args = '', $fields = '' ) {
		global $crb_socials;

		do_action( 'crb_pre_get_socials' );

		if ( empty( $crb_socials ) ) {
			return array();
		}

		if ( ! empty( $args ) ) {
			if ( $args === 'list' ) {
				return $crb_socials;
			}
		}

		$generate_fields = false;
		$prefix = 'crb_';
		$suffix = '_url';
		$populated_socials = array();

		foreach ( $crb_socials as $social_name => $social_data ) {
			if ( ! empty( $args ) ) {
				if ( $args === 'generate_fields' ) {
					$generate_fields = true;
				}
			}

			if ( $generate_fields ) {
				if ( empty( $public_name = $social_data['public_name'] ) ) {
					$public_name = __( 'Social', 'crb' );
				}

				$populated_socials[] = Field::make( 'text', $prefix . $social_name . $suffix, $public_name . __( ' URL', 'crb' ) );
			} else {
				$social_data['url'] = carbon_get_theme_option( $prefix . $social_name . $suffix );
				$populated_socials[ $social_name ] = $social_data;
			}
		}

		if ( $generate_fields && ! empty( $fields ) ) {
			if ( ! is_array( $fields ) ) {
				$populated_socials[] = $fields;
			} else {
				foreach ( $fields as $field ) {
					$populated_socials[] = $field;
				}
			}
		}

		return $populated_socials;
	}

	function crb_render_socials( $args = '' ) {
		$render_args = array(
			'echo' => true,
			'no_url_show' => true,
			'wrapper_before' => '<div class="socials"><ul>',
			'wrapper_after' => '</ul></div>',
			'item_wrapper_before' => '<li>',
			'item_wrapper_after' => '</li>',
			'render_icons' => true,
			'render_images' => false,
			'image_size' => 'thumbnail',
			'target' => 'blank',
			'reverse_order' => false,
		);

		if ( ! empty( $args ) ) {
			$render_args = array_merge( $render_args, $args );
		}

		$render_args = apply_filters( 'crb_pre_render_socials_args', $render_args );

		$generated_socials = crb_get_socials();

		if ( $render_args['reverse_order'] ) {
			$generated_socials = array_reverse( $generated_socials );
		}

		$socials_render_html = $render_args['wrapper_before'];

		foreach ( $generated_socials as $social_name => $social_data ) {
			if ( $render_args['no_url_show'] ) {
				if ( empty( $social_data['url'] ) ) {
					continue;
				}
			}
			$target = '';
			
			if ( $render_args['target'] === 'blank' ) {
				$target = 'target="_blank"';	
			}

			if ( $render_args['render_icons'] ) {
				if ( empty( $social_data['icon'] ) ) {
					continue;
				}

				$socials_render_html .= $render_args['item_wrapper_before'];

				if ( ! empty( $social_data['url'] ) ) {
					$socials_render_html .= '<a href="' . esc_url( $social_data['url'] ) . '" ' . $target . '>';
				}

				$socials_render_html .= '<i class="' . $social_data['icon'] . '"></i>';

				if ( ! empty( $social_data['url'] ) ) {
					$socials_render_html .= '</a>';
				}
				$socials_render_html .= $render_args['item_wrapper_after'];
			} elseif ( $render_args['render_images'] ) {

				if ( empty( $social_data['image'] ) ) {
					continue;
				}

				$image_url = '';

				if ( is_int( $social_data['image'] ) ) {
					$image_url = wp_get_attachment_image_url( $social_data['image'], $render_args['image_size'] );
				} else {
					$image_url = $social_data['image'];
				}
				
				if ( empty( $image_url ) ) {
					continue;
				}

				if ( empty( $public_name = $social_data['public_name'] ) ) {
					$public_name = __( 'Social', 'crb' );
				}

				ob_start();
				?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt=="<?php echo esc_html( $public_name ); ?>">
				<?php
				$html = ob_get_clean();

				$socials_render_html .= $render_args['item_wrapper_before'];
				if ( ! empty( $social_data['url'] ) ) {
					$socials_render_html .= '<a href="' . esc_url( $social_data['url'] ) . '" ' . $target . '>';
				}

				$socials_render_html .= $html;

				if ( ! empty( $social_data['url'] ) ) {
					$socials_render_html .= '</a>';
				}

				$socials_render_html .= $render_args['item_wrapper_after'];
			}
		}

		$socials_render_html .= $render_args['wrapper_after'];

		$socials_render_html = apply_filters( 'crb_rendered_socials_html', $socials_render_html );

		if ( ! $render_args['echo'] ) {
			return $socials_render_html;
		} else {
			echo $socials_render_html;
		}
	}

	function crb_init_ajax_function( $args, $script_data_name, $script_passing_args = '' ) {
		if ( empty( $args['function_name'] ) && empty( $args['handle'] ) ) {
			return;
		}

		$ajax_args = array(
			'use_nopriv' => true,
		);

		if ( ! empty( $args ) ) {
			$ajax_args = array_merge( $ajax_args, $args );
		}

		$script_passing_args['wp_admin_url'] = admin_url( 'admin-ajax.php' );

		wp_localize_script( $args['handle'], $script_data_name, $script_passing_args );

		add_action( 'wp_ajax_' . $args['function_name'] );

		if ( $args['use_nopriv'] ) {
			add_action( 'wp_ajax_nopriv_' . $args['function_name'] );
		}
	}

	function crb_get_nav_menu_name( $theme_location ) {
		$nav_menu_name = '';

		if ( ! empty( $nav_menu_id = get_nav_menu_locations()[ $theme_location ] ) ) {
			if ( ! empty( $nav_menu_object = wp_get_nav_menu_object( $nav_menu_id ) ) ) {
				$nav_menu_name = $nav_menu_object->name;
			}
		}

		return $nav_menu_name;
	}

} else {
	wp_die( __( 'Please install carbon fields.If you have already installed carbon fields please make sure carbon fields is loaded first!', 'crb' ) );
}
