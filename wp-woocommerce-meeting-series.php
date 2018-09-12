<?php
/*
Plugin Name:  Meeting Series for Woocommerce
Plugin URI:   https://developer.wordpress.org/plugins/the-basics/
Description:  Transmogrifies WooCommerce into something that sells Meeting Series.
Version:      20160911
Author:       WordPress.org
Author URI:   https://developer.wordpress.org/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wcmstextdomain
Domain Path:  /languages
*/

/**
 *  Add schedule and venue information to the product page
*/

// Outpuct details before product meta area
function wcms_output_product_details() {
  global $post;
  if ('' == $post_id) {
    $post_id = $post->ID;
  }
  echo wcms_product_schedule_html( $post_id );
  echo wcms_product_venue_html( $post_id );
}
add_action( 'woocommerce_product_meta_start', 'wcms_output_product_details', 10 );

// returns product schedule
function wcms_product_schedule_html( $post_id = '' ) {
  $html = '';
  $html .= '<div class="product-schedule-wrapper"><h4><span class="fas fa-clipboard-list"></span> Meeting Schedule</h4>';
  $html .= wcms_product_meeting_dates_list( $post_id );
  $html .= "</div>";
  return $html;
}

// returns an <ol> with the product meeting dates in a friently format
function wcms_product_meeting_dates_list( $post_id = '' ) {
  $html = '';
  $date_format = 'g:i A \o\n l, F jS, Y';
  $meetings = rwmb_get_value( 'wcms_meeting-date', [], $post_id );
  if ( !empty($meetings) ) {
    $html .= '<ol class=\'meeting-schedule\'>';
    foreach($meetings as $meeting) {
      $html .= '<li>';
      $html .= date( $date_format, $meeting );
      $html .= '</li>';
    }
    $html .= '</ol>';
  }
  return $html;
}

// returns the product venue
function wcms_product_venue_html( $post_id = '' ) {
  global $post;
  if ('' == $post_id) {
    $post_id = $post->ID;
  }
  $args = array( 'taxonomy' => 'meeting_venue',);
  $venues = wp_get_post_terms( $post_id, 'meeting_venue', $args );
  $html = '';
    if ( count($venues) ) {
    $html .= '<div class="product-venue-wrapper">';
      foreach  ($venues as $venue) {
      $address = $venue->description;
      $title = $venue->name;
      $search_url = 'https://www.google.com/search?q=' . urlencode( $title . '+' . $address );
      $html .= '<h4><i class="fas fa-map-marker-alt"></i> Meeting Venue</h4>';
      $html .= '<p><strong>' . $title . '</strong> at <a href="' . $search_url . '">' . $address . '</a></p>';
    }
    $html .= '</div>';
  }
  return $html;
}

/**
 * ADD CLASS DETAILS TO ORDER, CART AMD CHECKOUT
 */

// Return meeting details in a minimal, inline format for including as line item detail for the product in orders.
function wcms_meeting_details_concise_html( $post_id = '' ) {
  $venues = wp_get_post_terms( $post_id, 'meeting_venue' );
  $schedule = wcms_product_meeting_dates_list( $post_id );
  $html = '';
  $html .= '<strong>Schedule:</strong>' . $schedule;
  if ( !empty($venues) ) {
    foreach ($venues as $venue) {
      $venue_address = $venue->description;
      $venue_name = $venue->name;
      $html .= '<br/><strong>Venue:</strong><br/><p>' . $venue_name . '</strong> — ' . $venue_address . '</p>';
    }
  }
  return $html;
}

