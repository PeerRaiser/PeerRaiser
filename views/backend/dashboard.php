<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="pr_page wp-core-ui">

    <div id="peerraiser-js-message" class="pr_flash-message" style="display:none;">
        <p></p>
    </div>

    <div class="wrap peerraiser-wrap dashboard-wrap">

        <h1><strong><?php _e('PeerRaiser', 'peerraiser') ?></strong><sup class="version"><?= $peerraiser['plugin_version'] ?></sup></h1>

        <div class="column column-left">

            <?php if ( $peerraiser['show_welcome_message'] ) : ?>
                <div class="welcome-message">
                    <h2><?php printf( esc_html__( 'Welcome to your dashboard, %s', 'peerraiser' ), $peerraiser['display_name'] ); ?></h2>
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

            <div class="stats-wrapper group">
                <div class="stats first total-raised">
                    <p class="stat"><strong>$388,198</strong></p>
                    <p class="title">Total Raised</p>
                </div>
                <div class="stats campaigns">
                    <p class="stat"><strong><?= $peerraiser['campaigns_total'] ?></strong></p>
                    <p class="title"><?php _e('Campaigns', 'peerraiser') ?></p>
                </div>
                <div class="stats fundraisers">
                    <p class="stat"><strong><?= $peerraiser['fundraisers_total'] ?></strong></p>
                    <p class="title"><?php _e('Fundraisers', 'peerraiser') ?></p>
                </div>
                <div class="stats last donors">
                    <p class="stat"><strong>4,853</strong></p>
                    <p class="title">Donors</p>
                </div>
            </div>

            <div class="top-lists group">
                <div class="top-donors">
                    <h2><?php _e('Top Donors', 'peerraiser') ?></h2>

                    <ol>
                        <li><a href="#">Stephanie Espinoza</a></li>
                        <li><a href="#">Stephanie Elliott</a></li>
                        <li><a href="#">Aleida Escoto</a></li>
                        <li><a href="#">Gary Finlayson</a></li>
                        <li><a href="#">Michael Miller</a></li>
                        <li><a href="#">Mary Jarnigan</a></li>
                        <li><a href="#">John Alicea</a></li>
                        <li><a href="#">Stephen Johnson</a></li>
                        <li><a href="#">Thomas Healey</a></li>
                        <li><a href="#">Carleen Benavidez</a></li>
                    </ol>
                </div>
                <div class="top-fundraisers">
                    <h2><?php _e('Top Fundraisers', 'peerraiser') ?></h2>

                    <ol>
                        <li><a href="#">My cool fundraiser</a></li>
                        <li><a href="#">Help me raise money</a></li>
                    </ol>

                </div>
            </div>

        </div>
        <div class="column column-right">
            <h2><?php _e('Activity Feed', 'peerraiser') ?></h2>
            <ul class="activity-feed">
                <li class="donation">
                    <a href="">John Smith</a> donated <a href="#">$50.00</a> to "<a href="#">My cool fundraiser</a>"
                    <span class="date">1 Day ago</span>
                </li>
                <li class="fundraiser">
                    <a href="">Jane Adams</a> created fundraiser "<a href="#">My cool fundraiser!</a>" for the "<a href="#">10k Fun Run!</a>" campaign
                    <span class="date">2 Days ago</span>
                </li>
                <li class="campaign">
                    <a href="">Admin</a> created campaign "<a href="#">10k Fun Run!</a>"
                    <span class="date">2 Days ago</span>
                </li>
                <li class="settings">
                    <a href="">Admin</a> updated the Email settings
                    <span class="date">3 Days ago</span>
                </li>
                <li class="install">
                    PeerRaiser was installed
                    <span class="date">3 Days ago</span>
                </li>
            </ul>
        </div>

    </div>
</div>