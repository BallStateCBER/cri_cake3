<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Survey $survey
 * @var \App\Model\Entity\Community $community
 * @var mixed $qnaIdFields
 * @var string $titleForLayout
 * @var string $warning
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?= $this->element('back_to_overview_link', [
    'communityId' => $community['id'],
    'surveyType' => $survey['type']
]) ?>

<p>
    After a CRI questionnaire is created in <a href="http://surveymonkey.com/">SurveyMonkey</a>, you must create a link between the
    community and the new questionnaire in order to enable questionnaire invitations and response analysis.
</p>

<div class="survey_overview">
    <?php
        $tableTemplate = [
            'formGroup' => '{{label}}</td><td>{{input}}',
            'inputContainer' => '<tr><td class="form-group {{type}}{{required}}">{{content}}</td></tr>',
            'inputContainerError' => '<tr><td class="form-group {{type}}{{required}}">{{content}}{{error}}</td></tr>'
        ] + require(ROOT.DS.'config'.DS.'bootstrap_form.php');
        $this->Form->templates($tableTemplate);
    ?>

    <div class="panel panel-default link_survey">
        <div class="panel-heading">
            <h3 class="panel-title">
                Link
            </h3>
        </div>
        <div class="panel-body">
            <p>
                Status:
                <span class="link_status">
                    <?php if ($survey['sm_url']): ?>
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Linked
                    <?php else: ?>
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Not linked
                    <?php endif; ?>
                </span>
            </p>

            <p>
                Questionnaire URL:
                <span class="survey_url">
                    <?php if ($survey['sm_url']): ?>
                        <a href="<?= $survey['sm_url'] ?>">
                            <?= $survey['sm_url'] ?>
                        </a>
                    <?php else: ?>
                        unknown
                    <?php endif; ?>
                </span>
            </p>

            <p class="loading_messages">
            </p>

            <?= $this->Form->create($survey) ?>

            <ul class="actions" id="survey-link-buttons" data-is-new="<?= $survey->isNew() ? 1 : 0 ?>">
                <li>
                    <button class="lookup btn btn-default">
                        Select Questionnaire
                    </button>
                </li>
                <li>
                    <button class="show_details btn btn-default">
                        Show Details
                    </button>
                </li>
                <li>
                    <?= $this->Form->button(
                        $survey->isNew() ? 'Link Questionnaire' : 'Update Link',
                        [
                            'class' => 'btn btn-primary',
                            'id' => 'survey-link-submit'
                        ]
                    ) ?>
                </li>
                <?php if ($survey->isNew()): ?>
                    <li class="checkbox">
                        <label for="activateSurvey">
                            <input type="checkbox" name="active" value="1" id="activateSurvey" />
                            Also activate questionnaire
                        </label>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="lookup_results well"></div>

            <div class="details well">
                <?php
                    echo $this->Form->hidden('id');
                    echo $this->Form->hidden('type', ['id' => 'surveyType']);
                    echo $this->Form->hidden('community_id');
                ?>

                <table class="table">
                    <?php
                        $this->Form->templates($tableTemplate);
                        echo $this->Form->control(
                            'sm_id',
                            [
                                'label' => 'SurveyMonkey Survey ID',
                                'type' => 'number'
                            ]
                        );
                        echo $this->Form->control(
                            'sm_url',
                            [
                                'label' => 'SurveyMonkey Survey URL'
                            ]
                        );
                        use Cake\Utility\Inflector;
                        foreach ($qnaIdFields as $qnaIdField) {
                            $label = Inflector::humanize($qnaIdField);
                            $label = str_ireplace('qid', 'Question ID', $label);
                            $label = str_ireplace('aid', 'Answer ID', $label);
                            $label = str_ireplace('pwrrr', 'PWR<sup>3</sup>&trade;', $label);
                            echo $this->Form->control(
                                $qnaIdField,
                                [
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
            <?php if ($warning): ?>
                <p class="alert alert-warning">
                    <?= $warning ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<?php $this->element('script', ['script' => 'admin/survey-link']); ?>
<?php $this->append('buffered'); ?>
    surveyLink.init({
        community_id: <?= $community->id ?>,
        survey_type: '<?= $survey->type ?>'
    });
<?php $this->end(); ?>
