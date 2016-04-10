<?php

namespace PeerRaiser\Helper;

class View {

    /**
     * @var string
     */
    public static $pluginPage = 'peerraiser-dashboard-tab';

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
     * Get links to be rendered in the plugin backend navigation.
     *
     * @return array
     */
    public static function get_admin_menu() {
        $event = new \PeerRaiser\Core\Event();
        $event->set_echo( false );
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( 'peerraiser_admin_menu_data', $event );
        $menu = (array) $event->get_result();
        return $menu;
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
     * Check, if plugin is fully functional.
     *
     * @return bool
     */
    public static function plugin_is_working() {
        return true;
    }

    /**
     * Get current plugin mode.
     *
     * @return string $mode
     */
    public static function get_plugin_mode() {
        $plugin_options = get_option( 'peerraiser_options', array() );

        return ( $plugin_options['plugin_is_in_live_mode'] ) ? 'live' : 'test';
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
    public static function format_number( $number, $is_monetary = true ) {
        if ( $is_monetary ) {
            // format monetary values 99.99
            if ( $number < 100 ) {
                // format values up to 200 with two digits
                // 200 is used to make sure the maximum Single Sale price of 149.99 is still formatted with two digits
                $formatted = number_format_i18n( $number, 2 );
            } elseif ( $number >= 100 && $number < 1000 ) {
                // format values between 100 and 1,000 without digits
                $formatted = number_format_i18n( $number, 0 );
            } else {
                // reduce values above 1,000 to thousands and format them with one digit
                $formatted = number_format_i18n( $number / 1000, 1 ) . __( 'k', 'peerraiser' ); // 1,100 -> 1.1k
            }
        } else {
            // format count values
            if ( $number < 1000 ) {
                $formatted = number_format( $number );
            } else {
                // reduce values above 10,000 to thousands and format them with one digit
                $formatted = number_format( $number / 1000, 1 ) . __( 'k', 'peerraiser' ); // 1,100 -> 1.1k
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
        $error_message  = '<div class="lp_shortcodeError">';
        $error_message .= __( 'Problem with inserted shortcode:', 'peerraiser' ) . '<br>';
        $error_message .= $error_reason;
        $error_message .= '</div>';

        return $error_message;
    }

}
