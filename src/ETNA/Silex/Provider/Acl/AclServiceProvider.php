<?php

namespace ETNA\Silex\Provider\Acl;

use ETNA\RSA\RSA;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Silex\ServiceProviderInterface;
use Exception;

class AclServiceProvider implements ServiceProviderInterface
{

    /**
     * Check configuration
     */
    public function boot(Application $app)
    {
        // If auth is not set
        if (!isset($app['auth'])) {
            throw new \Exception(get_class($this) . "auth is not set", 401);
        }

        // If the app_name isn't provided in auth config file.
        if (!isset($app['auth.app_name'])) {
           throw new \Exception(get_class($this) . "auth.app_name is not set", 401);
        }

        $this->app = $app;
    }

    /**
     * Register before callbacks
     */
    public function register(Application $app)
    {
        $app->before([$this, "checkUserAccess"]);

        // Check user's identity
        $app->match("/api",  [$this, "check"]);
        $app->match("/api/", [$this, "check"]);
    }

    /**
     * Parse all users roles to transform $app['auth.app_name']_role to role
     * and verify if the user is not close
     */
    public function checkUserAccess(Request $req)
    {
        // We only match api's calls
        if (!preg_match("#^/api/?#", $req->getRequestUri())) {
            return ;
        }

        // If the cookie doesn't exists
        if (!$req->user) {
            return $this->app->json("Authorization Required", 401);
        }

        // We change all groups with prefix
        $req->user->groups = array_values(
            array_unique(
                array_map(
                    function ($role) {
                        return str_replace("{$this->app['auth.app_name']}_", "", trim($role));
                    },
                    $req->user->groups
                )
            )
        );

        if (in_array("close", $req->user->groups)) {
            return $this->app->json("Your are closed for this app !", 403);
        }

        $app["user"] = $req->user;
    }

    /**
     * Give the user Identity
     */
    public function check(Request $req)
    {
        $user = null;
        if (isset($req->user)) {
            $user = $req->user;
        }
        return $this->app->json($user, 200);
    }
}
