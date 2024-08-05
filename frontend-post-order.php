<?php
 /**
  * The plugin bootstrap file
  *
  * @link              https://robertdevore.com
  * @since             1.0.0
  * @package           Frontend_Post_Order
  *
  * @wordpress-plugin
  *
  * Plugin Name: Frontend Post Order
  * Description: A plugin to reorder posts via drag and drop on the frontend.
  * Plugin URI:  https://www.robertdevore.com/
  * Version:     1.0.0
  * Author:      Robert DeVore
  * Author URI:  https://robertdevore.com/
  * License:     GPL-2.0+
  * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
  * Text Domain: frontend-post-order
  * Domain Path: /languages
  */
 
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
*/
define( 'FRONTEND_POST_ORDER_VERSION', '1.0.0' );

/**
 * Enqueue necessary scripts and styles.
 */
function fpo_enqueue_scripts() {
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script(
        'fpo-custom-drag-drop', 
        plugin_dir_url( __FILE__ ) . 'js/custom-drag-drop.js', 
        [ 'jquery', 'jquery-ui-sortable' ], 
        null, 
        true
    );
    wp_localize_script(
        'fpo-custom-drag-drop', 
        'fpo_ajax_object', 
        [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'fpo_save_order_nonce' )
        ]
    );
}
add_action( 'wp_enqueue_scripts', 'fpo_enqueue_scripts' );

/**
 * Display posts in a sortable list for the specified post types.
 *
 * @param array $post_types Array of post types to display.
 */
function fpo_display_posts( $post_types ) {
    foreach ( $post_types as $post_type )  {
        $args = [
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ];
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            echo '<h2>' . esc_html( ucfirst( $post_type ) ) . '</h2>';
            echo '<ul id="sortable-' . esc_attr( $post_type ) . '">';
            while ( $query->have_posts() ) {
                $query->the_post();
                echo '<li id="post-' . get_the_ID() . '" class="ui-state-default">' . get_the_title() . '</li>';
            }
            echo '</ul>';
        }
        wp_reset_postdata();
    }
}

/**
 * Shortcode to display sortable posts.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function fpo_shortcode( $atts ) {
    $atts = shortcode_atts(
        [
            'type' => 'post',
        ],
        $atts,
        'sortable_posts'
    );

    $post_types = array_map( 'trim', explode( ',', $atts['type'] ) );

    ob_start();
    fpo_display_posts( $post_types );
    return ob_get_clean();
}
add_shortcode( 'frontend_post_order', 'fpo_shortcode' );

/**
 * Save the order of posts in the backend.
 */
function fpo_save_post_order() {
    check_ajax_referer( 'fpo_save_order_nonce', 'nonce' );

    if ( isset( $_POST['order'] ) )  {
        $order = explode( ',', $_POST['order'] );
        foreach ( $order as $position => $post_id ) {
            $post_id = ( int ) str_replace( 'post-', '', $post_id );
            wp_update_post( [
                'ID'         => $post_id,
                'menu_order' => $position,
            ] );
        }
        echo 'success';
    } else {
        echo 'error';
    }
    wp_die();
}
add_action( 'wp_ajax_save_post_order', 'fpo_save_post_order' );
