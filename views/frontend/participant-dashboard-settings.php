<div class="peerraiser-dashboard">
    <nav class="peerraiser-nav">
        <ul>
            <?php foreach ( $peerraiser['navigation'] as $key => $value ) : ?>
                <li class="<?php echo sanitize_title( $key ) ?>"><a href="?page=<?php echo $key ?>"><?php echo $value ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <h2><?php _e( 'My Info', 'peerraiser' ); ?></h2>

    <div class="alert" role="alert" style="display:none;"></div>

    <form id="fileupload" enctype="multipart/form-data">
        <div class="peerraiser-image-upload">
            <div class="profile-image-preview">
                <img src="<?php echo plugin_dir_url( PEERRAISER_FILE ) . 'assets/images/loading.gif' ?>" alt="" class="loading" style="display:none;">
                <img src="<?php echo $peerraiser['profile_photo'] ?>" alt="<?php _e( 'Default user thumbnail', 'peerraiser' ) ?>" class="avatar">
            </div>
            <div class="profile-image-upload">
                <p class="info-top">You can upload an avatar here or change it at <a href="http://gravatar.com" target="_blank">gravatar.com</a></p>

                <input type="file" name="profile_picture" id="profile-picture" class="file-input">
                <label for="profile-picture" class="file-label"><strong>Choose a file...</strong><span></span></label>

                <p class="info-bottom">JPG, PNG, or GIF. Maximum file size is 1MB</p>
            </div>
        </div>
        <?php wp_nonce_field( 'peerraiser_avatar_'.get_current_user_id(), 'peerraiser_upload_avatar_nonce', false, true ); ?>
    </form>

    <?php echo $peerraiser['settings_form']; ?>

    <h2><?php _e( 'Change Password', 'peerraiser' ); ?></h2>

    <?php echo $peerraiser['password_form']; ?>

</div>