<div class="change-password-form-wrapper" id="peerraiser_change_password_form">
    <div class="change-password-form-body">
        <form role="form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <?php if ( isset( $_GET['errors'] ) ) : ?>
                <div class="alert alert-danger">
                    <?php if ( $_GET['errors'] === 'password_incorrect' ) : ?>
                        <?php _e( '<strong>Oops!</strong> The password you entered is incorrect.', 'peerraiser' ) ?>
                    <?php elseif ( $_GET['errors'] === 'password_mismatch' ) : ?>
                        <?php _e( '<strong>Oops!</strong> The passwords you entered do not match.', 'peerraiser' ) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="cmb2-wrap form-table">

                <div class="cmb-row email">
                    <div class="cmb-th">
                        <label for="current-password"><?php _e( 'Current password', 'peerraiser' ) ?></label>
                    </div>
                    <div class="cmb-td">
                        <input id="current-password" class="form-input" type="password" name="current_password">
                    </div>
                </div>

                <div class="cmb-row password">
                    <div class="cmb-th">
                        <label for="new-password"><?php _e( 'New password', 'peerraiser' ) ?></label>
                    </div>
                    <div class="cmb-td">
                        <input id="new-password" class="form-input" type="password" name="new_password">
                    </div>
                </div>

                <div class="cmb-row confirm-password">
                    <div class="cmb-th">
                        <label for="new-password"><?php _e( 'Password confirmation', 'peerraiser' ) ?></label>
                    </div>
                    <div class="cmb-td">
                        <input id="new-password" class="form-input" type="password" name="confirm_password">
                    </div>
                </div>

            </div>

            <input type="submit" class="button-primary" value="<?php _e( 'Update Password', 'peerraiser' ) ?>">

            <input type="hidden" name="action" value="peerraiser_change_password">
            <?php wp_nonce_field( 'change_password_' . get_current_user_id(), 'peerraiser_change_password_nonce' ) ?>

        </form>
    </div>
</div>