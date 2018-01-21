<?php

namespace PeerRaiser\Helper;

class View {

    /**
     * Helper function to render a plugin backend navigation tab link.
     *
     * @param array $page array(
     *                      'url'   => String
     *                      'title' => String
     *                      'cap'   => String
     *                      'data'  => Array|String     // optional
     *                    )
     *
     * @return string $link
     */
    public static function get_admin_menu_link( $page ) {
        $query_args = array(
            'page' => $page['url'],
        );
        $href = admin_url( 'admin.php' );
        $href = add_query_arg( $query_args, $href );

        $data = '';
        if ( isset( $page['data'] ) ) {
            $data = json_encode( $page['data'] );
            $data = 'data="' . esc_attr( $data ) . '"';
        }

        return '<a href="' . $href . '" ' . $data . ' class="pr_navigation-tabs__link">' . $page['title'] . '</a>';
    }


    /**
     * Get date of next day.
     *
     * @param string $date
     *
     * @return string $nextDay
     */
    protected static function get_next_day( $date ) {
        $next_day = date( 'Y-m-d', mktime(
            date( 'H', strtotime( $date ) ),
            date( 'i', strtotime( $date ) ),
            date( 's', strtotime( $date ) ),
            date( 'm', strtotime( $date ) ),
            date( 'd', strtotime( $date ) ) + 1,
            date( 'Y', strtotime( $date ) )
        ) );

        return $next_day;
    }


    /**
     * Get date a given number of days prior to a given date.
     *
     * @param string $date
     * @param int    $ago number of days ago
     *
     * @return string $prior_date
     */
    protected static function get_date_days_ago( $date, $ago = 30 ) {
        $ago = absint( $ago );
        $prior_date = date( 'Y-m-d', mktime(
            date( 'H', strtotime( $date ) ),
            date( 'i', strtotime( $date ) ),
            date( 's', strtotime( $date ) ),
            date( 'm', strtotime( $date ) ),
            date( 'd', strtotime( $date ) ) - $ago,
            date( 'Y', strtotime( $date ) )
        ) );

        return $prior_date;
    }


    /**
     * Get the statistics data for the last 30 days as string, joined by a given delimiter.
     *
     * @param array  $statistic
     * @param string $type
     * @param string $delimiter
     *
     * @return string
     */
    public static function get_days_statistics_as_string( $statistic, $type = 'quantity', $delimiter = ',' ) {
        $today  = date( 'Y-m-d' );
        $date   = self::get_date_days_ago( date( $today ), 30 );

        $result = '';
        while ( $date <= $today ) {
            if ( $result !== '' ) {
                $result .= $delimiter;
            }
            if ( isset( $statistic[ $date ] ) ) {
                $result .= $statistic[ $date ][ $type ];
            } else {
                $result .= '0';
            }
            $date = self::get_next_day( $date );
        }

        return $result;
    }

    /**
     * Get current plugin mode.
     *
     * @return string $mode The plugin mode, 'test' or 'live'
     */
    public static function get_plugin_mode() {
        $plugin_options = get_option( 'peerraiser_options', array() );

        return ( wp_validate_boolean( $plugin_options['test_mode'] ) ) ? 'test' : 'live';
    }

    /**
     * Remove extra spaces from string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function remove_extra_spaces( $string ) {
        $string = trim( preg_replace( '/>\s+</', '><', $string ) );
        $string = preg_replace( '/\n\s*\n/', '', $string );

        return $string;
    }

    /**
     * Format number based on its type.
     *
     * @param float   $number
     * @param bool    $is_monetary
     *
     * @return string $formatted
     */
    public static function format_number( $number, $is_monetary = true, $with_html = false ) {
        if ( $is_monetary ) {
            $plugin_options = get_option( 'peerraiser_options', array() );
            $currency = $plugin_options['currency'];
            $currency_model = new \PeerRaiser\Model\Currency();
            $currency_symbol = $currency_model->get_currency_symbol_by_iso4217_code( $currency );
            // format monetary values 99.99
            $formatted = ( $with_html ) ? '<span class="currency-symbol">' . $currency_symbol . '</span>' : $currency_symbol;
            if ( $number < 100 ) {
                // format values up to 100 with two digits
                $formatted .= number_format_i18n( $number, 2 );
            } elseif ( $number >= 100 && $number < 1000 ) {
                // format values between 100 and 1,000 without digits
                $formatted .= number_format_i18n( $number, 0 );
            } else {
                // reduce values above 1,000 to thousands and format them with one digit
                $formatted .= number_format_i18n( $number / 1000, 1 ); // 1,100 -> 1.1k
                $formatted .= ( $with_html ) ? '<span class="thousand-symbol">' . __( 'k', 'peerraiser' ) . '</span>' : __( 'k', 'peerraiser' );
            }
        } else {
            // format count values
            if ( $number < 1000 ) {
                $formatted = number_format( $number );
            } else {
                // reduce values above 10,000 to thousands and format them with one digit
                $formatted = number_format( $number / 1000, 1 );
                $formatted .= ( $with_html ) ? '<span class="thousand-symbol">' . __( 'k', 'peerraiser' ) . '</span>' : __( 'k', 'peerraiser' );
            }
        }

        return $formatted;
    }

