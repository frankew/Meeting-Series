<?php
/*
Plugin Name:  Meeting Series for Woocommerce
Plugin URI:   https://winters.design
Description:  Change WooCommerce into something that sells registrations for Meeting Series.
Version:      2.0.1
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

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('WOOCOMMERCE_PRODUCT', 'product');
define('PRODUCT_FILTER_NAME', 'wcms_filter_product');

function add_theme_scripts()
{
    $pluginURL = plugins_url("", __FILE__);
    $CSSURL = "$pluginURL/woocommerce-meeting-series.css";
    wp_enqueue_style('woocommerce-meeting-series', $CSSURL);
}
add_action('wp_enqueue_scripts', 'add_theme_scripts');


/**
 * Require WooCommerce and metabox.io
 */
require_once dirname(__FILE__) . '/tgm-required.php';


/**
 *  ADD SCHEDULE AND VENUE INFORMATION TO THE PRODUCT PAGE
 *
*/

// Output details before product meta area
function wcms_output_meeting_details($post_id = '')
{
    global $post;
    if ('' == $post_id) {
        $post_id = $post->ID;
    }
    echo _wcms_meeting_schedule_html($post_id);
    echo _wcms_meeting_mentor_html($post_id);
    echo _wcms_meeting_venue_html($post_id);
}
add_action('woocommerce_before_add_to_cart_button', 'wcms_output_meeting_details', 0);

// returns product schedule
function _wcms_meeting_schedule_html($post_id = '')
{
    $html = '';
    $html .= '<div class="meeting-schedule-wrapper"><h4><span class="fas fa-clipboard-list"></span> Meeting Schedule</h4>';
    $html .= _wcms_meeting_dates_html($post_id);
    $html .= _wcms_meeting_duration_html($post_id);
    $html .= "</div>";
    return $html;
}

// returns an <ol> with the meetings in a friendly date format
function _wcms_meeting_dates_html($post_id = '')
{
    $html = '';
    $date_format = 'l, F jS, Y \a\t g:i a';
    $meetings = rwmb_get_value('wcms_meeting-date', [], $post_id);
    if (!empty($meetings)) {
        $html .= '<ol class="meeting-schedule">';
        foreach ($meetings as $meeting) {
            $html .= '<li>';
            $html .= date($date_format, $meeting);
            $html .= '</li>';
        }
        $html .= '</ol>';
    }
    return $html;
}

// returns an <ol> with the meetings duration text
function _wcms_meeting_duration_html($post_id = '')
{
    $html = '';
    $meeting_duration = rwmb_get_value('wcms_meeting-duration', [], $post_id);
    if (!empty($meeting_duration)) {
        $html .= "<p class=\"meeting-duration\">{$meeting_duration}</p>";
    }
    return $html;
}

// returns the product venue
function _wcms_meeting_venue_html($post_id = '')
{
    global $post;
    if ('' == $post_id) {
        $post_id = $post->ID;
    }
    $args = array( 'taxonomy' => 'meeting_venue',);
    $venues = wp_get_post_terms($post_id, 'meeting_venue', $args);
    $html = '';
    if (count($venues)) {
        $html .= '<div class="meeting-venue-wrapper">';
        $html .= '<h4><i class="fas fa-map-marker-alt"></i> Meeting Venue</h4>';
        foreach ($venues as $venue) {
            $title = $venue->name;
            $address = $venue->description;
            // $venue_id = $venue->post_id;

            // if ('Online Classes' == $title) { // This heinous hack courtesy COVID19
            $html .= "<p><strong>{$title}</strong> &mdash; {$address}</p>";
            // } elseif (!empty($address)) {
                // $search_url = 'https://www.google.com/search?q=' . urlencode($title . '+' . $address);
                // $html .= "<p><strong>{$title}</strong> at <a href=\"{$search_url}\">{$address}</a></p>";
            // } else {
                // $search_url = 'https://www.google.com/search?q=' . urlencode($title);
                // $html .= "<p><a href=\"{$search_url}\"><strong>{$title}</strong></a></p>";
            // }
        }
        $html .= '</div>';
    }
    return $html;
}

