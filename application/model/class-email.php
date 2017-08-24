<?php

namespace PeerRaiser\Model;

/**
 * Email Model
 *
 * Class for creating and sending emails
 */
class Email {

    /**
     * Sender's email address
     *
     * @since 1.1.3
     */
    private $from_email = '';

    /**
     * Sender's name
     *
     * @since 1.1.3
     */
    private $from_name = '';

    /**
     * Email content type
     *
     * @since 1.1.3
     */
    private $content_type = 'text/html';

    /**
     * Email headers
     *
     * @since 1.1.3
     */
    private $headers = '';

    /**
     * Whether the email is in HTML format
     *
     * @since 1.1.3
     */
    private $html = true;

    /**
     * Email template
     *
     * @since 1.1.3
     */
    private $template;

    public function __construct() {
    }

    /**
     * Run when setting data to inaccessible properties.
     *
     * @since 1.1.3
     * @param $key
     * @param $value
     */
    public function __set( $key, $value ) {
        $this->$key = $value;
    }

    /**
     * Run when reading data from inaccessible properties.
     *
     * @since 1.1.3
     * @param $key
     *
     * @return mixed
     */
    public function __get( $key ) {
        if ( method_exists( $this, 'get_' . $key ) ) {
            $value = call_user_func( array( $this, 'get_' . $key ) );
        } else {
            $value = $this->$key;
        }

        return $value;
    }

    /**
     * Get the email from name
     *
     * @since 1.1.3
     */
    public function get_from_name() {
        if ( empty( $this->from_name ) ) {
            $plugin_options  = get_option( 'peerraiser_options', array() );
            $this->from_name = isset( $plugin_options['from_name'] ) ? $plugin_options['from_name'] : get_bloginfo( 'name' );
        }

        return apply_filters( 'peerraiser_from_name', wp_specialchars_decode( $this->from_name ), $this );
    }

    /**
     * Get the email from address
     *
     * @since 1.1.3
     */
    public function get_from_email() {
        if ( empty( $this->from_email ) ) {
            $plugin_options  = get_option( 'peerraiser_options', array() );
            $this->from_email = $plugin_options['from_email'];
        }

        if ( empty( $this->from_email ) || ! is_email( $this->from_email ) ) {
            $this->from_email = get_option( 'admin_email' );
        }

        return apply_filters( 'peerraiser_from_email', $this->from_email, $this );
    }

    /**
     * Get the email content type
     *
     * @since 1.1.3
     */
    public function get_content_type() {
        $this->content_type = $this->html ? 'text/html' : 'text/plain';

        return apply_filters( 'peerraiser_email_content_type', $this->content_type, $this );
    }

    /**
     * Get the email headers
     *
     * @since 1.1.3
     */
    public function get_headers() {
        if ( empty( $this->headers ) ) {
            $this->headers  = "From: {$this->get_from_name()} <{$this->get_from_email()}>\r\n";
            $this->headers .= "Reply-To: {$this->get_from_email()}\r\n";
            $this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
        }

        return apply_filters( 'peerraiser_email_headers', $this->headers, $this );
    }

    /**
     * Get the email templates
     *
     * @since 1.1.3
     */
    public function get_templates() {
        $templates = array(
            'default' => __( 'Default Template', 'peerraiser' ),
            'none'    => __( 'No template, plain text only', 'peerraiser' )
        );

        return apply_filters( 'peerraiser_email_templates', $templates );
    }

    /**
     * Get the enabled email template
     *
     * @since 2.1
     *
     * @return string|null
     */
    public function get_template() {
        if ( ! $this->template ) {
            $this->template = apply_filters( 'peerraiser_email_template', 'default', $this );
        }

        return $this->template;
    }

    /**
     * Build the email
     *
     * @since 2.1
     * @param string $message
     *
     * @return string
     */
    public function build_email( $message ) {

        if ( false === $this->html ) {
            return apply_filters( 'peerraiser_email_message', wp_strip_all_tags( $message ), $this );
        }

        $message = $this->text_to_html( $message );

        // @todo: Get the header and footer
    }

    /**
     * Send the email
     * 
     * @param  string  $to               The To address to send to.
     * @param  string  $subject          The subject line of the email to send.
     * @param  string  $message          The body of the email to send.
     * @param  string|array $attachments Attachments to the email in a format supported by wp_mail()
     * @since 2.1
     *
     * @return bool Whether the email contents were sent successfully.
     */
    public function send( $to, $subject, $message, $attachments = '' ) {

    }

    /**
     * Converts text to formatted HTML
     *
     * @param $message
     *
     * @return string
     */
    public function text_to_html( $message ) {

        if ( 'text/html' == $this->content_type || true === $this->html ) {
            $message = apply_filters( 'peerraiser_email_template_wpautop', true ) ? wpautop( $message ) : $message;
        }

        return $message;
    }

}