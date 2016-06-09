<?php
    use Cake\Utility\Inflector;
    $displayedName = $type == 'Official' ? 'Leadership' : 'Organizations';
    $associationName = strtolower($type).'_survey';
    $smUrl = isset($community[$associationName]['sm_url']) ? $community[$associationName]['sm_url'] : null;
    $surveyId = isset($community[$associationName]['id']) ? $community[$associationName]['id'] : null;
?>
<div class="link_survey form-group">
    <strong>
        Community <?= $displayedName ?> Questionnaire:
    </strong>

    <div class="link_label">
        <?php if ($smUrl): ?>
            <span class="label label-success">
                Linked
            </span>
        <?php else: ?>
            <span class="label label-danger">
                Not linked
            </span>
        <?php endif; ?>
    </div>

    <div class="link_status">
        <?php if ($smUrl): ?>
            <a href="<?= $smUrl ?>">
                <?= $smUrl ?>
            </a>
        <?php endif; ?>
    </div>

    <ul class="actions">
        <li>
            <button class="lookup btn btn-default btn-sm">
                Select questionnaire
            </button>
        </li>
        <li>
            <button class="show_details btn btn-default btn-sm">
                Show details
            </button>
        </li>
        <?php if ($surveyId): ?>
            <li>
                <?= $this->Html->link(
                    'Go to questionnaire overview <span class="glyphicon glyphicon-share-alt"></span>',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Surveys',
                        'action' => 'view',
                        $surveyId
                    ],
                    [
                        'class' => 'btn btn-default btn-sm',
                        'escape' => false
                    ],
                    'Go to questionnaire overview page without updating community?'
                ) ?>
            </li>
        <?php endif; ?>
    </ul>

    <div class="lookup_results well"></div>

    <div class="details well">
        <?php
            echo $this->Form->hidden("$associationName.id");
            echo $this->Form->hidden("$associationName.type");
            echo $this->Form->hidden("$associationName.community_id");
        ?>

        <table class="table">
            <?php
                $this->Form->templates($tableTemplate);
                echo $this->Form->input(
                    "$associationName.sm_id",
                    [
                        'class' => 'form-control survey_sm_id',
                        'label' => 'SurveyMonkey Survey ID',
                        'type' => 'number'
                    ]
                );
                echo $this->Form->input(
                    "$associationName.sm_url",
                    [
                        'class' => 'form-control survey_url',
                        'label' => 'SurveyMonkey Survey URL'
                    ]
                );
                foreach ($qnaIdFields as $qnaIdField) {
                    $label = Inflector::humanize($qnaIdField);
                    $label = str_ireplace('qid', 'Question ID', $label);
                    $label = str_ireplace('aid', 'Answer ID', $label);
                    $label = str_ireplace('pwrrr', 'PWR<sup>3</sup>&trade;', $label);
                    echo $this->Form->input(
                        "$associationName.$qnaIdField",
                        [
                            'class' => 'form-control',
                            'data-fieldname' => $qnaIdField,
                            'label' => [
                                'escape' => false,
                                'text' => $label
                            ],
                            'type' => 'number'
                        ]
                    );
                }

                // Return to default Bootstrap form template
                $this->Form->templates('bootstrap_form');
            ?>
        </table>
    </div>
</div>