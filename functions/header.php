<?php

// Choose what to display in the header: 3 featured post, header image, or nothing

function cfcp_header_admin_init() {
	register_setting('cfcp_header_options', 'cfcp_header_options', 'cfcp_header_options_validate');
	if (cfcp_header_options('type') == 'featured') {
		add_action( 'add_meta_boxes', 'cfcp_set_featured_position' );
	}
	wp_register_style( 'myPluginStylesheet', get_bloginfo('template_url') . '/css/masthead.css' );
	wp_enqueue_style( 'myPluginStylesheet' );
}
add_action('admin_init', 'cfcp_header_admin_init');

/*

# Process

Header admin screen allows choosing between featured posts, image or nothing. Featured posts are chosen on the individual post page and the location is stored in post meta. When a post is published, it will occupy the selected slot (removing any previous post that was there).


# Data Formats

## Options

cfcp_header = array(
	'type' => 'featured|image|none',
	'posts' => array( // post ids
		'_1' => 123,
		'_2' => 124,
		'_3' => 125,
	),
	'image_url' => 'http://...', // image URL
);

## Post Meta

_cfcp_header_slot = 0|1|2|3

*/

function cfcp_header_featured_meta($post_id = null) {
	if (!$post_id) {
		$post_id = get_the_ID();
	}
	return intval(get_post_meta($post_id, '_cfcp_header_slot', true));
}

// getter/setter function
function cfcp_header_options($key = null, $val = null) {
	$data = get_option('cfcp_header_options', array(
		'type' => 'featured',
		'posts' => array(
			'_1' => null,
			'_2' => null,
			'_3' => null,
		),
		'image_url' => null
	));
	// if we have a val, save and return
	if (!empty($val)) {
		$data[$key] = $val;
		return update_option('cfcp_header_options', $data);
	}
	// return data
	if (empty($key)) {
		return $data;
	}
	else if (isset($data[$key])) {
		return $data[$key];
	}
	else {
		return false;
	}
}

function cfcp_header_featured_save_post($post_id, $post) {
	if (!defined('XMLRPC_REQUEST') && isset($_POST['_cfcp_header_slot'])) {
		update_post_meta($post_id, '_cfcp_header_slot', intval($_POST['_cfcp_header_slot']));
		if ($post->post_status == 'publish') {
			remove_action('publish_post', 'cfcp_header_featured_publish_post');
			cfcp_header_featured_publish_post($post_id);
		}
	}
}
add_action('save_post', 'cfcp_header_featured_save_post', 10, 2);

function cfcp_header_featured_publish_post($post_id) {
	if ($slot = get_post_meta($post_id, '_cfcp_header_slot', true)) {
		$posts = cfcp_header_options('posts');
// find previous post in slot
		$prev_id = (!empty($posts['_'.$slot]) ? $posts['_'.$slot] : false);
		if ($prev_id != $post_id) {
			if ($prev_id) {
// remove previous post in slot (meta)
				delete_post_meta($prev_id, '_cfcp_header_slot');
			}
			$posts['_'.$slot] = $post_id;
			cfcp_header_options('posts', $posts);
		}
	}
}
add_action('publish_post', 'cfcp_header_featured_publish_post');

