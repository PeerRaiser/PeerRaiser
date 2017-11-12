<form class="peerraiser-donation-form" method="post" enctype="multipart/form-data">

    <section class="peerraiser-campaign-selection <?php echo $peerraiser['campaign_select_class']; ?>">
        <select name="campaign" id="peerraiser_campaign">
            <?php if ( count( $peerraiser['campaigns'] ) > 1 ) : ?>
                <option value=""><?php _e( 'Select a Campaign to Support', 'peerraiser' ); ?> *</option>
            <?php endif; ?>
            <?php foreach( $peerraiser['campaigns'] as $campaign ) : ?>
                <option value="<?php echo $campaign->campaign_slug; ?>" <?php selected( get_query_var( 'peerraiser_campaign' ), $campaign->campaign_slug ) ?>><?php echo $campaign->campaign_name; ?></option>
            <?php endforeach; ?>
        </select>
	    <?php wp_nonce_field( 'ajax_get_fundraisers', 'get_fundraisers_nonce', false ); ?>
    </section>

    <?php if ( count( $peerraiser['fundraisers'] ) > 0 ) : ?>
        <section class="peerraiser-fundraiser-selection <?php echo $peerraiser['fundraiser_select_class']; ?>"<?php if ( isset( $_GET['campaign']) && empty( $peerraiser['fundraisers'] ) ) echo ' style="display:none;"'; ?>>
            <select name="fundraiser" id="fundraiser_select">
                <option value=""><?php _e( 'Select a Fundraiser to Support (optional)', 'peerraiser' ); ?></option>
                <?php foreach( $peerraiser['fundraisers'] as $fundraiser ) : ?>
                    <option value="<?php echo $fundraiser->fundraiser_slug; ?>" <?php selected( get_query_var( 'peerraiser_fundraiser' ), $fundraiser->fundraiser_slug ) ?>><?php echo $fundraiser->fundraiser_name; ?></option>
                <?php endforeach; ?>
            </select>
        </section>
    <?php endif; ?>

	<section class="peerraiser-donation-amounts">
		<label><h3><?php _e( 'Choose a Donation Amount', 'peerraiser' ); ?> </h3></label>
		<?php // TODO: Add setting for donation amounts instead of hard coding them ?>
		<ul class="peerraiser-donation-amount-buttons">
			<li class="default-amounts"><input type="radio" name="donation_amount" value="25" class="peerraiser-donation-button" id="button_1" checked><label for="button_1"><?php echo peerraiser_money_format( 25, true, false ); ?></label>
			<li class="default-amounts"><input type="radio" name="donation_amount" value="50" class="peerraiser-donation-button" id="button_2"><label for="button_2"><?php echo peerraiser_money_format( 50, true, false ); ?></label>
			<li class="default-amounts"><input type="radio" name="donation_amount" value="100" class="peerraiser-donation-button" id="button_3"><label for="button_3"><?php echo peerraiser_money_format( 100, true, false ); ?></label>
			<li class="default-amounts"><input type="radio" name="donation_amount" value="250" class="peerraiser-donation-button" id="button_4"><label for="button_4"><?php echo peerraiser_money_format( 250, true, false ); ?></label>
			<li class="default-amounts"><input type="radio" name="donation_amount" value="500" class="peerraiser-donation-button" id="button_5"><label for="button_5"><?php echo peerraiser_money_format( 500, true, false ); ?></label>
		</ul>
		<div class="peerraiser-donation-amount-other">
			<?php if ( $peerraiser['currency_position'] === 'before' ) : ?>
				<span class="peerraiser-currency-symbol"><?php echo $peerraiser['currency_symbol']; ?></span>
			<?php endif; ?>

			<?php if ( $peerraiser['donation_minimum'] ) : ?>
                <?php if ( $peerraiser['currency_position'] === 'before' ) : ?>
                    <?php /* translators: 1: Currency symbol 2: Minimum donation amount */ ?>
                    <?php $field_placeholder = sprintf( __( 'Other Amount (%1$s%2$s Min)', 'peerraiser' ), $peerraiser['currency_symbol'], $peerraiser['donation_minimum'] ); ?>
			    <?php else : ?>
                    <?php /* translators: 1: Minimum donation amount 2: Currency symbol  */ ?>
                    <?php $field_placeholder = sprintf( __( 'Other Amount (%1$s%2$s Min)', 'peerraiser' ), $peerraiser['donation_minimum'], $peerraiser['currency_symbol'] ); ?>
			    <?php endif; ?>
            <?php else : ?>
				<?php $field_placeholder = __( 'Other Amount', 'peerraiser' ); ?>
            <?php endif; ?>
			<input name="other_amount" class="peerraiser-donation-input" value="" maxlength="8" type="number" autocomplete="off" placeholder="<?php echo $field_placeholder; ?>" data-minimum="10" aria-label="Enter Donation Amount USD Min. $10">

			<?php if ( $peerraiser['currency_position'] === 'after' ) : ?>
				<span class="peerraiser-currency-symbol"><?php echo $peerraiser['currency_symbol']; ?></span>
			<?php endif; ?>
		</div>
	</section>

	<section class="donor-about">
		<h3><?php apply_filters( 'peerraiser_donation_form_about_title', _e( 'Tell us a little about yourself', 'peerraiser' ) ); ?> </h3>

        <div class="peerraiser-name-fields">
            <div id="peerraiser_field_first_name" class="peerraiser-field peerraiser-field-text required">
                <label for="peerraiser_first_name">
                    <?php _e( 'First Name', 'peerraiser' ); ?> <span class="required">*</span>
                </label>
                <input type="text" name="first_name" id="peerraiser_first_name" value="">
            </div>

            <div id="peerraiser_field_last_name" class="peerraiser-field peerraiser-field-text required">
                <label for="peerraiser_last_name">
                    <?php _e( 'Last Name', 'peerraiser' ); ?> <span class="required">*</span>
                </label>
                <input type="text" name="last_name" id="peerraiser_last_name" value="">
            </div>
        </div>

		<div id="peerraiser_field_email_address" class="peerraiser-field peerraiser-field-text required">
			<label for="peerraiser_email_address">
				<?php _e( 'Email Address', 'peerraiser' ); ?> <span class="required">*</span>
			</label>
			<input type="email" name="email_address" id="peerraiser_email_address" value="">
		</div>

        <div id="peerraiser_field_email_address" class="peerraiser-field peerraiser-field-text required">
            <label for="peerraiser_email_address">
				<?php _e( 'Display my name publicly as:', 'peerraiser' ); ?> <span class="required">*</span>
            </label>
            <input type="text" name="public_name" id="peerraiser_public_name" value="">
        </div>

        <div id="peerraiser_field_anonymous" class="peerraiser-field peerraiser-field-checkbox">
            <label for="peerraiser_anonymous">
                <input type="checkbox" name="is_anonymous" id="peerraiser_anonymous" value="true"><?php _e( "I would like my donation to remain anonymous", 'peerraiser' ); ?>
            </label>
        </div>
	</section>

	<button type="submit" class="peerraiser-donate-submit peerraiser-submit-button"><?php _e( 'Donate','peerraiser' ); ?></button>

	<input type="hidden" name="peerraiser_action" value="add_pending_donation">
	<?php wp_nonce_field( 'add_pending_donation', '_wpnonce', false ); ?>
</form>