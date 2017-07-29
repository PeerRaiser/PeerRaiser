<div class="peerraiser-dashboard">
    <nav class="peerraiser-nav">
        <ul>
            <?php foreach ( $peerraiser['navigation'] as $key => $value ) : ?>
                <li class="<?= sanitize_title( $key ) ?>"><a href="?page=<?= $key ?>"><?= $value ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <h2><?php _e( 'Donations Received', 'peerraiser' ); ?></h2>

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
        <?php if ( ! empty( $peerraiser['donations'] ) ) : $total = 0;?>
            <?php foreach( $peerraiser['donations'] as $donation ) : ?>
                <?php $donor = new \PeerRaiser\Model\Donor( $donation->donor_id ); ?>
                <tr>
                    <td><?php echo esc_attr( $donor->full_name ); ?></td>
                    <td><?php echo peerraiser_money_format( $donation->total ) ;?></td>
                    <?php $total += $donation->total; ?>

                    <td><?php echo mysql2date( 'F j, Y', $donation->date ); ?></td>

                    <td><a href="mailto:<?php echo esc_attr( $donor->email_address ); ?>"><?php echo _e( 'Email', 'peerraiser' ); ?></a></td>
                </tr>
            <?php endforeach; ?>
                <tr>
                    <td><strong><?php _e( 'TOTAL:', 'peerraiser' ) ?></strong></td>
                    <td colspan="3"><strong><?php echo peerraiser_money_format( $total ); ?></strong></td>
                </tr>
        <?php else : ?>
            <tr>
                <td colspan="4" class="text-center"><?php _e('You haven\'t received any donations yet.', 'peerraiser');  ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>