<?php

class CrbDuplicateCrbContent {

	public function __construct() {
		add_action( 'add_meta_boxes', array(
			&$this,
			'register_meta_box',
		) );
	}

	public function register_meta_box() {
		if ( ! preg_match( '~post=~', crb_get_searched_page() ) ) {
			return;
		}

		add_meta_box(
			'crb-content-field',
			__( 'Copy Content From Post', 'crb' ),
			array(
				&$this,
				'front_end',
			),
			array(
				'post',
				'page',
			),
			'side',
			'high'
		);
	}

	public function front_end() {
		global $wpdb;

		$table = $wpdb->postmeta;
		$current_post_id = get_the_ID();

		if ( get_post_type() === 'page' ) {
			$sql = "SELECT meta_value FROM {$table} WHERE post_id = {$current_post_id} AND meta_key = '_wp_page_template'";

			if ( empty( $meta_object = $wpdb->get_results( $sql )[0] ) ) {
				$page_template = 'default';
			}

			if ( empty( $page_template ) ) {
				if ( empty( $page_template = $meta_object->meta_value ) ) {
					$page_template = 'default';
				}
			}

			$sql = "SELECT post_id FROM {$table} WHERE post_id != {$current_post_id} AND meta_value = '{$page_template}'";

			if ( empty( $post_id_results = $wpdb->get_results( $sql ) ) ) {
				$post_id_results = array( );
			}		
		} else {
			$post_type = get_post_type();
			$table = $wpdb->posts;

			$sql = "SELECT ID FROM {$table} WHERE post_type = '{$post_type}' AND ID != {$current_post_id} AND post_status = 'publish'";

			if ( empty( $post_id_results = $wpdb->get_results( $sql ) ) ) {
				$post_id_results = array();
			}		
		}
		
		$post_ids = array();

		foreach ( $post_id_results as $post_id_result ) {
			if ( get_post_type() === 'page' ) {
				$post_ids[] = $post_id_result->post_id;
			} else {
				$post_ids[] = $post_id_result->ID;
			}
		}

		$page_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
		ob_start();
		?>
			<style type="text/css" media="screen">
				.crb-duplicate-field select,
				.crb-duplicate-field a { display: block; width: 100%; }	

				.crb-duplicate-field a { text-align: center; margin-top: 20px !important; }
			</style>

			<div class="crb-duplicate-field">
				<select id="crb-select-duplicate-templates" name="crb-duplicate-templates">
					<option value="none"><?php _e( 'None', 'crb' ); ?></option>
					<?php foreach ( $post_ids as $post_id ) : ?>
						<option value="<?php echo $post_id; ?>">
							<?php echo esc_html( get_the_title( $post_id ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<p style="display: none; color: #ff0000;" id="crb-select-duplicate-templates-error"><?php _e( 'Please choose a template', 'crb' ); ?></p>
				
				<script type="text/javascript">
					$( document ).ready( function() {
						const $duplicate_btn = $( '#crb-duplicate-btn' );
						const original_url = $duplicate_btn.attr( 'href' );
						const $select_field = $( '#crb-select-duplicate-templates' );

						$( '#crb-select-duplicate-templates' ).on( 'change', function( event ) {
							const $this = $( this );

							let new_href = original_url + '&duplicate_post_id=' + $this.val();

							$duplicate_btn.attr( 'href', new_href );
						} );

						$( '#crb-duplicate-btn' ).on( 'click', function ( event ) {
							const $this = $( this );

							if ( $select_field.val() === 'none' ) {
								event.preventDefault();
								$( '#crb-select-duplicate-templates-error' ).css( 'display', 'block' );
								return false;
							}
						} );
					} );
				</script>

				<a href="<?php echo esc_url( $page_url ); ?>" id="crb-duplicate-btn" class="button button-primary button-large crb-duplicate__btn">
					<?php _e( 'Copy Content', 'crb' ); ?>
				</a>
			</div>
		<?php
		$html = ob_get_clean();
		
		echo $html;
	}

}

new CrbDuplicateCrbContent();

add_action( 'init', 'crb_update_post_carbon_meta' );

function crb_update_post_carbon_meta() {
	if ( ! preg_match( '~post=~', crb_get_searched_page() ) ) {
			return;
	}

	if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
		return;
	}

	if ( empty( $_GET['duplicate_post_id'] ) ) {
		return;	
	}

	$duplicate_post_id = $_GET['duplicate_post_id'];

	$current_post_id = $_GET['post'];

	if ( $duplicate_post_id === $current_post_id ) {
		return;
	}

	global $wpdb;
	$table = $wpdb->posts;

	$sql = "SELECT post_content FROM {$table} WHERE ID = {$duplicate_post_id}";

	if ( empty( $post_data = $wpdb->get_results( $sql )[0] ) ) {
		return;
	}

	wp_update_post( array(
		'ID' => $current_post_id,
		'post_content' => $post_data->post_content,
	) );


	$table = $wpdb->postmeta;
	$sql = "SELECT meta_key, meta_value FROM {$table} WHERE post_id = {$duplicate_post_id} AND meta_key != '_edit_last' AND meta_key != '_edit_lock'";

	if ( empty( $post_metadata = $wpdb->get_results( $sql ) ) ) {
		return;
	}

	foreach ( $post_metadata as $meta_data ) {
		if ( empty( $meta_data->meta_key ) ) {
			continue;
		}

		update_post_meta( $current_post_id, $meta_data->meta_key, $meta_data->meta_value );
	}

}