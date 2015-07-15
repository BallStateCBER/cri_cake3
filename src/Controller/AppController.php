<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'loginAction' => [
                'controller' => 'Users',
                'action' => 'login'
            ],
            'logoutRedirect' => [
                'controller' => 'Pages',
                'action' => 'home'
            ],
            'authenticate' => [
                'Form' => [
                    'fields' => ['username' => 'email'],
                    'passwordHasher' => [
                        'className' => 'Fallback',
                        'hashers' => ['Default', 'Legacy']
                    ]
                ]
            ],
            'authorize' => ['Controller']
        ]);
    }

    public function isAuthorized($user)
    {
        if (! isset($user['role'])) {
            return false;
        }

        // Admin can access every action
        if ($user['role'] === 'admin') {
            return true;
        }

        // Clients and consultants can access the respective role-prefixed actions
        return $this->request->params['prefix'] === $user['role'];
    }

    protected function chooseClientToImpersonate()
    {
        $this->redirect([
            'prefix' => 'admin',
            'controller' => 'Users',
            'action' => 'choose_client',
            'redirect' => urlencode(Router::url([]))
        ]);
    }

    protected function getClientId()
    {
        if ($this->Auth->user('role') != 'admin') {
            return $this->Auth->user('id');
        }

        // Admins can set the ID of the client they're impersonating
        $clientId = $this->Cookie->read('clientId');
        if ($clientId) {
            return $clientId;
        }

        return $this->chooseClientToImpersonate();
    }

    /**
     * Accepts an array of stringy variables and returns a comma-delimited list with an optional conjunction before the last element
     * @param array $array
     * @param string $conjunction
     * @return string
     */
    protected function arrayToList($array, $conjunction = 'and')
    {
        $count = count($array);
        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return $array[0];
        }

        if ($conjunction) {
            $lastElement = array_pop($array);
            array_push($array, $conjunction.' '.$lastElement);
        }

        if ($count == 2) {
            return implode(' ', $array);
        }

        return implode(', ', $array);
    }

    /**
     * Uses cookie to remember current sorting and apply remembered sorting when none is currently specified
     * @param string $cookieParentKey
     */
    protected function cookieSort($cookieParentKey)
    {
        // Remember selected sort, but only remember direction if sort is specified
        $param = 'sort';
        if (isset($this->request->params['named'][$param])) {
            $value = $this->request->params['named'][$param];
            $key = "$cookieParentKey.$param";
            $this->Cookie->write($key, $value);

            $param = 'direction';
            if (isset($this->request->params['named'][$param])) {
                $value = $this->request->params['named'][$param];
                $key = "$cookieParentKey.$param";
                $this->Cookie->write($key, $value);

            // Forget direction
            } elseif ($this->Cookie->check($key)) {
                $this->Cookie->delete($key);
            }

        // If no sort specified, apply remembered sort
        } else {
            $param = 'sort';
            $key = "$cookieParentKey.$param";
            if ($this->Cookie->check($key)) {
                $this->request->params['named'][$param] = $this->Cookie->read($key);

                // And direction, if remembered
                $param = 'direction';
                $key = "$cookieParentKey.$param";
                if ($this->Cookie->check($key)) {
                    $this->request->params['named'][$param] = $this->Cookie->read($key);
                }
            }
        }
    }
}