// Add concise details as an order line item
function wcms_add_meeting_details_to_order_items( $item, $cart_item_key, $values, $order ) {
  $post_id = $item['product_id'];
  $details_html = '<br/>';
  $details_html .= wcms_meeting_details_concise_html( $post_id );
	$item->add_meta_data( 'Meeting Details', $details_html, true );
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wcms_add_meeting_details_to_order_items', 20, 4 );

// Add concise details to cart items.
function wcms_add_meeting_details_to_cart_item( $item_name,  $cart_item,  $cart_item_key ){
  if ( !is_page( 'cart' ) ) {
    return $item_name;
  }
  $post_id = $cart_item['product_id'];
  $details_html = wcms_meeting_details_concise_html( $post_id );
  $html = $item_name . '<br/><br/>' . $details_html;
  return $html;
}
add_filter( 'woocommerce_cart_item_name', 'wcms_add_meeting_details_to_cart_item', 10, 3 );

/**
 * VENUE TAXONOMY AND MEETING METABOXES
 */

// Register "meeting venue" product taxonomy
function wcms_create_meeting_venue_tax() {
	$labels = array(
		'name'              => _x( 'Meeting Venues', 'taxonomy general name', 'wcmstextdomain' ),
		'singular_name'     => _x( 'Meeting Venue', 'taxonomy singular name', 'wcmstextdomain' ),
		'search_items'      => __( 'Search Meeting Venues', 'wcmstextdomain' ),
		'all_items'         => __( 'All Meeting Venues', 'wcmstextdomain' ),
		'parent_item'       => __( 'Parent Meeting Venue', 'wcmstextdomain' ),
		'parent_item_colon' => __( 'Parent Meeting Venue:', 'wcmstextdomain' ),
		'edit_item'         => __( 'Edit Meeting Venue', 'wcmstextdomain' ),
		'update_item'       => __( 'Update Meeting Venue', 'wcmstextdomain' ),
		'add_new_item'      => __( 'Add New Meeting Venue', 'wcmstextdomain' ),
		'new_item_name'     => __( 'New Meeting Venue Name', 'wcmstextdomain' ),
		'menu_name'         => __( 'Meeting Venues', 'wcmstextdomain' ),
	);
	$args = array(
		'labels' => $labels,
		'description' => __( 'The place where classes meet', 'wcmstextdomain' ),
		'hierarchical' => true,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_nav_menus' => false,
		'show_in_rest' => false,
		'show_tagcloud' => false,
		'show_in_quick_edit' => true,
		'show_admin_column' => true,
	);
	register_taxonomy( 'meeting_venue', array('product', ), $args );
}
add_action( 'init', 'wcms_create_meeting_venue_tax' );

// Add metaboxes for meeting dates to products
function wcms_create_product_meeting_metaboxes( $meta_boxes ) {
	$prefix = 'wcms_';
	$meta_boxes[] = array(
		'id' => 'schedule',
		'title' => esc_html__( 'Meetings', 'wcmstextdomain' ),
		'post_types' => array('product' ),
    'context' => 'after_editor',
    'priority' => 'low',
		'autosave' => 'false',
		'fields' => array(
			array(
				'id' => $prefix . 'meeting-date',
				'type' => 'datetime',
        'name' => esc_html__( 'Choose a Date and Starting Time', 'wcmstextdomain' ),
        'clone' => 'true',
				'sort_clone' => 'true',
        'add_button' => esc_html__( 'Add Meeting', 'wcmstextdomain' ),
        'timestamp'  => true,
			),
		),
	);
	return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'wcms_create_product_meeting_metaboxes', 999 );

/**
 * CHANGE LABELS AND LANGUAGE
 */

// Rename woocommerce "Product" to "Series"
add_filter( 'woocommerce_register_post_type_product', 'wcms_rename_woocommerce_products_to_classes' );
function wcms_rename_woocommerce_products_to_classes( $args ){
    $labels = array(
        'name'               => __( 'All Series', 'wcmstextdomain' ),
        'singular_name'      => __( 'Meeting Series', 'wcmstextdomain' ),
        'menu_name'          => _x( 'All Series', 'Admin menu name', 'wcmstextdomain' ),
        'add_new'            => __( 'New Meeting Series', 'wcmstextdomain' ),
        'add_new_item'       => __( 'New Meeting Series', 'wcmstextdomain' ),
        'edit'               => __( 'Edit', 'wcmstextdomain' ),
        'edit_item'          => __( 'Edit', 'wcmstextdomain' ),
        'view_item'          => __( 'View Meeting Series', 'wcmstextdomain' ),
        'search_items'       => __( 'Search Series', 'wcmstextdomain' ),
        'not_found'          => __( 'No Meeting Series found', 'wcmstextdomain' ),
        'not_found_in_trash' => __( 'No Meeting Series found in trash', 'wcmstextdomain' ),
        'parent'             => __( 'Parent Series', 'wcmstextdomain' ),
    );

    $args['labels'] = $labels;
    $args['description'] = __( 'This is where you can add new Meeting Series to your store.', 'wcmstextdomain' );
    return $args;
}

// Rename woocommerce "Orders" to "Registration"
add_filter( 'woocommerce_register_post_type_shop_order', 'wcms_rename_woocommerce_order_to_registration' );
function wcms_rename_woocommerce_order_to_registration( $args ){
    $labels = array(
      'name' => __( 'Registrations', 'wcmstextdomain' ),
      'singular_name' => _x( 'Registration', 'shop_order post type singular name', 'wcmstextdomain' ),
      'add_new' => __( 'Add registration', 'wcmstextdomain' ),
      'add_new_item' => __( 'Add new registration', 'wcmstextdomain' ),
      'edit' => __( 'Edit', 'wcmstextdomain' ),
      'edit_item' => __( 'Edit registration', 'wcmstextdomain' ),
      'new_item' => __( 'New registration', 'wcmstextdomain' ),
      'view' => __( 'View registration', 'wcmstextdomain' ),
      'view_item' => __( 'View registration', 'wcmstextdomain' ),
      'search_items' => __( 'Search registrations', 'wcmstextdomain' ),
      'not_found' => __( 'No orders found', 'wcmstextdomain' ),
      'not_found_in_trash' => __( 'No orders found in trash', 'wcmstextdomain' ),
      'parent' => __( 'Parent registrations', 'wcmstextdomain' ),
      'menu_name' => _x( 'Registrations', 'Admin menu name', 'wcmstextdomain' ),
      'filter_items_list' => __( 'Filter registrations', 'wcmstextdomain' ),
      'items_list_navigation' => __( 'Orders navigation', 'wcmstextdomain' ),
      'items_list' => __( 'Orders list', 'wcmstextdomain' ),
    );

    $args['labels'] = $labels;
    $args['description'] = __( 'This is where store orders are stored.', 'wcmstextdomain' );
    return $args;
}

// Change 'add to cart' text on single product
add_filter( 'woocommerce_product_single_add_to_cart_text', 'wcms_add_to_cart_text' );
function wcms_add_to_cart_text() {
  return __( 'Register Now', 'your-slug' );
}

// Change 'add to cart' text on shop loops
add_filter( 'woocommerce_product_add_to_cart_text', 'wcms_archive_add_to_cart_text' );
function wcms_archive_add_to_cart_text() {
  global $product;
  $availability = $product->get_availability();
  $stock_status = $availability['class'];
  if ($stock_status == 'out-of-stock') {
     return __( 'Sold Out :(', 'wcmstextdomain' );
  }
 return __( 'Register Now', 'wcmstextdomain' );
}

// Rename Place Order button to Continue
function wcms_rename_place_order_button() {
  return 'Continue &rarr;'; 
}
add_filter( 'woocommerce_order_button_text', 'wcms_rename_place_order_button' );

// New title for order recieved page
function wcms_order_received_title( $old_title ) {
  return 'Thank you for your registration!';
}
add_filter( 'woocommerce_endpoint_order-received_title', 'wcms_order_received_title' );

// Change order recieved text and include prinnting link.
function wcms_change_order_received_text( $str, $order ) {
  $new_str = $str . ' We have sent the registration information receipt to your email, or you can <a href="javascript:window.print()">print this page</a>.';
  return $new_str;
}
add_filter('woocommerce_thankyou_order_received_text', 'wcms_change_order_received_text', 10, 2 );