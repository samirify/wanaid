<?php

declare(strict_types=1);

namespace Modules\Client\Exception;

use Exception;

class InvalidCustomerException extends Exception
{
    protected $message = "Invalid Customer!";
}
