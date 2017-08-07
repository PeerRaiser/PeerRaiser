<?php
/**
 * The template for displaying a PeerRaiser Team
 */
$team = peerraiser_get_current_team();
?>

<?php get_header(); ?>

<div id="peerraiser-team" class="<?php echo 'peerraiser-team-' . $team->ID ?> <?php echo 'peerraiser-team-' . esc_attr( $team->team_slug ); ?>">

    <div class="peerraiser-team-sidebar">

        <div class="peerraiser-team-thumbnail">
            <img src="<?php echo esc_url( $team->thumbnail_image ) ?>" alt="<?php _e( 'Team photo', 'peerraiser' ); ?>">
        </div>

        <?php dynamic_sidebar( 'peerraiser-team-sidebar' ); ?>

    </div>

    <div class="peerraiser-team-content has-sidebar">

        <h1 class="peerraiser-team-title"><?php echo esc_attr( $team->team_name ); ?></h1>

        <div class="peerraiser-team-description">
            <?php echo wpautop( wp_kses_post( $team->team_content ) ); ?>
        </div>

    </div>

</div>

<?php get_footer(); ?>
