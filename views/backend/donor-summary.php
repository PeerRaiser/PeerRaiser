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

    <h1 class="wp-heading-inline"><?php _e('Edit Donor', 'peerraiser'); ?></h1>
    <a href="<?php echo admin_url( 'admin.php?page=peerraiser-donors&view=add' ); ?>" class="page-title-action"><?php _e( 'Add New', 'peerraiser' ); ?></a>
    <hr class="wp-header-end">

    <form id="peerraiser-add-donor" class="peerraiser-form" action="" method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables peerraiser-metabox">

                        <?php do_action( 'peerraiser_before_donor_side_metaboxes' ); ?>

                        <div id="submitdiv" class="postbox">
                            <h2><span><?php _e( 'Donor Details', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="misc-publishing-actions">
                                        <div class="misc-pub-section donor-date">
                                            <span class="label"><?php _e( 'Donor Since:', 'peerraiser' ); ?></span>
                                            <strong><?php echo mysql2date( get_option('date_format'), $peerraiser['donor']->date ); ?></strong>
                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <a class="submitdelete deletion" href="<?php echo add_query_arg( array( 'peerraiser_action' => 'delete_donor', 'donor_id' => $peerraiser['donor']->ID, '_wpnonce' => wp_create_nonce( 'peerraiser_delete_donor_' . $peerraiser['donor']->ID ) ), admin_url( sprintf( 'admin.php?page=peerraiser-donors' ) ) ) ?>"><?php _e( 'Delete', 'peerraiser' ); ?></a>
                                        </div>
                                        <div id="publishing-action">
                                            <span class="spinner"></span>
                                            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e( 'Update', 'peerraiser' ); ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php do_action( 'peerraiser_after_donor_side_metaboxes' ); ?>
                    </div> <!-- / #side-sortables -->
                </div>
                <div id="postbox-container-2" class="postbox-container peerraiser-metabox">
                    <div id="normal-sortables">

                        <?php do_action( 'peerraiser_before_donor_card' ); ?>

                        <div id="donor-card">
                            <img src="<?php echo $peerraiser['profile_image_url']; ?>" alt="Profile Picture" class="profile-image">
                            <div class="donor-info">
                                <h1><?php echo $peerraiser['donor']->full_name; ?> <span>#<?php echo $peerraiser['donor']->ID ?></span></h1>
                                <div class="donor-meta">
                                    <?php if ( ! empty( $peerraiser['donor']->email_address ) ) : ?>
                                        <p class="email"><?php echo $peerraiser['donor']->email_address; ?></p>
                                    <?php endif; ?>

                                    <p class="since"><?php printf( __( 'Donor since %s', 'peerraiser' ), mysql2date( get_option('date_format'), $peerraiser['donor']->date ) ); ?></p>
                                    <?php if ( ! empty( $peerraiser['donor']->user_id ) ) : ?>
                                        <?php $user_info = get_userdata( $peerraiser['donor']->user_id ); ?>
                                        <p class="user-account"><?php printf( __( 'User Account: <a href="user-edit.php?user_id=%1$s">%2$s</a>', 'peerraiser' ), $user_info->ID, $user_info->user_login ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php do_action( 'peerraiser_before_donor_metaboxes' ); ?>

                        <div id="donor-options" class="postbox cmb2-postbox">
                            <h2><span><?php _e( 'Donor Options', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <?php echo cmb2_get_metabox_form( 'peerraiser-donor-info', 0, array( 'form_format' => '', ) ); ?>
                            </div>
                        </div>

                        <?php do_action( 'peerraiser_after_donor_metaboxes', $peerraiser ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'peerraiser_update_donor_' . $peerraiser['donor']->ID ); ?>
        <input type="hidden" name="donor_id" value="<?php echo $peerraiser['donor']->ID; ?>">
        <input type="hidden" name="peerraiser_action" value="update_donor">
    </form>
</div>
