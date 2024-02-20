<?php

namespace App\Http\Controllers\Abstracts;

class HttpResult 
{
    /**
     * The identifier that if related operation is success or not.
     *
     * @var bool
     */
    public $success = false;

    /**
     * The response message of related operation.
     *
     * @var string|null
     */
    public $message = '';

    /**
     * The data as payload.
     *
     * @var mixed|null
     */
    public $data;

    public function __construct()
    {
    }

    /**
     * @param bool $isSuccess
     * @return \App\Http\Controllers\Abstracts\HttpResult
     */
    public static function success(bool $isSuccess): HttpResult
    {
        $result = new HttpResult();
        $result->success = $isSuccess;
        return $result;
    }

    /**
     * @param string $message
     * @return \App\Http\Controllers\Abstracts\HttpResult
     */
    public function message(string $message): HttpResult
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param mixed $data
     * @return \App\Http\Controllers\Abstracts\HttpResult
     */
    public function data($data): HttpResult
    {
        $this->data = $data;
        return $this;
    }

    public function toJsonResponse($httpStatusCode = 200)
    {
        return response()->json($this, $httpStatusCode);
    }
}
