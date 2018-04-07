<?php

namespace PeerRaiser\Helper;

use \WP_Term_Query;

/**
 * Helper class for field data.
 */
class Field {

    /**
     * Returns an array of data formatted for select2 AJAX response
     *
     * @since     1.0.0
     * @param     array     $options
     * @return    array
     */
    public static function get_post_choices( $options = array() ) {

        // defaults
        $options = self::parse_args($options, array(
            'post_id'   => 0,
            's'         => '',
            'lang'      => false,
            'page'      => 1,
            'post_type' => '',
            'taxonomy'  => '',
        ));

        // vars
        $r = array();
        $args = array();

        // paged
        $args['posts_per_page'] = 20;
        $args['paged'] = $options['page'];

        if ( isset($options['taxonomy']) && isset($options['term_id']) ) {

        }

        // update $args
        if( !empty($options['post_type']) ) {
            $args['post_type'] = self::force_type_array( $options['post_type'] );
        } else {
            $args['post_type'] = self::get_post_types();
        }

        // create tax queries
        if( !empty($options['taxonomy']) ) {

            // append to $args
            $args['tax_query'] = array();

            // decode terms
            $taxonomies = self::decode_taxonomy_terms( $options['taxonomy'] );

            // now create the tax queries
            foreach( $taxonomies as $taxonomy => $terms ) {

                $args['tax_query'][] = array(
                    'taxonomy'  => $taxonomy,
                    'field'     => 'id',
                    'terms'     => $terms,
                );

            }
        }

        // search
        if( $options['s'] ) {
            $args['s'] = $options['s'];
        }

        // get posts grouped by post type
        $groups = self::get_grouped_posts( $args );

        if( !empty($groups) ) {

            foreach( array_keys($groups) as $group_title ) {

                // vars
                $posts = self::extract_var( $groups, $group_title );
                $titles = array();

                // data
                $data = array(
                    'text'      => $group_title,
                    'children'  => array()
                );

                foreach( array_keys($posts) as $post_id ) {
                    $posts[ $post_id ] = self::get_post_title( $posts[ $post_id ] );
                };

                // order by search
                if( !empty($args['s']) ) {
                    $posts = self::order_by_search( $posts, $args['s'] );
                }

                // append to $data
                foreach( array_keys($posts) as $post_id ) {
                    $data['children'][] = array(
                        'id'    => $post_id,
                        'text'  => $posts[ $post_id ]
                    );
                }

                // append to $r
                $r[] = $data;

            }

            // optgroup or single
            if ( count($args['post_type']) == 1 ) {
                $r = $r[0]['children'];
            }

        }

        return $r;

    }

    public static function get_donor_choices( $options = array() ) {
        $donor_table = new \PeerRaiser\Model\Database\Donor_Table();

        $args = array();

        if ( ! empty( $options['s'] ) ) {
            $args['donor_name'] = $options['s'];
        }

        $donors = $donor_table->get_donors( $args );

        $options = array();

        foreach( $donors as $donor ) {
            $options[] = array(
                'text' => $donor->full_name,
                'id'   => $donor->donor_id,
            );
        }

        return $options;
    }

    public static function get_campaign_choices( $options = array() ) {
        // defaults
        $options = self::parse_args($options, array(
            's'             => false,
            'page'          => 1,
            'number'        => 20,
            'offset'        => 0,
        ));

        $args = array(
            'taxonomy'   => array( 'peerraiser_campaign' ),
            'number'     => $options['number'],
            'order'      => 'ASC',
            'orderby'    => 'id',
            'hide_empty' => false
        );

        if ( $options['s'] ) {
            $args['name__like'] = $options['s'];
        }

        if ( absint( $options['page'] ) > 1 ) {
            $args['offset'] = ( $options['page'] - 1 ) * $options['number'];
        }

        $term_query = new WP_Term_Query( $args );

        $options = array();

        $terms = $term_query->get_terms();

        foreach( $term_query->get_terms() as $term ) {
            $options[] = array(
                'text' => $term->name,
                'id'   => $term->term_id,
            );
        }

        return $options;
    }

    public static function get_team_choices( $options = array() ) {
        // defaults
        $options = self::parse_args($options, array(
            's'             => false,
            'page'          => 1,
            'number'        => 20,
            'offset'        => 0,
        ));

        $args = array(
            'taxonomy'   => array( 'peerraiser_team' ),
            'number'     => $options['number'],
            'order'      => 'ASC',
            'orderby'    => 'id',
            'hide_empty' => false
        );

        if ( $options['s'] ) {
            $args['name__like'] = $options['s'];
        }

        if ( absint( $options['page'] ) > 1 ) {
            $args['offset'] = ( $options['page'] - 1 ) * $options['number'];
        }

        $term_query = new WP_Term_Query( $args );

        $options = array();

        $terms = $term_query->get_terms();

        foreach( $term_query->get_terms() as $term ) {
            $options[] = array(
                'text' => $term->name,
                'id'   => $term->term_id,
            );
        }

        return $options;
    }

