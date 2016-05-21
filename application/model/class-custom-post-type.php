<?php

namespace PeerRaiser\Model;

class Custom_Post_Type {

     /**
      * Array of post types used by this plugin
      * @var    array
      */
     protected $peerraiser_post_types;

     /**
      * Array of taxonomies used by this plugin
      * @var    array
      */
     protected $peerraiser_taxonomies;

     /**
      * The name of the post type.
      * @var    string
      */
     protected $post_type_name;

     /**
      * A sanitized version of the post type name.
      * @var    string
      */
     protected $sanitized_name;

     /**
      * The singular name of the post type. Human friendly string, capitalized with spaces
      * @var    string
      */
     protected $singular;

     /**
      * The plural name of the post type. Human friendly string, capitalized with spaces
      */
     protected $plural;

     /**
      * The post type slug. Machine friendly string, lowercase with hyphens
      * @var    string
      */
     protected $slug;

     /**
      * An array of user-specific options for the post type
      * @var    array
      */
     protected $options;

     /**
      * An array of user-specific options for the post type labels
      * @var    array
      */
     protected $post_labels;

     /**
      * An array of user-specific options for the taxonomy labels
      * @var    array
      */
     protected $taxonomy_labels;

     /**
      * Taxonomies associated with this custom post type
      * @var    array
      */
     protected $taxonomies;

     /**
      * Taxonomy settings
      * @var    array
      */
     protected $taxonomy_settings;

     /**
      * Existing taxonomies to be registed after the post has been registered
      * @var    array
      */
     protected $existing_taxonomies;

     /**
      * Columns that should appear on the admin edit screen. Used with add_admin_columns()
      * @var    array
      */
     protected $columns;

     public function __construct( $post_type_names, $options = array(), $post_labels = array(), $taxonomy_labels = array() ) {

         // If post type name is an array
         if ( is_array( $post_type_names ) ) {

             // Array to add names to
             $names = array( 'singular', 'plural', 'slug' );

             // Set the post type name
             $this->post_type_name = $post_type_names['post_type_name'];

             // Set the sanitized version of the name
             $this->sanitized_name = $this->sanitize_post_type_name( $post_type_names['post_type_name'] );

             // Loop over possible names
             foreach ( $names as $name ) {

                 // If the name has been set by user
                 if ( isset( $post_type_names[$name] ) ) {

                     // Use the user setting
                     $this->$name = $post_type_names[ $name ];

                 // Else generate the name
                 } else {

                     // Define the method to use
                     $method = 'get_'.$name;

                     // Generate the name
                     $this->$name = $this->$method();
                 }

             }

         // Else only the post type name is supplied
         } else {

             // Apply to post type name
             $this->post_type_name = $post_type_names;

             // Set the sanitized version of the name
             $this->sanitized_name = $this->sanitize_post_type_name( $post_type_names );

             // Set the slug name
             $this->slug = $this->get_slug( $post_type_names );

             // Set the plural name label
             $this->plural = $this->get_plural( $post_type_names );

             // Set the singular name label
             $this->singular = $this->get_singular( $post_type_names );

         }

         // Set the user submitted options to the object
         $this->options = $options;

         $this->post_labels  = $post_labels;

         $this->taxonomy_labels = $taxonomy_labels;

         // Register taxonomies
         $this->add_action( 'init', array( &$this, 'register_taxonomies' ) );

         // Register the post type
         $this->add_action( 'init', array( &$this, 'register_post_type' ) );

         // Register existing taxonomies
         $this->add_action( 'init', array( &$this, 'register_existing_taxonomies' ) );

         // Add taxonomy to admin edit columns
         $this->add_filter( 'manage_edit-' . $this->post_type_name . '_columns', array( &$this, 'add_admin_columns' ) );

         // Rewrite post update messages
         $this->add_filter( 'post_updated_messages', array( &$this, 'updated_messages' ) );
         $this->add_filter( 'bulk_post_updated_messages', array( &$this, 'bulk_updated_messages' ), 10, 2 );

     }

