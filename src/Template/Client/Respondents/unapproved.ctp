<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Community $community
 * @var \App\Model\Entity\Survey $survey
 * @var array $respondents
 * @var string $titleForLayout
 */
?>
<?php use Cake\Validation\Validation; ?>

<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?php
        if ($this->request->getParam('prefix') == 'admin') {
            echo $this->Html->link(
                '<span class="glyphicon glyphicon-arrow-left"></span> Back to Questionnaire Overview',
                [
                    'prefix' => 'admin',
                    'controller' => 'Surveys',
                    'action' => 'view',
                    $community->slug,
                    $survey->type
                ],
                [
                    'class' => 'btn btn-default',
                    'escape' => false
                ]
            );
        } else {
            echo $this->Html->link(
                '<span class="glyphicon glyphicon-arrow-left"></span> Back to Client Home',
                [
                    'prefix' => 'client',
                    'controller' => 'Communities',
                    'action' => 'index'
                ],
                [
                    'class' => 'btn btn-default',
                    'escape' => false
                ]
            );
        }
    ?>
</p>

<div id="unapproved_respondents">
    <?php if (empty($respondents['unaddressed'])): ?>
        <div class="alert alert-success" role="alert">
            All uninvited responses have been addressed.
        </div>
    <?php else: ?>

        <p>
            The following email addresses correspond to questionnaire responses that we've received that don't match up with anyone you invited, ordered from most recent to oldest.
        </p>

        <p>
            If you approve these responses, they will be used as part of your community's alignment calculation.
        </p>

        <?php if (count($respondents['unaddressed']) > 1): ?>
            <p id="bulk-actions">
                <button class="btn btn-default" data-action="approve">
                    Approve all
                </button>
                <button class="btn btn-default" data-action="dismiss">
                    Dismiss all
                </button>
            </p>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>
                        Email
                    </th>
                    <th>
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($respondents['unaddressed'] as $respondent): ?>
                    <tr>
                        <td>
                            <?php if ($respondent['name']): ?>
                                <?= $respondent['name'] ?>
                            <?php else: ?>
                                <span class="no_name">
                                    No name provided
                                </span>
                            <?php endif; ?>
                            <br />
                            <?php if (Validation::email($respondent['email'])): ?>
                                <a href="mailto:<?= $respondent['email'] ?>">
                                    <?= $respondent['email'] ?>
                                </a>
                            <?php else: ?>
                                <?= $respondent['email'] ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="actions">
                                <?= $this->Html->link(
                                    'Approve',
                                    [
                                        $this->request->getParam('prefix') => true,
                                        'controller' => 'Respondents',
                                        'action' => 'approveUninvited',
                                        $respondent['id']
                                    ],
                                    [
                                        'class' => 'btn btn-default approve',
                                        'data-respondent-id' => $respondent['id']
                                    ]
                                ) ?>
                                <?= $this->Html->link(
                                    'Dismiss',
                                    [
                                        $this->request->getParam('prefix') => true,
                                        'controller' => 'Respondents',
                                        'action' => 'dismissUninvited',
                                        $respondent['id']
                                    ],
                                    [
                                        'class' => 'btn btn-default dismiss',
                                        'data-respondent-id' => $respondent['id']
                                    ]
                                ) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if (! empty($respondents['dismissed'])): ?>
    <div id="dismissed_respondents">
        <h2>
            <button id="toggle_dismissed" class="btn btn-default">
                <?= count($respondents['dismissed']) ?>
                Dismissed
                <?= __n('Response', 'Responses', count($respondents['dismissed'])) ?>
            </button>
        </h2>

        <div>
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            Email
                        </th>
                        <th>
                            Actions
                        </th>
                    </tr>
                </thead>
                <?php foreach ($respondents['dismissed'] as $respondent): ?>
                    <tr>
                        <td>
                            <?php if ($respondent['name']): ?>
                                <?= $respondent['name'] ?>
                                <br />
                            <?php endif; ?>
                            <?php if (Validation::email($respondent['email'])): ?>
                                <a href="mailto:<?= $respondent['email'] ?>">
                                    <?= $respondent['email'] ?>
                                </a>
                            <?php else: ?>
                                <?= $respondent['email'] ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="actions">
                                <?= $this->Html->link(
                                    'Approve',
                                    [
                                        'prefix' => 'client',
                                        'controller' => 'Respondents',
                                        'action' => 'approveUninvited',
                                        $respondent['id']
                                    ],
                                    [
                                        'class' => 'btn btn-default approve',
                                        'data-respondent-id' => $respondent['id']
                                    ]
                                ) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php $this->element('script', ['script' => 'client/unapproved-respondents']); ?>
<?php $this->append('buffered'); ?>
    unapprovedRespondents.init();
<?php $this->end(); ?>