   /**
    * Remove the var from the array and return the var
    *
    * @since     1.0.0
    * @param     array     &$array
    * @param     string    $key
    *
    * @return    mixed
    */
    public static function extract_var( &$array, $key ) {

        // check if exists
        if ( is_array($array) && array_key_exists($key, $array) ) {

            // store value
            $v = $array[ $key ];

            // unset
            unset( $array[ $key ] );

            // return
            return $v;
        }

        return null;
    }

   /**
    * Remove the vars from the array and return it
    *
    * @since     1.0.0
    * @param     array    &$array
    * @param     array    $keys
    * @return    array
    */
    public static function extract_vars( &$array, $keys ) {

        $r = array();

        foreach( $keys as $key ) {
            $r[ $key ] = self::extract_var( $array, $key );
        }

        return $r;
    }

    /**
     * Force variable to be an array
     *
     * @since     1.0.0
     * @param     mixed    $var    Thing to be converted
     * @return    array            An array
     */
    public static function force_type_array( $var ) {

        // If it's already an array, return it
        if ( is_array($var) ) {
            return $var;
        }

        // Empty? Return empty array
        if( empty($var) && !is_numeric($var) ) {
            return array();
        }

        // Convert comma seperated string to array
        if( is_string($var) ) {
            return explode(',', $var);
        }

        // Anything else just put into an array
        return array( $var );
    }

    /**
    * Return an array of available post types
    *
    * @since     1.0.0
    * @param     array     $exclude    Post types to exclude
    * @param     array     $include    Post types to include
    *
    * @return    array                Available post types
    */
    public static function get_post_types( $exclude = array(), $include = array() ) {

        // get all custom post types
        $post_types = get_post_types();

        // core exclude
        $exclude = wp_parse_args( $exclude, array('revision', 'nav_menu_item') );

        // include
        if ( !empty($include) ) {
            foreach( array_keys($include) as $i ) {
                $post_type = $include[ $i ];
                if( post_type_exists($post_type) ) {
                    $post_types[ $post_type ] = $post_type;
                }
            }
        }

        // exclude
        foreach( array_values($exclude) as $i ) {
            unset( $post_types[ $i ] );
        }

        // simplify keys
        $post_types = array_values($post_types);

        // return
        return $post_types;

    }

   /**
    * Decodes the $taxonomy:$term strings into a nested array
    *
    * @since     1.0.0
    * @param     boolean    $terms    The terms to decode
    *
    * @return    array
    */
    public static function decode_taxonomy_terms( $terms = false ) {

        // load all taxonomies if not specified in args
        if ( !$terms ) {
            $terms = self::get_taxonomy_terms();
        }

        // vars
        $r = array();

        foreach( $terms as $term ) {
            // vars
            $data = self::decode_taxonomy_term( $term );

            // create empty array
            if ( !array_key_exists($data['taxonomy'], $r) ) {
                $r[ $data['taxonomy'] ] = array();
            }

            // append to taxonomy
            $r[ $data['taxonomy'] ][] = $data['term'];
        }

        return $r;

    }

    /**
    * Convert a term string into an array of term data
    *
    * @since     1.0.0
    * @param     string    $string    The string to convert
    * @return    array
    */
    public static function decode_taxonomy_term( $string ) {

        $r = array();

        $data = explode(':', $string);
        $taxonomy = 'category';
        $term = '';

        // check data
        if( isset($data[1]) ) {
            $taxonomy = $data[0];
            $term = $data[1];
        }

        // add data to $r
        $r['taxonomy'] = $taxonomy;
        $r['term'] = $term;

        return $r;

    }

   /**
    * Return an array of available taxonomy terms
    *
    * @since     1.0.0
    * @param     array     $taxonomies    Taxonomies
    * @return    array
    */
    public static function get_taxonomy_terms( $taxonomies = array() ) {

        // force array
        $taxonomies = self::force_type_array( $taxonomies );

        // get pretty taxonomy names
        $taxonomies = self::get_pretty_taxonomies( $taxonomies );

        $r = array();

        // populate $r
        foreach( array_keys($taxonomies) as $taxonomy ) {
            // vars
            $label = $taxonomies[ $taxonomy ];
            $terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );

            if ( !empty($terms) ) {
                $r[ $label ] = array();

                foreach( $terms as $term ) {
                    $k = "{$taxonomy}:{$term->slug}";
                    $r[ $label ][ $k ] = $term->name;
                }
            }

        }

