<h2><?php _e( 'Select a campaign', 'peerraiser' ); ?></h2>

<div class="peerraiser-registration-select-campaign">
    <?php if ( ! empty( $campaigns ) ) : ?>
        <select name="peerraiser_campaign" id="peerraiser-campaign" class="peerraiser-campaign-select">
            <option value=""></option>
            <?php foreach ( $campaigns as $campaign ) : ?>
                <option value="<?php echo $campaign->campaign_slug; ?>"><?php echo $campaign->campaign_name; ?></option>
            <?php endforeach; ?>
        </select>
    <?php else : ?>
        <p><?php _e( 'There are currently no campaigns available to register for.', 'peerraiser' ); ?></p>
    <?php endif; ?>
</div>