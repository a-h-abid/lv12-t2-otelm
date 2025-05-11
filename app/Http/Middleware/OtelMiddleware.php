<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use Symfony\Component\HttpFoundation\Response;

class OtelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);

        $tracer = Globals::tracerProvider()->getTracer('otel.laravel.http');

        $parent = Context::getCurrent();

        $spanBuilder = $tracer->spanBuilder('HTTP Request')
            ->setParent($parent)
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->setAttribute('http.method', $request->getMethod())
            ->setAttribute('http.url', $request->fullUrl())
            ->setAttribute('http.user_agent', $request->userAgent())
            ->setAttribute('http.request_id', $request->headers->get('X-Request-ID'))
            ->setAttribute('http.client_ip', $request->ip())
            ->setAttribute('http.route', $request->route()->getName());

        $span = $spanBuilder->startSpan();

        $response = $next($request);

        $span->setAttribute('http.status_code', $response->getStatusCode());
        $span->setAttribute('http.response_time', ((microtime(true) - $startTime) / 1000) . 'ms');
        $span->setAttribute('http.response_size', $response->headers->get('Content-Length'));
        $span->setAttribute('http.response_headers', json_encode($response->headers->all()));
        $span->end();

        return $response;
    }
}