        return $r;

    }

   /**
    * Return an array of available taxonomies
    *
    * @since     1.0.0
    *
    * @return    array
    */
    public static function get_taxonomies() {
        // get all taxonomies
        $taxonomies = get_taxonomies( false, 'objects' );
        $ignore = array( 'nav_menu', 'link_category' );
        $r = array();

        // populate $r
        foreach( $taxonomies as $taxonomy ) {
            if ( in_array($taxonomy->name, $ignore) ) {
                continue;
            }

            $r[ $taxonomy->name ] = $taxonomy->name;
        }

        return $r;
    }

    public static function get_pretty_taxonomies( $taxonomies = array() ) {
        // get post types
        if( empty($taxonomies) ) {
            // get all custom post types
            $taxonomies = self::get_taxonomies();
        }

        // get labels
        $ref = array();
        $r = array();

        foreach( array_keys($taxonomies) as $i ) {

            // vars
            $taxonomy = self::extract_var( $taxonomies, $i);
            $obj = get_taxonomy( $taxonomy );
            $name = $obj->labels->singular_name;

            // append to r
            $r[ $taxonomy ] = $name;

            // increase counter
            if( !isset($ref[ $name ]) ) {
                $ref[ $name ] = 0;
            }

            $ref[ $name ]++;
        }

        // get slugs
        foreach( array_keys($r) as $i ) {
            // vars
            $taxonomy = $r[ $i ];

            if( $ref[ $taxonomy ] > 1 ) {
                $r[ $i ] .= ' (' . $i . ')';
            }
        }

        return $r;

    }

    public static function get_pretty_post_types( $post_types = array() ) {
        // get post types
        if( empty($post_types) ) {
            // get all custom post types
            $post_types = self::get_post_types();
        }

        // get labels
        $ref = array();
        $r = array();

        foreach( $post_types as $post_type ) {
            // vars
            $label = $post_type;

            // check that object exists (case exists when importing field group from another install and post type does not exist)
            if( post_type_exists($post_type) ) {
                $obj = get_post_type_object($post_type);
                $label = $obj->labels->singular_name;
            }

            // append to r
            $r[ $post_type ] = $label;

            // increase counter
            if( !isset($ref[ $label ]) ) {
                $ref[ $label ] = 0;
            }

            $ref[ $label ]++;
        }

        // get slugs
        foreach( array_keys($r) as $i ) {
            // vars
            $post_type = $r[ $i ];

            if( $ref[ $post_type ] > 1 ) {
                $r[ $i ] .= ' (' . $i . ')';
            }
        }

        return $r;
    }

    /**
     * Returns all posts grouped by post_type
     *
     * @since     1.0.0
     * @param     array    $args
     * @return    array
     */
    public static function get_grouped_posts( $args ) {
        // vars
        $r = array();

        // defaults
        $args = self::parse_args( $args, array(
            'posts_per_page'            => -1,
            'paged'                     => 0,
            'post_type'                 => 'post',
            'orderby'                   => 'menu_order title',
            'order'                     => 'ASC',
            'post_status'               => 'any',
            'suppress_filters'          => false,
            'update_post_meta_cache'    => false,
        ));

        // find array of post_type
        $post_types = self::force_type_array( $args['post_type'] );
        $post_types_labels = self::get_pretty_post_types($post_types);

        // attachment doesn't work if it is the only item in an array
        if( count($post_types) == 1 ) {
            $args['post_type'] = current($post_types);
        }

        // Order by post type
        add_filter('posts_orderby', array( __CLASS__, 'orderby_post_type'), 10, 2);

        // get posts
        $posts = get_posts( $args );

        // Remove filter
        remove_filter('posts_orderby', array( __CLASS__, 'orderby_post_type') );

        // loop
        foreach( $post_types as $post_type ) {

            // vars
            $this_posts = array();
            $this_group = array();

            // populate $this_posts
            foreach( array_keys($posts) as $key ) {
                if( $posts[ $key ]->post_type == $post_type ) {
                    $this_posts[] = self::extract_var( $posts, $key );
                }
            }

            // bail early if no posts for this post type
            if( empty($this_posts) ) {
                continue;
            }

            // sort into hierachial order
            // this will fail if a search has taken place because parents wont exist
            if( is_post_type_hierarchical($post_type) && empty($args['s'])) {

                // vars
                $match_id = $this_posts[ 0 ]->ID;
                $offset = 0;
                $length = count($this_posts);
                $parent = self::maybe_get( $args, 'post_parent', 0 );

                // reset $this_posts
                $this_posts = array();

                // get all posts
                $all_args = array_merge($args, array(
                    'posts_per_page'    => -1,
                    'paged'             => 0,
                    'post_type'         => $post_type
                ));

                $all_posts = get_posts( $all_args );

                // loop over posts and find $i
                foreach( $all_posts as $offset => $p ) {
                    if( $p->ID == $match_id ) {
                        break;
                    }
                }

                // order posts
                $all_posts = get_page_children( $parent, $all_posts );

                for( $i = $offset; $i < ($offset + $length); $i++ ) {
                    $this_posts[] = self::extract_var( $all_posts, $i);
                }

            }

            // populate $this_posts
            foreach( array_keys($this_posts) as $key ) {
                // extract post
                $post = self::extract_var( $this_posts, $key );

                // add to group
                $this_group[ $post->ID ] = $post;
            }

            // group by post type
            $post_type_name = $post_types_labels[ $post_type ];
            $r[ $post_type_name ] = $this_group;

        }

        return $r;

    }

   /**
    * Merges together 2 arrays and converts any numeric values to int
    *
    * @since     1.0.0
    * @param     array    $args
    * @param     array    $defaults
    *
    * @return    array
    */
    public static function parse_args( $args, $defaults = array() ) {

        // $args may not be an array!
        if( !is_array($args) ) {
            $args = array();
        }

        // parse args
        $args = wp_parse_args( $args, $defaults );

        // parse types
        $args = self::parse_types( $args );

        // return
        return $args;

    }

    /**
    * Converts numeric values to into and trims strings
    *
    * @since     1.0.0
    * @param     mixed    $array
    *
    * @return    mixed
    */
    public static function parse_types( $array ) {

        // some keys are restricted
        $restricted = array(
            'label',
            'name',
            'value',
            'instructions',
            'nonce'
        );

        // loop
        foreach( array_keys($array) as $k ) {
            // parse type if not restricted
            if( !in_array($k, $restricted, true) ) {
                $array[ $k ] = self::parse_type( $array[ $k ] );
            }
        }

        return $array;
    }

    public static function parse_type( $v ) {

        // test for array
        if( is_array($v) ) {
            return self::parse_types($v);
        }

        // bail early if not string
        if( !is_string($v) ) {
            return $v;
        }

        // trim
        $v = trim($v);

        // numbers
        if( is_numeric($v) && strval((int)$v) === $v ) {
            $v = intval( $v );
        }

        return $v;

    }

    public static function get_post_title( $post = 0 ) {

        // load post if given an ID
        if( is_numeric($post) ) {
            $post = get_post($post);
        }

        // title
        $title = get_the_title( $post->ID );

        // empty
        if( $title === '' ) {
            $title = __('(no title)', 'peerraiser');
        }

        // ancestors
        if( $post->post_type != 'attachment' ) {
            $ancestors = get_ancestors( $post->ID, $post->post_type );
            $title = str_repeat('- ', count($ancestors)) . $title;
        }

        // status
        if( get_post_status( $post->ID ) != "publish" ) {
            $title .= ' (' . get_post_status( $post->ID ) . ')';
        }

        return $title;

    }

    public static function order_by_search( $array, $search ) {

        $weights = array();
        $needle = strtolower( $search );

        // add key prefix
        foreach( array_keys($array) as $k ) {
            $array[ '_' . $k ] = self::extract_var( $array, $k );
        }

        // add search weight
        foreach( $array as $k => $v ) {

            // vars
            $weight = 0;
            $haystack = strtolower( $v );
            $strpos = strpos( $haystack, $needle );

            // detect search match
            if( $strpos !== false ) {
                // set eright to length of match
                $weight = strlen( $search );

                // increase weight if match starts at begining of string
                if( $strpos == 0 ) {
                    $weight++;
                }
            }

            // append to wights
            $weights[ $k ] = $weight;
        }

        // sort the array with menu_order ascending
        array_multisort( $weights, SORT_DESC, $array );

        // remove key prefix
        foreach( array_keys($array) as $k ) {
            $array[ substr($k,1) ] = self::extract_var( $array, $k );
        }

        // return
        return $array;
    }

    /**
     * Returns a var if it exists in an array
     *
     * @since     1.0.0
     * @param     array     $array      The array to look in
     * @param     string    $key        The key to look for
     * @param     mixed     $default    The value to return if not found
     *
     * @return    int               post_id
     */
    public static function maybe_get( $array, $key, $default = null ) {
        // check if exists
        if( isset($array[ $key ]) ) {
            return $array[ $key ];
        }

        // return
        return $default;
    }

    /**
     * Filter for ordering posts by post type
     */
    public static function orderby_post_type( $ordeby, $wp_query ) {

        global $wpdb;

        // get post types
        $post_types = $wp_query->get('post_type');

        // prepend SQL
        if( is_array($post_types) ) {
            $post_types = implode("','", $post_types);
            $ordeby = "FIELD({$wpdb->posts}.post_type,'$post_types')," . $ordeby;
        }

        return $ordeby;
    }

    public static function get_image_id_by_url( $image_url ) {
        global $wpdb;
        $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));

        return $attachment[0];
    }

}
