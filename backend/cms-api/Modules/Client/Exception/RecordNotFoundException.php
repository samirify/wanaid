<?php

declare(strict_types=1);

namespace Modules\Client\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Exception;

/**
 *  Class RecordNotFoundException
 */
class RecordNotFoundException extends Exception implements NotFoundExceptionInterface
{
    protected $code = 404;
}
