<?php
/**
 * The template for displaying a PeerRaiser Team
 */
$team = peerraiser_get_current_team();
?>

<?php get_header(); ?>

<div id="peerraiser-team" class="<?php echo 'peerraiser-team-' . $team->ID ?> <?php echo 'peerraiser-team-' . esc_attr( $team->team_slug ); ?>">

    <div class="peerraiser-team-content<?php if ( is_active_sidebar( 'peerraiser-team-sidebar' ) ) echo ' has-sidebar'; ?>">

        <h1 class="peerraiser-team-title"><?php echo esc_attr( $team->team_name ); ?></h1>

        <div class="peerraiser-team-description">
            <?php echo wp_kses_post( $team->team_content ); ?>
        </div>

    </div>

    <?php if ( is_active_sidebar( 'peerraiser-team-sidebar' ) ) : ?>

        <div class="peerraiser-team-sidebar">

            <?php dynamic_sidebar( 'peerraiser-team-sidebar' ); ?>

        </div>

    <?php endif; ?>

</div>

<?php get_footer(); ?>
