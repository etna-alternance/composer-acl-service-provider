<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container) {
    $container->parameters()->set("application_name", "app");

    $container->extension("framework", [
        "secret" => getenv("APP_SECRET"),
        "test"   => true
    ]);
};
