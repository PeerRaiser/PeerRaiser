<table class="participants-list table table-striped">
    <thead>
        <tr>
            <th><?php _e( 'ID', 'peerraiser') ?></th>
            <th><?php _e( 'Username', 'peerraiser') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if ( $peerraiser['number_of_participants'] < 1 ) : ?>
        <tr>
            <td colspan="5" class="text-center"><?php _e('There are currently no Participants associated with this Team', 'peerraiser');  ?></td>
        </tr>
    <?php else : ?>
        <?php foreach ( $peerraiser['participants'] as $participant ) : ?>
            <tr>
                <td><?= $participant->ID ?></td>

                <?php
                    $participant_id = $participant->ID;
                    $user_info = get_userdata( $participant_id );
                ?>
                <td><a href="post.php?action=edit&post=<?= $participant_id ?>"><?= $user_info->user_login ?></a></td>
            </tr>
        <?php endforeach ?>
    <?php endif; ?>
    </tbody>
</table>

<?= $peerraiser['pagination'] ?>