     /**
      * Helper function to hook into WordPress
      *
      * @since    1.0.0
      * @param    string     $action           Name of the action
      * @param    string     $function         Function hook that will run on action
      * @param    integer    $priority         Order which the function will execute
      * @param    integer    $accepted_args    Number of arguments the function accepts
      */
     protected function add_action( $action, $function, $priority = 10, $accepted_args = 1 ) {
         // Pass variables into WordPress add_action function
         add_action( $action, $function, $priority, $accepted_args );
     }

     /**
      * Helper function to apply functions to WordPress filters
      *
      * @since    1.0.0
      * @param    string     $action           Name of the action
      * @param    string     $function         Function hook that will run on action
      * @param    integer    $priority         Order which the function will execute
      * @param    integer    $accepted_args    Number of arguments the function accepts
      */
     protected function add_filter( $action, $function, $priority = 10, $accepted_args = 1 ) {
         // Pass variables into Wordpress add_action function
         add_filter( $action, $function, $priority, $accepted_args );
     }

     /**
      * Creates a machine friendly slug from the post type name
      *
      * @since     1.0.0
      * @param     string    $name    The custom post type name
      * @return    string             The slug
      */
     protected function get_slug( $name = null ) {
         // If no name, use the post type name.
         if ( ! isset( $name ) ) {
             $name = $this->post_type_name;
         }
         // Convert to lower case, replace spaces and underscore with hypthens
         return str_replace( array(' ', '_'), '-', strtolower($name) );
     }

     /**
      * Creates a plural version of the name
      *
      * @since     1.0.0
      * @param     string    $name    The name of the custom post type
      * @return    string             The plural version of the name
      */
     protected function get_plural( $name = null ) {
         // If no name, the post_type_name is used
         if ( ! isset( $name ) ) {
             $name = $this->post_type_name;
         }

         $plural = array(
             '/(quiz)$/i'               => "$1zes",
             '/^(ox)$/i'                => "$1en",
             '/([m|l])ouse$/i'          => "$1ice",
             '/(matr|vert|ind)ix|ex$/i' => "$1ices",
             '/(x|ch|ss|sh)$/i'         => "$1es",
             '/([^aeiouy]|qu)y$/i'      => "$1ies",
             '/(hive)$/i'               => "$1s",
             '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
             '/(shea|lea|loa|thie)f$/i' => "$1ves",
             '/sis$/i'                  => "ses",
             '/([ti])um$/i'             => "$1a",
             '/(tomat|potat|ech|her|vet)o$/i'=> "$1oes",
             '/(bu)s$/i'                => "$1ses",
             '/(alias)$/i'              => "$1es",
             '/(octop)us$/i'            => "$1i",
             '/(ax|test)is$/i'          => "$1es",
             '/(us)$/i'                 => "$1es",
             '/(f)oot$/i'               => "$1eet",
             '/(g)oose$/i'              => "$1eese",
             '/(sex)$/i'                => "$1es",
             '/(child)$/i'              => "$1ren",
             '/(wom)an$/i'              => "$1en",
             '/(m)an$/i'                => "$1en",
             '/(t)ooth$/i'              => "$1eeth",
             '/(pe)rson$/i'             => "$1ople",
             '/(valve)$/i'              => "$1s",
             '/s$/i'                    => "s",
             '/$/'                      => "s"
         );

         $unchanging = array(
             'sheep',
             'fish',
             'deer',
             'series',
             'species',
             'money',
             'rice',
             'information',
             'equipment'
         );

         // If the singular and plural version is the same, just return it
         if ( in_array( strtolower( $name ), $unchanging ) ) {
             return $name;
         }

         // check for matches using regular expressions
         foreach ( $plural as $pattern => $result ) {
             if ( preg_match( $pattern, $name ) )
                 return preg_replace( $pattern, $result, $name );
         }

         return $name;

     }

