<?php

namespace Infrastructure\Common\Controller;

use Infrastructure\Common\Http\JsonResponder;
use Infrastructure\Common\Http\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PingAction
{
    private JsonResponder $responder;

    public function __construct(JsonResponder $responder)
    {
        $this->responder = $responder;
    }

    /**
     * @Route("/api/ping", name="ping", methods={"GET"})
     */
    public function get(): JsonResponse
    {
        return $this->responder->respondSuccess([
            'result' => 'pong',
        ]);
    }
}
