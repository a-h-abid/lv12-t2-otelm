<?php

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\ResourceAttributes;

$resource = ResourceInfoFactory::emptyResource()->merge(ResourceInfo::create(Attributes::create([
    ResourceAttributes::SERVICE_NAMESPACE => getenv('OTEL_SERVICE_NAMESPACE') ?: 'test-ns',
    ResourceAttributes::SERVICE_NAME => getenv('OTEL_SERVICE_NAME') ?: 'test-app-name',
    ResourceAttributes::SERVICE_VERSION => getenv('OTEL_SERVICE_VERSION') ?: '0.1',
    ResourceAttributes::DEPLOYMENT_ENVIRONMENT_NAME => getenv('OTEL_SERVICE_ENVIRONMENT') ?: 'local',
])));


// Configure Tracer
// $traceTransporter = (new StreamTransportFactory())->create('php://stdout', 'application/json');
// $spanExporter = new SpanExporter($traceTransporter);
// $tracerProvider = TracerProvider::builder()
//     ->addSpanProcessor(
//         new SimpleSpanProcessor($spanExporter)
//     )
//     ->setResource($resource)
//     ->setSampler(new ParentBased(new AlwaysOnSampler()))
//     ->build();
$tracerProvider = (new \OpenTelemetry\SDK\Trace\TracerProviderFactory())->create();

// Configure Meter
// $meterReader = new ExportingReader(
//     new MetricExporter(
//         (new StreamTransportFactory())->create('php://stdout', 'application/json')
//     )
// );
// $meterProvider = MeterProvider::builder()
//     ->setResource($resource)
//     ->addReader($meterReader)
//     ->build();

// Configure Logger
// $logExporter = new LogsExporter(
//     (new StreamTransportFactory())->create('php://stdout', 'application/json')
// );
// $loggerProvider = LoggerProvider::builder()
//     ->setResource($resource)
//     ->addLogRecordProcessor(
//         new SimpleLogRecordProcessor($logExporter)
//     )
//     ->build();

// Register the SDK
Sdk::builder()
    ->setTracerProvider($tracerProvider)
    // ->setMeterProvider($meterProvider)
    // ->setLoggerProvider($loggerProvider)
    ->setPropagator(TraceContextPropagator::getInstance())
    ->setAutoShutdown(true)
    ->buildAndRegisterGlobal();
