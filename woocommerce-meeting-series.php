<?php
/*
Plugin Name:  Meeting Series for Woocommerce
Plugin URI:   https://winters.design
Description:  Change WooCommerce into something that sells registrations for Meeting Series.
Version:      1.0.3
Author:       Frankie Winters
Author URI:   https://frankie.winters.design
License:      Unlicense
License URI:  http://unlicense.org
Text Domain:  wcmstextdomain

This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <http://unlicense.org/> 
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

function add_theme_scripts() {
    $pluginURL = plugins_url("",__FILE__);
    $CSSURL = "$pluginURL/woocommerce-meeting-series.css";
    wp_enqueue_style( 'woocommerce-meeting-series', $CSSURL);
}
add_action( 'wp_enqueue_scripts', 'add_theme_scripts' );
  

/**
 * Require WooCommerce and metabox.io
 */
require_once dirname( __FILE__ ) . '/tgm-required.php';


/**
 *  ADD SCHEDULE AND VENUE INFORMATION TO THE PRODUCT PAGE
 * 
*/

// Output details before product meta area
function wcms_output_product_details( $post_id = '') {
  global $post;
  if ('' == $post_id) {
    $post_id = $post->ID;
  }
  echo _wcms_product_schedule_html( $post_id );
  echo _wcms_product_venue_html( $post_id );
}
add_action( 'woocommerce_before_add_to_cart_button', 'wcms_output_product_details', 10 );

// returns product schedule
function _wcms_product_schedule_html( $post_id = '' ) {
  $html = '';
  $html .= '<div class="meeting-schedule-wrapper"><h4><span class="fas fa-clipboard-list"></span> Meeting Schedule</h4>';
  $html .= _wcms_meeting_dates_html( $post_id );
  $html .= "</div>";
  return $html;
}

