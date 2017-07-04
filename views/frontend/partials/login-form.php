<div class="login-form-wrapper">
    <div class="login-form-heading"><h3 class="login-title"><?php _e( 'Sign in', 'peerraiser' ) ?></h3>
        <div class="forgot-password"><a href="#"><?php _e( 'Forgot password?', 'peerraiser' ) ?></a></div>
    </div>

    <div class="login-form-body">
        <form role="form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <?php if ( isset( $_GET['errors'] ) ) : ?>
                <div class="error-message">
                    <?php if ( $_GET['errors'] === 'login' ) : ?>
                        <?php _e( '<strong>Oops!</strong> Incorrect Username or Password.', 'peerraiser' ) ?>
                    <?php elseif ( $_GET['errors'] === 'empty_fields' ) : ?>
                        <?php _e( '<strong>Oops!</strong> Username or Password was empty.', 'peerraiser' ) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="input-wrapper email">
                <label for="login-email"><?php _e( 'Username or Email Address', 'peerraiser' ) ?>
                    <input id="login-email" class="form-input" type="text" name="username" value="" placeholder="">
                </label>
            </div>

            <div class="input-wrapper password">
                <label for="login-password"><?php _e( 'Password', 'peerraiser' ) ?>
                    <input id="login-password" class="form-input" type="password" name="password">
                </label>
            </div>

            <div class="input-wrapper">
                <div class="checkbox">
                    <label>
                        <input id="login-remember" type="checkbox" name="remember" value="1"> <?php _e( 'Remember me', 'peerraiser' ) ?>
                    </label>
                </div>
            </div>

            <button type="submit" class="login-submit"><?php _e( 'Sign in', 'peerraiser' ) ?></button>

            <div class="login-form-bottom">
                <?php _e( "Don't have an account?", 'peerraiser') ?> <a href="<?php echo esc_url( $signup_page ); ?>"><?php _e( 'Sign Up Here', 'peerraiser' ) ?></a>
            </div>

            <input type="hidden" name="action" value="peerraiser_login">
            <?php if ( isset( $_GET['next_url'] ) ) : ?>
                <input type="hidden" name="next_url" value="<?= esc_url( $_GET['next_url'] ) ?>">
            <?php endif; ?>

        </form>
    </div>
</div>