<?php
/**
 * The template for displaying the PeerRaiser Campaign
 */
$campaign = peerraiser_get_current_campaign();
?>

<?php get_header(); ?>

<div id="peerraiser-campaign" class="<?php echo 'peerraiser-campaign-' . $campaign->ID ?> <?php echo 'peerraiser-campaign-' . esc_attr( $campaign->campaign_slug ); ?>">

    <div class="peerraiser-campaign-content<?php if ( is_active_sidebar( 'peerraiser-campaign-sidebar' ) ) echo ' has-sidebar'; ?>">

        <?php if ( $campaign->banner_image ) : ?>
            <div class="peerraiser-campaign-banner">
                <img src="<?php echo esc_url( $campaign->banner_image ); ?>" alt="<?php echo esc_attr( $campaign->campaign_name ); ?>">
            </div>
        <?php endif; ?>

        <h1 class="peerraiser-campaign-title"><?php echo esc_attr( $campaign->campaign_name ); ?></h1>

        <div class="peerraiser-campaign-description">
            <?php echo wpautop( wp_kses_post( $campaign->campaign_description ) ); ?>
        </div>

    </div>

    <?php if ( is_active_sidebar( 'peerraiser-campaign-sidebar' ) ) : ?>

        <div class="peerraiser-campaign-sidebar">

            <?php dynamic_sidebar( 'peerraiser-campaign-sidebar' ); ?>

        </div>

    <?php endif; ?>

</div>

<?php get_footer(); ?>
