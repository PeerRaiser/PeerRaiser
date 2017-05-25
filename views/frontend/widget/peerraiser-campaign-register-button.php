<?php
echo $args['before_widget'];
printf( '<a href="%1$s?campaign=%2$s">%3$s</a>', $peerraiser['donation_page'], $campaign->campaign_slug, $peerraiser['button_label'] );
echo $args['after_widget'];