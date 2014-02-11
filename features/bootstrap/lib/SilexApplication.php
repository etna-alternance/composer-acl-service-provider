<?php

namespace ETNA\FeatureContext;

use Silex;

trait SilexApplication
{
    static private $silex_app;

    /**
     * @BeforeScenario
     */
    static public function setupSilexApplication()
    {
        self::$silex_app = new Silex\Application();
        self::$silex_app["auth.force_guest"]         = true;
        self::$silex_app["auth.cookie_expiration"]   = false;
        self::$silex_app["auth.public_key.tmp_path"] = realpath(__DIR__ . "/../../../tmp/") . "/public-local.testing.key";
        self::$silex_app["auth.authenticator_url"] = "file://" . __DIR__ . "/../../../tmp/keys/";
        self::$silex_app["auth.cookie_expiration"] = "+10minutes";

        self::$silex_app->register(new \ETNA\Silex\Provider\Auth\AuthServiceProvider());
        global $app;
        $app = self::$silex_app;
    }
}
