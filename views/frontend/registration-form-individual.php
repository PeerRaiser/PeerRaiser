<?php do_action( 'peerraiser_before_registration_title' ); ?>

<h1><?php echo apply_filters( 'peerraiser_registration_start_team_heading', __( 'Create a Fundraising Page', 'peerraiser' ) ); ?></h1>

<?php do_action( 'peerraiser_after_registration_title' ); ?>

<div class="peerraiser-registration-form individual">
    <?php if ( ! empty( $errors ) ) : ?>
        <div class="error-message">
            <strong><?php _e( 'Oops!', 'peerraiser' ) ?></strong> <?php echo $errors; ?></div>
    <?php endif; ?>

    <?php do_action( 'peerraiser_before_registration_fields' ); ?>

    <?php echo $fields; ?>

    <?php do_action( 'peerraiser_after_registration_fields' ); ?>
</div>