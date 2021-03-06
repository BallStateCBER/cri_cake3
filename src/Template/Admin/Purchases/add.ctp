<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Purchase $purchase
 * @var string $titleForLayout
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to Purchase Records',
        [
            'prefix' => 'admin',
            'controller' => 'Purchases',
            'action' => 'index'
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
</p>

<p>
    You will need to add a payment record if a community pays for one of the CRI products
    by a method <em>other than</em> through the CRI website, such as with a check. Please include
    information about how this payment was received in the <strong>notes</strong> section.
</p>

<?= $this->Form->create($purchase) ?>

<?= $this->Form->control('community_id', [
    'class' => 'form-control',
    'empty' => true
]) ?>

<?= $this->Form->control('product_id', ['class' => 'form-control']) ?>

<?= $this->Form->control('source', [
    'class' => 'form-control',
    'empty' => true,
    'label' => 'Funding Source'
]) ?>

<?= $this->Form->control('notes', ['class' => 'form-control']) ?>

<?= $this->Form->button(
    'Add Payment Record',
    ['class' => 'btn btn-primary']
) ?>

<?= $this->Form->end() ?>
