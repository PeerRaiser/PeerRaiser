<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="pr_page wp-core-ui">

    <div id="pr_js_flashMessage" class="pr_flash-message" style="display:none;">
        <p></p>
    </div>

    <div class="wrap peerraiser-wrap dashboard-wrap">

        <h1><strong><?php _e('PeerRaiser', 'peerraiser') ?></strong><sup class="version"><?= $peerraiser['plugin_version'] ?></sup></h1>

        <div class="column column-left">

            <?php if ( $peerraiser['show_welcome_message'] ) : ?>
                <div class="welcome-message">
                    <h2><?php printf( esc_html__( 'Welcome to your dashboard, $s', 'peerraiser' ), $peerraiser['display_name'] ); ?></h2>
                    <p><?php _e('The dashboard provides an overview of your peer-to-peer campaigns, fundraising tips, and the latest news about this plugin.', 'peerraiser') ?></p>

                    <h3><?php _e("Let's get you started...", 'peerraiser') ?></h3>

                    <ul>
                        <li class="status-complete"><i class="fa fa-fw <?= $peerraiser['font_awesome_class']['step_1'] ?>" aria-hidden="true"></i><a href="https://peerraiser.com/join"><?php _e('Create a free PeerRaiser account', 'peerraiser') ?></a></li>
                        <li class="status-incomplete"><i class="fa fa-fw <?= $peerraiser['font_awesome_class']['step_2'] ?>" aria-hidden="true"></i><a href="<?= $peerraiser['admin_url'] ?>admin.php?page=peerraiser-settings"><?php _e('Configure your settings', 'peerraiser') ?></a></li>
                        <li class="status-incomplete"><i class="fa fa-fw <?= $peerraiser['font_awesome_class']['step_3'] ?>" aria-hidden="true"></i><a href="<?= $peerraiser['admin_url'] ?>post-new.php?post_type=pr_campaign"><?php _e('Create your first campaign', 'peerraiser') ?></a></li>
                    </ul>

                    <a href="#" class="close" data-message-type="welcome_message" data-nonce="<?= wp_create_nonce("dismiss_welcome_message") ?>"><i class="fa fa-times-circle" aria-hidden="true"></i></a>
                </div>
            <?php endif; ?>

        </div>
        <div class="column column-right">
            <h2><?php _e('Activity Feed', 'peerraiser') ?></h2>
            <p>Something cool happened today</p>
        </div>

    </div>
</div>