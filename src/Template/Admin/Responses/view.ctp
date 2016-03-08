<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to Survey Overview',
        [
            'prefix' => 'admin',
            'controller' => 'Surveys',
            'action' => 'view',
            $community->id,
            $survey->type
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
    <?php if (! empty($responses)): ?>
        <a href="#" class="btn btn-default" id="show_respondents">
            Show respondent info
        </a>
    <?php endif; ?>
</p>

<div id="admin_responses_view">
    <p>
        These are the currently known responses to
        <strong>
            <?= $community->name ?>'s
            community
            <?= $survey->type == 'official' ? 'leadership' : 'organization' ?>
        </strong>
        survey. Incomplete responses are excluded, and recent responses may have not been imported yet.
    </p>

    <section>
        <h2>
            PWR<sup>3</sup> Alignment
        </h2>
        <?php if (empty($responses)): ?>
            <p class="alert alert-info">
                No responses have been imported yet.
            </p>
        <?php else: ?>
            <div>
                <ul class="nav nav-tabs" role="tablist">
                    <li>
                        Compared to:
                    </li>
                    <?php if ($community->local_area): ?>
                        <li role="presentation">
                            <a href="#vsLocalArea" aria-controls="vsLocalArea" role="tab" data-toggle="tab">
                                <?= $community->local_area['name'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($community->parent_area): ?>
                        <li role="presentation">
                            <a href="#vsParentArea" aria-controls="vsParentArea" role="tab" data-toggle="tab">
                                <?= $community->parent_area['name'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content">
                    <?php
                        function getSortArrow($sortField, $params) {
                            if (isset($params['named']['sort']) && $params['named']['sort'] == $sortField) {
                                $direction = strtolower($params['named']['direction']) == 'desc' ? 'up' : 'down';
                                return '<span class="glyphicon glyphicon-arrow-'.$direction.'" aria-hidden="true"></span>';
                            }
                            return '';
                        }
                    ?>
                    <?php if ($community->local_area): ?>
                        <div role="tabpanel" class="tab-pane active" id="vsLocalArea">
                            <?= $this->element('Respondents'.DS.'admin_table', [
                                'area' => $community->local_area,
                                'alignmentField' => 'local_area_pwrrr_alignment'
                            ]) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($community->parent_area): ?>
                        <div role="tabpanel" class="tab-pane" id="vsParentArea">
                            <?= $this->element('Respondents'.DS.'admin_table', [
                                'area' => $community->parent_area,
                                'alignmentField' => 'parent_area_pwrrr_alignment'
                            ]) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section>
        <h2>
            Internal Alignment
        </h2>

        <?php if ($internalAlignment): ?>
            <table class="table" id="internal-alignment-breakdown">
                <thead>
                    <tr>
                        <th>
                            Sector
                        </th>
                        <th>
                            Internal Alignment of Approved Responses
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sectors as $sector): ?>
                        <tr>
                            <th>
                                <?= ucwords($sector) ?>
                            </th>
                            <td>
                                <?= round($internalAlignment[$sector], 3) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>
                            Sum
                        </td>
                        <td>
                            <?= round($internalAlignmentSum, 3) ?>
                            <span class="alignment-note <?= $internalAlignmentClass ?>">
                                <span class="glyphicon"></span>
                                <?= str_replace('-', ' ', $internalAlignmentClass) ?>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p class="alert alert-info">
                Not enough responses have been collected / approved to be able to determine this survey's internal alignment.
            </p>
        <?php endif; ?>
    </section>

    <section>
        <h2>
            Set PWR<sup>3</sup> Alignment &amp; Pass/Fail Status
        </h2>
        <?= $this->Form->create($survey) ?>
        <?php
            if ($survey->alignment_calculated) {
                $alignmentLastSetMsg = '<br />Last modified: ';
                $timestamp = strtotime($survey->alignment_calculated);
                $alignmentLastSetMsg .= date('F j', $timestamp).'<sup>'.date('S', $timestamp).'</sup>'.date(', Y', $timestamp);
            } else {
                $alignmentLastSetMsg = '';
            }
            echo $this->Form->input(
                'alignment',
                [
                    'class' => 'form-control',
                    'div' => [
                        'class' => 'form-group'
                    ],
                    'label' => [
                        'text' => 'Administrator-determined PWR<sup>3</sup> alignment (percent)'.$alignmentLastSetMsg,
                        'escape' => false
                    ],
                    'max' => 100,
                    'min' => 0,
                    'type' => 'number'
                ]
            );
        ?>
        <?= $this->Form->input(
            'alignment_passed',
            [
                'class' => 'form-control',
                'div' => [
                    'class' => 'form-group'
                ],
                'label' => 'Has this community passed its leadership alignment assessment?',
                'options' => [
                    0 => 'Not determined',
                    -1 => 'Failed',
                    1 => 'Passed'
                ],
                'type' => 'select'
            ]
        ) ?>
        <?= $this->Form->button(
            'Update',
            ['class' => 'btn btn-primary']
        ) ?>
        <?= $this->Form->end() ?>
    </section>
</div>

<?php $this->element('script', ['script' => 'admin']); ?>
<?php $this->append('buffered'); ?>
    adminViewResponses.init();
<?php $this->end(); ?>
