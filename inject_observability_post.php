<?php

require __DIR__ . '/bootstrap/app.php';

use Modules\Blog\Entities\Post;

$slug = 'observability-from-day-1';

$exists = Post::where('slug', $slug)->exists();
if ($exists) {
    echo "Post already exists\n";
    return;
}

$data = [
    'slug' => $slug,
    'title' => 'Observability from day 1: how we ship with confidence',
    'subtitle' => 'SLIs, SLOs, runbooks, and cost-aware telemetry at Automaton Soft',
    'category' => 'platform',
    'series' => 'delivery',
    'excerpt' => 'How we bake tracing, metrics, and logs into every service, keep SLOs honest, and avoid surprise cloud bills.',
    'content' => "## Why observability first\n- Faster MTTR and fewer regressions\n- Shared language with business via SLIs/SLOs\n- Enables safe releases (feature flags + fast rollback)\n\n## Our baseline stack\n- Metrics: Prometheus + VictoriaMetrics for long retention\n- Traces: OpenTelemetry + Tempo, tail sampling on noisy spans\n- Logs: Loki + JSON everywhere\n- Dashboards: Grafana folders per domain\n\n## SLIs we start with\n- Availability: success_rate p99 per critical endpoint\n- Latency: p95/p99 by endpoint and tenant\n- Saturation: queue depth, CPU/mem requests vs limits\n- Business: checkout success, payment auth rate, onboarding completion\n\n## SLO workflow\n1) Define user journey and error budget with product.\n2) Wire SLIs from golden signals + domain metrics.\n3) Alert on error budget burn rate, not single spikes.\n4) Run weekly SLO review, rotate ownership.\n\n## Runbooks and readiness\n- Each alert links to a runbook (playbook) with: owner, steps, rollback, comms template.\n- Chaos drills on staging monthly; on-call games every quarter.\n- Preflight checklist for new services: health endpoints, OTel enabled, dashboards shipped, alerts dry-run.\n\n## Cost-aware telemetry\n- Drop/aggregate verbose logs at the edge.\n- Tail sampling for traces; keep 100% for errors.\n- Cardinality guardrails on labels (tenant, route templates only).\n\n## Results\n- P99 checkout latency down 22%.\n- On-call pages/week cut by 40% after burn-rate alerts.\n- New service onboarding with full dashboards in under 1 day.\n\n## Lessons learned\n- Start with 5-7 charts per service, not 50.\n- Alert on symptoms + budgets, not every spike.\n- Treat observability as a product: version it, review it, own it.",
    'content_blocks' => [
        ['type' => 'hero', 'cover' => '/images/404.jpg', 'kpi' => '22% p99 improvement'],
        ['type' => 'stack', 'items' => ['OpenTelemetry', 'Tempo', 'Loki', 'Prometheus', 'Grafana']],
        ['type' => 'metrics', 'items' => [
            ['label' => 'P99 checkout', 'value' => '-22%'],
            ['label' => 'Pages/week', 'value' => '-40%'],
            ['label' => 'Onboarding dashboards', 'value' => '<1 day'],
        ]],
    ],
    'cover_image' => '/images/404.jpg',
    'og_image' => '/images/404.jpg',
    'tags' => ['observability', 'sre', 'otel', 'slo', 'grafana'],
    'language' => 'en',
    'status' => 'published',
    'is_featured' => true,
    'reading_time' => 9,
    'meta_title' => 'Observability from day 1: how we ship with confidence',
    'meta_description' => 'SLIs/SLOs, OpenTelemetry, and cost-aware telemetry practices we apply at Automaton Soft.',
    'canonical_url' => null,
    'published_at' => '2024-11-12 00:00:00',
    'author' => 'Automaton Soft',
];

Post::create($data);

echo "Inserted post: {$slug}\n";
