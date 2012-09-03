<?php

/*
  Plugin Name: CPT Slides
  Plugin URI: http://www.bostonwp.org
  Description: Adds actionable social slidemarking buttons to your site
  Version: 1
  Author: Jon Bishop
  Author URI: http://www.jonbishop.com
  License: GPLv2 or later
 */

/*
 * * Create the custom post type
 */

if (!defined('SPTSLIDES_URL'))
    define('CPTSLIDES_URL', plugin_dir_url(__FILE__));

if (!defined('CPTSLIDES_PATH'))
    define('CPTSLIDES_PATH', plugin_dir_path(__FILE__));

function cptslides_init() {
    $labels = array(
        'name' => 'Slides',
        'singular_name' => 'Slide',
        'add_new' => 'Add New', 'slide',
        'add_new_item' => 'Add New Slide',
        'edit_item' => 'Edit Slide',
        'new_item' => 'New Slide',
        'all_items' => 'All Slides',
        'view_item' => 'View Slide',
        'search_items' => 'Search Slides',
        'not_found' => 'No slides found',
        'not_found_in_trash' => 'No slides found in Trash',
        'parent_item_colon' => '',
        'menu_name' => 'Slides'
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'author', 'page-attributes')
    );

    register_post_type('cptslides', $args);
}
add_action('init', 'cptslides_init');

/*
 * * Create the custom post type
 */

function create_cptslides_taxonomies() {
    // Add new taxonomy, make it hierarchical (like categories)
    $labels = array(
        'name' => _x('Presentations', 'taxonomy general name'),
        'singular_name' => _x('Presentation', 'taxonomy singular name'),
        'search_items' => __('Search Presentations'),
        'all_items' => __('All Presentations'),
        'parent_item' => __('Parent Presentation'),
        'parent_item_colon' => __('Parent Presentation:'),
        'edit_item' => __('Edit Presentation'),
        'update_item' => __('Update Presentation'),
        'add_new_item' => __('Add New Presentation'),
        'new_item_name' => __('New Presentation Name'),
        'menu_name' => __('Presentations'),
    );

    register_taxonomy('cptslides_presentation', array('cptslides'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'presentation'),
    ));
}
add_action('init', 'create_cptslides_taxonomies', 0);

/*
 * * Create the custom post type
 */