// returns an <ol> with the meetings in a friendly date format
function _wcms_meeting_dates_html( $post_id = '' ) {
  $html = '';
  $date_format = 'g:i A \o\n l, F jS, Y';
  $meetings = rwmb_get_value( 'wcms_meeting-date', [], $post_id );
  if ( !empty($meetings) ) {
    $html .= '<ol class="meeting-schedule">';
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
function _wcms_product_venue_html( $post_id = '' ) {
  global $post;
  if ('' == $post_id) {
    $post_id = $post->ID;
  }
  $args = array( 'taxonomy' => 'meeting_venue',);
  $venues = wp_get_post_terms( $post_id, 'meeting_venue', $args );
  $html = '';
    if ( count($venues) ) {
    $html .= '<div class="meeting-venue-wrapper">';
    foreach  ($venues as $venue) {
      $address = $venue->description;
      $title = $venue->name;
      $html .= '<h4><i class="fas fa-map-marker-alt"></i> Meeting Venue</h4>';
      if ( !empty( $address ) ) {
        $search_url = 'https://www.google.com/search?q=' . urlencode( $title . '+' . $address );
        $html .= "<p><strong>{$title}</strong> at <a href=\"{$search_url}\">{$address}</a></p>";
      } else {
        $search_url = 'https://www.google.com/search?q=' . urlencode( $title );
        $html .= "<p><a href=\"{$search_url}\"><strong>{$title}</strong></a></p>";
      }
    }
    $html .= '</div>';
  }
  return $html;
}

/**
 * ADD MEETING DETAILS TO ORDER, CART AMD CHECKOUT
 * 
*/

// Return meeting details in a minimal, inline format for including as line item detail for the product in orders.
function _wcms_meeting_details_concise_html( $post_id = '' ) {
  $venues = wp_get_post_terms( $post_id, 'meeting_venue' );
  $schedule = _wcms_meeting_dates_html( $post_id );
  $html = '';
  $html .= '<strong>Schedule:</strong>' . $schedule;
  if ( !empty($venues) ) {
    foreach ($venues as $venue) {
      $venue_address = $venue->description;
      $venue_name = $venue->name;
      $html .= "<br/><strong>Venue:</strong><br/><p>{$venue_name}</strong> — {$venue_address}</p>";
    }
  }
  return $html;
}

// Add meeting details as an order line item.
function wcms_add_meeting_details_to_order_items( $item, $cart_item_key, $values, $order ) {
  $post_id = $item['product_id'];
  $details_html = '<br/>';
  $details_html .= _wcms_meeting_details_concise_html( $post_id );
	$item->add_meta_data( 'Meeting Details', $details_html, true );
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wcms_add_meeting_details_to_order_items', 20, 4 );

// Add meting details to cart items.
function wcms_add_meeting_details_to_cart_item( $item_name,  $cart_item,  $cart_item_key ){
  if ( !is_page( 'cart' ) ) {
    return $item_name;
  }
  $post_id = $cart_item['product_id'];
  $details_html = _wcms_meeting_details_concise_html( $post_id );
  $html = $item_name . '<br/><br/>' . $details_html;
  return $html;
}
add_filter( 'woocommerce_cart_item_name', 'wcms_add_meeting_details_to_cart_item', 10, 3 );

/**
 * ADD PRODUCT MEETING METABOXES AND VENUE TAXONOMY
 * 
*/

// Register "meeting venue" product taxonomy
function wcms_create_product_venue_tax() {
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
add_action( 'init', 'wcms_create_product_venue_tax' );

// Add metaboxes for meeting dates to products
function wcms_create_product_schedule_metaboxes( $meta_boxes ) {
	$prefix = 'wcms_';
	$meta_boxes[] = array(
		'id' => 'schedule',
		'title' => esc_html__( 'Meeting Schedule', 'wcmstextdomain' ),
		'post_types' => array('product' ),
	    'context' => 'after_editor',
	    'priority' => 'low',
		'autosave' => 'true',
		'fields' => array(
      array(
        'id' => $prefix . 'meeting-date',
				'type' => 'datetime',
        'name' => esc_html__( 'Dates and Starting Times', 'wcmstextdomain' ),
        'desc' => 'Add an entry for each time this series meets. Drag and Drop to reorder.',
        'clone' => 'true',
				'sort_clone' => 'true',
        'add_button' => esc_html__( 'Add Meeting', 'wcmstextdomain' ),
        'timestamp'  => true,
          // For date options, see here http://api.jqueryui.com/datepicker
          // For time options, see here http://trentrichardson.com/examples/timepicker/
          'js_options' => array(
              'stepMinute'      => 5,
              'showTimepicker'  => true,
              'timeFormat'      => 'h:mm tt',
              'showButtonPanel' => false,
              'oneLine'         => true,
              'timeText'    => 'Start Time:',
          ),
			),
		),
	);
	return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'wcms_create_product_schedule_metaboxes', 999 );

/**
 * ADD PRODUCT MEETING DESCRIPTION METABOXES
 * 
*/

// Insert the content of meeting_subject_page before the product description on the product page
function wcms_output_meeting_subject_page() {
  global $post;
  $product_title = get_the_title();
  $field_id = 'wcms_meeting_subject_page';
  $meeting_subject_post_id = rwmb_get_value( $field_id );
  $meeting_subject_page = get_post( $meeting_subject_post_id );
  $stock_classes =  ['instock', 'outofstock'];
  $final_classes = array_intersect( get_post_class(), $stock_classes );
  $final_classes[] = 'meeting-description-wrapper';
  $post = $meeting_subject_page;
  
  echo '<div class="' . implode(' ', $final_classes) . '">';
  setup_postdata( $post );
    echo "<h2><strong>" . get_the_title() . "</strong>: {$product_title}</h2>";
    // get_template_part( 'content', 'page' );
    echo "<aside class=\"" . implode( ' ', get_post_class('', $meeting_subject_post_id)) . "\">";
    echo "<div class=\"entry-content\">";
    echo the_content();
    echo "</div>";
    echo "</aside>";
  wp_reset_postdata();
  echo '</div>';
}
add_action( 'woocommerce_before_single_product', 'wcms_output_meeting_subject_page', 10 );

// Create the product -> meeting_subject_page relationship and metaboxes
function wcms_create_product_meeting_subject_metaboxes( $meta_boxes ) {
  $meeting_subject_field_id = 'wcms_meeting_subject_page';
  $meeting_subject_link_field_id = 'wcms_meeting_subject_link';
  $meta_boxes[] = array(
		'id' => 'meeting_subject_page',
		'title' => esc_html__( 'Meeting Series Subject', 'pbtextdomain' ),
		'post_types' => array( 'product' ),
		'context' => 'after_title',
		'priority' => 'default',
		'autosave' => 'false',
		'fields' => array(
			array(
				'id' => $meeting_subject_field_id,
				'type' => 'post',
        'name' => esc_html__( 'Meeting Series Subject', 'pbtextdomain' ),
        'desc' => esc_html__( 'Choose a page that describes the material covered in these meetings.', 'pbtextdomain' ),
				'post_type' => 'page',
				'field_type' => 'select_advanced',
      ),
      array(
        'name' => esc_html__( 'Edit Subject Page', 'pbtextdomain' ),
        'id'       => $meeting_subject_link_field_id,
        'type'     => 'custom_html',
        'callback' => 'wcms_edit_meeting_subject_link',
      )
    ),
  );
	return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'wcms_create_product_meeting_subject_metaboxes' );

// Create link on the product admin page to edit the meeting_subject_page
function wcms_edit_meeting_subject_link() {
  $meeting_subject_field_id = 'wcms_meeting_subject_page';
    if ( ! $post_being_edited_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ) ) {
     return '';
  }
  $post_being_edited = get_post($post_being_edited_id);
  $meeting_subject_post_id = rwmb_get_value($meeting_subject_field_id, '', $post_being_edited_id);
  if ( empty( $meeting_subject_post_id ) ) {
    return 'No subject page selected.';
  }
  $meeting_subject_page = get_post($meeting_subject_post_id);
  $meeting_subject_title = $meeting_subject_page->post_title;
  $meeting_subject_edit_url = get_edit_post_link($meeting_subject_post_id);
  $html = "<u><a href=\"{$meeting_subject_edit_url}\">Edit &ldquo;{$meeting_subject_title}&rdquo;</a></u>";
  $html .=  "<br/>Use your back button to return here after saving changes to the subject page.";

  return $html;
}

/**
 * CHANGE LABELS, LANGUAGE, AND BUTTONS
 * 
*/

// Rename woocommerce "Product" to "Series"
function wcms_rename_woocommerce_products_to_classes( $args ){
  $labels = array(
    'name'               => __( 'Meeting Series', 'wcmstextdomain' ),
    'singular_name'      => __( 'Meeting Series', 'wcmstextdomain' ),
    'menu_name'          => __( 'Meeting Series', 'Admin menu name', 'wcmstextdomain' ),
    'add_new'            => __( 'New Series', 'wcmstextdomain' ),
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
add_filter( 'woocommerce_register_post_type_product', 'wcms_rename_woocommerce_products_to_classes' );

// Rename woocommerce "Orders" to "Registration"
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
add_filter( 'woocommerce_register_post_type_shop_order', 'wcms_rename_woocommerce_order_to_registration' );

// Change 'add to cart' text on single product
function wcms_add_to_cart_label() {
  return __( 'Register Now', 'your-slug' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'wcms_add_to_cart_label' );

// Replaces the add to cart button in the product loop with one that links
// to the product page instead of adding the item to the car. 
// Labels: "Details and Registration" or "Sold Out :(""
function wcms_loop_add_to_cart_link( $button, $product  ) {
  global $product;
  $availability = $product->get_availability();
  $stock_status = $availability['class'];
  $button_text = '';
  if ($stock_status == 'out-of-stock') {
    $button_text .= __( 'Sold Out :(', 'wcmstextdomain' );
  } else {
    $button_text .=  __( 'Details and Registration', 'wcmstextdomain' );
  }
  $button = '<a class="button" href="' . $product->get_permalink() . '">' . $button_text . '</a>';
  return $button;
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'wcms_loop_add_to_cart_link', 10, 2 );

// Rename 'place order' button to 'continue'
function wcms_order_button_label() {
  return 'Continue &rarr;'; 
}
add_filter( 'woocommerce_order_button_text', 'wcms_order_button_label' );

// Change order availability text
function wcms_availabilty_label( $availability, $_product ) {
  global $product;
  
  if ( $_product->is_in_stock() ) {
    $stock = $product->get_stock_quantity();
    if ($stock == 1) {
      $availability['availability'] = __($stock . ' Space Left!', 'woocommerce');
    } else {
      $availability['availability'] = __($stock . ' Spaces Left!', 'woocommerce');
    }
  }
  if ( !$_product->is_in_stock() ) {
     $availability['availability'] = __('Sold Out!', 'woocommerce');
  }
  
  return $availability;
}
add_filter( 'woocommerce_get_availability', 'wcms_availabilty_label', 1, 2);

// New title for order recieved page
function wcms_order_received_title( $old_title ) {
  return 'Thank you for your registration!';
}
add_filter( 'woocommerce_endpoint_order-received_title', 'wcms_order_received_title' );

// Change 'order recieved' text and include printing link.
function wcms_order_received_text( $str, $order ) {
  $new_str = ' We have sent the registration details and receipt to your email, or you can <a href="javascript:window.print()">print this page</a>.';
  return $new_str;
}
add_filter('woocommerce_thankyou_order_received_text', 'wcms_order_received_text', 10, 2 );
