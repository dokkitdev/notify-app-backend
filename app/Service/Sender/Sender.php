<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/7/18
 * Time: 3:32 PM
 */

namespace App\Service\Sender;


use Aws\Credentials\CredentialProvider;
use Aws\Ses\SesClient;

class Sender
{
    private static $instance;
    /** @var \SendGrid */
    public $send_grid;
    const FROM = 'salesteam@blueflamegas.co.uk';

    private function __construct()
    {
        $this->send_grid = new \SendGrid("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");
    }

    private static function init()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function send($to, $subject = 'Sales', $html, $from = null)
    {
        $instance = self::init();
        $from = $from ?: self::FROM;
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($from);
        $email->setSubject($subject);
        $email->addTo($to);
        $email->addContent(
            "text/html", $html
        );
        try {
            $response = $instance->send_grid->send($email);
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }
}