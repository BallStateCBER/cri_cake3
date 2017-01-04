<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<section>
    <h2>
        OCRA Report
        <?php $icon = '<img src="/data_center/img/icons/document-excel-table.png" alt="Microsoft Excel (.xlsx)" />'; ?>
        <?= $this->Html->link(
            $icon . ' Download',
            ['action' => 'ocra'],
            [
                'class' => 'btn btn-sm btn-default',
                'escape' => false,
                'title' => 'Download an OCRA version of this report as a Microsoft Excel (.xlsx) file'
            ]
        ) ?>
    </h2>
    <p>
        The OCRA Report excludes PWR<sup>3</sup> and internal alignment calculations, but is otherwise the same as
        the admin version of the report.
    </p>
</section>

<section>
    <h2>
        Admin Report
        <?= $this->Html->link(
            $icon . ' Download',
            ['action' => 'admin'],
            [
                'class' => 'btn btn-sm btn-default',
                'escape' => false,
                'title' => 'Download the version of this report for CRI administrators as a Microsoft Excel (.xlsx) file'
            ]
        ) ?>
    </h2>
    <p>
        The admin report can be viewed in your browser below or downloaded as a spreadsheet. Below, click on each survey
        type to expand and see more details, and click on the notes icon
        (<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>) to view notes related to a community.
    </p>
    <p>
        <strong>Communities in bold</strong> have had activity in the last 30 days. Click on the calendar icon
        (<span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>) to view details.
    </p>

    <table class="table" id="report">
        <colgroup>
            <col span="1" />
        </colgroup>
        <colgroup class="survey">
            <col span="13" />
        </colgroup>
        <colgroup class="survey">
            <col span="12" />
        </colgroup>
        <thead>
            <tr class="survey-group-header">
                <td colspan="3"></td>
                <th colspan="14" data-full-colspan="14" data-survey-type="officials" class="survey">
                    <button class="survey-toggler btn btn-link" data-survey-type="officials">
                        Community Leadership
                    </button>
                </th>
                <th colspan="13" data-full-colspan="13" data-survey-type="organizations" class="survey">
                    <button class="survey-toggler btn btn-link" data-survey-type="organizations">
                        Community Organizations
                    </button>
                </th>
            </tr>
            <tr class="internal-alignment-headers">
                <td colspan="1"></td>
                <td colspan="1" class="spacer"></td>
                <td colspan="4" data-survey-type="officials" class="empty"></td>
                <th colspan="6" data-survey-type="officials">
                    Internal Alignment
                </th>
                <td colspan="3" data-survey-type="officials" class="empty"></td>
                <td colspan="4" data-survey-type="organizations" class="empty"></td>
                <th colspan="6" data-survey-type="organizations">
                    Internal Alignment
                </th>
                <td colspan="2" data-survey-type="organizations" class="empty"></td>
            </tr>
            <tr class="general-header">
                <th data-sort="string">
                    Community
                </th>
                <?= $this->Reports->surveyHeader($sectors, 'officials'); ?>
                <?= $this->Reports->surveyHeader($sectors, 'organizations'); ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report as $communityId => $community): ?>
                <tr class="<?= $community['recentActivity'] ? 'active' : null ?>">
                    <td>
                        <?= $community['name'] ?>
                        <?php if ($community['notes']): ?>
                            <button type="button" class="btn btn-link notes" data-toggle="modal" data-target="#notes-modal" title="View notes" data-community-id="<?= $communityId ?>" data-community-name="<?= $community['name'] ?>">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </button>
                        <?php endif; ?>
                        <?php if ($community['recentActivity']): ?>
                            <button type="button" class="btn btn-link recent-activity" data-toggle="modal" data-target="#notes-modal" title="View recent activity" data-community-id="<?= $communityId ?>" data-community-name="<?= $community['name'] ?>">
                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </button>
                        <?php endif; ?>
                        <br />
                        <span class="area-details">
                            <?= $community['parentArea'] ?>
                        </span>
                        <br />
                        <span class="area-details">
                            <?= $community['parentAreaFips'] ?>
                        </span>

                        <?php if ($community['recentActivity']): ?>
                            <?php
                                $community['recentActivity'] = array_slice($community['recentActivity'], 0, 5);
                                $count = count($community['recentActivity']);
                            ?>
                            <div class="recent-activity hidden-modal-content" data-community-id="<?= $communityId ?>">
                                <p>
                                    <?php if ($count > 1): ?>
                                        The <?= $count ?> most recent updates
                                    <?php elseif ($count == 1): ?>
                                        The only update
                                    <?php endif; ?>
                                    to <?= $community['name'] ?> in the last 30 days:
                                </p>
                                <ul>
                                    <?php foreach ($community['recentActivity'] as $activityRecord): ?>
                                        <li>
                                            <strong>
                                                <?= $this->Time->format(
                                                    $activityRecord->created,
                                                    'MMM d Y, h:mma',
                                                    false,
                                                    'America/New_York'
                                                ) ?>
                                            </strong>
                                            -
                                            <?= $this->ActivityRecords->event($activityRecord) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <p>
                                    <?= $this->Html->link(
                                        'View all activity associated with ' . $community['name'],
                                        [
                                            'prefix' => 'admin',
                                            'controller' => 'ActivityRecords',
                                            'action' => 'community',
                                            $communityId
                                        ]
                                    ) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </td>

                    <?php $survey = $community['official_survey']; ?>
                    <td class="survey" data-survey-type="officials" <?= $this->Reports->sortValue($survey['invitations']) ?>>
                        <?= $survey['invitations'] ?>
                    </td>
                    <td class="survey" data-survey-type="officials" <?= $this->Reports->sortValue($survey['responses']) ?>>
                        <?= $survey['responses'] ?>
                    </td>
                    <td class="survey" data-survey-type="officials" <?= $this->Reports->sortValue($survey['responseRate']) ?>>
                        <?= $survey['responseRate'] ?>
                    </td>
                    <td class="survey" data-survey-type="officials" <?= $this->Reports->sortValue($survey['alignment']) ?>>
                        <?= $survey['alignment'] ?>
                    </td>
                    <?php foreach ($sectors as $sector): ?>
                        <td class="survey" data-survey-type="officials" <?= $this->Reports->sortValue($survey['internalAlignment'][$sector]) ?>>
                            <?= $survey['internalAlignment'][$sector] ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="survey" data-survey-type="officials" <?= $this->Reports->sortValue($survey['internalAlignment']['total']) ?>>
                        <?= $survey['internalAlignment']['total'] ?>
                    </td>
                    <td class="survey" data-survey-type="officials">
                        <?= $community['presentationsGiven']['a'] ?>
                    </td>
                    <td class="survey" data-survey-type="officials">
                        <?= $community['presentationsGiven']['b'] ?>
                    </td>
                    <td class="survey-status">
                        <?= $survey['status'] ?>
                    </td>

                    <?php $survey = $community['organization_survey']; ?>
                    <td class="survey" data-survey-type="organizations" <?= $this->Reports->sortValue($survey['invitations']) ?>>
                        <?= $survey['invitations'] ?>
                    </td>
                    <td class="survey" data-survey-type="organizations" <?= $this->Reports->sortValue($survey['responses']) ?>>
                        <?= $survey['responses'] ?>
                    </td>
                    <td class="survey" data-survey-type="organizations" <?= $this->Reports->sortValue($survey['responseRate']) ?>>
                        <?= $survey['responseRate'] ?>
                    </td>
                    <td class="survey" data-survey-type="organizations" <?= $this->Reports->sortValue($survey['alignment']) ?>>
                        <?= $survey['alignment'] ?>
                    </td>
                    <?php foreach ($sectors as $sector): ?>
                        <td class="survey" data-survey-type="organizations" <?= $this->Reports->sortValue($survey['internalAlignment'][$sector]) ?>>
                            <?= $survey['internalAlignment'][$sector] ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="survey" data-survey-type="organizations" <?= $this->Reports->sortValue($survey['internalAlignment']['total']) ?>>
                        <?= $survey['internalAlignment']['total'] ?>
                    </td>
                    <td class="survey" data-survey-type="organizations">
                        <?= $community['presentationsGiven']['c'] ?>
                    </td>
                    <td class="survey-status">
                        <?= $survey['status'] ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php $this->append('top-html'); ?>
    <div class="modal fade" id="notes-modal" tabindex="-1" role="dialog" aria-labelledby="notes-modal-label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="notes-modal-label">Modal title</h4>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
<?php $this->end(); ?>

<?php $this->Html->script('stupidtable.min', ['block' => 'scriptBottom']); ?>
<?php $this->append('buffered'); ?>
    adminReport.notes = <?= json_encode($notes) ?>;
    adminReport.init();
<?php $this->end(); ?>
