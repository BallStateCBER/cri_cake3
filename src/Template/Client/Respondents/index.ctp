<?php
    use Cake\Validation\Validation;
?>

<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
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
    ) ?>
</p>

<?php if (empty($respondents)): ?>
    <p class="alert alert-info">
        No invitations have been sent out for this questionnaire.
    </p>
<?php else: ?>

    <?= $this->element('pagination') ?>

    <table class="table respondents">
        <thead>
            <tr>
                <th>
                    Respondent
                    <?= $this->Paginator->sort('name', 'name') ?>
                    /
                    <?= $this->Paginator->sort('email', 'email') ?>
                </th>

                <?php if ($surveyType == 'official'): ?>
                    <th>
                        <?= $this->Paginator->sort('approved', 'Approved') ?>
                    </th>
                <?php endif; ?>

                <th>
                    Completed Questionnaire
                </th>
                <th>
                    Completion Date
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($respondents as $respondent): ?>
                <tr>
                    <td>
                        <?= $respondent->name ? $respondent->name : '(No name)' ?>
                        <br />
                        <?php if ($respondent->title): ?>
                            <span class="title">
                                <?= $respondent->title ?>
                            </span>
                            <br />
                        <?php endif; ?>
                        <span class="email">
                            <?php if (Validation::email($respondent->email)): ?>
                                <a href="mailto:<?= $respondent->email ?>">
                                    <?= $respondent->email ?>
                                </a>
                            <?php else: ?>
                                <?= $respondent->email ? $respondent->email : '(No email)' ?>
                            <?php endif; ?>
                        </span>
                    </td>

                    <?php if ($surveyType == 'official'): ?>
                        <td class="boolean_icon">
                            <span class="glyphicon glyphicon-<?= $respondent->approved == 1 ? 'ok' : 'remove' ?>"></span>
                        </td>
                    <?php endif; ?>

                    <td class="boolean_icon">
                        <span class="glyphicon glyphicon-<?= empty($respondent->responses) ? 'remove' : 'ok' ?>"></span>
                    </td>
                    <td>
                        <?php
                            if (isset($respondent->responses[0]['response_date']) && $respondent->responses[0]['response_date'] != null) {
                                $timestamp = strtotime($respondent->responses[0]['response_date']);
                                echo date('F j, Y', $timestamp);
                            }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?= $this->element('pagination') ?>

<?php endif; ?>