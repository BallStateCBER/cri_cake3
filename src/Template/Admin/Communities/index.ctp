<div id="communities_admin_index">
    <div class="page-header">
        <h1>
            <?= $titleForLayout ?>
        </h1>
    </div>

    <p>
        <?php foreach ($buttons as $groupLabel => $buttonGroup): ?>
            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <?= $groupLabel ?>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <?php foreach ($buttonGroup as $label => $filters): ?>
                        <li>
                            <?= $this->Html->link($label, compact('filters')) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <button class="btn btn-default" id="search_toggler">
            <span class="glyphicon glyphicon-search"></span>
            Search
        </button>

        <?= $this->Html->link(
            '<img src="/data_center/img/icons/document-excel-table.png" alt="Microsoft Excel (.xlsx)" /> Download',
            ['action' => 'spreadsheet'],
            [
                'class' => 'btn btn-default',
                'escape' => false,
                'title' => 'Download this page as a Microsoft Excel (.xlsx) file'
            ]
        ) ?>

        <button class="btn btn-link" id="glossary_toggler">
            Icon Glossary
        </button>

        <?= $this->Html->link(
            'Add Community',
            [
                'prefix' => 'admin',
                'action' => 'add'
            ],
            ['class' => 'btn btn-success']
        ) ?>
    </p>

    <div class="alert alert-info" id="glossary">
        <table>
            <tbody>
                <tr>
                    <td>
                        <span class="glyphicon glyphicon-road fast_track" aria-hidden="true"></span> :
                    </td>
                    <td>
                        Fast track
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span> :
                    </td>
                    <td>
                        Community has passed its alignment test
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> :
                    </td>
                    <td>
                        Community has failed its alignment test
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="display: none;" class="input-group" id="admin_community_search_form">
        <div class="input-group-addon">
            <span class="glyphicon glyphicon-search"></span>
        </div>
        <input type="text" name="search" class="form-control" placeholder="Enter community name" />
    </div>

    <?php if (isset($this->request->query['search'])): ?>
        <p class="alert alert-info" id="search_term">
            Search term: <strong><?= $this->request->query['search'] ?></strong>
            <?= $this->Html->link(
                'clear search',
                [
                    'prefix' => 'admin',
                    'controller' => 'Communities',
                    'action' => 'index',
                    '?' => []
                ]
            ) ?>
        </p>
    <?php endif; ?>

    <?= $this->element('pagination') ?>

    <table class="table communities">
        <thead>
            <tr>
                <?php
                    function getSortArrow($sortField, $query) {
                        if (isset($query['sort']) && $query['sort'] == $sortField) {
                            $direction = strtolower($query['direction']) == 'desc' ? 'up' : 'down';
                            return '<span class="glyphicon glyphicon-arrow-'.$direction.'" aria-hidden="true"></span>';
                        }
                        return '';
                    }
                ?>
                <th>
                    <?php
                        $arrow = getSortArrow('Communities.name', $this->request->query);
                        echo $this->Paginator->sort('Communities.name', 'Community'.$arrow, ['escape' => false]);
                    ?>
                    /
                    <?php
                        $arrow = getSortArrow('ParentArea.name', $this->request->query);
                        echo $this->Paginator->sort('ParentAreas.name', 'Area'.$arrow, ['escape' => false]);
                    ?>
                </th>
                <th>
                    Stage
                </th>
                <th>
                    Officials Questionnaire
                </th>
                <th>
                    Organizations Questionnaire
                </th>
                <th class="actions">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($communities)): ?>
                <tr>
                    <td colspan="4" class="no_results">
                        No communities found matching the specified parameters
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($communities as $community): ?>
                <tr data-community-name="<?= $community->name ?>">
                    <td>
                        <?= $community->name ?>
                        <br />
                        <span class="area_name">
                            <?= $community->parent_area['name'] ?>
                        </span>
                    </td>
                    <td>
                        <?= str_replace('.0', '', $community->score) ?>
                        <?php if ($community->fast_track): ?>
                            <span class="glyphicon glyphicon-road fast_track" aria-hidden="true" title="Fast Track"></span>
                        <?php endif; ?>
                    </td>

                    <?php foreach (['official_survey', 'organization_survey'] as $surveyType): ?>
                        <td>
                            <div class="dropdown">
                                <?php if (isset($community->{$surveyType}['sm_id']) && $community->{$surveyType}['sm_id']): ?>
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                        Running <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li class="dropdown-header">
                                            <?php if ($community->{$surveyType}['alignment'] === null): ?>
                                                Alignment: Not set
                                            <?php else: ?>
                                                Alignment: <?php echo $community->{$surveyType}['alignment']; ?>%
                                                <?php if ($community->{$surveyType}['alignment_passed'] == -1): ?>
                                                    <span class="glyphicon glyphicon-remove-sign" aria-hidden="true" title="Failed to pass"></span>
                                                <?php elseif ($community->{$surveyType}['alignment_passed'] == 1): ?>
                                                    <span class="glyphicon glyphicon-ok-sign" aria-hidden="true" title="Passed"></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </li>

                                        <?php if ($community->{$surveyType}['respondents_last_modified_date']): ?>
                                            <li class="dropdown-header">
                                                Last response:
                                                <?= $community->{$surveyType}['respondents_last_modified_date']->format('n/j/Y') ?>
                                            </li>
                                        <?php endif; ?>

                                        <li role="separator" class="divider"></li>

                                        <li>
                                            <?= $this->Html->link(
                                                '<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> Overview',
                                                [
                                                    'prefix' => 'admin',
                                                    'controller' => 'Surveys',
                                                    'action' => $community->{$surveyType}['sm_id'] ? 'view' : 'link',
                                                    $community->id,
                                                    str_replace('_survey', '', $surveyType)
                                                ],
                                                ['escape' => false]
                                            ) ?>
                                        </li>
                                        <li>
                                            <?= $this->Html->link(
                                                '<span class="glyphicon glyphicon-link" aria-hidden="true"></span> Questionnaire link',
                                                [
                                                    'prefix' => 'admin',
                                                    'controller' => 'Surveys',
                                                    'action' => 'link',
                                                    $community->id,
                                                    str_replace('_survey', '', $surveyType)
                                                ],
                                                ['escape' => false]
                                            ) ?>
                                        </li>
                                        <li>
                                            <?= $this->Html->link(
                                                '<span class="glyphicon glyphicon-send" aria-hidden="true"></span> Invitations',
                                                [
                                                    'prefix' => 'admin',
                                                    'controller' => 'Surveys',
                                                    'action' => 'invite',
                                                    $community->{$surveyType}['id']
                                                ],
                                                ['escape' => false]
                                            ) ?>
                                        </li>
                                        <li>
                                            <?= $this->Html->link(
                                                '<span class="glyphicon glyphicon-alert" aria-hidden="true"></span> Reminders',
                                                [
                                                    'prefix' => 'admin',
                                                    'controller' => 'Surveys',
                                                    'action' => 'remind',
                                                    $community->{$surveyType}['id']
                                                ],
                                                ['escape' => false]
                                            ) ?>
                                        </li>
                                        <li>
                                            <?= $this->Html->link(
                                                '<span class="glyphicon glyphicon-scale" aria-hidden="true"></span> Alignment',
                                                [
                                                    'prefix' => 'admin',
                                                    'controller' => 'Responses',
                                                    'action' => 'view',
                                                    $community->{$surveyType}['id']
                                                ],
                                                ['escape' => false]
                                            ) ?>
                                        </li>
                                    </ul>
                                <?php else: ?>
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                        Not set up <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li>
                                            <?= $this->Html->link(
                                                '<span class="glyphicon glyphicon-link" aria-hidden="true"></span> Link to SurveyMonkey questionnaire',
                                                [
                                                    'prefix' => 'admin',
                                                    'controller' => 'Surveys',
                                                    'action' => 'link',
                                                    $community->id,
                                                    str_replace('_survey', '', $surveyType)
                                                ],
                                                ['escape' => false]
                                            ) ?>
                                        </li>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </td>
                    <?php endforeach; ?>

                    <td class="actions">
                        <div class="dropdown">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                Actions <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <?= $this->Html->link(
                                        '<span class="glyphicon glyphicon-tasks" aria-hidden="true"></span> Progress',
                                        [
                                            'prefix' => 'admin',
                                            'action' => 'progress',
                                            $community->id
                                        ],
                                        ['escape' => false]
                                    ) ?>
                                </li>
                                <li>
                                    <?= $this->Html->link(
                                        '<span class="glyphicon glyphicon-user" aria-hidden="true"></span> Clients ('.count($community->clients).')',
                                        [
                                            'prefix' => 'admin',
                                            'action' => 'clients',
                                            $community->id
                                        ],
                                        ['escape' => false]
                                    ) ?>
                                </li>
                                <?php if (! empty($community->clients)): ?>
                                    <li>
                                        <?= $this->Html->link(
                                            '<span class="glyphicon glyphicon-home" aria-hidden="true"></span> Client Home',
                                            [
                                                'prefix' => 'admin',
                                                'action' => 'clienthome',
                                                $community->id
                                            ],
                                            ['escape' => false]
                                        ) ?>
                                    </li>
                                <?php endif; ?>

                                <li>
                                    <?= $this->Html->link(
                                        '<span class="glyphicon glyphicon-stats" aria-hidden="true"></span> Performance Charts',
                                        [
                                            'prefix' => false,
                                            'action' => 'view',
                                            $community->id
                                        ],
                                        ['escape' => false]
                                    ) ?>
                                </li>
                                <li>
                                    <?= $this->Html->link(
                                        '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit Community',
                                        [
                                            'prefix' => 'admin',
                                            'action' => 'edit',
                                            $community->id
                                        ],
                                        ['escape' => false]
                                    ) ?>
                                </li>
                                <li>
                                    <?= $this->Form->postLink(
                                        '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete Community',
                                        [
                                            'prefix' => 'admin',
                                            'action' => 'delete',
                                            $community->id
                                        ],
                                        [
                                            'confirm' => "Are you sure you want to delete {$community->name}? This cannot be undone.",
                                            'escape' => false
                                        ]
                                    ); ?>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?= $this->element('pagination') ?>
</div>
<?php
    $this->element('script', ['script' => 'admin']);
    echo $this->element('DataCenter.jquery_ui');
?>

<?php $this->append('buffered'); ?>
    adminCommunitiesIndex.init();
<?php $this->end();