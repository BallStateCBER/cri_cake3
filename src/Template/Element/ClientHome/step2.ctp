<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Community $community
 * @var mixed $autoImportFrequency
 * @var array $criteria
 * @var array $importErrors
 * @var mixed $officialResponsesChecked
 * @var mixed $officialSurveyId
 * @var mixed $optOuts
 * @var array $purchaseUrls
 * @var mixed $score
 * @var array $step2SurveyPurchased
 * @var array $surveyExists
 * @var array $surveyIsActive
 * @var array $surveyIsComplete
 */
?>
<?= $this->ClientHome->tbodyForStep(2, $score) ?>
    <tr>
        <th colspan="3">
            <button class="step-header step-header-expandable">
                Step Two: Leadership Alignment Assessment
            </button>
        </th>
    </tr>

    <?= $this->ClientHome->surveyReadyRow([
        'description' => $criteria[2]['survey_created'][0],
        'onCurrentStep' => ($score == 2),
        'surveyActive' => $surveyIsActive['official'],
        'surveyComplete' => $surveyIsComplete['official'],
        'surveyExists' => $surveyExists['official']
    ]) ?>

    <?= $this->ClientHome->invitationRow([
        'surveyId' => $officialSurveyId,
        'description' => $criteria[2]['invitations_sent'][0],
        'invitationsSent' => $criteria[2]['invitations_sent'][1],
        'surveyActive' => $surveyIsActive['official'],
        'respondentType' => 'officials'
    ]) ?>

    <?= $this->ClientHome->responsesRow([
        'autoImportFrequency' => $autoImportFrequency,
        'description' => $criteria[2]['responses_received'][0],
        'importErrors' => $importErrors['official'],
        'onCurrentStep' => ($score == 2),
        'responsesReceived' => $criteria[2]['responses_received'][1],
        'step' => 2,
        'surveyActive' => $surveyIsActive['official'],
        'surveyId' => $officialSurveyId,
        'timeResponsesLastChecked' => $officialResponsesChecked
    ]) ?>

    <?= $this->ClientHome->responseRateRow([
        'description' => $criteria[2]['response_threshold_reached'][0],
        'responsesReceived' => $criteria[2]['responses_received'][1],
        'step' => 2,
        'surveyActive' => $surveyIsActive['official'],
        'surveyId' => $officialSurveyId,
        'thresholdReached' => $criteria[2]['response_threshold_reached'][1]
    ]) ?>

    <?= $this->ClientHome->unapprovedResponsesRow([
        'allUnapprovedAddressed' => $criteria[2]['unapproved_addressed'][1],
        'description' => $criteria[2]['unapproved_addressed'][0],
        'hasUninvitedResponses' => $criteria[2]['hasUninvitedResponses'],
        'surveyId' => $officialSurveyId
    ]) ?>

    <?= $this->ClientHome->presentationScheduledRow('A', $community->presentation_a) ?>

    <?= $this->ClientHome->presentationCompletedRow('A', $community->presentation_a) ?>

    <?php $optedOut = in_array(\App\Model\Table\ProductsTable::OFFICIALS_SUMMIT, $optOuts); ?>
    <?= $this->ClientHome->leadershipSummitRow([
        'communityId' => $community['id'],
        'description' => $criteria[2]['leadership_summit_purchased'][0],
        'optedOut' => $optedOut,
        'purchased' => $criteria[2]['leadership_summit_purchased'][1],
        'purchaseUrl' => $purchaseUrls[2]
    ]) ?>

    <?php if (! $optedOut && $criteria[2]['leadership_summit_purchased'][1]): ?>
        <?= $this->ClientHome->presentationScheduledRow('B', $community->presentation_b) ?>
        <?= $this->ClientHome->presentationCompletedRow('B', $community->presentation_b) ?>
    <?php endif; ?>

    <?= $this->ClientHome->orgSurveyPurchasedRow([
        'communityId' => $community['id'],
        'description' => $step2SurveyPurchased[0],
        'optedOut' => in_array(\App\Model\Table\ProductsTable::ORGANIZATIONS_SURVEY, $optOuts),
        'purchased' => $step2SurveyPurchased[1],
        'purchaseUrl' => $purchaseUrls[3]
    ]) ?>
</tbody>
