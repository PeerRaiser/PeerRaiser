<?php
namespace PeerRaiser\Controller\Admin;

class Admin_Notices extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'admin_notices',  array( $this, 'add_peerraiser_notices' ) );
        add_action( 'admin_notices',  array( $this, 'display_notices' ) );
        add_action( 'admin_bar_menu', array( $this, 'maybe_display_test_mode_notice' ), 1000, 1 );
    }

    public function add_peerraiser_notices() {
        if ( ! isset( $_GET['peerraiser_notice'] ) ) {
            return;
        }

        $admin_notice_model = new \PeerRaiser\Model\Admin\Admin_Notices();
        $notice = $admin_notice_model->get_notice_message( $_GET['peerraiser_notice'] );

        \PeerRaiser\Model\Admin\Admin_Notices::add_notice( $notice['message'], $notice['class'], $notice['dismissible'] );
    }

    public function display_notices() {
        $notices = \PeerRaiser\Model\Admin\Admin_Notices::get_notices();
        foreach ( $notices as $notice ) {
            $class = ( $notice['is-dismissible'] ) ? $notice['class'] . ' is-dismissible' : $notice['class'];
            ?>
                <div class="notice <?= $class ?>">
                    <p><?php echo $notice['message'] ?></p>
                </div>
            <?php
        }
    }

    /**
     * Display admin bar when active.
     *
     * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
     *
     * @return bool
     */
    public function maybe_display_test_mode_notice( $wp_admin_bar ) {
        if ( peerraiser_get_option( 'test_mode' ) !== 'true' ) {
            return false;
        }

        // Add the main siteadmin menu item.
        $wp_admin_bar->add_menu( array(
            'id'     => 'peerraiser-test-notice',
            'href'   => admin_url( 'admin.php?page=peerraiser-settings' ),
            'parent' => 'top-secondary',
            'title'  => esc_html__( 'PeerRaiser Test Mode Active', 'peerraiser' ),
            'meta'   => array( 'class' => 'peerraiser-test-mode-active' ),
        ) );

    }
}