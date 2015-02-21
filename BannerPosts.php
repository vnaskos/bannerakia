<?php
/*
Plugin Name: Bannerakia
Plugin URI: https://github.com/vnaskos/bannerakia
Description: Banner display and management tool.
Version: 1.0
Author: Naskos Vasilis
Author URI: http://nask00s.tk
License: GPLv2
*/

add_action( 'init', 'create_banner' );

function create_banner() {
	register_post_type( 'banner',
		array(
			'labels' => array(
				'name' => 'Banners',
				'singular_name' => 'Banner',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Banner',
				'edit' => 'Edit',
				'edit_item' => 'Edit Banner',
				'new_item' => 'New Banner Post',
				'view' => 'View',
				'view_item' => 'View Banner',
				'search_items' => 'Search For Banners',
				'not_found' => 'No Banner found',
				'not_found_in_trash' => 'No Banner found in Trash',
				'parent' => 'Parent Banner'
			),
 
			'public' => true,
			'menu_position' => 4,
			'supports' => array( 'title' ),
			'taxonomies' => array( '' ),
			'menu_icon' => plugins_url( 'images/thumb.png', __FILE__ ),
			'has_archive' => false
		)
	);
	
	wp_enqueue_style("plugin_style", plugins_url("css/style.css", __FILE__ ), false, "1.0");
}
add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );

function banners_plugin_admin() {
	add_meta_box( 'banners_meta_box',
		'Banner Details',
		'display_banner_meta_box',
		'banner', 'normal', 'high'
	);
	wp_enqueue_script('controls', plugins_url('scripts/controls.js', __FILE__ ), array('jquery'), '1.0', true);
	wp_enqueue_script('my-upload', plugins_url('scripts/image-script.js', __FILE__ ), array('jquery'), '1.0', true);
}
add_action( 'admin_init', 'banners_plugin_admin' );

function display_banner_meta_box( $banner ) {
	// Retrieve homepage post field values
	$bannerURL = esc_html( get_post_meta( $banner->ID, 'banner-url', true ) );
	$image = esc_html( get_post_meta( $banner->ID, 'image', true ) );
	
	?>
	<div class="banner-section bn-opts">
		<div class="bn-input bn-text">
			<label for="url-text">URL</label>
			<input type="text" name="url-text" id="url-text" value="<?php echo $bannerURL; ?>"/>
			<small>redirect to URL</small><div class="clearfix"></div>
		</div>
		<div class="bn-input bn-text">
			<label for="image_uri">Image</label>
			<img width="250px" class="custom_media_image" src="<?php echo $image; ?>" /><br />
			<input id="image_uri" type="text" class="widefat custom_media_url" name="image_uri" value="<?php echo $image; ?>">
			<a href="#" class="button custom_media_upload">Upload</a>
		</div>
	</div>
	<?php
}

function add_banner_fields( $banner_id, $banner ) {
	if ( $banner->post_type == 'banner' ) {
		if ( isset( $_POST['url-text'] ) && $_POST['url-text'] != '' ) {
			update_post_meta( $banner_id, 'banner-url', $_POST['url-text'] );
		}
		if ( isset( $_POST['image_uri'] ) && $_POST['image_uri'] != '' ) {
			update_post_meta( $banner_id, 'image', $_POST['image_uri'] );
		}
	}
}
add_action( 'save_post', 'add_banner_fields', 10, 2 );


class bannerakia extends WP_Widget {
 
	function __construct() {
		parent::__construct(
			'bannerakia', // Base ID
			__('Bannerakia'), // Name
			array( 'description' => __( 'Display banner lists' ) ) // Args
		);
	}
	
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		
		if ( !empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		echo '<div class="widget-holder">';
			
			$this->query_by_id($instance);
		
		echo '</div>';
		
		echo $args['after_widget'];
	}
	
	public function form( $instance ) {
		
		$title = isset($instance['title']) ? $instance['title'] : '';
		
		$order = isset($instance['order']) ? $instance['order'] : '';
		$order_array = explode(",",$order);
		$banner_order = is_array($order_array) ? $order_array : array();
		$args = array(
			'orderby' 			=> 'post__in',
			'posts_per_page'	=> -1,
			'post_type' 		=> 'banner',
			'post__in'			=> $banner_order,
		); ?>
		
		<div id="bannerakia-form">
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php echo 'Title:'; ?>
			</label> 
			<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('banners'); ?>">
				<?php echo 'All banners (select and add banners to display):'; ?>
			</label>
		
			<select name="<?php echo $this->get_field_name('banners'); ?>" class="unordered-list" multiple="multiple">
				<?php $allBanners = get_posts(array('post_type' => 'banner', 'posts_per_page' => -1)); ?>
				<?php foreach($allBanners as $banner): ?>
					<option value="<?php echo $banner->ID; ?>">
						<?php echo $banner->post_title; ?>
					</option>
				<?php endforeach; ?>
			</select>
		
			<input type="button" class="add-button" value="Add" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'list' ); ?>">
				<?php echo 'Displayed banners (in this order):'; ?>
			</label>
			
			<select name="<?php echo $this->get_field_name('list'); ?>" class="ordered-list" multiple="multiple">
				<?php $results = new WP_query($args); ?>
				<?php if($results->have_posts()) :
					while($results->have_posts()) : $results->the_post(); 
						echo '<option value="' . get_the_ID() . '" >' . get_the_title() . '</option>';
					endwhile;
				endif; ?>
			</select>
			<input type="button" class="remove-button" value="Remove" />
			<input type="button" class="up-button" value="Up" />
			<input type="button" class="down-button" value="Down" />
		</p>
		<input type="hidden" class="hidden-order" name="<?php echo $this->get_field_name('order'); ?>" value="<?php echo $order; ?>" />
		</div>
		<?php
	}
	
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = empty($new_instance['title']) ? '' : strip_tags($new_instance['title']);
		$instance['order'] = empty($new_instance['order']) ? '' : strip_tags($new_instance['order']);
		
		return $instance;
	}
	
	public function query_by_id($instance) {	
		$order = empty($instance['order']) ? '' : strip_tags($instance['order']);
		$order_array = explode(",",$order);
		
		$args = array(
			'orderby' 			=> 'post__in',
			'posts_per_page'	=> -1,
			'post_type' 		=> 'banner',
			'post__in'			=> $order_array,
		);
		$results = new WP_query($args);
		
		if(!$results->have_posts()) :
			return;
		endif; ?>
		
		<ul class="bannerakia-list">
			<?php while($results->have_posts()):
				$results->the_post(); 
				$url = get_post_meta( get_the_ID(), 'banner-url', true );
				$image = get_post_meta( get_the_ID(), 'image', true );
				?>
			
				<li>
					<div class="banner-img">
						<a href="<?php echo $url; ?>" title="<?php the_title(); ?>">
							<?php echo wp_get_attachment_image(pn_get_attachment_id_from_url($image), array(300,300)); ?>
						</a>
					</div>
				</li>
			<?php endwhile; wp_reset_postdata(); ?>
		</ul>
		<?php 
	}
}
add_action('widgets_init', create_function('', 'return register_widget("bannerakia");'));

function pn_get_attachment_id_from_url( $attachment_url = '' ) {
	global $wpdb;
	$attachment_id = false;

	if ( '' == $attachment_url )
		return;
	
	$upload_dir_paths = wp_upload_dir();

	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

		// Remove the upload path base directory from the attachment URL
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

		// Finally, run a custom database query to get the attachment ID from the modified attachment URL
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
	}

	return $attachment_id;
}

?>
