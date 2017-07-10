<h1><?php echo apply_filters( 'peerraiser_registration_start_team_heading', __( 'Join a Team', 'peerraiser' ) ); ?></h1>

<form action="">
    <input type="search">
    <div class="peerraiser-team-search-results">
        <?php if ( ! empty( $teams ) ) : ?>
            <?php foreach( $teams as $team ) : ?>
                <div class="peerraiser-team">
                    <img src="<?php echo $team->get_thumbnail_url(); ?>" class="peerraiser-team-thumbnail">
                    <div class="peerraiser-team-info">
                        <h3><?php echo $team->team_name; ?></h3>
                        <p class="peerraiser-team-leader"><?php $team->get_team_leader_name; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <?php _e( 'There are no teams for this campaign yet.', 'peerraiser' ); ?>
        <?php endif; ?>
    </div>
</form>