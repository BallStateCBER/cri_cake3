<?php
/**
 * @var \App\Model\Entity\User $user
 * @var \App\View\AppView $this
 * @var string $resetUrl
 */
?>
<p>
    <?= $user->name ?>,
</p>

<p>
    We received your request to reset your password. If you visit the following URL in the next 24 hours, you will be prompted to enter a new password.
</p>

<p>
    <a href="<?= $resetUrl ?>">
        <?= $resetUrl ?>
    </a>
</p>
