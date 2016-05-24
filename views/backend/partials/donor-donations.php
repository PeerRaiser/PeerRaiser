<table class="donations-list table table-striped">
    <thead>
        <tr>
            <th><?php _e( 'ID', 'peerraiser') ?></th>
            <th><?php _e( 'Amount', 'peerraiser') ?></th>
            <th><?php _e( 'Campaign', 'peerraiser') ?></th>
            <th><?php _e( 'Fundraiser', 'peerraiser') ?></th>
            <th><?php _e( 'Date', 'peerraiser') ?></th>
            <th><?php _e( 'Method', 'peerraiser') ?></th>
            <th><?php _e( 'Live Mode?', 'peerraiser') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if ( $peerraiser['number_of_donations'] < 1 ) : ?>
        <tr>
            <td colspan="5" class="text-center"><?php _e('There are currently no donations associated with this Donor', 'peerraiser');  ?></td>
        </tr>
    <?php else : ?>
        <?php foreach ( $peerraiser['donations'] as $donation ) : ?>
            <tr>
                <td><a href="post.php?action=edit&post=<?= $donation->ID ?>"><?= $donation->ID ?></a></td>

                <?php $amount = get_post_meta( $donation->ID, '_donation_amount', true ); ?>
                <td><?= $peerraiser['currency_symbol'] . $amount ?></td>

                <?php $campaign_id = get_post_meta( $donation->ID, '_campaign', true ); ?>
                <td><a href="post.php?action=edit&post=<?= $campaign_id ?>"><?= get_the_title( $campaign_id ) ?></a></td>

                <?php if ( $fundraiser_id = get_post_meta( $donation->ID, '_fundraiser', true ) ) : ?>
                    <td><a href="post.php?action=edit&post=<?= $fundraiser_id ?>"><?= get_the_title( $fundraiser_id ) ?></a></td>
                <?php else : ?>
                    <td><?php _e( 'None', 'peerraiser' ) ?></td>
                <?php endif; ?>

                <td><?= get_the_date( get_option( 'date_format' ), $donation->ID ) ?></td>

                <td><?= get_post_meta( $donation->ID, '_payment_method', true ) ?></td>

                <?php $test_mode = get_post_meta( $donation->ID, '_test_mode', true ) ?>
                <td class="text-center"><span class="<?= $test_mode ? 'times' : 'check' ?>"></span></td>
            </tr>
        <?php endforeach ?>
    <?php endif; ?>
    </tbody>
</table>

<?= $peerraiser['pagination'] ?>