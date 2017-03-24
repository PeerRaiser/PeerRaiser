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

    <h1 class="wp-heading-inline"><?php _e( 'Donation #12', 'peerraiser' ); ?></h1>
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
                                                <span class="label">Date/Time</span>
                                                <strong><?php echo mysql2date( get_option('date_format'), $peerraiser['donation']->date ); ?></strong>
                                            </div>
                                            <div class="donation-method">
                                                <span class="label">Payment Method</span>
                                                <strong>Credit Card</strong>
                                            </div>
                                            <div class="donation-key">
                                                <span class="label">Transaction Key</span>
                                                <strong><?php echo $peerraiser['donation']->transaction_id; ?></strong>
                                            </div>
                                            <div class="donor-ip">
                                                <span class="label">IP Address</span>
                                                <strong><?php echo $peerraiser['donation']->ip; ?></strong>
                                            </div>
                                            <div class="is-test-mode">
                                                <span class="label">Test mode?</span>
                                                <strong><?php echo ( $peerraiser['donation']->is_test ) ? 'Yes' : 'No'; ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <a class="submitdelete deletion" href="http://localhost/wordpress/wp-admin/post.php?post=1080&amp;action=trash&amp;_wpnonce=f77a7b0df6"><?php _e( 'Delete Donation', 'peerraiser' ); ?></a>
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
                                <p class="summary"><?php printf( '%s made a donation of <strong>$%f</strong> on <strong>%s</strong>', $peerraiser['donor']->donor_name, number_format( $peerraiser['donation']->total, 2 ), mysql2date( get_option('date_format'), $peerraiser['donation']->date ) ); ?></p>
                                <table class="transaction-info table table-striped">
                                    <thead>
                                        <tr>
                                            <th colspan="2"><?php _e( 'Allocation', 'peerraiser') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Campaign:</strong></td>
                                            <td><a href="post.php?action=edit&post=">Campaign Here</a></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Fundraiser:</strong></td>
                                            <?php if ( true ) : ?>
                                                <td><a href="post.php?action=edit&post=0">Fundraiser Name</a></td>
                                            <?php else : ?>
                                                <td>Fundraiser</td>
                                            <?php endif; ?>
                                        </tr>
                                        <tr>
                                            <td><strong>Team:</strong></td>
                                            <?php if ( true ) : ?>
                                                <td><a href="post.php?action=edit&post=0">Team Name</a></td>
                                            <?php else : ?>
                                                <td>Team Title</td>
                                            <?php endif; ?>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Donation:</strong></td>
                                            <td><strong>$<?php echo number_format( $peerraiser['donation']->total, 2 ); ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php do_action( 'peerraiser_after_donation_metaboxes' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'peerraiser_add_donation_nonce' ); ?>
        <input type="hidden" name="peerraiser_action" value="add_donation">
    </form>
</div>