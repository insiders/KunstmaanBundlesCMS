<?php
declare(strict_types=1);

namespace Kunstmaan\MultiDomainBundle\EventListener;

use Kunstmaan\AdminBundle\FlashMessages\FlashTypes;
use Kunstmaan\AdminBundle\Helper\DomainConfigurationInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AdminRedirectListener
{
    protected $domainConfiguration;
    protected $adminKey;

    public function __construct(
        DomainConfigurationInterface $domainConfiguration,
        $adminKey
    ) {
        $this->domainConfiguration = $domainConfiguration;
        $this->adminKey = $adminKey;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();
        if ($response instanceof RedirectResponse) {
            return;
        }

        $request = $event->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        preg_match(sprintf('/^\/%s$(\/)?/', $this->adminKey), $request->getPathInfo(), $maches);
        if (count($maches) === 0) {
            return;
        }

        $host = $request->getHost();
        $domainHost = $this->domainConfiguration->getHost();

        if ($request->getHost() !== $this->domainConfiguration->getHost()) {
            // Add flash message for admin pages
            $this->session->getFlashBag()->add(
                FlashTypes::WARNING,
                $this->translator->trans('multi_domain.host_override_active')
            );
        }
    }
}
