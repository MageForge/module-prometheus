<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Controller;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\ResponseInterface;
use MageForge\Prometheus\Service\MetricsService;

class Metrics implements FrontControllerInterface
{
    public function __construct(
        protected readonly MetricsService $metricsService,
        protected readonly HttpResponse $httpResponse
    ) {
    }

    public function dispatch(RequestInterface $request) : ResponseInterface
    {
        // TODO: collect metrics if configured so
        // TODO: IP whitelisting

        return $this->httpResponse->setBody($this->metricsService->getTextOutput());
    }
}
