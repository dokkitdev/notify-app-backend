<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/7/18
 * Time: 3:32 PM
 */

namespace App\Service\Sender;


use SendGrid\Mail\Attachment;

class Sender
{
    private static $instance;
    /** @var \SendGrid */
    public $send_grid;
    const FROM = 'salesteam@blueflameheat.co.uk';

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

    public static function send($to, $subject = 'Sales', $html, $from = null, $attachment = null, $attachmentName = 'file.pdf')
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
        if ($attachment) {
            if (file_exists($attachment)) {
                dump('with attachment');
                $att1 = new Attachment();
                $att1->setContent(file_get_contents($attachment));
                $att1->setType("application/pdf");
                $att1->setFilename($attachmentName);
                $att1->setDisposition("attachment");
                $email->addAttachment($att1);
            }
        }
        try {
            $response = $instance->send_grid->send($email);
//            print $response->statusCode() . "\n";
//            print_r($response->headers());
//            print $response->body() . "\n";
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }
}
