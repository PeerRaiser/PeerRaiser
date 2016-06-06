<?php

namespace PeerRaiser\Helper;

/**
 * PeerRaiser donation helper.
 */
class Stats {

    public static function get_total_donations(){
        global $wpdb;

        $total = get_transient( 'peerraiser_donations_total' );

        if ( false === $total ) {

            $total = (float) 0;

            $args = array(
                'post_type'      => 'pr_donation',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            );
            $donations = new \WP_Query( $args );

            $post_ids = wp_list_pluck( $donations->posts, 'ID' );

            if ( $post_ids ) {
                $post_ids = implode( ',', $post_ids );
                $total += $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_donation_amount' AND post_id IN({$post_ids})" );
            }

            // Cache results for 1 day. This cache is cleared automatically when a payment is made
            set_transient( 'peerraiser_donations_total', $total, 86400 );

        }

        if( $total < 0 ) {
            $total = 0;
        }

        return $total;
    }

    public static function get_total_donations_by_fundraiser( $fundraiser_id ) {
        global $wpdb;

        $total = (float) 0;

        $args = array(
            'post_type'       => 'pr_donation',
            'posts_per_page'  => -1,
            'post_status'     => 'publish',
            'connected_type'  => 'donation_to_fundraiser',
            'connected_items' => $fundraiser_id
        );
        $donations = new \WP_Query( $args );

        $post_ids = wp_list_pluck( $donations->posts, 'ID' );

        if ( $post_ids ) {
            $post_ids = implode( ',', $post_ids );
            $total += $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_donation_amount' AND post_id IN({$post_ids})" );
        }

        return $total;

    }

    public static function get_total_donations_by_campaign( $campaign_id ) {
        global $wpdb;

        $total = (float) 0;

        $args = array(
            'post_type'       => 'pr_donation',
            'posts_per_page'  => -1,
            'post_status'     => 'publish',
            'connected_type'  => 'donation_to_campaign',
            'connected_items' => $campaign_id
        );
        $donations = new \WP_Query( $args );

        $post_ids = wp_list_pluck( $donations->posts, 'ID' );

        if ( $post_ids ) {
            $post_ids = implode( ',', $post_ids );
            $total += $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_donation_amount' AND post_id IN({$post_ids})" );
        }

        return $total;

    }

    public static function get_total_donations_by_team( $team_id ) {
        global $wpdb;

        $total = (float) 0;

        $args = array(
            'post_type'       => 'fundraiser',
            'posts_per_page'  => -1,
            'post_status'     => 'publish',
            'connected_type'  => 'fundraiser_to_team',
            'connected_items' => $team_id
        );
        $fundraisers = new \WP_Query( $args );

        $post_ids = wp_list_pluck( $fundraisers->posts, 'ID' );

        $args = array(
            'post_type'       => 'pr_donation',
            'posts_per_page'  => -1,
            'post_status'     => 'publish',
            'connected_type'  => 'donation_to_fundraiser',
            'connected_items' => $post_ids
        );
        $donations = new \WP_Query( $args );

        $post_ids = wp_list_pluck( $donations->posts, 'ID' );

        if ( $post_ids ) {
            $post_ids = implode( ',', $post_ids );
            $total += $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_donation_amount' AND post_id IN({$post_ids})" );
        }

        $total;

    }

    public static function get_top_donors( $limit = 10 ){
        global $wpdb;

        return $wpdb->get_results("
        SELECT SUM(meta_value) as total, p2p_to as ID FROM $wpdb->postmeta AS m
        INNER JOIN $wpdb->posts AS p
           ON m.post_id = p.ID
        INNER JOIN {$wpdb->prefix}p2p AS r
           ON m.post_id = r.p2p_from
        WHERE m.meta_key = '_donation_amount'
        AND p.post_type = 'pr_donation'
        AND r.p2p_type = 'donation_to_donor'
        AND p.post_status = 'publish'
        GROUP BY r.p2p_to
        ORDER BY SUM(meta_value) DESC
        LIMIT $limit
        ");

    }

    public static function get_top_fundraisers( $limit = 10 ){
        global $wpdb;

        return $wpdb->get_results("
        SELECT SUM(meta_value) as total, p2p_to as ID FROM $wpdb->postmeta AS m
        INNER JOIN $wpdb->posts AS p
           ON m.post_id = p.ID
        INNER JOIN {$wpdb->prefix}p2p AS r
           ON m.post_id = r.p2p_from
        WHERE m.meta_key = '_donation_amount'
        AND p.post_type = 'pr_donation'
        AND r.p2p_type = 'donation_to_fundraiser'
        AND p.post_status = 'publish'
        GROUP BY r.p2p_to
        ORDER BY SUM(meta_value) DESC
        LIMIT $limit
        ");

    }
}