<?php

namespace ETNA\Silex\Provider\Acl;

use Exception;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class AclServiceProvider implements ServiceProviderInterface
{
    /** @var \Silex\Application */
    private $app;

    /**
     * Check configuration
     */
    public function boot(Application $app)
    {
        $this->app = $app;

        $this->checkParams([
            "auth",
            "auth.app_name",
        ]);
    }

    /**
     * @param array $params
     */
    private function checkParams($params)
    {
        foreach ($params as $param_name) {
            if (false === isset($this->app[$param_name])) {
                throw new \Exception(get_class($this) . ": {$param_name} is not set", 401);
            }
        }
    }

    /**
     * Register before callbacks
     *
     * @param \Silex\Application $app
     */
    public function register(Application $app)
    {
        $app->before([$this, "checkUserAccess"]);

        // Check user's identity
        $callback = [$this, "check"];
        $app->match("/api", $callback);
        $app->match("/api/", $callback);
    }

    /**
     * Parse all users roles to transform $app['auth.app_name']_role to role
     * and verify if the user is not close
     *
     * @param Request $req
     */
    public function checkUserAccess(Request $req)
    {
        // We only match api's calls
        $regex = "#^/api/?#";
        if (true === isset($this->app['auth.api_path'])) {
            $regex = "#{$this->app['auth.api_path']}#";
        }

        switch (true) {
            // we do NOTHING if the request route doesn't match the $app['auth.api_path']
            // or is a CORS or doesn't have credential
            case 1 !== preg_match($regex, $req->getRequestUri()):
            case $req->getMethod() === 'OPTIONS':
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

        $app["user"] = $req->user;
    }

    /**
     * Give the user Identity
     *
     * @param Request $req
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