     /**
      * Returns the human friendly singular name
      *
      * @since     1.0.0
      * @param     string    $name    The slug name you want to unpluralize
      * @return    string             The human readable singular name
      */
     function get_singular( $name = null ) {
         // If no name is passed the post_type_name is used.
         if ( ! isset( $name ) ) {
             $name = $this->post_type_name;
         }
         // Return the string.
         return $this->get_human_friendly( $name );
     }

     /**
      * Returns the human friendly name
      *
      * @since     1.0.0
      * @param     string    $name    The name you want to make human friendly
      * @return    string             The human friendly name
      */
     function get_human_friendly( $name = null ) {
         // If no name is passed the post_type_name is used.
         if ( ! isset( $name ) ) {
             $name = $this->post_type_name;
         }
         // Return human friendly name.
         return ucwords( strtolower( str_replace( "-", " ", str_replace( "_", " ", $name ) ) ) );
     }

     /**
      * Registers the custom post type
      *
      * @since     1.0.0
      * @return    object|error    Returns the post type object on success, an error on failure
      */
     function register_post_type() {

         // Friendly post type names.
         $plural   = $this->plural;
         $singular = $this->singular;
         $slug     = $this->slug;
         // Default labels.
         $labels = array(
             'name'               => sprintf( __( '%s', 'peerraiser' ), $plural ),
             'singular_name'      => sprintf( __( '%s', 'peerraiser' ), $singular ),
             'menu_name'          => sprintf( __( '%s', 'peerraiser' ), $plural ),
             'all_items'          => sprintf( __( '%s', 'peerraiser' ), $plural ),
             'add_new'            => __( 'Add New', 'peerraiser' ),
             'add_new_item'       => sprintf( __( 'Add New %s', 'peerraiser' ), $singular ),
             'edit_item'          => sprintf( __( 'Edit %s', 'peerraiser' ), $singular ),
             'new_item'           => sprintf( __( 'New %s', 'peerraiser' ), $singular ),
             'view_item'          => sprintf( __( 'View %s', 'peerraiser' ), $singular ),
             'search_items'       => sprintf( __( 'Search %s', 'peerraiser' ), $plural ),
             'not_found'          => sprintf( __( 'No %s found.', 'peerraiser' ), $plural ),
             'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'peerraiser' ), $plural ),
             'parent_item_colon'  => sprintf( __( 'Parent %s:', 'peerraiser' ), $singular )
         );
         $labels = array_merge( $labels, $this->post_labels );
         // Default options.
         $defaults = array(
             'labels' => $labels,
             'public' => true,
             'rewrite' => array(
                 'slug' => $slug,
             )
         );
         // Merge user submitted options with defaults.
         $options = array_replace_recursive( $defaults, $this->options );
         // Set the object options as full options passed.
         $this->options = $options;
         // Check that the post type doesn't already exist.
         if ( ! post_type_exists( $this->sanitized_name ) ) {
             // Register the post type.
             return register_post_type( $this->sanitized_name, $options );
         } else {
             return get_post_type_object( $this->sanitized_name );
         }
     }

