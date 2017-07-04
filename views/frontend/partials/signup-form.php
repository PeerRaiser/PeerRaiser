<div class="signup-form-wrapper">
    <div class="signup-form-heading"><h3 class="signup-title"><?php _e( 'Create an Account', 'peerraiser' ) ?></h3></div>

    <div class="signup-form-body">
        <form role="form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <?php if ( isset( $_GET['errors'] ) ) : ?>
                <div class="error-message">
                    <?php if ( $_GET['errors'] === 'username_exists' ) : ?>
                        <?php _e( '<strong>Oops!</strong> That username is already taken. Please try again.', 'peerraiser' ) ?>
                    <?php elseif ( $_GET['errors'] === 'empty_fields' ) : ?>
                        <?php _e( '<strong>Oops!</strong> A required field was empty.', 'peerraiser' ) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="input-wrapper name">
                <label for="signup-firstname"><span><?php _e( 'Name', 'peerraiser' ) ?></span>
                    <input id="signup-firstname" class="form-input" type="text" name="firstname" value="" placeholder="<?php _e( 'First', 'peerraiser' ) ?>">
                    <input id="signup-lastname" class="form-input" type="text" name="lastname" value="" placeholder="<?php _e( 'Last', 'peerraiser' ) ?>">
                </label>
            </div>

            <div class="input-wrapper email">
                <label for="signup-email"><?php _e( 'Email Address', 'peerraiser' ) ?>
                    <input id="signup-email" class="form-input" type="email" name="email">
                </label>
            </div>

            <div class="input-wrapper username">
                <label for="signup-username"><?php _e( 'Username', 'peerraiser' ) ?>
                    <input id="signup-username" class="form-input" type="text" name="username">
                </label>
            </div>

            <div class="input-wrapper password">
                <label for="signup-password"><?php _e( 'Password', 'peerraiser' ) ?>
                    <input id="signup-password" class="form-input" type="password" name="password">
                </label>
            </div>

            <button type="submit" class="signup-submit"><?php _e( 'Sign Up', 'peerraiser' ) ?></button>

            <p class="terms-of-use">By creating an account, you agree to our <a href="#">Terms of Use</a>.</p>

            <div class="signup-form-bottom">
                <?php _e( 'Already have an account?', 'peerraiser') ?> <a href="<?php echo esc_url( $login_page ); ?>"><?php _e( 'Sign in', 'peerraiser' ) ?></a>
            </div>

            <input type="hidden" name="action" value="peerraiser_signup">
            <?php if ( isset( $_GET['next_url'] ) ) : ?>
                <input type="hidden" name="next_url" value="<?= esc_url( $_GET['next_url'] ) ?>">
            <?php endif; ?>

        </form>
    </div>
</div>