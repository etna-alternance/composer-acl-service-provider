<?php
/**
 * PHP version 7.1
 * @author BLU <dev@etna-alternance.net>
 */

declare(strict_types=1);

namespace ETNA\Acl\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Cette classe décrit le service auth qui va intéragir directement avec
 * le cookie authenticator contenu dans la requête HTTP.
 */
class AclAccessService implements EventSubscriberInterface
{
    /** @var ContainerInterface Le conteneur symfony */
    private $container;

    /**
     * Constructeur du service.
     *
     * @param ContainerInterface $container Le container de l'application symfony
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * C'est la fonction qui sera appelée par symfony lors d'un des events indiqué par getSubscribedEvents.
     *
     * @param FilterControllerEvent $event L'évènement
     */
    public function onKernelController(FilterControllerEvent $event): void
    {
        $controller = $event->getController();

        /*
         * cf la doc de symfony :
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!\is_array($controller)) {
            return;
        }

        $this->userAccess($event->getRequest());
    }

    /**
     * Parse all users roles to transform $app['auth.app_name']_role to role
     * and verify if the user is not close.
     *
     * @param Request $req
     */
    private function userAccess(Request $req): void
    {
        // We only match api's calls
        $regex = '#^/api/?#';
        if (true === $this->container->hasParameter('auth.api_path')) {
            $regex = '#' . $this->container->getParameter('auth.api_path') . '#';
        }

        $user = $req->attributes->get('auth.user', null);
        switch (true) {
            // If the request route doesn't match the $app['auth.api_path']
            case 1 !== preg_match($regex, $req->getRequestUri()):
            // If the request method is OPTIONS
            case 'OPTIONS' === $req->getMethod():
            // If the request doesn't have credential
            case null === $user:
                return;
        }

        $app_name = $this->container->getParameter('auth.app_name');
        // We change all groups with prefix
        $user->groups = array_values(
            array_unique(
                array_map(
                    function ($role) use ($app_name) {
                        return str_replace("{$app_name}_", '', $role);
                    },
                    $user->groups
                )
            )
        );

        if (true === \in_array('close', $user->groups)) {
            throw new HttpException(403, 'You are closed for this app !');
        }

        $req->attributes->set('auth.user', $user);
    }

    /**
     * Retourne la liste des différents events sur lesquels cette classe va intervenir
     * En l'occurence, avant d'accéder à une des fonction d'un des controlleurs.
     *
     * @return array<string,array<string|integer>>
     */
    public static function getSubscribedEvents()
    {
        // 255 correspond à la plus haute priorité
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 254],
        ];
    }
}
