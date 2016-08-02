<div class="peerraiser-dashboard">
    <nav class="peerraiser-nav">
        <ul>
            <?php foreach ( $peerraiser['navigation'] as $key => $value ) : ?>
                <li class="<?= sanitize_title( $key ) ?>"><a href="?page=<?= $key ?>"><?= $value ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <table class="donations-list table table-striped">
        <thead>
            <tr>
                <th><?php _e( 'Donor', 'peerraiser') ?></th>
                <th><?php _e( 'Amount', 'peerraiser') ?></th>
                <th><?php _e( 'Date', 'peerraiser') ?></th>
                <th><?php _e( 'Actions', 'peerraiser') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ( $peerraiser['donations']->have_posts() ) : $total = 0;?>
            <?php while( $peerraiser['donations']->have_posts() ) : $peerraiser['donations']->the_post() ?>
                <tr>
                    <?php $donor_id = get_post_meta( get_the_ID(), '_donor', true) ?>
                    <td><?= get_post_meta( $donor_id, '_donor_first_name', true) ?> <?= get_post_meta( $donor_id, '_donor_last_name', true) ?></td>

                    <?php $donation_amount = get_post_meta( get_the_ID(), '_donation_amount', true ); ?>
                    <td><?= $peerraiser['currency_symbol'] . $donation_amount; ?></td>
                    <?php $total += floatval( $donation_amount ); ?>

                    <td><?= get_the_date( get_option( 'date_format' ), get_the_ID() ) ?></td>

                    <td><a href="">Email</a></td>
                </tr>
            <?php endwhile; ?>
                <tr>
                    <td><strong><?php _e( 'TOTAL:', 'peerraiser' ) ?></strong></td>
                    <td colspan="3"><strong><?= $peerraiser['currency_symbol'] . $total; ?></strong></td>
                </tr>
        <?php else : ?>
            <tr>
                <td colspan="4" class="text-center"><?php _e('You haven\'t received any donations yet.', 'peerraiser');  ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>