<div class="wrap peerraiser" id="peerraiser-settings">

    <h2><?= $headline ?></h2>

    <p><a href="#">Create a Campaign</a></p>

    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Asperiores, deserunt repudiandae aliquid amet, voluptates dolorum et aut similique magni dolor ratione cumque eaque at quis nostrum incidunt repellat. Eaque, cupiditate!</p>

    <?= $fields ?>

    <?php if( !isset($_GET['tab']) || $_GET['tab'] == 'campaigns' ) : ?>

        <!-- Campaign Tab -->

    <?php endif ?>

</div>