<?php
namespace PeerRaiser\Controller\Admin;

class Admin_Notices extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'admin_notices', array( $this, 'display_notices' ) );
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
}