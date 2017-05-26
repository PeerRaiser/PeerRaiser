<form>
	<section class="donation-amounts" id="donate-amount">
		<div class="row">
			<label><h3><?php _e( 'Choose a Donation Amount', 'peerraiser' ); ?> </h3></label>
		</div>
		<ul class="small-block-grid-2 medium-block-grid-5 amount-grid" id="amount-grid">
			<li class="default-amounts"><a href="#" data-donation-value="25" class="peerraiser-donation-button">$25</a></li>
			<li class="default-amounts"><a href="#" data-donation-value="50" class="peerraiser-donation-button">$50</a></li>
			<li class="default-amounts"><a href="#" data-donation-value="100" class="peerraiser-donation-button">$100</a></li>
			<li class="default-amounts"><a href="#" data-donation-value="250" class="peerraiser-donation-button">$250</a></li>
			<li class="default-amounts"><a href="#" data-donation-value="500" class="peerraiser-donation-button">$500</a></li>
		</ul>
		<div class="row collapse">
			<div class="peerraiser-prefix-currency-symbol">
				<span class="peerraiser-currency-symbol">$</span>
			</div>
			<div class="peerraiser-donation-input">
				<input value="" class="prefixed" maxlength="8" type="number" autocomplete="off" placeholder="USD Min. $10" data-minimum="10" aria-label="Enter Donation Amount USD Min. $10">
			</div>
		</div>
	</section>
</form>