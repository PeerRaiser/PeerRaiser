<?php

namespace PeerRaiser\Core\Logger\Formatter;

/**
 * PeerRaiser logger HTML formatter.
 */
class Html extends Normalizer
{

    /**
     * Format a set of log records.
     *
     * @param    array    $records    A set of records to format
     *
     * @return    mixed               The formatted set of records
     */
    public function format_batch( array $records ) {
        $message = '';
        foreach ( $records as $record ) {
            $message .= $this->format( $record );
        }

        return $message;
    }

    /**
     * Format a log record.
     *
     * @param array $record A record to format
     *
     * @return mixed The formatted record
     */
    public function format( array $record ) {
        $output  = '<li class="pr_debugger-content-list__item">';
        $output .= '<table class="pr_js_debuggerContentTable pr_debugger-content__table pr_is-hidden">';

        // generate thead of log record
        $output .= $this->add_head_row( (string) $record['message'], $record['level'] );

        // generate tbody of log record with details
        $output .= '<tbody class="pr_js_logEntryDetails pr_debugger-content__table-body" style="display:none;">';
        $output .= '<tr><td class="pr_debugger-content__table-td" colspan="2"><table class="pr_debugger-content__table">';

        if ( $record['context'] ) {
            foreach ( $record['context'] as $key => $value ) {
                $output .= $this->add_row( $key, $this->convert_to_string( $value ) );
            }
        }

        if ( $record['extra'] ) {
            foreach ( $record['extra'] as $key => $value ) {
                $output .= $this->add_row( $key, $this->convert_to_string( $value ) );
            }
        }

        $output .= '</td></tr></table>';
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</li>';

        return $output;
    }

    /**
     * Create the header row for a log record.
     *
     * @param string   $message  log message
     * @param int      $level    log level
     *
     * @return string
     */
    private function add_head_row( $message = '', $level ) {
        $show_details_link = '<a href="#" class="pr_js_toggleLogDetails" data-icon="l">' . wp_kses( __( 'Details', 'peerraiser' ), 'post' ) . '</a>';

        $html = '<thead class="pr_js_debuggerContentTableTitle pr_debugger-content__table-title">
            <tr>
                <td class="pr_debugger-content__table-td"><span class="pr_debugger__log-level pr_debugger__log-level--' . esc_attr( $level ) . ' pr_vectorIcon"></span>' . wp_kses( $message, 'post' ) . '</td>
                <td class="pr_debugger-content__table-td">' . wp_kses( $show_details_link, 'post' ) . '</td>
            </tr>
        </thead>';

        return $html;
    }

    /**
     * Create an HTML table row.
     *
     * @param  string $th       Row header content
     * @param  string $td       Row standard cell content
     * @param  bool   $escapeTd false if td content must not be HTML escaped
     *
     * @return string
     */
    private function add_row( $th, $td = ' ', $escapeTd = true ) {
        $th = htmlspecialchars( $th, ENT_NOQUOTES, 'UTF-8' );

        if ( $escapeTd ) {
            $td = htmlspecialchars( $td, ENT_NOQUOTES, 'UTF-8' );
        }

        $html = '<tr>
                    <th class="pr_debugger-content__table-th" title="'. esc_attr( $th ) . '">' . $th . '</th>
                    <td class="pr_debugger-content__table-td">' . $td . '</td>
                </tr>';

        return $html;
    }

    /**
     * Convert data into string
     *
     * @param mixed $data
     *
     * @return string
     */
    protected function convert_to_string( $data ) {
        if ( null === $data || is_scalar( $data ) ) {
            return (string) $data;
        }

        $data = $this->normalize( $data );
        if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
            return json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        }

        return str_replace( '\\/', '/', json_encode( $data ) );
    }
}
