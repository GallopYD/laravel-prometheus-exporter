<?php
namespace Tests\Middleware;

use Tests\TestCase;
use Illuminate\Routing\Router;
use Triadev\PrometheusExporter\Contract\PrometheusExporterContract;

class RequestPerRouteTest extends TestCase
{
    /** @var PrometheusExporterContract */
    private $prometheusExporter;
    
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
        
        /** @var Router $router */
        $router = $this->app['router'];
        
        $router->get('requestPerRoute', function () {
            return 'valid';
        })->middleware('lpe.requestPerRoute')->name('requestPerRoute');
        
        $this->prometheusExporter = app(PrometheusExporterContract::class);
    }
    
    /**
     * @test
     */
    public function it_counts_metrics_for_request_per_route_middleware()
    {
        $this->get('requestPerRoute');
        
        $metricResponse = $this->get('/triadev/pe/metrics');
        
        $requestsTotal = null;
        if (preg_match('/app_requests_total{route="requestPerRoute",method="GET",status_code="200"} (?<metric>[0-9]+)/', $metricResponse->getContent(), $matches)) {
            $requestsTotal = $matches['metric'];
        }
        
        $requestsLatencyTotal = null;
        if (preg_match('/app_requests_latency_milliseconds_count{route="requestPerRoute",method="GET",status_code="200"} (?<metric>[0-9]+)/', $metricResponse->getContent(), $matches)) {
            $requestsLatencyTotal = $matches['metric'];
        }
        
        $this->assertEquals(1, $requestsTotal);
        $this->assertEquals(1, $requestsLatencyTotal);
    }
}