    /**
     * Number normalization
     *
     * @param $number
     *
     * @return float
     */
    public static function normalize( $number ) {
        global $wp_locale;

        $number = str_replace( $wp_locale->number_format['thousands_sep'], '', (string) $number );
        $number = str_replace( $wp_locale->number_format['decimal_point'], '.', $number );

        return (float) $number;
    }


    /**
     * Get error message for shortcode.
     *
     * @param string  $error_reason
     * @param array   $atts         shortcode attributes
     *
     * @return string $error_message
     */
    public static function get_error_message( $error_reason, $atts ) {
        $error_message  = '<div class="peerraiser-shortcode-error">';
        $error_message .= __( 'Problem with inserted shortcode:', 'peerraiser' ) . '<br>';
        $error_message .= $error_reason;
        $error_message .= '</div>';

        return $error_message;
    }

    public static function get_admin_pagination( $args ){
        $defaults = array(
            'range'           => 4,
            'custom_query'    => FALSE,
            'before_output'   => '<div class="admin-nav"><ul class="pager">',
            'after_output'    => '</ul></div>',
            'paged'           => 1,
            'paged_name'      => 'paged',
        );

        $args = wp_parse_args( $args, $defaults );

        $args['range'] = (int) $args['range'] - 1;
        if ( !$args['custom_query'] )
            $args['custom_query'] = @$GLOBALS['wp_query'];
        $count = (int) $args['custom_query']->max_num_pages;
        $page  = intval( $args['paged'] );
        $ceil  = ceil( $args['range'] / 2 );

        if ( $count <= 1 )
            return FALSE;

        if ( !$page )
            $page = 1;

        if ( $count > $args['range'] ) {
            if ( $page <= $args['range'] ) {
                $min = 1;
                $max = $args['range'] + 1;
            } elseif ( $page >= ($count - $ceil) ) {
                $min = $count - $args['range'];
                $max = $count;
            } elseif ( $page >= $args['range'] && $page < ($count - $ceil) ) {
                $min = $page - $ceil;
                $max = $page + $ceil;
            }
        } else {
            $min = 1;
            $max = $count;
        }

        $html = '';
        $previous = intval($page) - 1;
        $previous = esc_attr( add_query_arg( $args['paged_name'], $previous, $_SERVER['REQUEST_URI'] ) );

        if ( $previous && (1 != $page) ) {
	        $html .= '<li><a href="' . $previous . '">&laquo;</a></li>';
        }

        if ( !empty($min) && !empty($max) ) {
            for( $i = $min; $i <= $max; $i++ ) {
                if ($page == $i) {
                    $html .= '<li class="active"><span class="active">' . str_pad( (int)$i, 2, '0', STR_PAD_LEFT ) . '</span></li>';
                } else {
                    $pagenum_link = add_query_arg( $args['paged_name'], $i, $_SERVER['REQUEST_URI'] );
                    $html .= sprintf( '<li><a href="%s">%002d</a></li>', esc_attr( $pagenum_link ), $i );
                }
            }
        }

        $next = intval($page) + 1;
        $next = esc_attr( add_query_arg( $args['paged_name'], $next, $_SERVER['REQUEST_URI'] ) );
        if ($next && ($count != $page) ) {
	        $html .= '<li><a href="' . $next . '">&raquo;</a></li>';
        }

        if ( isset($html) ) {
	        return $args['before_output'] . $html . $args['after_output'];
        }
    }

