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
                    <p><?php //_e('The dashboard provides an overview of your peer-to-peer campaigns, fundraising tips, and the latest news about this plugin.', 'peerraiser') ?></p>

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
                    <p class="stat"><strong><?= $peerraiser['donations_total'] ?></strong></p>
                    <p class="title"><?php _e('Total Raised', 'peerraiser') ?></p>
                </div>
                <div class="stats campaigns">
                    <p class="stat"><strong><?= $peerraiser['campaigns_total'] ?></strong></p>
                    <p class="title"><?= _n( 'Campaign', 'Campaigns', $peerraiser['campaigns_total'], 'peerraiser' ) ?></p>
                </div>
                <div class="stats fundraisers">
                    <p class="stat"><strong><?= $peerraiser['fundraisers_total'] ?></strong></p>
                    <p class="title"><?= _n( 'Fundraiser', 'Fundraisers', $peerraiser['fundraisers_total'], 'peerraiser' ) ?></p>
                </div>
                <div class="stats last donors">
                    <p class="stat"><strong><?= $peerraiser['donors_total'] ?></strong></strong></p>
                    <p class="title"><?= _n( 'Donor', 'Donors', $peerraiser['donors_total'], 'peerraiser' ) ?></p>
                </div>
            </div>

            <div class="top-lists group">
                <div class="top-donors">
                    <h2><?php _e('Top Donors', 'peerraiser') ?></h2>

                    <ol>
                        <?php foreach ( $peerraiser['top_donors'] as $donor) : ?>
                            <li><a href="post.php?action=edit&post=<?= $donor->ID ?>"><?= get_post_meta( $donor->ID, '_donor_first_name', true ) ?> <?= get_post_meta( $donor->ID, '_donor_last_name', true ) ?></a> - <?= $peerraiser['currency_symbol'] . number_format_i18n($donor->total, 2) ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <div class="top-fundraisers">
                    <h2><?php _e('Top Fundraisers', 'peerraiser') ?></h2>

                    <ol>
                        <?php foreach ( $peerraiser['top_fundraisers'] as $fundraiser) : ?>
                            <li><a href="post.php?action=edit&post=<?= $fundraiser->ID ?>"><?= get_the_title( $fundraiser->ID ) ?></a> - <?= $peerraiser['currency_symbol'] . number_format_i18n($fundraiser->total, 2) ?></li>
                        <?php endforeach; ?>
                    </ol>

                </div>
            </div>

        </div>
        <div class="column column-right">
            <h2><?php _e('Activity Feed', 'peerraiser') ?></h2>
            <ul class="activity-feed">
                <?php foreach ($peerraiser['activity_feed'] as $activity) : ?>
                    <li class="<?= $activity['type']; ?>">
                        <?= $activity['message']; ?>
                        <span class="date">
                            <?= human_time_diff( $activity['time'], current_time( 'timestamp' ) ); ?> ago
                        </span>
                    </li>
                <?php endforeach; ?>
                <!--
                <li class="settings">
                    <a href="">Admin</a> updated the Email settings
                    <span class="date">3 Days ago</span>
                </li>
                </li> -->
            </ul>
        </div>

    </div>
</div>