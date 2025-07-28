<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class MetricsController extends Controller
{
    public function __invoke(CollectorRegistry $registry, RenderTextFormat $renderer): Response
    {
        $metrics = $renderer->render($registry->getMetricFamilySamples());

        return response($metrics, 200)
            ->header('Content-Type', RenderTextFormat::MIME_TYPE);
    }
}