function cfcp_header_options_fields($fields) {
	$type = cfcp_header_options('type');
	ob_start();
?>
				<ul id="cfp-header-options">
					<li id="cfp-header-featured">
						<label for="cfcp-header-type-featured">
							<input type="radio" name="cfcp_header_options[type]" id="cfcp-header-type-featured" value="featured" <?php checked('featured', $type); ?>> <?php _e('Featured Posts', 'favepersonal'); ?>
							<img src="<?php bloginfo('template_url'); ?>/functions/header/img/header-option-posts.png" alt="<?php _e('Featured Posts', 'favepersonal'); ?>" height="56" width="250" />
						</label>
					</li>
					<li id="cfp-header-image">
						<label for="cfcp-header-type-image">
							<input type="radio" name="cfcp_header_options[type]" id="cfcp-header-type-image" value="image" <?php checked('image', $type); ?>> <?php _e('Header Image', 'favepersonal'); ?>
							<img src="<?php bloginfo('template_url'); ?>/functions/header/img/header-option-image.png" alt="<?php _e('Header Image', 'favepersonal'); ?>" height="56" width="250" />
						</label>
						<a href="<?php echo admin_url('themes.php?page=custom-header'); ?>"><?php _e('Upload or choose a header image', 'favepersonal'); ?></a>
					</li>
					<li id="cfp-header-none">
						<label for="cfcp-header-type-none">
							<input type="radio" name="cfcp_header_options[type]" id="cfcp-header-type-none" value="none" <?php checked('none', $type); ?>> <?php _e('No Header', 'favepersonal'); ?>
						</label>
					</li>
				</ul>
<?php
	$field = ob_get_clean();
	$header = array(
		'header_display' => array(
			'label' => '<label>'.__('Header display', 'favepersonal').'</label>',
			'field' => $field
		),
	);
	return array_merge($header, $fields);
}
add_filter('cfct_options_fields', 'cfcp_header_options_fields', 99);

// make a few changes to the default header image screen
function cfcp_custom_header_options() {
?>
<script type="text/javascript">
jQuery(function($) {
	$('.wrap h2:first').append('<div class="updated"><p><?php printf( __( 'This theme supports multiple header options, <a href="%s">manage your theme options here</a>.' ), admin_url( 'themes.php?page=carrington-settings' ) ); ?></p></div>');
	$('#removeheader').closest('tr').remove();
});
</script>
<?php
}
add_action('custom_header_options', 'cfcp_custom_header_options');

function cfcp_header_custom_menu_text() {
	remove_submenu_page('themes.php', 'custom-header');
	global $custom_image_header;
	add_theme_page(
		__('Header Image', 'favepersonal'),
		__('Header Image', 'favepersonal'),
		'edit_theme_options',
		'custom-header',
		array(&$custom_image_header, 'admin_page')
	);
}
add_action('admin_menu', 'cfcp_header_custom_menu_text', 100);

function cfcp_header_admin_bar() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('header');
	if (current_user_can('edit_theme_options')) {
		$wp_admin_bar->add_menu(array(
			'parent' => 'appearance', 
			'id' => 'header', 
			'title' => __('Header Image', 'favepersonal'), 
			'href' => admin_url('themes.php?page=custom-header')
		));
	}
}
add_action('wp_before_admin_bar_render', 'cfcp_header_admin_bar');

function cfcp_header_options_update() {
	$settings = $_POST['cfcp_header_options'];
	if (!is_array($settings) || !isset($settings['type']) || !in_array($settings['type'], array('featured', 'image', 'none'))) {
		wp_die(__('Sorry, there was an error saving the header settings.', 'favepersonal'));
	}
	cfcp_header_options('type', $settings['type']);
}
add_action('cfct_update_settings', 'cfcp_header_options_update');

// Adds a box to the main column on the Post and Page edit screens
function cfcp_set_featured_position() {
	add_meta_box(
		'cfp-set-featured-position',
		__( 'Featured Post Position', 'myplugin_textdomain' ),
		'cfcp_header_featured_slot_form',
		'post',
		'normal',
		'high'
	);
}