function cptslides_updated_messages($messages) {
    global $post, $post_ID;

    $messages['cptslides'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => sprintf(__('Slide updated. <a href="%s">View slide in presentation</a>'), esc_url(get_permalink($post_ID))),
        2 => __('Custom field updated.'),
        3 => __('Custom field deleted.'),
        4 => __('Slide updated.'),
        5 => isset($_GET['revision']) ? sprintf(__('Slide restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
        6 => sprintf(__('Slide published. <a href="%s">View slide in presentation</a>'), esc_url(get_permalink($post_ID))),
        7 => __('Slide saved.'),
        8 => sprintf(__('Slide submitted. <a target="_blank" href="%s">Preview presentation</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
        9 => sprintf(__('Slide scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview presentation</a>'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
        10 => sprintf(__('Slide draft updated. <a target="_blank" href="%s">Preview presentation</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
    );
    return $messages;
}
add_filter('post_updated_messages', 'cptslides_updated_messages');

/*
 * * Create the custom post type
 */

function cptslides_help_text($contextual_help, $screen_id, $screen) {
    //$contextual_help .= $screen_id; // use this to help determine $screen->id
    if ('cptslides' == $screen->id) {
        $contextual_help =
                '<p>' . __('Things to remember when creating custom post types:') . '</p>' .
                '<ul>' .
                '<li>' . __('It is better if you prefix your name with a short "namespace" that identifies your plugin, theme or website that implements the custom post type.') . '</li>' .
                '<li>' . __('Custom post types have little to do with blog posts and are better classified as custom content types.') . '</li>' .
                '<li>' . __('Remember you can change the labels and messaging on your custom post as well as the contextual help (this).') . '</li>';
    } elseif ('edit-cptslides' == $screen->id) {
        $contextual_help =
                '<p>' . __('These slides are all part of individual presentations grouped by taxonomies.') . '</p>';
    } else if ('edit-cptslides_presentation'  == $screen->id) {

        
    }
    return $contextual_help;
}
add_action('contextual_help', 'cptslides_help_text', 10, 3);

/*
 * * Add sorting to admin slides list
 */

// Set up our column headers
function cptslides_edit_columns($columns) {
    $columns = array(
        "cb" => "<input type=\"checkbox\" />",
        "title" => "Title",
        "presentation" => "Presentation",
        "order" => "Order",
        "author" => "Author",
        "date" => "Date"
    );

    return $columns;
}

// Output data under new column headers
function cptslides_custom_columns($column) {
    global $post;

    switch ($column) {
        case "order":
            if (isset($post->menu_order)) {
                echo $post->menu_order;
            } else {
                echo 0;
            }
            break;
        case "presentation":
            $presentation = get_the_term_list($post->ID, 'cptslides_presentation', '', '', '');
            echo $presentation;
            break;
    }
}

// Modify orderby query to order by our new data
function cptslides_column_orderby($orderby, $wp_query) {
    global $wpdb;

    $wp_query->query = wp_parse_args($wp_query->query);
    if (isset($wp_query->query['orderby'])) {
        if ('order' == @$wp_query->query['orderby'])
            $orderby = "menu_order " . $wp_query->get('order');
    }
    return $orderby;
}

// More complex orderby query to order by taxonomy
// Adapted from: http://scribu.net/wordpress/sortable-taxonomy-columns.html
function cptslides_position_clauses($clauses, $wp_query) {
    global $wpdb;

    if (isset($wp_query->query['orderby']) && 'presentation' == $wp_query->query['orderby']) {

        $clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;

        $clauses['where'] .= " AND (taxonomy = 'cptslides_presentation' OR taxonomy IS NULL)";
        $clauses['groupby'] = "object_id";
        $clauses['orderby'] = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
        $clauses['orderby'] .= ( 'ASC' == strtoupper($wp_query->get('order')) ) ? 'ASC' : 'DESC';
    }
    return $clauses;
}

// Register our new table headers so they are clickable
function cptslides_column_register_sortable($columns) {
    $columns['presentation'] = 'presentation';
    $columns['order'] = 'order';
    return $columns;
}
add_filter('manage_edit-cptslides_sortable_columns', 'cptslides_column_register_sortable');
add_filter('posts_orderby', 'cptslides_column_orderby', 10, 2);
add_filter('posts_clauses', 'cptslides_position_clauses', 10, 2);
add_action("manage_posts_custom_column", "cptslides_custom_columns");
add_filter("manage_edit-cptslides_columns", "cptslides_edit_columns");

/*
 * * Redirect all requests to our presentation post tpye to the 
 */

function cptslides_redirect() {
    global $post;
    // Instead of accessing slides directly, redirect to archive and focus on requested slide
    if (get_post_type() == 'cptslides' && !is_tax()) {
        // Find the slide's presenation link
        $terms = get_the_terms($post->ID, 'cptslides_presentation');
        foreach ($terms as $term) {
            $term_link = get_tag_link($term);
            break;
        }
        // Loop through slides to find the resuested slides order in the presentation
        $counter = 1;
        $post_id = $post->ID;
        $args = array(
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'posts_per_page' => -1,
            'cptslides_presentation' => 'custom-post-types',
            'post_status' => 'publish'
        );
        $query = new WP_Query($args);
        if ($query->have_posts())
            while ($query->have_posts()): $query->the_post();
                $counter++;
                if ($post_id == $post->ID) {
                    $term_link .= '#' . $counter;
                    break;
                }
            endwhile;
        // Go to the presentation
        wp_redirect($term_link);
        die();
    }
}

add_action('template_redirect', 'cptslides_redirect');

/*
 * * Use a custom template
 */

function cptslides_template($template) {
    global $post;
    if ($post->post_type == 'cptslides') {
        return CPTSLIDES_PATH . 'template.php';
    }
    return $template;
}

add_filter('template_include', 'cptslides_template', 1, 1);
?>
