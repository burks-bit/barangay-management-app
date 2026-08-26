<?php

namespace App\Services;

use App\Models\AssistanceRequest;
use App\Models\AssistanceType;
use App\Models\BarangayProfile;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ReportDefinition;
use App\Models\RequestType;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Mpdf\Mpdf;

/**
 * Census / report builder.
 *
 * Lets admin and moderators build analytics over raised service requests,
 * complaints and assistance requests, filtered by status (and other
 * dimensions), grouped by a chosen breakdown, and save them as reusable
 * named reports.
 */
class ReportBuilderService
{
    /**
     * Dataset configuration: available group-by dimensions, statuses,
     * secondary filter source and the date column used for filtering/trends.
     */
    public const DATASETS = [
        'service_requests' => [
            'label' => 'Service Requests',
            'date_column' => 'submitted_at',
            'statuses' => ['submitted', 'for_verification', 'approved', 'rejected', 'processing', 'ready_for_release', 'released', 'cancelled'],
            'groups' => [
                'status' => 'Status',
                'type' => 'Document Type',
                'month' => 'Month Submitted',
                'source' => 'Source (Online / Walk-in)',
            ],
            'secondary_label' => 'Document Type',
        ],
        'complaints' => [
            'label' => 'Complaints',
            'date_column' => 'created_at',
            'statuses' => ['submitted', 'under_review', 'verified', 'assigned', 'under_investigation', 'for_mediation', 'action_taken', 'resolved', 'rejected', 'closed'],
            'groups' => [
                'status' => 'Status',
                'priority' => 'Priority',
                'category' => 'Category',
                'month' => 'Month Filed',
            ],
            'secondary_label' => 'Category',
        ],
        'assistance_requests' => [
            'label' => 'Assistance Requests',
            'date_column' => 'created_at',
            'statuses' => ['submitted', 'for_verification', 'under_assessment', 'approved', 'rejected', 'for_release', 'released', 'cancelled'],
            'groups' => [
                'status' => 'Status',
                'type' => 'Assistance Type',
                'month' => 'Month Requested',
            ],
            'secondary_label' => 'Assistance Type',
        ],
    ];

    public function listReports()
    {
        return ReportDefinition::with('creator:id,name')->latest()->get();
    }

    /**
     * Ad-hoc builder page data. Accepts the current configuration via query
     * string so "Run" simply navigates with new params (partial reload).
     */
    public function builder(Request $request): array
    {
        $config = $this->validatedConfig($request);

        return [
            'options' => $this->options(),
            'config' => $config,
            'results' => $request->boolean('run') ? $this->computeResults($config['dataset'], $config['group_by'], $config['filters']) : null,
        ];
    }

    public function create(Request $request, int $userId): ReportDefinition
    {
        $validated = $this->validateDefinition($request);

        return ReportDefinition::create([
            ...$validated,
            'created_by' => $userId,
        ]);
    }

    public function show(ReportDefinition $report_definition): array
    {
        $definition = $this->normalizeDefinition($report_definition);

        return [
            'report' => $report_definition->load('creator:id,name'),
            'options' => $this->options(),
            'config' => $definition['config'],
            'results' => $this->computeResults($definition['config']['dataset'], $definition['config']['group_by'], $definition['config']['filters']),
        ];
    }

    public function update(Request $request, ReportDefinition $report_definition): void
    {
        $validated = $this->validateDefinition($request, $report_definition);

        $report_definition->update($validated);
    }

    public function delete(ReportDefinition $report_definition): void
    {
        $report_definition->delete();
    }

    /**
     * Print an ad-hoc builder configuration as a PDF (opens in a new tab).
     */
    public function printBuilder(Request $request)
    {
        $config = $this->validatedConfig($request);

        return $this->streamAnalyticsPdf($this->analyticsPayload($config));
    }

    /**
     * Print a saved report definition as a PDF (opens in a new tab).
     */
    public function print(ReportDefinition $report_definition)
    {
        $definition = $this->normalizeDefinition($report_definition);

        return $this->streamAnalyticsPdf(
            $this->analyticsPayload($definition['config'], $report_definition)
        );
    }

    /**
     * Build the view payload for the analytics PDF.
     */
    private function analyticsPayload(array $config, ?ReportDefinition $definition = null): array
    {
        $meta = self::DATASETS[$config['dataset']];
        $filters = $config['filters'];
        $results = $this->computeResults($config['dataset'], $config['group_by'], $filters);

        $secondaryId = $filters['secondary_id'] ?? null;
        $secondaryValue = null;
        if ($secondaryId) {
            $secondaryValue = match ($config['dataset']) {
                'service_requests' => RequestType::find($secondaryId)?->name,
                'complaints' => ComplaintCategory::find($secondaryId)?->name,
                default => AssistanceType::find($secondaryId)?->name,
            };
        }

        return [
            'barangay' => BarangayProfile::where('is_active', true)->first(),
            'title' => $definition?->name ?? 'Census Analytics Report',
            'description' => $definition?->description,
            'datasetLabel' => $meta['label'],
            'groupLabel' => $meta['groups'][$config['group_by']] ?? ucfirst(str_replace('_', ' ', $config['group_by'])),
            'statusLabel' => collect($filters['statuses'] ?? [])
                ->map(fn ($status) => ucfirst(str_replace('_', ' ', $status)))
                ->implode(', ') ?: 'All statuses',
            'dateRangeLabel' => trim(($filters['from'] ?? '') . ' – ' . ($filters['to'] ?? '')) ?: 'All time',
            'secondaryFilterLabel' => $meta['secondary_label'],
            'secondaryValue' => $secondaryValue,
            'results' => $results,
            'maxRowCount' => $results['rows']->max('count') ?: 0,
            'maxMonthlyCount' => $results['monthly']->max('count') ?: 0,
            'generatedAt' => now()->format('M d, Y h:i A'),
            'generatedBy' => auth()->user()?->name ?? '-',
        ];
    }

