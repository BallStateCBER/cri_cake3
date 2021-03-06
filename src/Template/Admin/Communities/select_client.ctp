<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $client
 * @var mixed $clients
 * @var mixed $communityId
 * @var mixed $communityName
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
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to Clients',
        [
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'clients',
            $communityId
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-list"></span> Add a New Client',
        [
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'addClient',
            $communityId
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
</p>

<?= $this->Form->create($client) ?>

<?= $this->Form->control(
    'client_id',
    [
        'class' => 'form-control',
        'empty' => true,
        'label' => 'Select Client',
        'options' => $clients
    ]
) ?>

<?= $this->Form->button(
    'Add this client to '.$communityName,
    ['class' => 'btn btn-primary']
) ?>

<?= $this->Form->end() ?>
