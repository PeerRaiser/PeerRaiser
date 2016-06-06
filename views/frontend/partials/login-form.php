<div class="login-form-wrapper">
    <div class="login-form-heading"><h3 class="login-title"><strong><?php __( 'Sign in', 'peerraiser' ) ?> </strong></h3>
        <div class="forgot-password"><a href="#"><?php __( 'Forgot password?', 'peerraiser' ) ?></a></div>
    </div>

    <div class="login-form-body">
        <form role="form">
            <div class="error-message">
                <a class="close" href="#">×</a><?php __( 'Incorrect Username or Password!', 'peerraiser' ) ?>
            </div>

            <div class="input-wrapper email">
                <input id="login-email" type="email" name="email" value="" placeholder="<?php __( 'email address', 'peerraiser' ) ?>">
            </div>

            <div class="input-wrapper password">
                <input id="login-password" type="password" name="password" placeholder="<?php __( 'password', 'peerraiser' ) ?>">
            </div>

            <div class="input-wrapper">
                <div class="checkbox">
                    <label>
                        <input id="login-remember" type="checkbox" name="remember" value="1"> <?php __( 'Remember me', 'peerraiser' ) ?>
                    </label>
                </div>
            </div>

            <button type="submit" class="login-submit"><?php __( 'Sign in', 'peerraiser' ) ?></button>

            <hr>

            <div>
                Don't have an account!
                <a href="#" onclick="$('#loginbox').hide(); $('#signupbox').show()">
                    Sign Up Here
                </a>
            </div>

        </form>
    </div>
</div>