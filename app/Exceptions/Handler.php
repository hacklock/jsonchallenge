<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Een lijst van de uitzonderingstypen die niet worden gerapporteerd.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * Een lijst met de invoer die nooit wordt geflitst voor validatie-uitzonderingen.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Een uitzondering melden of registreren.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Een uitzondering omzetten in een HTTP-antwoord.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
