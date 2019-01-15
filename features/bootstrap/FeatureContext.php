<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use Symfony\Component\HttpFoundation\Request;

use ETNA\FeatureContext\BaseContext;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends BaseContext
{
    public function __construct()
    {
    }
}