// returns the product venue
function _wcms_meeting_mentor_html($post_id = '')
{
    global $post;
    if ('' == $post_id) {
        $post_id = $post->ID;
    }
    $html = '';
    $field_id = 'wcms_meeting_mentor_page';
    $meeting_mentor_post_id = rwmb_get_value($field_id);
    if (!empty($meeting_mentor_post_id)) {
        $html .= '<div class="meeting-mentor-wrapper">';
        $mentor_title = get_the_title($meeting_mentor_post_id);
        $mentor_description = get_the_excerpt($meeting_mentor_post_id);
        $mentor_page_link = get_the_permalink($meeting_mentor_post_id);
        $html .= "<h4><i class='fas fa-user-check'></i> Led by {$mentor_title}</h4>";
        $html .= "<p>{$mentor_description} <a href='{$mentor_page_link}'>Read More...</a></p>";
        $html .= '</div>';
    }
    return $html;
}

/**
 * ADD MEETING DETAILS TO ORDER, CART AMD CHECKOUT
 *
*/

// Return meeting details in a minimal, inline format for including as line item detail for the product in orders.
function _wcms_meeting_details_concise_html($post_id = '')
{
    $venues = wp_get_post_terms($post_id, 'meeting_venue');
    $html = '';
    $html .= '<strong>Schedule:</strong>' . $schedule;
    $html .= _wcms_meeting_dates_html($post_id);
    $html .= _wcms_meeting_duration_html($post_id);
    if (!empty($venues)) {
        foreach ($venues as $venue) {
            $venue_address = $venue->description;
            $venue_name = $venue->name;
            $html .= "<strong>Venue:</strong><br/><p>{$venue_name}</strong> — {$venue_address}</p>";
        }
    }
    return $html;
}

// Add meeting details as an order line item.
function wcms_add_meeting_details_to_order_items($item, $cart_item_key, $values, $order)
{
    $post_id = $item['product_id'];
    $details_html = '<br/>';
    $details_html .= _wcms_meeting_details_concise_html($post_id);
    $item->add_meta_data('Meeting Details', $details_html, true);
}
add_action('woocommerce_checkout_create_order_line_item', 'wcms_add_meeting_details_to_order_items', 20, 4);

// Add meting details to cart items.
function wcms_add_meeting_details_to_cart_item($item_name, $cart_item, $cart_item_key)
{
    if (!is_page('cart')) {
        return $item_name;
    }
    $post_id = $cart_item['product_id'];
    $details_html = _wcms_meeting_details_concise_html($post_id);
    $html = $item_name . '<br/><br/>' . $details_html;
    return $html;
}
add_filter('woocommerce_cart_item_name', 'wcms_add_meeting_details_to_cart_item', 10, 3);

/**
 * ADD PRODUCT MEETING METABOXES AND VENUE TAXONOMY
 *
*/

