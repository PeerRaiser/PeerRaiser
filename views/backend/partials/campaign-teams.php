<table class="teams-list table table-striped">
    <thead>
        <tr>
            <th><?php _e( 'Team Name', 'peerraiser') ?></th>
            <th><?php _e( 'Team Leader', 'peerraiser') ?></th>
            <th><?php _e( 'Raised', 'peerraiser') ?></th>
            <th><?php _e( 'Goal', 'peerraiser') ?></th>
            <th><?php _e( 'Fundraisers', 'peerraiser') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if ( $peerraiser['number_of_teams'] < 1 ) : ?>
        <tr>
            <td colspan="5" class="text-center"><?php _e('There are currently no Teams associated with this Campaign', 'peerraiser');  ?></td>
        </tr>
    <?php else : ?>
        <?php foreach ( $peerraiser['teams'] as $team ) : ?>
            <tr>
                <td><a href="post.php?action=edit&post=<?= $team->ID ?>"><?= get_the_title( $team->ID ) ?></a></td>

                <?php
                    $leader_id = get_post_meta( $team->ID, '_team_leader', true);
                    $user_info = get_userdata( $leader_id );
                ?>
                <td><a href="user-edit.php?user_id=<?= $leader_id ?>"><?= $user_info->user_login ?></a></td>

                <td><?= $peerraiser['currency_symbol'] . number_format_i18n( \PeerRaiser\Helper\Stats::get_total_donations_by_team( $team->ID ), 2); ?></td>

                <?php $goal_amount = get_post_meta( $team->ID, '_goal_amount', true ) ?>
                <td><?= ( !empty($goal_amount) && $goal_amount != '0.00' ) ? $peerraiser['currency_symbol'] . number_format_i18n( $goal_amount, 2) : '&mdash;' ?></td>

                <?php
                $args = array(
                    'post_type'       => 'fundraiser',
                    'posts_per_page'  => -1,
                    'post_status'     => 'publish',
                    'connected_type'  => 'fundraiser_to_team',
                    'connected_items' => $team->ID
                );
                $fundraisers = new \WP_Query( $args );
                ?>

                <td><?= $fundraisers->found_posts ?></td>
            </tr>
        <?php endforeach ?>
    <?php endif; ?>
    </tbody>
</table>

<?= $peerraiser['pagination'] ?>