    public static function add_file_to_media_library( $filename ) {
        // Locate the file /assets/images plugin folder
        $file = plugin_dir_path( PEERRAISER_FILE ) . 'assets/images/' . $filename;

        // If the file doesn't exist, then write to the error log and return false
        if ( ! file_exists( $file ) || 0 === strlen( trim( $filename ) ) ) {
            error_log( 'PeerRaiser: The file you are attempting to upload, ' . $file . ', does not exist.' );
            return false;
        }

        // Upload directory info
        $uploads     = wp_upload_dir();
        $uploads_dir = $uploads['path'];
        $uploads_url = $uploads['url'];

        // Copy the file from the /assets/images directory to the uploads directory
        copy( $file, trailingslashit( $uploads_dir ) . $filename );

        /* Get the URL to the file and grab the file and load
         * it into WordPress (and the Media Library)
         */
        $url = trailingslashit( $uploads_url ) . $filename;
        $result = media_sideload_image( $url, 0, $filename, 'src' );

        // If there's an error, then we'll write it to the error log.
        if ( is_wp_error( $result ) ) {
            error_log( print_r( $result, true ) );
            return false;
        }

        return $result;
    }

    /**
     * Returns avatar URL
     *
     * @since     1.0.0
     * @return    string    Custom avatar, Gravatar, or default avatar image URL.
     */
    public static function get_avatar() {
        // Get the current user's email address
        $current_user  = wp_get_current_user();
        $email_address = $current_user->user_email;

        // User's Custom Avatar ID
        $avatar_id = get_user_meta( $current_user->ID, '_peerraiser_custom_avatar', true );

        if ( $avatar_id == ! "" ) {
            return wp_get_attachment_image_url( $avatar_id, 'peerraiser_campaign_thumbnail' );
        }

        // User doesn't have a custom avatar, return gravatar or default avatar
        $plugin_options = get_option( 'peerraiser_options', array() );
        $default_avatar = $plugin_options['user_thumbnail_image'];

        $args = array(
            'size'    => 125,
            'default' => $default_avatar,
        );

        return get_avatar_url( $email_address, $args );
    }

	public static function get_time_fields( $edit = 1, $date = '' ) {
		global $wp_locale;

		$time_adj = current_time( 'timestamp' );
		$date     = empty( $date ) ? current_time( 'timestamp' ) : $date;

		$jj = ( $edit ) ? mysql2date( 'd', $date, false ) : gmdate( 'd', $time_adj );
		$mm = ( $edit ) ? mysql2date( 'm', $date, false ) : gmdate( 'm', $time_adj );
		$aa = ( $edit ) ? mysql2date( 'Y', $date, false ) : gmdate( 'Y', $time_adj );

		$cur_jj = gmdate( 'd', $time_adj );
		$cur_mm = gmdate( 'm', $time_adj );
		$cur_aa = gmdate( 'Y', $time_adj );

		$month = '<label><span class="screen-reader-text">' . __( 'Month' ) . '</span><select id="mm" name="mm">';
		for ( $i = 1; $i < 13; $i = $i + 1 ) {
			$monthnum  = zeroise( $i, 2 );
			$monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
			$month     .= "\t\t\t" . '<option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected( $monthnum, $mm, false ) . '>';
			/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
			$month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $monthtext ) . "</option>\n";
		}
		$month .= '</select></label>';

		$day    = '<label><span class="screen-reader-text">' . __( 'Day' ) . '</span><input type="text" id="jj" name="jj" value="' . $jj . '" size="2" maxlength="2" utocomplete="off" /></label>';
		$year   = '<label><span class="screen-reader-text">' . __( 'Year' ) . '</span><input type="text" id="aa" name="aa" value="' . $aa . '" size="4" maxlength="4" utocomplete="off" /></label>';

		echo '<div class="timestamp-wrap">';
		/* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
		printf( __( '%1$s %2$s, %3$s' ), $month, $day, $year );

		echo "\n\n";
		$map = array(
			'mm' => array( $mm, $cur_mm ),
			'jj' => array( $jj, $cur_jj ),
			'aa' => array( $aa, $cur_aa ),
		);
		foreach ( $map as $timeunit => $value ) {
			list( $unit, $curr ) = $value;

			echo '<input type="hidden" id="hidden_' . $timeunit . '" name="hidden_' . $timeunit . '" value="' . $unit . '" />' . "\n";
			$cur_timeunit = 'cur_' . $timeunit;
			echo '<input type="hidden" id="' . $cur_timeunit . '" name="' . $cur_timeunit . '" value="' . $curr . '" />' . "\n";
		}
		?>

		<p>
			<a href="#edit_timestamp" class="save-timestamp hide-if-no-js button"><?php _e('OK'); ?></a>
			<a href="#edit_timestamp" class="cancel-timestamp hide-if-no-js button-cancel"><?php _e('Cancel'); ?></a>
		</p>
		<?php
	}

}
