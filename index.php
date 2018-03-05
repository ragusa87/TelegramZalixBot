<?php

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

require __DIR__ . '/vendor/autoload.php';

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle()
        );
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', array(
            'secret' => isset($_SERVER["SECRET"]) ? $_SERVER["SECRET"] : md5(date("Ymd"))
        ));
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/{apiKey}', 'kernel:telegramAction');
        $routes->add("/", "kernel:indexAction");
    }

    /**
     * Tiny wrapper for DefaultController
     */
    public function telegramAction(Request $request, $apiKey)
    {
        $default = new \App\DefaultController();
        return $default->handle($request, $apiKey);
    }

    /**
     * Homepage
     */
    public function indexAction()
    {
        return new \Symfony\Component\HttpFoundation\Response("Please use this as a telegram bot");
    }
}

$env = isset($_SERVER["env"]) ? $_SERVER["env"] : "dev";
$kernel = new Kernel($env, "dev" === $env);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);