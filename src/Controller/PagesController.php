<?php
declare(strict_types=1);

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

use App\Maps\Map;
use Cake\Mailer\MailerAwareTrait;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    use MailerAwareTrait;

    /**
     * initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow();
    }

    /**
     * Method for /pages/home
     *
     * @return void
     */
    public function home()
    {
        $this->set([
            'map' => [
                'colors' => Map::getColors(),
                'data' => Map::getMapData(),
            ],
            'titleForLayout' => '',
        ]);
    }

    /**
     * Method for /pages/glossary
     *
     * @return void
     */
    public function glossary()
    {
        $this->set('titleForLayout', 'Glossary');
    }

    /**
     * Method for /pages/faq-community
     *
     * @return void
     */
    public function faqCommunity()
    {
        $this->set('titleForLayout', 'Frequently Asked Questions for Communities');
    }

    /**
     * Method for /pages/credits
     *
     * @return void
     */
    public function credits()
    {
        $this->set('titleForLayout', 'Credits and Sources');
    }

    /**
     * Method for /pages/enroll
     *
     * @return \Cake\Http\Response
     */
    public function enroll()
    {
        return $this->redirect('https://www.surveymonkey.com/s/XFT6CSZ');
    }

    /**
     * Action that users get redirected to when accessing certain pages while the site's in maintenance mode
     *
     * @return void
     */
    public function maintenance()
    {
        $this->set('titleForLayout', 'Temporarily Unavailable');
    }
}
