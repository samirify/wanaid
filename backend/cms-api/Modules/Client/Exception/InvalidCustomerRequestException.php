<?php

declare(strict_types=1);

namespace Modules\Client\Exception;

use Exception;

class InvalidCustomerRequestException extends Exception
{
    protected $message = "Both customer_id  and project_id are required!";
    protected $code = 400;
}
