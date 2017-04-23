<?php
if ( ! defined( 'ABSPATH' ) ) {
	// prevent direct access to this file
	exit;
}
?>
<div class="wrap">
	<div id="peerraiser-js-message" class="pr_flash-message" style="display:none;">
		<p></p>
	</div>

	<h1 class="wp-heading-inline"><?php printf( esc_html__( 'Donor #%d', 'peerraiser' ), $peerraiser['donor']->ID ); ?></h1>
	<hr class="wp-header-end">

	<form id="peerraiser-add-donor" class="peerraiser-form" action="" method="post">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables peerraiser-metabox">

						<?php do_action( 'peerraiser_before_donor_side_metaboxes' ); ?>

						<div id="submitdiv" class="postbox">
							<h2><span><?php _e( 'Donation Details', 'peerraiser' ); ?></span></h2>
							<div class="inside">
								<div class="submitbox" id="submitpost">
									<div id="misc-publishing-actions">
										<div class="donor-info">
											<div class="donor-date">
												<span class="label"><?php _e( 'Date/Time', 'peerraiser' ); ?></span>
												<strong><?php echo mysql2date( get_option('date_format'), $peerraiser['donor']->date ); ?></strong>
											</div>
										</div>
									</div>
									<div id="major-publishing-actions">
										<div id="delete-action">
											<a class="submitdelete deletion" href="<?php echo add_query_arg( array( 'peerraiser_action' => 'delete_donor', 'donor_id' => $peerraiser['donor']->ID, '_wpnonce' => wp_create_nonce( 'peerraiser_delete_donor_' . $peerraiser['donor']->ID ) ), admin_url( sprintf( 'admin.php?page=peerraiser-donors' ) ) ) ?>"><?php _e( 'Delete', 'peerraiser' ); ?></a>
										</div>
										<div id="publishing-action">
											<span class="spinner"></span>
											<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e( 'Save', 'peerraiser' ); ?>">
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>

						<?php do_action( 'peerraiser_after_donor_side_metaboxes' ); ?>
					</div> <!-- / #side-sortables -->
				</div>
				<div id="postbox-container-2" class="postbox-container peerraiser-metabox">
					<div id="normal-sortables">

						<?php do_action( 'peerraiser_before_donor_card' ); ?>

						<div id="donor-card">
							<img src="<?php echo $peerraiser['profile_image_url']; ?>" alt="Profile Picture" class="profile-image">
							<div class="donor-info">
								<h1><?php echo $peerraiser['donor']->donor_name; ?> <span>#<?php echo $peerraiser['donor']->ID ?></span></h1>
								<div class="donor-meta">
									<?php if ( ! empty( $peerraiser['donor']->email_address ) ) : ?>
										<p class="email"><?php echo $peerraiser['donor']->email_address ?></p>
									<?php endif; ?>

									<p class="since"><?php printf( __( 'Donor since %s', 'peerraiser' ), mysql2date( get_option('date_format'), $peerraiser['donor']->date ) ); ?></p>
									<?php if ( ! empty( $peerraiser['donor']->user_id ) ) : ?>
										<p class="user-account">User Account: <?php echo $peerraiser['donor']->user_id; ?></p>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<?php do_action( 'peerraiser_before_donor_metaboxes' ); ?>

						<div id="donor-summary" class="postbox">
							<h2><span><?php _e( 'Donation Summary', 'peerraiser' ); ?></span></h2>
							<div class="inside">
								<p class="summary"><?php printf( '%s made a donor of <strong>$%.2F</strong> on <strong>%s</strong>', $peerraiser['donor']->donor_name, number_format( $peerraiser['donor']->total, 2 ), mysql2date( get_option('date_format'), $peerraiser['donor']->date ) ); ?></p>
								<table class="transaction-info table table-striped">
									<thead>
									<tr>
										<th colspan="2"><?php _e( 'Allocation', 'peerraiser') ?></th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td><strong><?php _e( 'Campaign', 'peerraiser' ); ?>:</strong></td>
										<?php if ( $campaign_id = $peerraiser['donor']->campaign_id ) : ?>
											<?php $campaign = peerraiser_get_campaign( $campaign_id ) ?>
											<?php if ( empty( $campaign->campaign_name ) ) : ?>
												<td><em><?php _e( 'Deleted', 'peerraiser' ); ?></em></td>
											<?php else : ?>
												<td><a href="admin.php?page=peerraiser-campaigns&view=summary&campaign=<?php echo $campaign->ID; ?>"><?php echo $campaign->campaign_name; ?></a></td>
											<?php endif; ?>
										<?php else : ?>
											<td><?php _e( 'N/A', 'peerraiser' ); ?></td>
										<?php endif; ?>
									</tr>
									<tr>
										<td><strong><?php _e( 'Fundraiser', 'peerraiser' ); ?>:</strong></td>
										<?php if ( $fundraiser_id = $peerraiser['donor']->fundraiser_id ) : ?>
											<?php $fundraiser = peerraiser_get_fundraiser( $fundraiser_id ); ?>
											<td><a href="post.php?action=edit&post=<?php echo $fundraiser->ID; ?>"><?php echo $fundraiser->fundraiser_name; ?></a></td>
										<?php else : ?>
											<td><?php _e( 'N/A', 'peerraiser' ); ?></td>
										<?php endif; ?>
									</tr>
									<tr>
										<td><strong><?php _e( 'Team', 'peerraiser' ); ?>:</strong></td>
										<?php if ( $team_id = $peerraiser['donor']->team_id ) : ?>
											<td><a href="admin.php?page=peerraiser-teams&view=team-details&team=">Team Name</a></td>
										<?php else : ?>
											<td><?php _e( 'N/A', 'peerraiser' ); ?></td>
										<?php endif; ?>
									</tr>
									<tr>
										<td><strong><?php _e( 'Total Donation', 'peerraiser' ); ?>:</strong></td>
										<td><strong>$<?php echo number_format( $peerraiser['donor']->total, 2 ); ?></strong></td>
									</tr>
									</tbody>
								</table>
							</div>
						</div>

						<?php do_action( 'peerraiser_after_donor_metaboxes', $peerraiser ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php wp_nonce_field( 'peerraiser_update_donor_' . $peerraiser['donor']->ID ); ?>
		<input type="hidden" name="donor_id" value="<?php echo $peerraiser['donor']->ID; ?>">
		<input type="hidden" name="peerraiser_action" value="update_donor">
	</form>
</div>
