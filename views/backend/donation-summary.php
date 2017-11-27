<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>
<div class="wrap">
    <div id="peerraiser-js-message" class="pr_flash-message" style="display:none;">
        <p></p>
    </div>

    <h1 class="wp-heading-inline"><?php printf( esc_html__( 'Donation #%d', 'peerraiser' ), $peerraiser['donation']->ID ); ?></h1>
    <a href="<?php echo admin_url( 'admin.php?page=peerraiser-donations&view=add' ); ?>" class="page-title-action"><?php _e( 'Add New', 'peerraiser' ); ?></a>
    <hr class="wp-header-end">

    <form id="peerraiser-add-donation" class="peerraiser-form" action="" method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables peerraiser-metabox">

                        <?php do_action( 'peerraiser_before_donation_side_metaboxes' ); ?>

                        <div id="submitdiv" class="postbox">
                            <h2><span><?php _e( 'Donation Details', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="misc-publishing-actions">
                                        <div class="donation-info">
                                            <div class="donation-date">
                                                <span class="label"><?php _e( 'Date/Time', 'peerraiser' ); ?></span>
                                                <strong><?php echo mysql2date( get_option('date_format'), $peerraiser['donation']->date ); ?></strong>
                                            </div>
                                            <div class="donation-status <?php echo $peerraiser['donation']->status; ?>">
                                                <span class="label"><?php _e( 'Donation Status', 'peerraiser' ); ?></span>
                                                <strong><?php echo ucwords( $peerraiser['donation']->status ) ?></strong>
                                                <a href="#donation_status" class="edit-donation-status hide-if-no-js" role="button"><span aria-hidden="true">Edit</span> <span class="screen-reader-text"><?php _e( 'Edit status', 'peerraiser' ); ?></span></a>
                                                <div id="donation-status-select" class="hide-if-js">
                                                    <input type="hidden" name="donation_status_hidden" value="<?php echo $peerraiser['donation']->status; ?>">
                                                    <select name="donation_status" id="donation-status">
                                                        <option value="completed" <?php selected( $peerraiser['donation']->status, 'completed' ); ?>><?php _e( 'Completed', 'peerraiser' ); ?></option>
                                                        <option value="pending" <?php selected( $peerraiser['donation']->status, 'pending' ); ?>><?php _e( 'Pending', 'peerraiser' ); ?></option>
                                                        <option value="failed" <?php selected( $peerraiser['donation']->status, 'failed' ); ?>><?php _e( 'Failed', 'peerraiser' ); ?></option>
                                                    </select>
                                                    <a href="#donation_status" class="save hide-if-no-js button"><?php _e( 'OK', 'peerraiser' ); ?></a>
                                                    <a href="#donation_status" class="cancel hide-if-no-js button-cancel"><?php _e( 'Cancel', 'peerraiser' ); ?></a>
                                                </div>
                                            </div>
                                            <div class="donation-method">
                                                <span class="label"><?php _e( 'Payment Method', 'peerraiser' ); ?></span>
                                                <strong><?php echo ucwords( $peerraiser['donation']->donation_type ); ?></strong>
                                            </div>
                                            <?php if ( ! empty( $peerraiser['donation']->gateway ) ) : ?>
                                                <div class="donation-gateway">
                                                    <span class="label"><?php _e( 'Gateway', 'peerraiser' ); ?></span>
                                                    <strong><?php echo ucwords( $peerraiser['donation']->gateway ); ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <div class="donation-key">
                                                <span class="label"><?php _e( 'Transaction Key', 'peerraiser' ); ?></span>
                                                <strong><?php echo $peerraiser['donation']->transaction_id; ?></strong>
                                            </div>
                                            <div class="donor-ip">
                                                <span class="label"><?php _e( 'IP Address', 'peerraiser' ); ?></span>
                                                <strong><?php echo $peerraiser['donation']->ip; ?></strong>
                                            </div>
                                            <div class="is-test-mode">
                                                <span class="label"><?php _e( 'Test mode?', 'peerraiser' ); ?></span>
                                                <strong><?php echo ( $peerraiser['donation']->is_test ) ? 'Yes' : 'No'; ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <a class="submitdelete deletion" href="<?php echo add_query_arg( array( 'peerraiser_action' => 'delete_donation', 'donation_id' => $peerraiser['donation']->ID, '_wpnonce' => wp_create_nonce( 'peerraiser_delete_donation_' . $peerraiser['donation']->ID ) ), admin_url( sprintf( 'admin.php?page=peerraiser-donations' ) ) ) ?>"><?php _e( 'Delete', 'peerraiser' ); ?></a>
                                        </div>
                                        <div id="publishing-action">
                                            <span class="spinner"></span>
                                            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e( 'Save', 'peerraiser' ); ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php do_action( 'peerraiser_after_donation_side_metaboxes' ); ?>
                    </div> <!-- / #side-sortables -->
                </div>
                <div id="postbox-container-2" class="postbox-container peerraiser-metabox">
                    <div id="normal-sortables">
                        <?php do_action( 'peerraiser_before_donation_metaboxes' ); ?>

                        <div id="donation-summary" class="postbox">
                            <h2><span><?php _e( 'Donation Summary', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <?php $donor_link = "<a href='admin.php?page=peerraiser-donors&view=summary&donor={$peerraiser['donor']->ID}'>{$peerraiser['donor']->full_name}</a>"; ?>
                                <p class="summary"><?php printf( '%1$s made a donation of <strong>%2$s</strong> on <strong>%3$s</strong>', $donor_link, peerraiser_money_format( $peerraiser['donation']->total ), mysql2date( get_option('date_format'), $peerraiser['donation']->date ) ); ?></p>
                                <table class="transaction-info table table-striped">
                                    <thead>
                                        <tr>
                                            <th colspan="2"><?php _e( 'Allocation', 'peerraiser') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong><?php _e( 'Campaign', 'peerraiser' ); ?>:</strong></td>
                                            <?php if ( $campaign_id = $peerraiser['donation']->campaign_id ) : ?>
                                                <?php $campaign = peerraiser_get_campaign( $campaign_id ) ?>
                                                <?php if ( empty( $campaign->campaign_name ) ) : ?>
                                                    <td><em><?php _e( 'Deleted', 'peerraiser' ); ?></em></td>
                                                <?php else : ?>
                                                    <td><a href="admin.php?page=peerraiser-campaigns&view=summary&campaign=<?php echo $campaign->ID; ?>"><?php echo $campaign->campaign_name; ?></a></td>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <td><?php _e( 'N/A', 'peerraiser' ); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                        <tr>
                                            <td><strong><?php _e( 'Fundraiser', 'peerraiser' ); ?>:</strong></td>
                                            <?php if ( $fundraiser_id = $peerraiser['donation']->fundraiser_id ) : ?>
                                                <?php $fundraiser = peerraiser_get_fundraiser( $fundraiser_id ); ?>
                                                <td><a href="post.php?action=edit&post=<?php echo $fundraiser->ID; ?>"><?php echo $fundraiser->fundraiser_name; ?></a></td>
                                            <?php else : ?>
                                                <td><?php _e( 'N/A', 'peerraiser' ); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                        <tr>
                                            <td><strong><?php _e( 'Team', 'peerraiser' ); ?>:</strong></td>
                                            <?php if ( $team_id = $peerraiser['donation']->team_id ) : ?>
                                                <?php $team = peerraiser_get_team( $team_id ); ?>
                                                <td><a href="admin.php?page=peerraiser-teams&view=summary&team=<?php echo $team->ID; ?>"><?php echo $team->team_name; ?></a></td>
                                            <?php else : ?>
                                                <td><?php _e( 'N/A', 'peerraiser' ); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                        <tr>
                                            <td><strong><?php _e( 'Subtotal', 'peerraiser' ); ?>:</strong></td>
                                            <td><strong><?php echo peerraiser_money_format( $peerraiser['donation']->subtotal ); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php _e( 'Total Donation', 'peerraiser' ); ?>:</strong></td>
                                            <td><strong><?php echo peerraiser_money_format( $peerraiser['donation']->total ); ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php do_action( 'peerraiser_after_donation_metaboxes', $peerraiser ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'peerraiser_update_donation_' . $peerraiser['donation']->ID ); ?>
        <input type="hidden" name="donation_id" value="<?php echo $peerraiser['donation']->ID; ?>">
        <input type="hidden" name="peerraiser_action" value="update_donation">
    </form>
</div>
