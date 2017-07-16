<?php
/**
 * The template for displaying the PeerRaiser Campaign
 */
$fundraiser = peerraiser_get_current_fundraiser();
?>

<?php get_header(); ?>

<div id="peerraiser-fundraiser" class="<?php echo 'peerraiser-fundraiser-' . $fundraiser->ID ?> <?php echo 'peerraiser-fundraiser-' . $fundraiser->fundraiser_slug; ?>">

	<?php if ( is_active_sidebar( 'peerraiser-fundraiser-sidebar' ) ) : ?>

        <div class="peerraiser-fundraiser-sidebar">

			<?php dynamic_sidebar( 'peerraiser-fundraiser-sidebar' ); ?>

        </div>

	<?php endif; ?>

    <div class="peerraiser-fundraiser-content<?php if ( is_active_sidebar( 'peerraiser-fundraiser-sidebar' ) ) echo ' has-sidebar'; ?>">

        <h1 class="peerraiser-fundraiser-title"><?php echo $fundraiser->fundraiser_name; ?></h1>

        <div class="peerraiser-fundraiser-description">
		    <?php echo $fundraiser->fundraiser_content; ?>
        </div>

    </div>

</div>

<?php get_footer(); ?>
