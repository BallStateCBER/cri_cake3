<?php
/**
 * @var \App\View\AppView $this
 * @var array $community
 * @var mixed $surveyType
 */
?>
<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> ' .
        'Back to Questionnaire Overview',
        [
            'prefix' => 'admin',
            'controller' => 'Surveys',
            'action' => 'view',
            $community['slug'],
            $surveyType
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
</p>