     /**
      * Register Taxonomy
      *
      * @since     1.0.0
      * @param     string    $taxonomy_names    Taxonomy slug
      * @param     array     $options           Taxonomy options
      */
     function register_taxonomy( $taxonomy_names, $options = array() ) {
         // Post type defaults to $this post type if unspecified.
         $post_type = $this->post_type_name;
         // An array of the names required excluding taxonomy_name.
         $names = array(
             'singular',
             'plural',
             'slug'
         );
         // if an array of names are passed
         if ( is_array( $taxonomy_names ) ) {
             // Set the taxonomy name
             $taxonomy_name = $taxonomy_names['taxonomy_name'];
             // Cycle through possible names.
             foreach ( $names as $name ) {
                 // If the user has set the name.
                 if ( isset( $taxonomy_names[ $name ] ) ) {
                     // Use user submitted name.
                     $$name = $taxonomy_names[ $name ];
                     // Else generate the name.
                 } else {
                     // Define the function to be used.
                     $method = 'get_' . $name;
                     // Generate the name
                     $$name = $this->$method( $taxonomy_name );
                 }
             }
             // Else if only the taxonomy_name has been supplied.
         } else  {
             // Create user friendly names.
             $taxonomy_name = $this->sanitize_taxonomy_name( $taxonomy_names );
             $singular = $this->get_singular( $taxonomy_names );
             $plural   = $this->get_plural( $taxonomy_names );
             $slug     = $this->get_slug( $taxonomy_names );
         }
         // Default labels.
         $labels = array(
             'name'                       => sprintf( __( '%s', 'peerraiser' ), $plural ),
             'singular_name'              => sprintf( __( '%s', 'peerraiser' ), $singular ),
             'menu_name'                  => sprintf( __( '%s', 'peerraiser' ), $plural ),
             'all_items'                  => sprintf( __( 'All %s', 'peerraiser' ), $plural ),
             'edit_item'                  => sprintf( __( 'Edit %s', 'peerraiser' ), $singular ),
             'view_item'                  => sprintf( __( 'View %s', 'peerraiser' ), $singular ),
             'update_item'                => sprintf( __( 'Update %s', 'peerraiser' ), $singular ),
             'add_new_item'               => sprintf( __( 'Add New %s', 'peerraiser' ), $singular ),
             'new_item_name'              => sprintf( __( 'New %s Name', 'peerraiser' ), $singular ),
             'parent_item'                => sprintf( __( 'Parent %s', 'peerraiser' ), $plural ),
             'parent_item_colon'          => sprintf( __( 'Parent %s:', 'peerraiser' ), $plural ),
             'search_items'               => sprintf( __( 'Search %s', 'peerraiser' ), $plural ),
             'popular_items'              => sprintf( __( 'Popular %s', 'peerraiser' ), $plural ),
             'separate_items_with_commas' => sprintf( __( 'Seperate %s with commas', 'peerraiser' ), $plural ),
             'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'peerraiser' ), $plural ),
             'choose_from_most_used'      => sprintf( __( 'Choose from most used %s', 'peerraiser' ), $plural ),
             'not_found'                  => sprintf( __( 'No %s found', 'peerraiser' ), $plural ),
         );
         // Default options.
         $defaults = array(
             'labels' => $labels,
             'hierarchical' => true,
             'rewrite' => array(
                 'slug' => $slug
             )
         );
         // Merge default options with user submitted options.
         $options = array_replace_recursive( $defaults, $options );
         // Add the taxonomy to the object array, this is used to add columns and filters to admin panel.
         $this->taxonomies[] = $taxonomy_name;
         // Create array used when registering taxonomies.
         $this->taxonomy_settings[ $taxonomy_name ] = $options;
     }

     /**
      * Cycle through taxonomies added with the class and register them
      *
      * @since     1.0.0
      */
     function register_taxonomies() {
         if ( is_array( $this->taxonomy_settings ) ) {
             // Foreach taxonomy registered with the post type.
             foreach ( $this->taxonomy_settings as $taxonomy_name => $options ) {
                 // Register the taxonomy if it doesn't exist.
                 if ( ! taxonomy_exists( $taxonomy_name ) ) {
                     // Register the taxonomy with Wordpress
                     register_taxonomy( $taxonomy_name, $this->sanitized_name, $options );
                 } else {
                     // If taxonomy exists, register it later with register_existing_taxonomies
                     $this->existing_taxonomies[] = $taxonomy_name;
                 }
             }
         }
     }

