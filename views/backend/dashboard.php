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

        <h1><strong><?php _e('PeerRaiser', 'peerraiser') ?></strong><sup class="version"><?php echo $peerraiser['plugin_version'] ?></sup></h1>

        <div class="column column-left">

            <?php if ( $peerraiser['show_welcome_message'] ) : ?>
                <div class="welcome-message">
                    <h2><?php printf( esc_html__( 'Hi %s, welcome to your dashboard!', 'peerraiser' ), $peerraiser['display_name'] ); ?></h2>
                    <p><?php //_e('The dashboard provides an overview of your peer-to-peer campaigns, fundraising tips, and the latest news about this plugin.', 'peerraiser') ?></p>

                    <h3><?php _e("Get started by following these steps:", 'peerraiser') ?></h3>

                    <ul>
                        <li><i class="fa fa-fw <?php echo $peerraiser['font_awesome_class']['step_1'] ?>" aria-hidden="true"></i><a href="<?php echo $peerraiser['admin_url'] ?>admin.php?page=peerraiser-settings&tab=account"><?php _e('Connect your PeerRaiser account', 'peerraiser') ?></a></li>
                        <li><i class="fa fa-fw <?php echo $peerraiser['font_awesome_class']['step_2'] ?>" aria-hidden="true"></i><a href="<?php echo $peerraiser['admin_url'] ?>admin.php?page=peerraiser-campaigns"><?php _e('Create your first campaign', 'peerraiser') ?></a></li>
                        <li><i class="fa fa-fw <?php echo $peerraiser['font_awesome_class']['step_3'] ?>" aria-hidden="true"></i><a href="<?php echo $peerraiser['donate_url'] ?>"><?php _e('Make a test donation', 'peerraiser') ?></a></li>
                        <li><i class="fa fa-fw <?php echo $peerraiser['font_awesome_class']['step_4'] ?>" aria-hidden="true"></i><a href="<?php echo $peerraiser['admin_url'] ?>admin.php?page=peerraiser-settings"><?php _e('Disable test mode', 'peerraiser') ?></a></li>
                    </ul>

                    <a href="#" class="close" data-message-type="welcome_message" data-nonce="<?php echo wp_create_nonce("dismiss_welcome_message") ?>"><i class="fa fa-times-circle" aria-hidden="true"></i></a>

                </div>
            <?php endif; ?>

            <div class="stats-wrapper group">
                <div class="stats-container group first">
                    <div class="stats total-raised">
                        <?php if ( peerraiser_is_test_mode() ) : ?>
                            <p class="title"><?php _e('Test Donations', 'peerraiser') ?></p>
                        <?php else : ?>
                            <p class="title"><?php _e('Donations', 'peerraiser') ?></p>
                        <?php endif; ?>
                        <p class="stat"><strong><?php echo $peerraiser['donations_total'] ?></strong></p>
                    </div>
                    <div class="stats-bottom view">
                        <a href="admin.php?page=peerraiser-donations" class="view-all"><?php _e( 'View All', 'peerraiser'); ?></a>
                    </div>
                    <div class="stats-bottom add">
                        <a href="admin.php?page=peerraiser-donations&view=add" class="add-new"><i class="fa fa-plus" aria-hidden="true"></i></a>
                    </div>
                </div>
                <div class="stats-container group">
                    <div class="stats campaigns">
                        <p class="title"><?php echo _n( 'Campaign', 'Campaigns', $peerraiser['campaigns_total'], 'peerraiser' ) ?></p>
                        <p class="stat"><strong><?php echo $peerraiser['campaigns_total'] ?></strong></p>
                    </div>
                    <div class="stats-bottom view">
                        <a href="admin.php?page=peerraiser-campaigns" class="view-all"><?php _e( 'View All', 'peerraiser'); ?></a>
                    </div>
                    <div class="stats-bottom add">
                        <a href="admin.php?page=peerraiser-campaigns&view=add" class="add-new"><i class="fa fa-plus" aria-hidden="true"></i></a>
                    </div>
                </div>
                <div class="stats-container group">
                    <div class="stats fundraisers">
                        <p class="title"><?php echo _n( 'Fundraiser', 'Fundraisers', $peerraiser['fundraisers_total'], 'peerraiser' ) ?></p>
                        <p class="stat"><strong><?php echo $peerraiser['fundraisers_total'] ?></strong></p>
                    </div>
                    <div class="stats-bottom view">
                        <a href="edit.php?post_type=fundraiser" class="view-all"><?php _e( 'View All', 'peerraiser'); ?></a>
                    </div>
                    <div class="stats-bottom add">
                        <a href="post-new.php?post_type=fundraiser" class="add-new"><i class="fa fa-plus" aria-hidden="true"></i></a>
                    </div>
                </div>
                <div class="stats-container last">
                    <div class="stats donors">
                        <p class="title"><?php echo _n( 'Donor', 'Donors', $peerraiser['donors_total'], 'peerraiser' ) ?></p>
                        <p class="stat"><strong><?php echo $peerraiser['donors_total'] ?></strong></strong></p>
                    </div>
                    <div class="stats-bottom view">
                        <a href="admin.php?page=peerraiser-donors" class="view-all"><?php _e( 'View All', 'peerraiser'); ?></a>
                    </div>
                    <div class="stats-bottom add">
                        <a href="admin.php?page=peerraiser-donors&view=add" class="add-new"><i class="fa fa-plus" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>

            <div class="top-lists group">
                <div class="top-donors">
                    <p class="title"><?php _e('Top Donors', 'peerraiser') ?></p>

	                <?php if ( empty( $peerraiser['top_donors'] ) ) : ?>
                        <p><?php _e( 'No donors have made donations yet.', 'peerraiser' ); ?></p>
	                <?php else : ?>
                        <ol>
                            <?php foreach ( $peerraiser['top_donors'] as $donor) : ?>
                                <li><a href="admin.php?page=peerraiser-donors&view=summary&donor=<?php echo $donor->donor_id; ?>"><?php echo $donor->full_name; ?></a><span class="amount"><?php echo peerraiser_money_format( $donor->total ); ?></li></span>
                            <?php endforeach; ?>
                        </ol>
	                <?php endif; ?>
                </div>
                <div class="top-fundraisers">
                    <p class="title"><?php _e('Top Fundraisers', 'peerraiser') ?></p>

                    <?php if ( empty( $peerraiser['top_fundraisers'] ) ) : ?>
                        <p><?php _e( 'No fundraisers have received donations yet.', 'peerraiser' ); ?></p>
                    <?php else : ?>
                        <ol>
                            <?php foreach ( $peerraiser['top_fundraisers'] as $fundraiser) : ?>
                                <?php if ( ! peerraiser_is_test_mode() ) : ?>
                                    <li><a href="post.php?action=edit&post=<?php echo $fundraiser->ID ?>"><?php echo get_the_title( $fundraiser->ID ) ?></a><span class="amount"><?php echo peerraiser_money_format( $fundraiser->donation_value ); ?></li></span>
                                <?php else : ?>
                                    <li><a href="post.php?action=edit&post=<?php echo $fundraiser->ID ?>"><?php echo get_the_title( $fundraiser->ID ) ?></a><span class="amount"><?php echo peerraiser_money_format( $fundraiser->test_donation_value ); ?></li></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                </div>
            </div>

        </div>
        <div class="column column-right">
            <h2><?php _e('Activity Feed', 'peerraiser') ?></h2>
            <ul class="activity-feed">
                <?php foreach ($peerraiser['activity_feed'] as $activity) : ?>
                    <li class="<?php echo $activity['type']; ?>">
                        <?php echo $activity['message']; ?>
                        <span class="date">
                            <?php echo human_time_diff( $activity['time'], current_time( 'timestamp' ) ); ?> ago
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</div>
