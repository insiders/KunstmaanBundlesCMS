<?php

namespace Kunstmaan\SeoBundle\Controller;

use Kunstmaan\SeoBundle\Event\RobotsEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class RobotsController extends AbstractController
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    #[Route(path: '/robots.txt', name: 'KunstmaanSeoBundle_robots', defaults: ['_format' => 'txt'])]
    public function indexAction(Request $request): Response
    {
        $event = new RobotsEvent();

        $event = $this->dispatcher->dispatch($event);

        return $this->render('@KunstmaanSeo/Admin/Robots/index.html.twig', ['robots' => $event->getContent()]);
    }
}
