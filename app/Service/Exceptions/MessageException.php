<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-05-24
 * Time: 09:15
 */

namespace App\Service\Exceptions;


class MessageException extends \Exception
{
    private $description;

    public function __construct(
        $description,
        $message = 'Exception',
        $code = 0,
        \Exception $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }
}