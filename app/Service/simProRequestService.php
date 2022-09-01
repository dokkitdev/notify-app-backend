<?php
/**
 * Created by PhpStorm.
 * User: OmenWD
 * Date: 04/09/18
 * Time: 14:53
 */

namespace App\Service;


use App\Models\Appointment;
use App\Models\Customer;
use App\Models\HousingJob;
use App\Models\Templates;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class simProRequestService
{
    private $token = 'e929be7849b90e553082e592552739082c7614ab';
    const API_URL = 'https://blueflamecornwallltd.simprocloud.com';
    public $result_count;

    public function __construct()
    {
    }


    protected function request($method, $url, $data = [], &$attemptCount = 0)
    {
        $client = new Client();
        $url = $url.((strpos($url, '?') != false) ? '&' : '?').'access_token='.$this->token;
        try {
            $res = $client->request($method, $url, $data);
            if ((int)$res->getStatusCode() == 200 || (int)$res->getStatusCode() == 201 || (int)$res->getStatusCode(
                ) == 204) {
                return $res;
            }
        } catch (\Exception $e) {
            if ($attemptCount < 3) {
                sleep(2);
                $attemptCount++;

                //print 'ac='.$attemptCount.'<br>';
                return $this->request($method, $url, $data, $attemptCount);
            } else {
                Log::error('simPRORequestError '.$e->getMessage());
            }
        }

        return false;
    }


    public function getRequestPage($method, $url, $additionalHeaders = [])
    {
        $res = $this->request(
            $method,
            self::API_URL.$url,
            [
                'headers' => array_merge(
                    [
                        'Accept' => 'application/json',
                    ],
                    $additionalHeaders
                ),
            ]
        );
        if ($res) {
            $headers = $res->getHeaders();
            $urls[] = $url;
            if (isset($headers['Result-Total'][0])) {
                $this->result_count = $headers['Result-Total'][0];
            }
            if (isset($headers['Result-Pages'][0]) && $headers['Result-Pages'][0] > 1) {
                for ($i = 2; $i < $headers['Result-Pages'][0] + 1; $i++) {
                    $urls[] = $url.((strpos($url, '?') != false) ? '&' : '?').'page='.$i;
                }
            }

            return $urls;
        } else {
            return false;
        }
    }


    public function getRequest($method, $url, $additionalHeaders = [])
    {
        $res = $this->request(
            $method,
            self::API_URL.$url,
            [
                'headers' => array_merge(
                    [
                        'Accept' => 'application/json',
                    ],
                    $additionalHeaders
                ),
            ]
        );
        if ($res) {
            return json_decode($res->getBody());
        }

        return false;
    }

    public function deleteRequest($url)
    {
        $client = new Client();
        $res = $client->delete(
            self::API_URL.$url.'?access_token='.$this->token,
            [
                'headers' => [
                    'Accept' => 'application/json', #todo required
                ],
            ]
        );
        if ($res) {
            return json_decode($res->getBody());
        }

        return false;
    }

    public function patchRequest($method, $url, $data)
    {
        $res = $this->request(
            $method,
            self::API_URL.$url,
            [
                'headers' => [
                    'Accept' => 'application/json', #todo required
                ],
                'json' => $data,
            ]
        );
        if ($res) {
            return json_decode($res->getBody());
        }

        return false;
    }


    function uploadAppointment(Appointment $a)
    {
//        dump('upload');
        $today = new \DateTime();
        $pdf_folder = Config::get('constants.storage_pdf');
        if (!is_file($pdf_folder.'/'.$a->pdf)) {
            return;
        }
        $b64Doc = base64_encode(file_get_contents($pdf_folder.'/'.$a->pdf));
        $file_name = $a->job_id.'.appointment.'.$today->format('Y-m-d').'.pdf';
        $res = $this->patchRequest(
            'POST',
            '/api/v1.0/companies/0/jobs/'.$a->job_id.'/attachments/files/',
            [
                'Filename' => $file_name,
                'Base64Data' => $b64Doc,
                'Public' => true,
                'Email' => false,
            ]
        );
    }

    function uploadAppointmentChl(Appointment $a)
    {
        $letter_type = '';
        switch ($a->letter_type) {
            case Templates::APPOINTMENT_LETTER_CHL_1:
                $letter_type = 'appointment';
                break;
            case Templates::APPOINTMENT_LETTER_CHL_2:
                $letter_type = 'no-access-2';
                break;
            case Templates::APPOINTMENT_LETTER_CHL_3:
                $letter_type = 'no-access-3';
                break;
        }
        $today = new \DateTime();
        $file_name = 'Job#'.$a->job_id.'.'.$letter_type.'.'.$today->format('Y-m-d').'.pdf';

        $pdf_folder = Config::get('constants.storage_pdf');
        if (!is_file($pdf_folder.'/'.$a->pdf)) {
            return;
        }
        $b64Doc = base64_encode(file_get_contents($pdf_folder.'/'.$a->pdf));
        $res = $this->patchRequest(
            'POST',
            '/api/v1.0/companies/0/jobs/'.$a->job_id.'/attachments/files/',
            [
                'Filename' => $file_name,
                'Base64Data' => $b64Doc,
                'Public' => true,
                'Email' => false,
            ]
        );
    }

    function uploadHousing(HousingJob $h)
    {
        $today = new \DateTime();
        $pdf_folder = Config::get('constants.storage_pdf');
        if (!is_file($pdf_folder.'/'.$h->pdf)) {
            return;
        }
        $b64Doc = base64_encode(file_get_contents($pdf_folder.'/'.$h->pdf));
        $tag = $h->tags;
        $tag = str_replace("(Letter)", '', $tag);
        $tag = strtolower($tag);
        $tag = trim($tag);
        $tag = str_replace(' ', '-', $tag);
        $file_name = $h->job_id.'.'.$tag.'.'.$today->format('Y-m-d').'.pdf';
        $res = $this->patchRequest(
            'POST',
            '/api/v1.0/companies/0/jobs/'.$h->job_id.'/attachments/files/',
            [
                'Filename' => $file_name,
                'Base64Data' => $b64Doc,
                'Public' => true,
                'Email' => false,
            ]
        );
    }


    function uploadPrivate(Customer $customer, $pdf)
    {
//        /api/v1.0/companies/0/employees/123/attachments/files/
        $pdf_folder = Config::get('constants.storage_pdf');
        if (!is_file($pdf_folder.'/'.$pdf)) {
            return;
        }
        $b64Doc = base64_encode(file_get_contents($pdf_folder.'/'.$pdf));
        $res = $this->patchRequest(
            'POST',
            '/api/v1.0/companies/0/customers/'.$customer->company_id.'/attachments/files/',
            [
                'Filename' => $pdf,
                'Base64Data' => $b64Doc,
            ]
        );
    }

    function uploadNewPrivate($customer, $pdf)
    {
        $pdf_folder = Config::get('constants.storage_pdf');
        if (!is_file($pdf_folder.'/'.$pdf)) {
            return;
        }
        $b64Doc = base64_encode(file_get_contents($pdf_folder.'/'.$pdf));

//        $res = $this->patchRequest('POST', '/api/v1.0/companies/0/recurringInvoices/'. $customer->recurring_invoice_id . '/attachments/files/', [
        $res = $this->patchRequest(
            'POST',
            '/api/v1.0/companies/0/customers/'.$customer->customer_id.'/attachments/files/',
            [
                'Filename' => $pdf,
                'Base64Data' => $b64Doc,
            ]
        );
    }

    public function getAccessToken()
    {
        return $this->token;
    }
}