function cfcp_header_featured_slot_form() {
// get featured posts
	$featured_ids = cfcp_header_options('posts');
// echo 3 boxes
?>
	<ul class="cf-clearfix">
<?php
	foreach ($featured_ids as $slot => $id) {
		$featured = (!empty($id) ? get_post($id) : false);
		cfcp_header_featured_slot_item($post, $featured, str_replace('_', '', $slot));
	}
?>
	</ul>
	<input type="hidden" name="_cfcp_header_slot" id="_cfcp_header_slot" value="<?php echo cfcp_header_featured_meta($post->ID); ?>" />
	<script type="text/javascript">
	jQuery(function($) {
		$('#cfp-set-featured-position li').click(function() {
			
// if already selected, deselect
			var c = $(this).attr('class');
			if (typeof c != 'undefined' && c.indexOf('cfp-featured-') != -1) {
				$('#_cfcp_header_slot').val('0');
				$(this).removeClass('cfp-featured-pending').removeClass('cfp-featured-set')
			}
// select
			$('#_cfcp_header_slot').val($(this).attr('id').replace('cfp-featured-position-', ''));
			$(this).siblings().removeClass('cfp-featured-pending').removeClass('cfp-featured-set').end().addClass('cfp-featured-pending');
		});
	});
	</script>
<?php
}

function cfcp_header_featured_slot_item($post, $featured, $i = 1) {
// set class
	if (cfcp_header_featured_meta($post->ID) == $i) {
		$class = 'class="'.($featured->post_status == 'publish' ? 'cfp-featured-set' : 'cfp-featured-pending').'"';
	}
	else {
		$class = '';
	}

// no post set?
	if (!$featured) {
?>
		<li id="cfp-featured-position-<?php echo $i; ?>" <?php echo $class; ?>>
			<p class="none"><?php _e('(empty)', 'favepersonal'); ?></p>
		</li>
<?php
	}
	else {
// show post type
?>
		<li id="cfp-featured-position-<?php echo $i; ?>" <?php echo $class; ?>>
			<h4 class="cfp-featured-title"><?php echo esc_html($featured->post_title); ?></h4>
			<p class="cfp-featured-meta"><?php echo esc_html(get_post_format_string(get_post_format($featured->ID))); ?> &middot; <?php echo get_the_time('F j, Y', $featured); ?></p>
		</li>
<?php
	}
}

// Front-end Display functions

function cfcp_header_display() {
	switch (cfcp_header_options('type')) {
		case 'featured':
			cfcp_header_display_featured();
		break;
		case 'image':
			cfcp_header_display_image();
		break;
		case 'none':
		default:
	}
}

function cfcp_header_featured_slots() {
	$slots = cfcp_header_options('posts');
	$count = 0;
	$ids = array();
	foreach ($slots as $post_id) {
		if (!empty($post_id)) {
			$ids[] = $post_id;
			$count++;
		}
	}

	$posts = new WP_Query(array(
		'post__in' => wp_parse_id_list($ids)
	));
// if we have less than 3 posts set, grab the latest posts to fill the empty spots
	if ($count < 3) {
		$filler = new WP_Query(array(
			'posts_per_page' => (3 - $count),
			'post__not_in' => wp_parse_id_list($ids)
		));
	}
// run the slots
	$filler_i = 0;
	foreach ($slots as $slot => $post_id) {
		if (empty($post_id) && count($filler->posts)) {
			$slots[$slot] = $filler->posts[$filler_i]->ID;
			unset($filler->posts[$filler_i]);
			$filler_i++;
		}
	}
	return $slots;
}

function cfcp_header_display_featured() {
	$post_ids = cfcp_header_featured_slots();
	ob_start();
	foreach ($post_ids as $slot => $post_id) {
		cfcp_header_display_featured_post(str_replace('_', '', $slot), $post_id);
	}
	$content = ob_get_clean();
	cfct_template_file('header', 'featured-posts', compact('content'));
}

function cfcp_header_display_featured_post($slot, $post_id) {
	if (empty($post_id)) {
		$file = 'empty';
	}
	else {
		$file = 'default';
		if ($post_format = get_post_format($post_id)) {
// find files
			$format_files = cfct_files(CFCT_PATH.'header/featured');
// check for format file, or fall to default
			if (count($format_files) && in_array('format-'.$post_format.'.php', $format_files)) {
				$file = 'format-'.$post_format;
			}
		}
	}
	cfct_template_file('header/featured', $file, compact('slot', 'post_id'));
}

function cfcp_header_display_image() {
	cfct_template_file('header', 'featured-image');
}