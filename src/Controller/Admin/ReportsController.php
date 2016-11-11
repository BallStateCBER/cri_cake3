<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Reports\Reports;
use Cake\ORM\TableRegistry;

class ReportsController extends AppController
{
    /**
     * Method for /admin/reports/index
     *
     * @return void
     */
    public function index()
    {
        $surveysTable = TableRegistry::get('Surveys');
        $reports = new Reports();
        $this->set([
            'report' => $reports->getReport(),
            'sectors' => $surveysTable->getSectors(),
            'titleForLayout' => 'CRI Reports'
        ]);
    }


    /**
     * Method for /admin/reports/ocra
     *
     * @return void
     */
    public function ocra()
    {
        if (! isset($_GET['debug'])) {
            $date = date('M-d-Y');
            $this->respondWithSpreadsheet("CRI Report - OCRA - $date.xlsx");
        }
        $reports = new Reports();
        $this->set([
            'reportSpreadsheet' => $reports->getReportSpreadsheet('ocra')
        ]);
        $this->render('view');
    }

    /**
     * Method for /admin/reports/admin
     *
     * @return void
     */
    public function admin()
    {
        if (! isset($_GET['debug'])) {
            $date = date('M-d-Y');
            $this->respondWithSpreadsheet("CRI Report - Admin - $date.xlsx");
        }
        $reports = new Reports();
        $this->set([
            'reportSpreadsheet' => $reports->getReportSpreadsheet('admin')
        ]);
        $this->render('view');
    }

    /**
     * Sets up the response to prompt a download of a spreadsheet
     *
     * @param string $filename
     * @return void
     */
    private function respondWithSpreadsheet($filename)
    {
        $this->response->type(['excel2007' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        $this->response->type('excel2007');
        $this->response->download($filename);
        $this->viewBuilder()->layout('spreadsheet');
    }
}
