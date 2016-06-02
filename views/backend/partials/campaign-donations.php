<table class="donations-list table table-striped">
    <thead>
        <tr>
            <th><?php _e( 'ID', 'peerraiser') ?></th>
            <th><?php _e( 'Details', 'peerraiser') ?></th>
            <th><?php _e( 'Donor', 'peerraiser') ?></th>
            <th><?php _e( 'Amount', 'peerraiser') ?></th>
            <th><?php _e( 'Date', 'peerraiser') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if ( $peerraiser['number_of_donations'] < 1 ) : ?>
        <tr>
            <td colspan="5" class="text-center"><?php _e('There are currently no Donations associated with this Campaign', 'peerraiser');  ?></td>
        </tr>
    <?php else : ?>
        <?php foreach ( $peerraiser['donations'] as $donation ) : ?>
            <tr>
                <td><?= $donation->ID ?></td>

                <td><a href="post.php?action=edit&post=<?= $donation->ID ?>"><?php _e( 'View Details', 'peerraiser' ) ?></a></td>

                <?php $donor_id = get_post_meta( $donation->ID, '_donor', true) ?>
                <td><?= get_post_meta( $donor_id, '_donor_first_name', true) ?> <?= get_post_meta( $donor_id, '_donor_last_name', true) ?></td>

                <td><?= $peerraiser['currency_symbol'] . get_post_meta( $donation->ID, '_donation_amount', true ) ?></td>

                <td><?= get_the_date( get_option( 'date_format' ), $donation->ID ) ?></td>
            </tr>
        <?php endforeach ?>
    <?php endif; ?>
    </tbody>
</table>

<?= $peerraiser['pagination'] ?>