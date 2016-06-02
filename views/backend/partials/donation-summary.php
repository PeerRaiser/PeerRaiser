<div id="donation-summary">

    <p class="summary"><?= $peerraiser['first_name'] ?> <?= $peerraiser['last_name'] ?> made a donation of <strong><?= $peerraiser['currency_symbol'] .  $peerraiser['donation_amount']?></strong> on <strong><?= $peerraiser['donation_date'] ?></strong></p>

    <table class="transaction-info table table-striped">
        <thead>
            <tr>
                <th colspan="2"><?php _e( 'Allocation', 'peerraiser') ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Campaign:</strong></td>
                <td><a href="post.php?action=edit&post=<?= $peerraiser['campaign_id'] ?>"><?= $peerraiser['campaign_title'] ?></a></td>
            </tr>
            <tr>
                <td><strong>Fundraiser:</strong></td>
                <?php if ( $peerraiser['team_id'] ) : ?>
                    <td><a href="post.php?action=edit&post=<?= $peerraiser['fundraiser_id'] ?>"><?= $peerraiser['fundraiser_title'] ?></a></td>
                <?php else : ?>
                    <td><?= $peerraiser['fundraiser_title'] ?></td>
                <?php endif; ?>
            </tr>
            <tr>
                <td><strong>Team:</strong></td>
                <?php if ( $peerraiser['team_id'] ) : ?>
                    <td><a href="post.php?action=edit&post=<?= $peerraiser['team_id'] ?>"><?= $peerraiser['team_title'] ?></a></td>
                <?php else : ?>
                    <td><?= $peerraiser['team_title'] ?></td>
                <?php endif; ?>
            </tr>
            <tr>
                <td><strong>Total Donation:</strong></td>
                <td><strong><?= $peerraiser['currency_symbol'] .  $peerraiser['donation_amount']?></strong></td>
            </tr>
        </tbody>
    </table>

</div>