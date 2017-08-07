<?php
/**
 * The template for displaying the PeerRaiser Campaign
 */
$fundraiser = peerraiser_get_current_fundraiser();
?>

<?php get_header(); ?>

<div id="peerraiser-fundraiser" class="<?php echo 'peerraiser-fundraiser-' . $fundraiser->ID ?> <?php echo 'peerraiser-fundraiser-' . esc_url( $fundraiser->fundraiser_slug ); ?>">

    <div class="peerraiser-fundraiser-sidebar">

        <div class="peerraiser-fundraiser-thumbnail">
            <img src="<?php echo esc_url( $fundraiser->get_thumbnail_url() ) ?>" alt="<?php _e( 'Profile picture', 'peerraiser' ); ?>">
        </div>

        <?php dynamic_sidebar( 'peerraiser-fundraiser-sidebar' ); ?>

    </div>

    <div class="peerraiser-fundraiser-content has-sidebar">

        <h1 class="peerraiser-fundraiser-title"><?php echo esc_attr( $fundraiser->fundraiser_name ); ?></h1>

        <div class="peerraiser-fundraiser-description">
            <?php echo wpautop( wp_kses_post( $fundraiser->fundraiser_content ) ); ?>
        </div>

    </div>

</div>

<?php get_footer(); ?>
