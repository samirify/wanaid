<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\ResponseTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

// use \Illuminate\Support\Facades\Cookie as FacadesCookie;
// use Symfony\Component\HttpFoundation\Cookie;

trait SAAApiResponse
{

    use ResponseTrait;
    /**
     * Return a json formatted success response
     * @param string|array|null $data
     * @param int $code
     * @param int $cookie
     * $return Illuminate\Http\Response
     */
    public function successResponse(string|array $data = null, int $code = Response::HTTP_OK, ?Cookie $cookie = null): Response
    {
        // $name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite
        // $cookie = new Cookie('dev-sud', 'test', time() + 4400, '/', 'test-site.com', true, true);
        // FacadesCookie::make('fff', 'kkk');

        $responseContentArray = empty($data) ? [
            'success' => true,
        ] : [
            'success' => true,
            'data' => $data
        ];

        if (!is_null($cookie)) {
            return response($responseContentArray, $code)
                ->header('Content-Type', 'application/json')
                ->withCookie($cookie);
        } else {
            return response($responseContentArray, $code)
                ->header('Content-Type', 'application/json');
        }
    }

    /**
     * Return a json formatted error response
     * @param string|array $message
     * @param int $code
     * $return Illuminate\Http\JsonResponse
     */
    public function errorResponse($message, $code, $extraProps = []): JsonResponse
    {
        return response()->json(
            array_merge([
                'errors' => [$message],
                'success' => false
            ], $extraProps),
            $code !== 0 ? (int)$code : 500
        );
    }

    /**
     * Return a json formatted error response
     * @param string|array $message
     * @param int $code
     * $return Illuminate\Http\JsonResponse
     */
    public function handleExceptionResponse(Throwable $th, array $messages = []): JsonResponse
    {
        $availableErrorCodes = [400, 404, 401, 403];

        $error = $th->getMessage();
        $code = (string)$th->getCode();

        switch ($code) {
            case "42S01":
                $error = 'Module exists!';
                $code = 500;
                break;
            case "23000":
                $error = $messages['duplicate_msg'] ?? $error;
                $code = 500;
                break;
            case "22001":
                $error = $messages['too_long_msg'] ?? 'Value too long!';
                $code = 500;
                break;
            default:
                $originalCode = (int)$th->getCode();
                $code = http_response_code($originalCode) ? $originalCode : 500;
                break;
        }

        $finalCode = in_array((int)$code, $availableErrorCodes) ? $code : 500;

        return $this->errorResponse([$error], $finalCode);
    }

    /**
     * Return a json formatted error response
     * @param string|array $message
     * @param int $code
     * $return Illuminate\Http\Response
     */
    public function errorMessage(string|array $message, int $code): Response
    {
        return response($message, $code)->header('Content-Type', 'application/json');
    }
}