// Register "meeting venue" product taxonomy
function wcms_create_product_venue_tax()
{
    $labels = array(
        'name'              => _x('Meeting Venues', 'taxonomy general name', 'wcmstextdomain'),
        'singular_name'     => _x('Meeting Venue', 'taxonomy singular name', 'wcmstextdomain'),
        'search_items'      => __('Search Meeting Venues', 'wcmstextdomain'),
        'all_items'         => __('All Meeting Venues', 'wcmstextdomain'),
        'parent_item'       => __('Parent Meeting Venue', 'wcmstextdomain'),
        'parent_item_colon' => __('Parent Meeting Venue:', 'wcmstextdomain'),
        'edit_item'         => __('Edit Meeting Venue', 'wcmstextdomain'),
        'update_item'       => __('Update Meeting Venue', 'wcmstextdomain'),
        'add_new_item'      => __('Add New Meeting Venue', 'wcmstextdomain'),
        'new_item_name'     => __('New Meeting Venue Name', 'wcmstextdomain'),
        'menu_name'         => __('Meeting Venues', 'wcmstextdomain'),
    );
    $args = array(
        'labels' => $labels,
        'description' => __('The place where classes meet', 'wcmstextdomain'),
        'hierarchical' => false,
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
    register_taxonomy('meeting_venue', array(WOOCOMMERCE_PRODUCT, ), $args);
}
add_action('init', 'wcms_create_product_venue_tax');

// Add metaboxes for meeting dates to products
function wcms_create_product_schedule_metaboxes($meta_boxes)
{
    $meta_boxes[] = array(
        'id' => 'schedule',
        'title' => esc_html__('Meeting Schedule', 'wcmstextdomain'),
        'post_types' => array(WOOCOMMERCE_PRODUCT ),
        'context' => 'after_title',
        // 'priority' => 'high',
        'autosave' => 'true',
        'fields' => array(
            array(
                'id' => 'wcms_meeting-date',
                'type' => 'datetime',
                'name' => esc_html__('Dates and Starting Times', 'wcmstextdomain'),
                'desc' => 'Add an entry for each time this series meets. Drag and Drop to reorder.',
                'clone' => 'true',
                'sort_clone' => 'true',
                'add_button' => esc_html__('Add Meeting', 'wcmstextdomain'),
                'timestamp'  => true,
                    // For date options, see here http://api.jqueryui.com/datepicker
                    // For time options, see here http://trentrichardson.com/examples/timepicker/
                    'js_options' => array(
                            'stepMinute'      => 5,
                            'showTimepicker'  => true,
                            'changeYear'     => true,
                            'changeMonth'     => true,
                            'timeFormat'      => 'h:mm tt',
                            // 'showButtonPanel' => false,
                            'oneLine'         => true,
                            'timeText'    => 'Start Time:',
                    ),
            ),
            array(
                'id' => 'wcms_meeting-duration',
                'type' => 'textarea',
                'name' => esc_html__('Meeting Duration', 'wcmstextdomain'),
                'placeholder' => esc_html__('e.g., “Each meeting is 2.5 hours” or “We have four ninety-minute sessions with a break for lunch. We finish up around 5pm each day.”', 'wcmstextdomain'),
            ),
        ),
    );
    return $meta_boxes;
}
add_filter('rwmb_meta_boxes', 'wcms_create_product_schedule_metaboxes');

/**
 * ADD PRODUCT MEETING DESCRIPTION METABOXES
 *
*/

// Insert the content of meeting_subject_page before the product description on the product page
function wcms_output_meeting_subject_page()
{
    global $post;
    $product_title = get_the_title();
    $field_id = 'wcms_meeting_subject_page';
    $meeting_subject_post_id = rwmb_get_value($field_id);
    if (!empty($meeting_subject_post_id)) {
        $meeting_subject_page = get_post($meeting_subject_post_id);
        $stock_classes =  ['instock', 'outofstock'];
        $final_classes = array_intersect(get_post_class(), $stock_classes);
        $final_classes[] = 'meeting-subject-wrapper';
        $post = $meeting_subject_page;
        echo '<div class="' . implode(' ', $final_classes) . '">';
        setup_postdata($post);
        echo "<h2><strong>" . get_the_title() . "</strong>: {$product_title}</h2>";
        echo "<aside class=\"" . implode(' ', get_post_class('', $meeting_subject_post_id)) . "\">";
        echo "<div class=\"entry-content\">";
        echo the_content();
        echo "</div>";
        echo "</aside>";
        wp_reset_postdata();
        echo '</div>';
    }
}
add_action('woocommerce_before_single_product', 'wcms_output_meeting_subject_page', 10);

// Create the product -> meeting_subject_page relationship and metaboxes
function wcms_create_meeting_subject_metaboxes($meta_boxes)
{
    $meeting_subject_field_id = 'wcms_meeting_subject_page';
    $meeting_subject_link_field_id = 'wcms_meeting_subject_link';
    $meta_boxes[] = array(
        'id' => 'meeting_subject_page',
        'title' => esc_html__('Meeting Series Subject', 'pbtextdomain'),
        'post_types' => array( WOOCOMMERCE_PRODUCT ),
        'context' => 'after_title',
        // 'priority' => 'high',
        'autosave' => 'false',
        'fields' => array(
            array(
                'id' => $meeting_subject_field_id,
                'type' => 'post',
                'name' => esc_html__('Meeting Series Subject', 'pbtextdomain'),
                'desc' => esc_html__('Choose a page for the material covered in the meetings.', 'pbtextdomain'),
                'post_type' => 'page',
                'field_type' => 'select_advanced',
            ),
            array(
                'name' => esc_html__('Edit Subject Page', 'pbtextdomain'),
                'id'       => $meeting_subject_link_field_id,
                'type'     => 'custom_html',
                'callback' => 'wcms_edit_meeting_subject_link',
            )
        ),
    );
    return $meta_boxes;
}
add_filter('rwmb_meta_boxes', 'wcms_create_meeting_subject_metaboxes');


// Create the product -> meeting_mentor_page relationship and metaboxes
function wcms_create_meeting_mentor_metaboxes($meta_boxes)
{
    $meeting_mentor_field_id = 'wcms_meeting_mentor_page';
    $meeting_mentor_link_field_id = 'wcms_meeting_mentor_link';
    $meta_boxes[] = array(
        'id' => 'meeting_mentor_page',
        'title' => esc_html__('Meeting Series Mentor', 'pbtextdomain'),
        'post_types' => array( WOOCOMMERCE_PRODUCT ),
        'context' => 'after_title',
        // 'priority' => 'high',
        'autosave' => 'false',
        'fields' => array(
            array(
                'id' => $meeting_mentor_field_id,
                'type' => 'post',
                'name' => esc_html__('Mentor', 'pbtextdomain'),
                'desc' => esc_html__('Choose a page for the leader or instructor of the meetings.', 'pbtextdomain'),
                'post_type' => 'page',
                'field_type' => 'select_advanced',
            ),
            array(
                'name' => esc_html__('Edit Mentor Page', 'pbtextdomain'),
                'id'       => $meeting_mentor_link_field_id,
                'type'     => 'custom_html',
                'callback' => 'wcms_edit_meeting_mentor_link',
            )
        ),
    );
    return $meta_boxes;
}
add_filter('rwmb_meta_boxes', 'wcms_create_meeting_mentor_metaboxes');


// Create link on the product admin page to edit the meeting_subject_page
function wcms_edit_meeting_subject_link()
{
    $meeting_subject_field_id = 'wcms_meeting_subject_page';
    if (! $post_being_edited_id = filter_input(INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT)) {
        return '';
    }
    $post_being_edited = get_post($post_being_edited_id);
    $meeting_subject_post_id = rwmb_get_value($meeting_subject_field_id, '', $post_being_edited_id);
    if (empty($meeting_subject_post_id)) {
        return 'No subject page selected.';
    }
    $meeting_subject_page = get_post($meeting_subject_post_id);
    $meeting_subject_title = $meeting_subject_page->post_title;
    $meeting_subject_edit_url = get_edit_post_link($meeting_subject_post_id);
    $html = "<u><a href=\"{$meeting_subject_edit_url}\">Edit &ldquo;{$meeting_subject_title}&rdquo;</a></u>";
    $html .=  "<br/>Use your back button to return here after saving changes to the subject page.";

    return $html;
}

// Create link on the product admin page to edit the meeting_mentor_page
function wcms_edit_meeting_mentor_link()
{
    $meeting_mentor_field_id = 'wcms_meeting_mentor_page';
    if (! $post_being_edited_id = filter_input(INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT)) {
        return '';
    }
    $post_being_edited = get_post($post_being_edited_id);
    $meeting_mentor_post_id = rwmb_get_value($meeting_mentor_field_id, '', $post_being_edited_id);
    if (empty($meeting_mentor_post_id)) {
        return 'No mentor page selected.';
    }
    $meeting_mentor_page = get_post($meeting_mentor_post_id);
    $meeting_mentor_title = $meeting_mentor_page->post_title;
    $meeting_mentor_edit_url = get_edit_post_link($meeting_mentor_post_id);
    $html = "<u><a href=\"{$meeting_mentor_edit_url}\">Edit &ldquo;{$meeting_mentor_title}&rdquo;</a></u>";
    $html .=  "<br/>Use your back button to return here after saving changes to the mentor page.";

    return $html;
}

/**
 * CHANGE LABELS, LANGUAGE, AND BUTTONS
 *
*/

// Rename woocommerce "Product" to "Series"
function wcms_rename_woocommerce_products_to_classes($args)
{
    $labels = array(
        'name'               => __('Meeting Series', 'wcmstextdomain'),
        'singular_name'      => __('Meeting Series', 'wcmstextdomain'),
        'menu_name'          => __('Meeting Series', 'Admin menu name', 'wcmstextdomain'),
        'add_new'            => __('New Series', 'wcmstextdomain'),
        'add_new_item'       => __('New Meeting Series', 'wcmstextdomain'),
        'edit'               => __('Edit', 'wcmstextdomain'),
        'edit_item'          => __('Edit', 'wcmstextdomain'),
        'view_item'          => __('View Meeting Series', 'wcmstextdomain'),
        'search_items'       => __('Search Series', 'wcmstextdomain'),
        'not_found'          => __('No Meeting Series found', 'wcmstextdomain'),
        'not_found_in_trash' => __('No Meeting Series found in trash', 'wcmstextdomain'),
        'parent'             => __('Parent Series', 'wcmstextdomain'),
    );
    $args['labels'] = $labels;
    $args['description'] = __('This is where you can add new Meeting Series to your store.', 'wcmstextdomain');
    return $args;
}
add_filter('woocommerce_register_post_type_product', 'wcms_rename_woocommerce_products_to_classes');

// Rename woocommerce "Orders" to "Registration"
function wcms_rename_woocommerce_order_to_registration($args)
{
    $labels = array(
        'name' => __('Registrations', 'wcmstextdomain'),
        'singular_name' => _x('Registration', 'shop_order post type singular name', 'wcmstextdomain'),
        'add_new' => __('Add registration', 'wcmstextdomain'),
        'add_new_item' => __('Add new registration', 'wcmstextdomain'),
        'edit' => __('Edit', 'wcmstextdomain'),
        'edit_item' => __('Edit registration', 'wcmstextdomain'),
        'new_item' => __('New registration', 'wcmstextdomain'),
        'view' => __('View registration', 'wcmstextdomain'),
        'view_item' => __('View registration', 'wcmstextdomain'),
        'search_items' => __('Search registrations', 'wcmstextdomain'),
        'not_found' => __('No orders found', 'wcmstextdomain'),
        'not_found_in_trash' => __('No orders found in trash', 'wcmstextdomain'),
        'parent' => __('Parent registrations', 'wcmstextdomain'),
        'menu_name' => _x('Registrations', 'Admin menu name', 'wcmstextdomain'),
        'filter_items_list' => __('Filter registrations', 'wcmstextdomain'),
        'items_list_navigation' => __('Orders navigation', 'wcmstextdomain'),
        'items_list' => __('Orders list', 'wcmstextdomain'),
    );
    $args['labels'] = $labels;
    $args['description'] = __('This is where store orders are stored.', 'wcmstextdomain');
    return $args;
}
add_filter('woocommerce_register_post_type_shop_order', 'wcms_rename_woocommerce_order_to_registration');

// Change 'add to cart' text on single product
function wcms_add_to_cart_label()
{
    return __('Register Now', 'your-slug');
}
add_filter('woocommerce_product_single_add_to_cart_text', 'wcms_add_to_cart_label');

function wcms_title_placeholder($title, $post)
{
    if ($post->post_type == WOOCOMMERCE_PRODUCT) {
        $my_title = "Series name";
        return $my_title;
    }
    return $title;
}
add_filter('enter_title_here', 'wcms_title_placeholder', 20, 2);


// Replaces the add to cart button in the product loop with one that links
// to the product page instead of adding the item to the car.
// Labels: "Details and Registration" or "Sold Out :(""
function wcms_loop_add_to_cart_link($button, $product)
{
    global $product;
    $availability = $product->get_availability();
    $stock_status = $availability['class'];
    $button_text = '';
    if ($stock_status == 'out-of-stock') {
        $button_text .= __('Sold Out :(', 'wcmstextdomain');
    } else {
        $button_text .=  __('Details and Registration', 'wcmstextdomain');
    }
    $button = '<a class="button" href="' . $product->get_permalink() . '">' . $button_text . '</a>';
    return $button;
}
add_filter('woocommerce_loop_add_to_cart_link', 'wcms_loop_add_to_cart_link', 10, 2);

// Rename 'place order' button to 'continue'
function wcms_order_button_label()
{
    return 'Continue &rarr;';
}
add_filter('woocommerce_order_button_text', 'wcms_order_button_label');

// Change order availability text
function wcms_availabilty_label($availability, $_product)
{
    global $product;

    if ($_product->is_in_stock()) {
        $stock = $product->get_stock_quantity();
        if ($stock == 1) {
            $availability['availability'] = __($stock . ' Space Left!', 'wcmstextdomain');
        } else {
            $availability['availability'] = __($stock . ' Spaces Left!', 'wcmstextdomain');
        }
    }
    if (!$_product->is_in_stock()) {
        $availability['availability'] = __('Sold Out!', 'wcmstextdomain');
    }

    return $availability;
}
add_filter('woocommerce_get_availability', 'wcms_availabilty_label', 1, 2);

// New title for order recieved page
function wcms_order_received_title($old_title)
{
    return 'Thank you for your registration!';
}
add_filter('woocommerce_endpoint_order-received_title', 'wcms_order_received_title');

// Change 'order recieved' text and include printing link.
function wcms_order_received_text($str, $order)
{
    $new_str = 'We have sent the receipt to your email, or you can <a href="javascript:window.print()">print this page</a>. We\'ll be in touch soon to confirm your registration!';
    return $new_str;
}
add_filter('woocommerce_thankyou_order_received_text', 'wcms_order_received_text', 10, 2);

/**
 * Adds product filtering dropdown to the orders list
 *
 */
function filter_orders_by_product_selection()
{
    global $typenow;
    if ('shop_order' != $typenow) {
        return;
    }
    $args = array(
        'posts_per_page' => - 1,
        'orderby'        => 'title',
        'order'          => 'asc',
        'post_type'      => WOOCOMMERCE_PRODUCT,
        'post_status'    => 'publish',
    );
    $all_posts = get_posts($args);
    $selected = empty($_REQUEST[PRODUCT_FILTER_NAME]) ? '' : $_REQUEST[PRODUCT_FILTER_NAME]; ?><select name="<?php echo PRODUCT_FILTER_NAME ?>">
    <option value="">
        <?php esc_html_e('All series', 'wc-filter-orders'); ?>
    </option>
    <?php foreach ($all_posts as $product) : ?>
    <option <?php selected($selected, $product->ID)?> value="<?php echo $product->ID ?>"><?php echo $product->post_title ?></option>
    <?php endforeach; ?>
</select>
<?php
}

function attach_filter_orders_by_product()
{
    if (is_admin() && ! defined('DOING_AJAX')) {
        add_action('restrict_manage_posts', 'filter_orders_by_product_selection');
        add_filter('posts_where', 'filter_orders_by_product');
    }
}
add_action('plugins_loaded', 'attach_filter_orders_by_product');

function filter_orders_by_product($where)
{
    if (is_search()) {
        global $wpdb;
        $t_posts = $wpdb->posts;
        $t_order_items = $wpdb->prefix . "woocommerce_order_items";
        $t_order_itemmeta = $wpdb->prefix . "woocommerce_order_itemmeta";

        if (isset($_GET[PRODUCT_FILTER_NAME]) && !empty($_GET[PRODUCT_FILTER_NAME])) {
            $product = intval($_GET[PRODUCT_FILTER_NAME]);
            $where .= " AND $product IN (SELECT $t_order_itemmeta.meta_value FROM $t_order_items LEFT JOIN $t_order_itemmeta on $t_order_itemmeta.order_item_id=$t_order_items.order_item_id WHERE $t_order_items.order_item_type='line_item' AND $t_order_itemmeta.meta_key='_product_id' AND $t_posts.ID=$t_order_items.order_id)";
        }
    }
    return $where;
}