     /**
      * Loop through existing taxonomies and registers them after the post type has been registered
      *
      * @since     1.0.0
      */
     function register_existing_taxonomies() {
         if( is_array( $this->existing_taxonomies ) ) {
             foreach( $this->existing_taxonomies as $taxonomy_name ) {
                 register_taxonomy_for_object_type( $taxonomy_name, $this->sanitized_name );
             }
         }
     }

     /**
      * Add column to the admin edit screen
      *
      * @since    1.0.0
      * @param    array    $columns    All of the columns
      */
     function add_admin_columns( $columns ) {
         // If no user columns have been specified, add taxonomies
         if ( ! isset( $this->columns ) ) {
             $new_columns = array();
             // determine which column to add custom taxonomies after
             if ( is_array( $this->taxonomies ) && in_array( 'post_tag', $this->taxonomies ) || $this->post_type_name === 'post' ) {
                 $after = 'tags';
             } elseif( is_array( $this->taxonomies ) && in_array( 'category', $this->taxonomies ) || $this->post_type_name === 'post' ) {
                 $after = 'categories';
             } elseif( post_type_supports( $this->post_type_name, 'author' ) ) {
                 $after = 'author';
             } else {
                 $after = 'title';
             }
             // foreach existing columns
             foreach( $columns as $key => $title ) {
                 // add existing column to the new column array
                 $new_columns[$key] = $title;
                 // we want to add taxonomy columns after a specific column
                 if( $key === $after ) {
                     // If there are taxonomies registered to the post type.
                     if ( is_array( $this->taxonomies ) ) {
                         // Create a column for each taxonomy.
                         foreach( $this->taxonomies as $tax ) {
                             // WordPress adds Categories and Tags automatically, ignore these
                             if( $tax !== 'category' && $tax !== 'post_tag' ) {
                                 // Get the taxonomy object for labels.
                                 $taxonomy_object = get_taxonomy( $tax );
                                 // Column key is the slug, value is friendly name.
                                 $new_columns[ $tax ] = sprintf( __( '%s', 'peerraiser' ), $taxonomy_object->labels->name );
                             }
                         }
                     }
                 }
             }
             // overide with new columns
             $columns = $new_columns;
         } else {
             // Use user submitted columns, these are defined using the object columns() method.
             $columns = $this->columns;
         }
         return $columns;
     }

     /**
      * Choose columns to be displayed on the admin edit screen
      *
      * @since     1.0.0
      * @param     string    $columns    Column name
      */
     function columns( $columns ) {
       $this->columns = $columns;
     }

     /**
      * Populate columns
      *
      * Define what and how to populate a speicific admin column.
      *
      * @param string $column_name The name of the column to populate.
      * @param mixed $callback An anonyous function or callable array to call when populating the column.
      */

     /**
      * Define what and how to populate a specific admin column
      *
      * @since     1.0.0
      * @param     string    $column_name    Name of the column
      * @param     mixed     $callback       Anonymous function or callable array to invoke when populating the column
      */
     function populate_column( $column_name, $callback ) {
         $this->custom_populate_columns[ $column_name ] = $callback;
     }

     /**
      * Define which columns are sortable on the admin edit screen
      *
      * @since     1.0.0
      * @param     array     $columns    Columns that are sortable
      */
     function sortable( $columns = array() ) {
         // Assign user defined sortable columns to object variable.
         $this->sortable = $columns;
         // Run filter to make columns sortable.
         $this->add_filter( 'manage_edit-' . $this->post_type_name . '_sortable_columns', array( &$this, 'make_columns_sortable' ) );
         // Run action that sorts columns on request.
         $this->add_action( 'load-edit.php', array( &$this, 'load_edit' ) );
     }

