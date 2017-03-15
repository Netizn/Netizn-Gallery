<?php
/*
Plugin Name: Netizn Gallery Back-end
Plugin URI: http://netizn.co/
Description: Adds gallery administration to WP Admin
Author: Martin Petts
Version: 1.0
Author URI: http://netizn.co/
*/

class Netizn_Gallery {

  public $post_types = array('page', 'post');

  private static $saved_meta_boxes = false;
  public static $meta_box_errors  = array();

  public function __construct() {
    $this->post_types = apply_filters('netizn_gallery_post_types', $this->post_types);
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10 );
    add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );
    add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
  }

  public function scripts($hook) {
    if($hook == 'post.php') {
      wp_enqueue_style( 'netizn-gallery-css', plugins_url('assets/css/netizn-gallery.css', __FILE__) );
      wp_enqueue_script( 'netizn-gallery-js', plugins_url('assets/js/netizn-gallery-min.js', __FILE__), array('jquery') );
    }
  }

  public function add_meta_boxes() {
		add_meta_box( 'netizn-gallery', __( 'Image Gallery', 'netizn-gallery' ), array($this, 'output'), $post_types, 'side', 'low' );
  }

  public function output( $post ) {
    wp_nonce_field( 'netizn_gallery_save_data', 'netizn_gallery_meta_nonce' );
		?>
		<div id="netizn-gallery-container">
			<ul class="netizn-gallery-images">
				<?php
					if ( metadata_exists( 'post', $post->ID, '_netizn_gallery' ) ) {
						$gallery = get_post_meta( $post->ID, '_netizn_gallery', true );
					}

					$attachments         = array_filter( explode( ',', $gallery ) );
					$update_meta         = false;
					$updated_gallery_ids = array();

					if ( ! empty( $attachments ) ) {
						foreach ( $attachments as $attachment_id ) {
							$attachment = wp_get_attachment_image( $attachment_id, 'thumbnail' );

							// if attachment is empty skip
							if ( empty( $attachment ) ) {
								$update_meta = true;
								continue;
							}

							echo '<li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '">
								' . $attachment . '
								<ul class="actions">
									<li><a href="#" class="delete tips" data-tip="' . esc_attr__( 'Delete image', 'netizn-gallery' ) . '">' . __( 'Delete', 'netizn-gallery' ) . '</a></li>
								</ul>
							</li>';

							// rebuild ids to be saved
							$updated_gallery_ids[] = $attachment_id;
						}

						// need to update product meta to set new gallery ids
						if ( $update_meta ) {
							update_post_meta( $post->ID, '_netizn_gallery', implode( ',', $updated_gallery_ids ) );
						}
					}
				?>
			</ul>

			<input type="hidden" id="netizn-gallery-field" name="netizn_gallery" value="<?php echo esc_attr( $gallery ); ?>" />

		</div>
		<p class="add-images hide-if-no-js">
			<a href="#" data-choose="<?php esc_attr_e( 'Add Images to Gallery', 'netizn-gallery' ); ?>" data-update="<?php esc_attr_e( 'Add to gallery', 'netizn-gallery' ); ?>" data-delete="<?php esc_attr_e( 'Delete image', 'netizn-gallery' ); ?>" data-text="<?php esc_attr_e( 'Delete', 'netizn-gallery' ); ?>"><?php _e( 'Add gallery images', 'netizn-gallery' ); ?></a>
		</p>
		<?php
	}

  public function save_meta_boxes( $post_id, $post ) {
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['netizn_gallery_meta_nonce'] ) || ! wp_verify_nonce( $_POST['netizn_gallery_meta_nonce'], 'netizn_gallery_save_data' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// We need this save event to run once to avoid potential endless loops. This would have been perfect:
		//	remove_action( current_filter(), __METHOD__ );
		// But cannot be used due to https://github.com/woothemes/woocommerce/issues/6485
		// When that is patched in core we can use the above. For now:
		self::$saved_meta_boxes = true;

    $this->save_gallery($post_id, $post);
	}

  public function save_gallery( $post_id, $post ) {
    $attachment_ids = isset( $_POST['netizn_gallery'] ) ? array_filter( explode( ',', wc_clean( $_POST['netizn_gallery'] ) ) ) : array();

    update_post_meta( $post_id, '_netizn_gallery', implode( ',', $attachment_ids ) );
  }

}
new Netizn_Gallery();

if(!function_exists('netizn_gallery_ids')) {
  function netizn_gallery_ids($post_id = false) {
    if(empty($post_id)) {
      global $post;
      $post_id = $post->ID;
    }
    if ( metadata_exists( 'post', $post_id, '_netizn_gallery' ) ) {
      $gallery = get_post_meta( $post_id, '_netizn_gallery', true );
      $attachments = array_filter( explode( ',', $gallery ) );
      return $attachments;
    }
    return null;
  }
}
