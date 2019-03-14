<?php

namespace TestApp\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ETNA\Auth\Services\AuthCookieService;

class BaseController extends Controller
{
    /**
     * @Route("/restricted", methods={"GET"}, name="restricted")
     */
    public function home(AuthCookieService $auth, Request $req)
    {
        return new JsonResponse($req->attributes->get("auth.user"), 200);
    }

    /**
     * @Route("/restricted", methods={"OPTIONS"}, name="opt_restricted")
     */
    public function optRestricted(AuthCookieService $auth, Request $req)
    {
        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/open", methods={"GET"}, name="open")
     */
    public function open(AuthCookieService $auth, Request $req)
    {
        return new JsonResponse($req->attributes->get("auth.user"), 200);
    }
}
