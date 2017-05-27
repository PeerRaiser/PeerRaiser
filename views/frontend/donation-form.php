<form>
	<section class="peerraiser-donation-amounts">
		<label><h3><?php _e( 'Choose a Donation Amount', 'peerraiser' ); ?> </h3></label>
		<ul class="peerraiser-donation-amount-buttons">
			<li class="default-amounts"><input type="radio" name="donation_amount" value="25" class="peerraiser-donation-button" id="button_1"><label for="button_1">$25</label>
			<li class="default-amounts"><input type="radio" name="donation_amount" value="50" class="peerraiser-donation-button" id="button_2"><label for="button_2">$50</label>
			<li class="default-amounts"><input type="radio" name="donation_amount" value="100" class="peerraiser-donation-button" id="button_3"><label for="button_3">$100</label>
			<li class="default-amounts"><input type="radio" name="donation_amount" value="250" class="peerraiser-donation-button" id="button_4"><label for="button_4">$250</label>
			<li class="default-amounts"><input type="radio" name="donation_amount" value="500" class="peerraiser-donation-button" id="button_5"><label for="button_5">$500</label>
		</ul>
		<div class="row collapse">
			<div class="peerraiser-donation-amount-other">
				<span class="peerraiser-currency-symbol">$</span>
				<input class="peerraiser-donation-input" value="" maxlength="8" type="number" autocomplete="off" placeholder="Other Amount ($10 Min)" data-minimum="10" aria-label="Enter Donation Amount USD Min. $10">
			</div>
		</div>
	</section>
</form>