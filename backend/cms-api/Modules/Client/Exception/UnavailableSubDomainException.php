<?php

declare(strict_types=1);

namespace Modules\Client\Exception;

use Exception;

class UnavailableSubDomainException extends Exception
{
    protected $code = 400;
    protected $message = "Unavailable Sub-Domain!";
}
