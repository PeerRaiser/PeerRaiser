<div id="donor-card">
    <img src="<?= $peerraiser['profile_image_url'] ?>" alt="Profile Picture" class="<?= $peerraiser['donor_class'] ?> profile-image">
    <div class="donor-info">
        <h1><?= $peerraiser['first_name'] ?> <?= $peerraiser['last_name'] ?> <span>#<?= $peerraiser['donor_id'] ?></span></h1>
        <div class="donor-meta">
            <p class="email"><?= $peerraiser['donor_email'] ?></p>
            <p class="since">Donor since <?= $peerraiser['donor_since'] ?></p>
            <p class="user-account">User Account: <?= $peerraiser['donor_user_account'] ?></p>
        </div>
    </div>
    <a href="post.php?action=edit&post=<?= $peerraiser['donor_id'] ?>" class="donor-link btn">View Donor Record</a>
</div>