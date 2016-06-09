<?php
namespace App\Controller\Component;

use App\Mailer\Mailer;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class SurveyProcessingComponent extends Component
{
    public $components = ['Flash', 'Auth'];

    public $approvedRespondents = [];
    public $communityId = null;
    public $errorEmails = [];
    public $invitees = [];
    public $recipients = [];
    public $redundantEmails = [];
    public $respondentType = null;
    public $successEmails = [];
    public $surveyId = null;
    public $unaddressedUnapprovedRespondents = [];
    public $uninvApprovedEmails = [];

    public function processInvitations($communityId, $respondentType, $surveyId)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $this->approvedRespondents = $respondentsTable->getApprovedList($surveyId);
        $this->unaddressedUnapprovedRespondents = $respondentsTable->getUnaddressedUnapprovedList($surveyId);
        $this->communityId = $communityId;
        $this->respondentType = $respondentType;
        $this->surveyId = $surveyId;

        $this->setInvitees();
        $this->cleanInvitees();
        $this->removeApproved();

        foreach ($this->invitees as $i => $invitee) {
            if ($this->isUnapproved($invitee['email'])) {
                $this->approveInvitee($invitee);
                continue;
            }

            $this->createRespondent($invitee);
        }

        $Mailer = new Mailer();
        $success = $Mailer->sendInvitations([
            'surveyId' => $this->surveyId,
            'communityId' => $this->communityId,
            'senderEmail' => $this->Auth->user('email'),
            'senderName' => $this->Auth->user('name'),
            'recipients' => $this->recipients
        ]);
        if ($success) {
            $this->successEmails = array_merge($this->successEmails, $this->recipients);
        } else {
            $this->errorEmails = array_merge($this->errorEmails, $this->recipients);
        }

        $this->setInvitationFlashMessages();
        $this->request->data = [];
    }

    private function setInvitees()
    {
        $invitees = $this->request->data('invitees');
        $invitees = is_array($invitees) ? $invitees : [];
        $this->invitees = $invitees;
    }

    /**
     * Clean name, email, and title and remove any invitees with no email address
     */
    private function cleanInvitees()
    {
        foreach ($this->invitees as $i => &$invitee) {
            foreach (['name', 'email', 'title'] as $field) {
                $invitee[$field] = trim($invitee[$field]);
            }

            $invitee['email'] = strtolower($invitee['email']);

            if (empty($invitee['email'])) {
                unset($this->invitees[$i]);
            }
        }
    }

    /**
     * Removes invitees if they've already been invited / approved
     */
    private function removeApproved()
    {
        foreach ($this->invitees as $i => $invitee) {
            if (in_array($invitee['email'], $this->approvedRespondents)) {
                $this->redundantEmails[] = $invitee['email'];
                unset($this->invitees[$i]);
            }
        }
    }

    private function approveInvitee($invitee)
    {
        $this->uninvApprovedEmails[] = $invitee['email'];
        $respondentsTable = TableRegistry::get('Respondents');
        $respondent = $respondentsTable->findBySurveyIdAndEmail($this->surveyId, $invitee['email'])->first();

        // Approve
        $respondent->approved = 1;

        // Update details
        foreach (['name', 'title'] as $field) {
            if ($invitee[$field]) {
                $respondent->$field = $invitee[$field];
            }
        }

        // Save
        if (! $respondentsTable->save($respondent)) {
            $this->errorEmails[] = $invitee['email'];
        }

        // Add to approved list
        $this->approvedRespondents[] = $invitee['email'];

        // Remove from unapproved list
        $k = array_search($invitee['email'], $this->unaddressedUnapprovedRespondents);
        unset($this->unaddressedUnapprovedRespondents[$k]);
    }

    /**
     * Returns true if email corresponds to an uninvited respondent pending approval / dismissal
     *
     * @param string $email
     * @return boolean
     */
    private function isUnapproved($email)
    {
        return in_array($email, $this->unaddressedUnapprovedRespondents);
    }

    /**
     * Adds a new respondent and adds them to the invitation email queue
     *
     * @param $invitee array
     */
    private function createRespondent($invitee)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $respondent = $respondentsTable->newEntity([
            'approved' => 1,
            'community_id' => $this->communityId,
            'email' => $invitee['email'],
            'invited' => true,
            'name' => $invitee['name'],
            'survey_id' => $this->surveyId,
            'title' => $invitee['title'],
            'type' => $this->respondentType
        ]);
        $errors = $respondent->errors();
        if (empty($errors) && $respondentsTable->save($respondent)) {
            $this->recipients[] = $respondent->email;
            $this->approvedRespondents[] = $respondent->email;
        } else {
            $this->errorEmails[] = $invitee['email'];
            if (Configure::read('debug')) {
                $this->Flash->dump($respondent->errors());
            }
        }
    }

    public function setInvitationFlashMessages()
    {
        $seCount = count($this->successEmails);
        if ($seCount) {
            $list = $this->arrayToList($this->successEmails);
            $msg = 'Questionnaire '.__n('invitation', 'invitations', $seCount).' sent to '.$list;
            $this->Flash->success($msg);
        }

        $reCount = count($this->redundantEmails);
        if ($reCount) {
            $list = $this->arrayToList($this->redundantEmails);
            $msg = $list.__n(' has', ' have', $reCount).' already received a questionnaire invitation';
            $this->Flash->set($msg);
        }

        $eeCount = count($this->errorEmails);
        if ($eeCount) {
            $list = $this->arrayToList($this->errorEmails);
            $msg = 'There was an error inviting '.$list.'. Please try again or contact an administrator if you need assistance.';
            $this->Flash->error($msg);
        }

        $rieCount = count($this->uninvApprovedEmails);
        if ($rieCount) {
            $list = $this->arrayToList($this->uninvApprovedEmails);
            $msg = 'The uninvited '.__n('response', 'responses', $rieCount).' received from '.$list.__n(' has', ' have', $rieCount).' been approved';
            $this->Flash->success($msg);
        }
    }

    /**
     * Accepts an array of stringy variables and returns a comma-delimited list with an optional conjunction before the last element
     * @param array $array
     * @param string $conjunction
     * @return string
     */
    public function arrayToList($array, $conjunction = 'and')
    {
        $count = count($array);
        if (! $count) {
            return '';
        } elseif ($count == 1) {
            return $array[0];
        } elseif ($count > 1) {
            if ($conjunction) {
                $last_element = array_pop($array);
                array_push($array, $conjunction.' '.$last_element);
            }
            if ($count == 2) {
                return implode(' ', $array);
            } else {
                return implode(', ', $array);
            }
        }
    }

    public function getCurrentResponses($surveyId)
    {
        $responsesTable = TableRegistry::get('Responses');
        $responses = $responsesTable->find('all')
            ->where(['Responses.survey_id' => $surveyId])
            ->contain([
                'Respondents' => function ($q) {
                    return $q->select(['id', 'email', 'name', 'title', 'approved']);
                }
            ])
            ->order(['Responses.response_date' => 'DESC'])
            ->all();

        // Only return the most recent response for each respondent
        $retval = [];
        foreach ($responses as $i => $response) {
            $respondentId = $response['respondent']['id'];

            if (isset($retval[$respondentId]['revision_count'])) {
                $retval[$respondentId]['revision_count']++;
                continue;
            }

            $retval[$respondentId] = $response;
            $retval[$respondentId]['revision_count'] = 0;
        }

        return $retval;
    }

    public static function getAlignmentSum($responses, $alignmentField)
    {
        $alignmentSum = 0;
        foreach ($responses as $i => $response) {
            if ($response['respondent']['approved'] == 1) {
                $alignmentSum += $response->$alignmentField;
            }
        }
        return $alignmentSum;
    }

    public static function getApprovedCount($responses)
    {
        $approvedCount = 0;
        foreach ($responses as $i => $response) {
            if ($response['respondent']['approved'] == 1) {
                $approvedCount++;
            }
        }
        return $approvedCount;
    }
}
