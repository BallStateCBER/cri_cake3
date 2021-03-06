<?php
declare(strict_types=1);

namespace App\Test\TestCase\Alerts;

use App\Alerts\Alertable;
use App\Model\Table\DeliverablesTable;
use App\Model\Table\ProductsTable;
use Cake\I18n\Date;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Class AlertableTest
 * @package App\Test\TestCase\Alerts
 * @property array $fixtures
 * @property CommunitiesTable $communities
 * @property DeliveriesTable $deliveries
 * @property OptOutsTable $optOuts
 * @property PurchasesTable $purchases
 * @property ResponsesTable $responses
 * @property SurveysTable $surveys
 * @property UsersTable $users
 */
class AlertableTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ClientsCommunities',
        'app.Communities',
        'app.Deliverables',
        'app.Deliveries',
        'app.OptOuts',
        'app.Products',
        'app.Purchases',
        'app.Responses',
        'app.Surveys',
        'app.Users',
    ];
    private $communities;
    private $deliveries;
    private $optOuts;
    private $purchases;
    private $responses;
    private $surveys;
    private $users;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->communities = TableRegistry::getTableLocator()->get('Communities');
        $this->deliveries = TableRegistry::getTableLocator()->get('Deliveries');
        $this->optOuts = TableRegistry::getTableLocator()->get('OptOuts');
        $this->purchases = TableRegistry::getTableLocator()->get('Purchases');
        $this->responses = TableRegistry::getTableLocator()->get('Responses');
        $this->surveys = TableRegistry::getTableLocator()->get('Surveys');
        $this->users = TableRegistry::getTableLocator()->get('Users');
    }

    /**
     * Tests that the community does not qualify for the specified alert
     *
     * @param int $communityId Community ID to be checked
     * @param string $alertName An alert name, such as 'deliverPresentationA'
     * @return void
     */
    private function assertUnalertable($communityId, $alertName)
    {
        $alertable = new Alertable($communityId);
        $this->assertFalse($alertable->{$alertName}());
    }

    /**
     * Tests that the community qualifies for the specified alert
     *
     * @param int $communityId Community ID to be checked
     * @param string $alertName An alert name, such as 'deliverPresentationA'
     * @return void
     */
    private function assertAlertable($communityId, $alertName)
    {
        $alertable = new Alertable($communityId);
        $this->assertTrue($alertable->{$alertName}());
    }

    /**
     * Tests the "community is inactive" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testDeliverPresFailInactiveCommunity($communityId, $presentationLetter)
    {
        $this->deactivateCommunity($communityId);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests the "survey is active" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param int $surveyId Survey ID
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testDeliverPresFailActiveSurvey($communityId, $surveyId, $presentationLetter)
    {
        $survey = $this->surveys->get($surveyId);
        $survey->active = true;
        $this->surveys->save($survey);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests the "survey is active" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param int $surveyId Survey ID
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testDeliverPresFailNoResponses($communityId, $surveyId, $presentationLetter)
    {
        // Survey with no responses
        $this->responses->deleteAll(['survey_id' => $surveyId]);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests "presentation has been delivered" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param int $deliverableId Deliverable ID
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testDeliverPresFailDelivered($communityId, $deliverableId, $presentationLetter)
    {
        $delivery = $this->deliveries->newEntity([
            'deliverable_id' => $deliverableId,
            'user_id' => 1,
            'community_id' => $communityId,
        ]);
        $this->deliveries->save($delivery);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests "product has not been purchased" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testDeliverPresFailNotPurchased($communityId, $presentationLetter)
    {
        $this->purchases->deleteAll(['community_id' => $communityId]);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests "presentation has been opted out of" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param int $productId Product ID
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter)
    {
        $this->optOuts->addOptOut([
            'community_id' => $communityId,
            'product_id' => $productId,
            'user_id' => 1,
        ]);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests "presentation date has passed" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testDeliverPresFailDatePassed($communityId, $presentationLetter)
    {
        $community = $this->communities->get($communityId);
        $community->{'presentation_' . strtolower($presentationLetter)} = new Date('yesterday');
        $this->communities->save($community);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Marks the specified community as inactive
     *
     * @param int $communityId Community ID
     * @return void
     */
    private function deactivateCommunity($communityId)
    {
        $community = $this->communities->get($communityId);
        $community->active = false;
        $this->communities->save($community);
    }

    /**
     * Activates the specified survey
     *
     * @param int $communityId Community ID
     * @param string $surveyType 'official' or 'organization'
     * @return void
     */
    private function activateSurvey($communityId, $surveyType)
    {
        /** @var Survey $survey */
        $survey = $this->surveys->find()->where(['community_id' => $communityId, 'type' => $surveyType])->first();
        $survey->active = true;
        $this->surveys->save($survey);
    }

    /**
     * Adds a response to the specified community (by reassigning an arbitrary existing response)
     *
     * @param int $communityId Community ID
     * @param string $surveyType 'official' or 'organization'
     * @return void
     */
    private function addResponse($communityId, $surveyType)
    {
        /** @var Survey $survey */
        $survey = $this->surveys->find()->where(['community_id' => $communityId, 'type' => $surveyType])->first();
        /** @var Response $response */
        $response = $this->responses->find()->first();
        $response->survey_id = $survey->id;
        $this->responses->save($response);
    }

    /**
     * Tests "community is inactive" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testSchedulePresFailInactiveCommunity($communityId, $presentationLetter)
    {
        $this->deactivateCommunity($communityId);
        $this->assertUnalertable($communityId, "schedulePresentation$presentationLetter");
    }

    /**
     * Tests Alertable::schedulePresentationA()'s fail conditions
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testSchedulePresFailNotDelivered($communityId, $presentationLetter)
    {
        $this->deliveries->deleteAll(['community_id' => $communityId]);
        $this->assertUnalertable($communityId, "schedulePresentation$presentationLetter");
    }

    /**
     * Tests Alertable::schedulePresentationA()'s fail conditions
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testSchedulePresFailScheduled($communityId, $presentationLetter)
    {
        $community = $this->communities->get($communityId);
        $community->{'presentation_' . strtolower($presentationLetter)} = '2000-01-01';
        $this->communities->save($community);
        $this->assertUnalertable($communityId, "schedulePresentation$presentationLetter");
    }

    /**
     * Tests Alertable::schedulePresentationA()'s fail conditions
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param string $presentationLetter A, B, C, or D
     * @return void
     */
    private function _testSchedulePresFailNotPurchased($communityId, $presentationLetter)
    {
        $this->purchases->deleteAll(['community_id' => $communityId]);
        $this->assertUnalertable($communityId, "schedulePresentation$presentationLetter");
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailInactiveCommunity()
    {
        $communityId = 4;
        $presentationLetter = 'A';
        $this->_testDeliverPresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailActiveSurvey()
    {
        $communityId = 4;
        $surveyId = 4;
        $presentationLetter = 'A';
        $this->_testDeliverPresFailActiveSurvey($communityId, $surveyId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailNoResponses()
    {
        $communityId = 4;
        $surveyId = 4;
        $presentationLetter = 'A';
        $this->_testDeliverPresFailNoResponses($communityId, $surveyId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailDelivered()
    {
        $communityId = 4;
        $deliverableId = DeliverablesTable::PRESENTATION_A_MATERIALS;
        $presentationLetter = 'A';
        $this->_testDeliverPresFailDelivered($communityId, $deliverableId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailNotPurchased()
    {
        $communityId = 4;
        $presentationLetter = 'A';
        $this->_testDeliverPresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailOptedOut()
    {
        $communityId = 4;
        $productId = ProductsTable::OFFICIALS_SURVEY;
        $presentationLetter = 'A';
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailDatePassed()
    {
        $communityId = 4;
        $presentationLetter = 'A';
        $this->_testDeliverPresFailDatePassed($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationA()'s pass conditions:
     *
     * - Active community
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     * - The date of the presentation has not passed
     *
     * @return void
     */
    public function testDeliverPresentationAPass()
    {
        $communityId = 4;
        $this->assertAlertable($communityId, 'deliverPresentationA');
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailInactiveCommunity()
    {
        $communityId = 4;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailActiveSurvey()
    {
        $communityId = 4;
        $surveyId = 5;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailActiveSurvey($communityId, $surveyId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailNoResponses()
    {
        $communityId = 4;
        $surveyId = 5;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailNoResponses($communityId, $surveyId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailDelivered()
    {
        $communityId = 4;
        $deliverableId = DeliverablesTable::PRESENTATION_C_MATERIALS;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailDelivered($communityId, $deliverableId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailNotPurchased()
    {
        $communityId = 4;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailOptedOut()
    {
        $communityId = 4;
        $productId = ProductsTable::ORGANIZATIONS_SURVEY;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailDatePassed()
    {
        $communityId = 4;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailDatePassed($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s pass conditions:
     *
     * - Active community
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     * - The date of the presentation has not passed
     *
     * @return void
     */
    public function testDeliverPresentationCPass()
    {
        $communityId = 4;
        $this->assertAlertable($communityId, 'deliverPresentationC');
    }

    /**
     * Tests Alertable::deliverPresentationB()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationBFailInactiveCommunity()
    {
        $communityId = 4;
        $presentationLetter = 'B';
        $this->_testDeliverPresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationB()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationBFailDelivered()
    {
        $communityId = 4;
        $deliverableId = DeliverablesTable::PRESENTATION_B_MATERIALS;
        $presentationLetter = 'B';
        $this->_testDeliverPresFailDelivered($communityId, $deliverableId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationB()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationBFailNotPurchased()
    {
        $communityId = 4;
        $presentationLetter = 'B';
        $this->_testDeliverPresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationB()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationBFailOptedOut()
    {
        $communityId = 4;
        $productId = ProductsTable::OFFICIALS_SUMMIT;
        $presentationLetter = 'B';
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationB()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationBFailDatePassed()
    {
        $communityId = 4;
        $presentationLetter = 'B';
        $this->_testDeliverPresFailDatePassed($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationB()'s pass conditions:
     *
     * - Active community
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     * - The date of the presentation has not passed
     *
     * @return void
     */
    public function testDeliverPresentationBPass()
    {
        $communityId = 4;
        $this->assertAlertable($communityId, 'deliverPresentationB');
    }

    /**
     * Tests Alertable::deliverPresentationD()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationDFailInactiveCommunity()
    {
        $communityId = 4;
        $presentationLetter = 'D';
        $this->_testDeliverPresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationD()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationDFailDelivered()
    {
        $communityId = 4;
        $deliverableId = DeliverablesTable::PRESENTATION_D_MATERIALS;
        $presentationLetter = 'D';
        $this->_testDeliverPresFailDelivered($communityId, $deliverableId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationD()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationDFailNotPurchased()
    {
        $communityId = 4;
        $presentationLetter = 'D';
        $this->_testDeliverPresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationD()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationDFailOptedOut()
    {
        $communityId = 4;
        $productId = ProductsTable::ORGANIZATIONS_SUMMIT;
        $presentationLetter = 'D';
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationD()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationDFailDatePassed()
    {
        $communityId = 4;
        $presentationLetter = 'D';
        $this->_testDeliverPresFailDatePassed($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationD()'s pass conditions:
     *
     * - Active community
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     * - The date of the presentation has not passed
     *
     * @return void
     */
    public function testDeliverPresentationDPass()
    {
        $communityId = 4;
        $this->assertAlertable($communityId, 'deliverPresentationD');
    }

    /**
     * Tests Alertable::createOfficialsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOfficialsSurveyFailInactiveCommunity()
    {
        $communityId = 5;
        $this->deactivateCommunity($communityId);
        $this->assertUnalertable($communityId, "createOfficialsSurvey");
    }

    /**
     * Tests Alertable::createOfficialsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOfficialsSurveyFailSurveyExists()
    {
        $communityId = 5;
        $survey = $this->surveys->find()->where(['type' => 'official'])->first();
        $survey->community_id = $communityId;
        $this->surveys->save($survey);
        $this->assertUnalertable($communityId, "createOfficialsSurvey");
    }

    /**
     * Tests Alertable::createOfficialsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOfficialsSurveyFailNotPurchased()
    {
        $communityId = 5;
        $this->purchases->deleteAll(['community_id' => $communityId]);
        $this->assertUnalertable($communityId, "createOfficialsSurvey");
    }

    /**
     * Tests Alertable::createOfficialsSurvey()'s pass conditions:
     *
     * - Active community
     * - Survey does not exist
     * - The corresponding product has been purchased
     *
     * @return void
     */
    public function testCreateOfficialsSurveyPass()
    {
        $communityId = 5;
        $this->assertAlertable($communityId, 'createOfficialsSurvey');
    }

    /**
     * Tests Alertable::createOrganizationsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOrganizationsSurveyFailInactiveCommunity()
    {
        $communityId = 5;
        $this->deactivateCommunity($communityId);
        $this->assertUnalertable($communityId, "createOrganizationsSurvey");
    }

    /**
     * Tests Alertable::createOrganizationsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOrganizationsSurveyFailSurveyExists()
    {
        $communityId = 5;
        $survey = $this->surveys->find()->where(['type' => 'organization'])->first();
        $survey->community_id = $communityId;
        $this->surveys->save($survey);
        $this->assertUnalertable($communityId, "createOrganizationsSurvey");
    }

    /**
     * Tests Alertable::createOrganizationsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOrganizationsSurveyFailNotPurchased()
    {
        $communityId = 5;
        $this->purchases->deleteAll(['community_id' => $communityId]);
        $this->assertUnalertable($communityId, "createOrganizationsSurvey");
    }

    /**
     * Tests Alertable::createOrganizationsSurvey()'s pass conditions:
     *
     * - Active community
     * - Survey does not exist
     * - The corresponding product has been purchased
     *
     * @return void
     */
    public function testCreateOrganizationsSurveyPass()
    {
        $communityId = 5;
        $this->assertAlertable($communityId, 'createOrganizationsSurvey');
    }

    /**
     * Tests Alertable::createClients()'s fail conditions
     *
     * @return void
     */
    public function testCreateClientsFailInactiveCommunity()
    {
        $communityId = 5;
        $this->deactivateCommunity($communityId);
        $this->assertUnalertable($communityId, "createClients");
    }

    /**
     * Tests Alertable::createClients()'s fail conditions
     *
     * @return void
     */
    public function testCreateClientsFailHasClient()
    {
        $communityId = 5;
        $community = $this->communities->get($communityId);
        $client = $this->users->find()->first();
        $this->communities->Clients->link($community, [$client]);
        $this->assertUnalertable($communityId, "createClients");
    }

    /**
     * Tests Alertable::createClients()'s pass conditions:
     *
     * - Active community
     * - Community has no clients
     *
     * @return void
     */
    public function testCreateClientsPass()
    {
        $communityId = 5;
        $this->assertAlertable($communityId, 'createClients');
    }

    /**
     * Tests Alertable::activateOfficialsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testActivateOfficialsSurveyFailInactiveCommunity()
    {
        $communityId = 6;
        $this->deactivateCommunity($communityId);
        $this->assertUnalertable($communityId, 'activateOfficialsSurvey');
    }

    /**
     * Tests Alertable::activateOfficialsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testActivateOfficialsSurveyFailActiveSurvey()
    {
        $communityId = 6;
        $surveyType = 'official';
        $this->activateSurvey($communityId, $surveyType);
        $this->assertUnalertable($communityId, 'activateOfficialsSurvey');
    }

    /**
     * Tests Alertable::activateOfficialsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testActivateOfficialsSurveyFailHasResponses()
    {
        $communityId = 6;
        $surveyType = 'official';
        $this->addResponse($communityId, $surveyType);
        $this->assertUnalertable($communityId, 'activateOfficialsSurvey');
    }

    /**
     * Tests Alertable::activateOfficialsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testActivateOfficialsSurveyFailNotPurchased()
    {
        $communityId = 6;
        $this->purchases->deleteAll(['community_id' => $communityId]);
        $this->assertUnalertable($communityId, 'activateOfficialsSurvey');
    }

    /**
     * Tests Alertable::activateOfficialsSurvey()'s pass conditions:
     *
     * - Active community
     * - Survey is inactive
     * - Survey has no responses
     * - The corresponding product has been purchased
     *
     * @return void
     */
    public function testActivateOfficialsSurveyPass()
    {
        $communityId = 6;
        $this->assertAlertable($communityId, 'activateOfficialsSurvey');
    }

    /**
     * Tests Alertable::activateOrganizationsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testActivateOrgSurveyFailInactiveCommunity()
    {
        $communityId = 6;
        $this->deactivateCommunity($communityId);
        $this->assertUnalertable($communityId, 'activateOrganizationsSurvey');
    }

    /**
     * Tests Alertable::activateOrganizationsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testActivateOrgSurveyFailActiveSurvey()
    {
        $communityId = 6;
        $surveyType = 'organization';
        $this->activateSurvey($communityId, $surveyType);
        $this->assertUnalertable($communityId, 'activateOrganizationsSurvey');
    }

    /**
     * Tests Alertable::activateOrganizationsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testActivateOrgSurveyFailHasResponses()
    {
        $communityId = 6;
        $surveyType = 'organization';
        $this->addResponse($communityId, $surveyType);
        $this->assertUnalertable($communityId, 'activateOrganizationsSurvey');
    }

    /**
     * Tests Alertable::activateOrganizationsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testActivateOrgSurveyFailNotPurchased()
    {
        $communityId = 6;
        $this->purchases->deleteAll(['community_id' => $communityId]);
        $this->assertUnalertable($communityId, 'activateOrganizationsSurvey');
    }

    /**
     * Tests Alertable::activateOrganizationsSurvey()'s pass conditions:
     *
     * - Active community
     * - Survey is inactive
     * - Survey has no responses
     * - The corresponding product has been purchased
     *
     * @return void
     */
    public function testActivateOrgSurveyPass()
    {
        $communityId = 6;
        $this->assertAlertable($communityId, 'activateOrganizationsSurvey');
    }

    /**
     * Tests Alertable::schedulePresentationA()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationAFailInactiveCommunity()
    {
        $communityId = 7;
        $presentationLetter = 'A';
        $this->_testSchedulePresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationA()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationAFailNotDelivered()
    {
        $communityId = 7;
        $presentationLetter = 'A';
        $this->_testSchedulePresFailNotDelivered($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationA()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationAFailScheduled()
    {
        $communityId = 7;
        $presentationLetter = 'A';
        $this->_testSchedulePresFailScheduled($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationA()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationAFailNotPurchased()
    {
        $communityId = 7;
        $presentationLetter = 'A';
        $this->_testSchedulePresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationA()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationAFailOptedOut()
    {
        $communityId = 7;
        $presentationLetter = 'A';
        $productId = ProductsTable::OFFICIALS_SURVEY;
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationA()'s pass conditions:
     *
     * - Active community
     * - Presentation materials have been delivered
     * - Presentation has not been scheduled
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     *
     * @return void
     */
    public function testSchedulePresentationAPass()
    {
        $communityId = 7;
        $this->assertAlertable($communityId, 'schedulePresentationA');
    }

    /**
     * Tests Alertable::schedulePresentationB()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationBFailInactiveCommunity()
    {
        $communityId = 7;
        $presentationLetter = 'B';
        $this->_testSchedulePresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationB()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationBFailNotDelivered()
    {
        $communityId = 7;
        $presentationLetter = 'B';
        $this->_testSchedulePresFailNotDelivered($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationB()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationBFailScheduled()
    {
        $communityId = 7;
        $presentationLetter = 'B';
        $this->_testSchedulePresFailScheduled($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationB()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationBFailNotPurchased()
    {
        $communityId = 7;
        $presentationLetter = 'B';
        $this->_testSchedulePresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationB()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationBFailOptedOut()
    {
        $communityId = 7;
        $presentationLetter = 'B';
        $productId = ProductsTable::OFFICIALS_SUMMIT;
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationB()'s pass conditions:
     *
     * - Active community
     * - Presentation materials have been delivered
     * - Presentation has not been scheduled
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     *
     * @return void
     */
    public function testSchedulePresentationBPass()
    {
        $communityId = 7;
        $this->assertAlertable($communityId, 'schedulePresentationB');
    }

    /**
     * Tests Alertable::schedulePresentationC()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationCFailInactiveCommunity()
    {
        $communityId = 7;
        $presentationLetter = 'C';
        $this->_testSchedulePresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationC()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationCFailNotDelivered()
    {
        $communityId = 7;
        $presentationLetter = 'C';
        $this->_testSchedulePresFailNotDelivered($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationC()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationCFailScheduled()
    {
        $communityId = 7;
        $presentationLetter = 'C';
        $this->_testSchedulePresFailScheduled($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationC()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationCFailNotPurchased()
    {
        $communityId = 7;
        $presentationLetter = 'C';
        $this->_testSchedulePresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationC()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationCFailOptedOut()
    {
        $communityId = 7;
        $presentationLetter = 'C';
        $productId = ProductsTable::ORGANIZATIONS_SURVEY;
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationC()'s pass conditions:
     *
     * - Active community
     * - Presentation materials have been delivered
     * - Presentation has not been scheduled
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     *
     * @return void
     */
    public function testSchedulePresentationCPass()
    {
        $communityId = 7;
        $this->assertAlertable($communityId, 'schedulePresentationC');
    }

    /**
     * Tests Alertable::schedulePresentationD()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationDFailInactiveCommunity()
    {
        $communityId = 7;
        $presentationLetter = 'D';
        $this->_testSchedulePresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationD()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationDFailNotDelivered()
    {
        $communityId = 7;
        $presentationLetter = 'D';
        $this->_testSchedulePresFailNotDelivered($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationD()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationDFailScheduled()
    {
        $communityId = 7;
        $presentationLetter = 'D';
        $this->_testSchedulePresFailScheduled($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationD()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationDFailNotPurchased()
    {
        $communityId = 7;
        $presentationLetter = 'D';
        $this->_testSchedulePresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationD()'s fail conditions
     *
     * @return void
     */
    public function testSchedulePresentationDFailOptedOut()
    {
        $communityId = 7;
        $presentationLetter = 'D';
        $productId = ProductsTable::ORGANIZATIONS_SUMMIT;
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::schedulePresentationD()'s pass conditions:
     *
     * - Active community
     * - Presentation materials have been delivered
     * - Presentation has not been scheduled
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     *
     * @return void
     */
    public function testSchedulePresentationDPass()
    {
        $communityId = 7;
        $this->assertAlertable($communityId, 'schedulePresentationD');
    }

    /**
     * Tests Alertable::deliverPolicyDev()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPolicyDevFailInactiveCommunity()
    {
        $communityId = 8;
        $this->deactivateCommunity($communityId);
        $this->assertUnalertable($communityId, 'deliverPolicyDev');
    }

    /**
     * Tests Alertable::deliverPolicyDev()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPolicyDevFailNotStepFour()
    {
        $communityId = 8;
        $community = $this->communities->get($communityId);
        $community->score = 1;
        $this->communities->save($community);
        $this->assertUnalertable($communityId, 'deliverPolicyDev');
    }

    /**
     * Tests Alertable::deliverPolicyDev()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPolicyDevFailDelivered()
    {
        $communityId = 8;
        $deliverableId = DeliverablesTable::POLICY_DEVELOPMENT;
        $delivery = $this->deliveries->newEntity([
            'deliverable_id' => $deliverableId,
            'user_id' => 1,
            'community_id' => $communityId,
        ]);
        $this->deliveries->save($delivery);
        $this->assertUnalertable($communityId, 'deliverPolicyDev');
    }

    /**
     * Tests Alertable::deliverPolicyDev()'s pass conditions:
     *
     * - Active community
     * - Community is on Step Four
     * - Policy dev has not yet been delivered
     *
     * @return void
     */
    public function testDeliverPolicyDevPass()
    {
        $communityId = 8;
        $this->assertAlertable($communityId, 'deliverPolicyDev');
    }
}
