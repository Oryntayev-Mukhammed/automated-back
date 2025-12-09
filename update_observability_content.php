<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$content = <<<MD
## Why observability first
- Faster MTTR and fewer regressions
- Shared language with business via SLIs/SLOs
- Enables safe releases (feature flags + fast rollback)

## Our baseline stack
- Metrics: Prometheus + VictoriaMetrics for long retention
- Traces: OpenTelemetry + Tempo, tail sampling on noisy spans
- Logs: Loki + JSON everywhere
- Dashboards: Grafana folders per domain

## SLIs we start with
- Availability: success_rate p99 per critical endpoint
- Latency: p95/p99 by endpoint and tenant
- Saturation: queue depth, CPU/mem requests vs limits
- Business: checkout success, payment auth rate, onboarding completion

## SLO workflow
1) Define user journey and error budget with product.
2) Wire SLIs from golden signals + domain metrics.
3) Alert on error budget burn rate, not single spikes.
4) Run weekly SLO review, rotate ownership.

## Runbooks and readiness
- Each alert links to a runbook (playbook) with: owner, steps, rollback, comms template.
- Chaos drills on staging monthly; on-call games every quarter.
- Preflight checklist for new services: health endpoints, OTel enabled, dashboards shipped, alerts dry-run.

## Cost-aware telemetry
- Drop/aggregate verbose logs at the edge.
- Tail sampling for traces; keep 100% for errors.
- Cardinality guardrails on labels (tenant, route templates only).

## Results
- P99 checkout latency down 22%.
- On-call pages/week cut by 40% after burn-rate alerts.
- New service onboarding with full dashboards in under 1 day.

## Lessons learned
- Start with 5-7 charts per service, not 50.
- Alert on symptoms + budgets, not every spike.
- Treat observability as a product: version it, review it, own it.
MD;

DB::table('posts')->where('slug', 'observability-from-day-1')->update(['content' => $content]);

echo "Updated content for observability-from-day-1\n";
