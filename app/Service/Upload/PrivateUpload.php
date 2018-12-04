<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/4/18
 * Time: 2:39 PM
 */

namespace App\Service\Upload;


use App\Service\simProRequestService;

class PrivateUpload
{
    /** @var simProRequestService */
    private $simpro;

    public function __construct()
    {
        $this->simpro = new simProRequestService();
    }


    public function run()
    {
        $pages = $this->simpro->getRequestPage('get', '/api/v1.0/companies/0/customers/companies/');
        if ($pages) {
            foreach ($pages as $url) {
                $this->getPageByUrl($url);
            }
        }
    }

    public function parsePageByUrl($url)
    {
        $page = $this->simpro->getRequest('get', $url);
        dump($page);
        die;
    }

    public function parseCompany($)

}