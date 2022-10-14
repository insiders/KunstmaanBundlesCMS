<?php

namespace Kunstmaan\DashboardBundle\Helper\Google\Analytics;

use Google_AnalyticsService;
use Kunstmaan\DashboardBundle\Helper\Google\ClientHelper;

class ServiceHelper
{
    /** @var ClientHelper */
    private $clientHelper;

    public function __construct(ClientHelper $clientHelper)
    {
        $this->clientHelper = $clientHelper;
    }

    /**
     * @return Google_AnalyticsService
     */
    public function getService()
    {
        return new Google_AnalyticsService($this->clientHelper->getClient());
    }

    /**
     * @return ClientHelper
     */
    public function getClientHelper()
    {
        return $this->clientHelper;
    }
}
