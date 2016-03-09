<?php

namespace ETNA\Silex\Provider\Acl;

use ETNA\RSA\RSA;
use Silex\Application;
use Silex\Api\BootableProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Exception;

class AclServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{

    /**
     * Check configuration
     */
    public function boot(Application $app)
    {
        // If auth is not set
        if (false === isset($app['auth'])) {
            throw new \Exception(get_class($this) . " auth is not set", 401);
        }

        // If the app_name isn't provided in auth config file.
        if (false === isset($app['auth.app_name'])) {
            throw new \Exception(get_class($this) . " auth.app_name is not set", 401);
        }

        $this->app = $app;
    }

    /**
     * Register before callbacks
     */
    public function register(Container $app)
    {
        $app->before([$this, "checkUserAccess"]);

        // Check user's identity
        $app->match("/api",  [$this, "check"]);
        $app->match("/api/", [$this, "check"]);
    }

    /**
     * Parse all users roles to transform $app['auth.app_name']_role to role
     * and verify if the user is not close
     * @param Request $req
     *
     * @return null|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkUserAccess(Request $req)
    {
        // We only match api's calls
        $regex = "#^/api/?#";
        if (true === isset($this->app['auth.api_path'])) {
            $regex = "#{$this->app['auth.api_path']}#";
        }

        switch (true) {
            // If the request route doesn't match the $app['auth.api_path']
            case 1 !== preg_match($regex, $req->getRequestUri()):
            // If the request method is OPTIONS
            case $req->getMethod() === 'OPTIONS':
            // If the request doesn't have credential
            case false === isset($req->user):
                return;
        }

        $app_name = $this->app['auth.app_name'];
        // We change all groups with prefix
        $req->user->groups = array_values(
            array_unique(
                array_map(
                    function ($role) use ($app_name) {
                        return str_replace("{$app_name}_", "", $role);
                    },
                    $req->user->groups
                )
            )
        );

        if (true === in_array("close", $req->user->groups)) {
            return $this->app->json("Your are closed for this app !", 403);
        }

        $this->app["user"] = $req->user;
    }

    /**
     * Give the user Identity
     *
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function check(Request $req)
    {
        $user = null;
        if (true === isset($req->user)) {
            $user = $req->user;
        }
        return $this->app->json($user, 200);
    }
}
