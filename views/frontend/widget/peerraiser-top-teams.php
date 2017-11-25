<?php echo $args['before_widget']; ?>

<?php if ( ! empty( $instance['title'] ) ) : ?>
    <?php echo $args['before_title']; ?>
        <?php echo $instance['title']; ?>
    <?php echo $args['after_title']; ?>
<?php endif; ?>
<?php if ( ! empty( $top_teams ) ):  ?>
    <ol>
        <?php foreach ( $top_teams as $team ) : ?>
            <li><a href="<?php echo $team->get_permalink(); ?>"><?php echo $team->team_name; ?></a> (<?php echo peerraiser_money_format( $team->donation_value ); ?>)</li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<?php echo $args['after_widget']; ?>