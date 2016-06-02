<table class="fundraisers-list table table-striped">
    <thead>
        <tr>
            <th><?php _e( 'ID', 'peerraiser') ?></th>
            <th><?php _e( 'Fundraiser', 'peerraiser') ?></th>
            <th><?php _e( 'Campaign', 'peerraiser') ?></th>
            <th><?php _e( 'Participant', 'peerraiser') ?></th>
            <th><?php _e( 'Goal', 'peerraiser') ?></th>
            <th><?php _e( 'Raised', 'peerraiser') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if ( $peerraiser['number_of_fundraisers'] < 1 ) : ?>
        <tr>
            <td colspan="5" class="text-center"><?php _e('There are currently no Fundraisers associated with this Team', 'peerraiser');  ?></td>
        </tr>
    <?php else : ?>
        <?php foreach ( $peerraiser['fundraisers'] as $fundraiser ) : ?>
            <tr>
                <td><?= $fundraiser->ID ?></td>

                <td><a href="post.php?action=edit&post=<?= $fundraiser->ID ?>"><?= get_the_title( $fundraiser->ID ) ?></a></td>

                <?php $campaign_id = get_post_meta( $fundraiser->ID, '_fundraiser_campaign', true ) ?>
                <td><a href="post.php?action=edit&post=<?= $campaign_id ?>"><?= get_the_title( $campaign_id ) ?></a></td>

                <?php
                    $participant_id = get_post_meta( $fundraiser->ID, '_fundraiser_participant', true );
                    $participant_info = get_userdata( $participant_id );
                ?>
                <td><a href="user-edit.php?user_id=<?= $participant_id ?>"><?= $participant_info->user_login ?></a></td>

                <?php $goal = get_post_meta( $fundraiser->ID, '_fundraiser_goal', true ) ?>
                <td><?= ( !empty($goal) && $goal != '0.00' ) ? $peerraiser['currency_symbol'] . number_format_i18n( $goal, 2) : '&mdash;' ?></td>

                <td><?= $peerraiser['currency_symbol'] . \PeerRaiser\Helper\Stats::get_total_donations_by_fundraiser( $fundraiser->ID ) ?></td>
            </tr>
        <?php endforeach ?>
    <?php endif; ?>
    </tbody>
</table>

<?= $peerraiser['pagination'] ?>