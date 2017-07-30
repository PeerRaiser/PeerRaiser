<h1><?php echo $headline; ?></h1>

<?php if ( ! empty( $choices ) ) : ?>
<ul class="peerraiser-fundraising-choices">
    <?php foreach ( $choices as $key => $value ) : ?>
        <li class="<?php echo $key; ?>"><a href="./<?php echo $key; ?>"><?php echo $value; ?></a></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>