    /**
     * Render the analytics PDF with mPDF and stream it inline so the
     * browser opens it in a new tab.
     */
    private function streamAnalyticsPdf(array $data)
    {
        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'tempDir' => storage_path('app/mpdf')]);
        $mpdf->WriteHTML(view('pdfs.analytics.report', $data)->render());

        $filename = Str::slug($data['title']) . '-' . now()->format('Ymd-His') . '.pdf';

        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    /**
     * Options payload consumed by the builder UI.
     */
    private function options(): array
    {
        $datasets = [];
        $groups = [];
        $statuses = [];
        $secondary = [];

        foreach (self::DATASETS as $value => $meta) {
            $datasets[] = ['value' => $value, 'label' => $meta['label']];
            $groups[$value] = collect($meta['groups'])->map(fn ($label, $key) => ['value' => $key, 'label' => $label])->values();
            $statuses[$value] = collect($meta['statuses'])->map(fn ($s) => ['value' => $s, 'label' => ucfirst(str_replace('_', ' ', $s))])->values();
        }

        $secondary['service_requests'] = [
            'label' => self::DATASETS['service_requests']['secondary_label'],
            'options' => RequestType::orderBy('name')->get(['id', 'name']),
        ];
        $secondary['complaints'] = [
            'label' => self::DATASETS['complaints']['secondary_label'],
            'options' => ComplaintCategory::orderBy('name')->get(['id', 'name']),
        ];
        $secondary['assistance_requests'] = [
            'label' => self::DATASETS['assistance_requests']['secondary_label'],
            'options' => AssistanceType::orderBy('name')->get(['id', 'name']),
        ];

        return compact('datasets', 'groups', 'statuses', 'secondary');
    }

    /**
     * Validate and normalize an ad-hoc configuration from the request.
     */
    private function validatedConfig(Request $request): array
    {
        $dataset = $request->input('dataset', 'service_requests');
        abort_unless(array_key_exists($dataset, self::DATASETS), 422, 'Unknown dataset.');

        $allowedGroups = array_keys(self::DATASETS[$dataset]['groups']);
        $group_by = $request->input('group_by', 'status');
        abort_unless(in_array($group_by, $allowedGroups, true), 422, 'Invalid grouping for this dataset.');

        $requestedStatuses = (array) $request->input('statuses', []);
        $statuses = array_values(array_intersect(self::DATASETS[$dataset]['statuses'], $requestedStatuses));

        return [
            'dataset' => $dataset,
            'group_by' => $group_by,
            'filters' => [
                'statuses' => $statuses,
                'from' => $request->input('from') ?: null,
                'to' => $request->input('to') ?: null,
                'secondary_id' => $request->input('secondary_id') ?: null,
            ],
        ];
    }

    /**
     * Validate a saved report definition payload.
     */
    private function validateDefinition(Request $request, ?ReportDefinition $existing = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'dataset' => ['required', Rule::in(array_keys(self::DATASETS))],
            'group_by' => ['required', 'string'],
            'filters.statuses' => ['nullable', 'array'],
            'filters.statuses.*' => ['string'],
            'filters.from' => ['nullable', 'date'],
            'filters.to' => ['nullable', 'date', 'after_or_equal:filters.from'],
            'filters.secondary_id' => ['nullable', 'integer'],
        ]);

        abort_unless(
            in_array($data['group_by'], array_keys(self::DATASETS[$data['dataset']]['groups']), true),
            422,
            'Invalid grouping for this dataset.'
        );

        $data['filters'] = [
            'statuses' => array_values(array_intersect(self::DATASETS[$data['dataset']]['statuses'], (array) ($data['filters']['statuses'] ?? []))),
            'from' => $data['filters']['from'] ?? null,
            'to' => $data['filters']['to'] ?? null,
            'secondary_id' => $data['filters']['secondary_id'] ?? null,
        ];

        return $data;
    }

    /**
     * Normalize a stored definition into the same shape as an ad-hoc config.
     */
    private function normalizeDefinition(ReportDefinition $definition): array
    {
        $filters = $definition->filters ?? [];

        return [
            'config' => [
                'dataset' => $definition->dataset,
                'group_by' => $definition->group_by,
                'filters' => [
                    'statuses' => array_values(array_intersect(
                        self::DATASETS[$definition->dataset]['statuses'],
                        (array) ($filters['statuses'] ?? [])
                    )),
                    'from' => $filters['from'] ?? null,
                    'to' => $filters['to'] ?? null,
                    'secondary_id' => $filters['secondary_id'] ?? null,
                ],
            ],
        ];
    }

    /**
     * Compute the analytics for a configuration: total, breakdown rows and
     * a 6-month trend (respecting the configured filters).
     */
    private function computeResults(string $dataset, string $groupBy, array $filters): array
    {
        $meta = self::DATASETS[$dataset];
        $dateColumn = match ($dataset) {
            'service_requests' => 'service_requests.submitted_at',
            default => match ($dataset) {
                'complaints' => 'complaints.created_at',
                default => 'assistance_requests.created_at',
            },
        };

        $base = $this->baseQuery($dataset, $filters);
        $total = (clone $base)->count();

        // Breakdown rows
        $breakdownQuery = $this->applyGrouping(clone $base, $dataset, $groupBy);
        $expression = $this->groupExpression($dataset, $groupBy);

        $rows = $breakdownQuery
            ->selectRaw("{$expression} as label, COUNT(*) as aggregate")
            ->groupBy('label')
            ->orderByDesc('aggregate')
            ->get()
            ->map(fn ($row) => [
                'label' => $this->formatLabel((string) $row->label, $groupBy),
                'count' => (int) $row->aggregate,
            ])
            ->values();

        // 6-month trend
        $monthly = (clone $base)
            ->selectRaw("DATE_FORMAT({$dateColumn}, '%Y-%m') as month, COUNT(*) as aggregate")
            ->where($dateColumn, '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => ['month' => $row->month, 'count' => (int) $row->aggregate])
            ->values();

        return [
            'total' => $total,
            'rows' => $rows,
            'monthly' => $monthly,
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Base filtered query for a dataset.
     */
    private function baseQuery(string $dataset, array $filters)
    {
        $statuses = $filters['statuses'] ?? [];
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $secondaryId = $filters['secondary_id'] ?? null;

        return match ($dataset) {
            'service_requests' => ServiceRequest::query()
                ->when($statuses, fn ($q) => $q->whereIn('status', $statuses))
                ->when($from, fn ($q) => $q->whereDate('submitted_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('submitted_at', '<=', $to))
                ->when($secondaryId, fn ($q) => $q->where('request_type_id', $secondaryId)),
            'complaints' => Complaint::query()
                ->when($statuses, fn ($q) => $q->whereIn('status', $statuses))
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->when($secondaryId, fn ($q) => $q->where('category_id', $secondaryId)),
            'assistance_requests' => AssistanceRequest::query()
                ->when($statuses, fn ($q) => $q->whereIn('status', $statuses))
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->when($secondaryId, fn ($q) => $q->where('assistance_type_id', $secondaryId)),
        };
    }

    /**
     * Add joins needed for the chosen grouping dimension.
     */
    private function applyGrouping($query, string $dataset, string $groupBy)
    {
        if ($dataset === 'service_requests' && $groupBy === 'type') {
            $query->join('request_types', 'service_requests.request_type_id', '=', 'request_types.id');
        }
        if ($dataset === 'complaints' && $groupBy === 'category') {
            $query->join('complaint_categories', 'complaints.category_id', '=', 'complaint_categories.id');
        }
        if ($dataset === 'assistance_requests' && $groupBy === 'type') {
            $query->join('assistance_types', 'assistance_requests.assistance_type_id', '=', 'assistance_types.id');
        }

        return $query;
    }

    /**
     * Raw SQL expression used to label each breakdown row.
     */
    private function groupExpression(string $dataset, string $groupBy): string
    {
        return match ([$dataset, $groupBy]) {
            ['service_requests', 'status'] => 'service_requests.status',
            ['service_requests', 'type'] => 'request_types.name',
            ['service_requests', 'month'] => "DATE_FORMAT(service_requests.submitted_at, '%Y-%m')",
            ['service_requests', 'source'] => 'service_requests.source',
            ['complaints', 'status'] => 'complaints.status',
            ['complaints', 'priority'] => 'complaints.priority',
            ['complaints', 'category'] => 'complaint_categories.name',
            ['complaints', 'month'] => "DATE_FORMAT(complaints.created_at, '%Y-%m')",
            ['assistance_requests', 'status'] => 'assistance_requests.status',
            ['assistance_requests', 'type'] => 'assistance_types.name',
            ['assistance_requests', 'month'] => "DATE_FORMAT(assistance_requests.created_at, '%Y-%m')",
            default => throw new \InvalidArgumentException('Unsupported grouping.'),
        };
    }

    /**
     * Humanize row labels (snake_case statuses, month numbers).
     */
    private function formatLabel(string $label, string $groupBy): string
    {
        if ($label === '') {
            return '(none)';
        }

        if ($groupBy === 'month' && preg_match('/^\d{4}-\d{2}$/', $label)) {
            return Carbon::createFromFormat('Y-m', $label)->format('M Y');
        }

        return ucfirst(str_replace('_', ' ', $label));
    }
}