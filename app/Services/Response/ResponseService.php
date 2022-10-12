<?php


namespace App\Services\Response;


class ResponseService
{
    /**
     * ResponseService constructor.
     * @param $sendJsonResponse
     */
    private static function responsePrams($status, $errors = [], $data = []): array
    {
        return [
            'status' => $status,
            'errors' => (object)$errors,
            'data' => (object)$data,
        ];
    }

    public static function sendJsonResponse($status, $code = 200, $errors = [], $data = []) {
        return response()->json(
            self::responsePrams($status, $errors, $data),
            $code
        );
    }

    public static function success($data = []) {
        return self::sendJsonResponse(true, 200, [],$data);
    }

    public static function notFound($data = []) {
        return self::sendJsonResponse(false, 404, [],[]);
    }

    public static function notAuthorize() {
        return self::sendJsonResponse(false, 401, [],[]);
    }
}