     /**
      * Internal function that modifies the post type names in updated messages
      *
      * @since     1.0.0
      * @param     array    $messages    Post update messages
      * @return    array                 Post update messages
      */
     function updated_messages( $messages ) {
         $post = get_post();
         $singular = $this->singular;
         $messages[$this->post_type_name] = array(
             0 => '',
             1 => sprintf( __( '%s updated.', 'peerraiser' ), $singular ),
             2 => __( 'Custom field updated.', 'peerraiser' ),
             3 => __( 'Custom field deleted.', 'peerraiser' ),
             4 => sprintf( __( '%s updated.', 'peerraiser' ), $singular ),
             5 => isset( $_GET['revision'] ) ? sprintf( __( '%2$s restored to revision from %1$s', 'peerraiser' ), wp_post_revision_title( (int) $_GET['revision'], false ), $singular ) : false,
             6 => sprintf( __( '%s updated.', 'peerraiser' ), $singular ),
             7 => sprintf( __( '%s saved.', 'peerraiser' ), $singular ),
             8 => sprintf( __( '%s submitted.', 'peerraiser' ), $singular ),
             9 => sprintf(
                 __( '%2$s scheduled for: <strong>%1$s</strong>.', 'peerraiser' ),
                 date_i18n( __( 'M j, Y @ G:i', 'peerraiser' ), strtotime( $post->post_date ) ),
                 $singular
             ),
             10 => sprintf( __( '%s draft updated.', 'peerraiser' ), $singular ),
         );
         return $messages;
     }

     /**
      * Internal function that modifies the post type names in bulk updated messages
      *
      * @since     1.0.0
      * @param     array    $bulk_messages    Bulk updated messages
      * @param     array    $bulk_counts      Array containing the number of each type updated
      * @return    array                      Bulk updated messages
      */
     function bulk_updated_messages( $bulk_messages, $bulk_counts ) {
         $singular = $this->singular;
         $plural = $this->plural;
         $bulk_messages[ $this->post_type_name ] = array(
             'updated'   => _n( '%s '.$singular.' updated.', '%s '.$plural.' updated.', $bulk_counts['updated'] ),
             'locked'    => _n( '%s '.$singular.' not updated, somebody is editing it.', '%s '.$plural.' not updated, somebody is editing them.', $bulk_counts['locked'] ),
             'deleted'   => _n( '%s '.$singular.' permanently deleted.', '%s '.$plural.' permanently deleted.', $bulk_counts['deleted'] ),
             'trashed'   => _n( '%s '.$singular.' moved to the Trash.', '%s '.$plural.' moved to the Trash.', $bulk_counts['trashed'] ),
             'untrashed' => _n( '%s '.$singular.' restored from the Trash.', '%s '.$plural.' restored from the Trash.', $bulk_counts['untrashed'] ),
         );
         return $bulk_messages;
     }

     /**
      * Flush the rewrite rules
      *
      * @since     1.0.0
      */
     public function flush() {
         flush_rewrite_rules();
     }

     /**
      * Sanitize Key
      *
      * Filters a string so it can be used safely as a custom post type name.
      *   * All non alphnumeric chacters, dashes, and underscores are removed.
      *   * Spaces are replaced with underscore.
      *   * The key is prepended with "pr_" to ensure it's unique
      *   * The key is trimmed to a max of 20 characters
      *
      * @since     1.0.0
      * @param     string    $name    The string to be sanitized
      * @return    string             Sanitized string
      */
     protected function sanitize_post_type_name( $name ) {
         $raw_key = $name;
         $name = strtolower( $name );
         $name = preg_replace( array('/\s+/', '/[^a-z0-9\-_]/'), array('_'), $name );

         $name = substr( $name, 0, 20 );

         return apply_filters( 'peerraiser_sanitize_post_type_name', $name, $raw_key );
     }


     protected function sanitize_taxonomy_name( $name ) {
         $raw_key = $name;
         $name = strtolower( $name );
         $name = preg_replace( array('/\s+/', '/[^a-z0-9\-_]/'), array('_'), $name );

         $name = substr( $name, 0, 32 );

         return apply_filters( 'peerraiser_sanitize_taxonomy_name', $name, $raw_key );
     }

}