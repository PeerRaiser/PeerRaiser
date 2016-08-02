<div class="peerraiser-dashboard">
    <nav class="peerraiser-nav">
        <ul>
            <?php foreach ( $peerraiser['navigation'] as $key => $value ) : ?>
                <li class="<?= sanitize_title( $key ) ?>"><a href="?page=<?= $key ?>"><?= $value ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
</div>