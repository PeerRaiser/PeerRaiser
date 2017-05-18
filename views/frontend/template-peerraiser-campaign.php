<?php
/**
 * The template for displaying the PeerRaiser Campaign
 */
?>

<?php get_header(); ?>

<?php $campaign = peerraiser_get_current_campaign(); ?>

<h1><?php echo $campaign->campaign_name; ?></h1>

<p><?php echo $campaign->campaign_description; ?></p>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
