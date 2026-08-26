<div class="subtitle">
    <div class="report-title">{{ $title }}</div>
    @if ($description)
        <div class="report-description">{{ $description }}</div>
    @endif
</div>

<!-- Configuration summary -->
<table class="config-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="label">Dataset:</td>
        <td>{{ $datasetLabel }}</td>
        <td class="label">Grouped By:</td>
        <td>{{ $groupLabel }}</td>
    </tr>
    <tr>
        <td class="label">Status Filters:</td>
        <td>{{ $statusLabel }}</td>
        <td class="label">Date Range:</td>
        <td>{{ $dateRangeLabel }}</td>
    </tr>
    <tr>
        <td class="label">{{ $secondaryFilterLabel }}:</td>
        <td colspan="3">{{ $secondaryValue ?? 'All' }}</td>
    </tr>
</table>

<!-- Summary -->
<table class="summary-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="summary-cell">
            <div class="summary-value">{{ number_format($results['total']) }}</div>
            <div class="summary-caption">Total Records</div>
        </td>
        <td class="summary-cell">
            <div class="summary-value">{{ count($results['rows']) }}</div>
            <div class="summary-caption">Breakdown Groups</div>
        </td>
        <td class="summary-cell">
            <div class="summary-value">{{ $generatedAt }}</div>
            <div class="summary-caption">Generated At</div>
        </td>
    </tr>
</table>

<!-- Breakdown -->
<h3 class="section-title">Breakdown by {{ $groupLabel }}</h3>
@if (count($results['rows']))
<table class="data-table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="text-left">{{ $groupLabel }}</th>
            <th class="text-right" style="width: 60px;">Count</th>
            <th class="text-right" style="width: 70px;">Share</th>
            <th class="text-left" style="width: 180px;">Distribution</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($results['rows'] as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td class="text-right">{{ number_format($row['count']) }}</td>
                <td class="text-right">{{ $results['total'] ? round(($row['count'] / $results['total']) * 100, 1) : 0 }}%</td>
                <td>
                    <div class="bar-track"><div class="bar-fill" style="width: {{ $maxRowCount ? round(($row['count'] / $maxRowCount) * 100, 1) : 0 }}%;"></div></div>
                </td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td>Total</td>
            <td class="text-right">{{ number_format($results['total']) }}</td>
            <td class="text-right">100%</td>
            <td></td>
        </tr>
    </tbody>
</table>
@else
<p class="empty-note">No records match the selected filters.</p>
@endif

<!-- Monthly trend -->
<h3 class="section-title">Last 6 Months Trend</h3>
@if (count($results['monthly']))
<table class="data-table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="text-left">Month</th>
            <th class="text-right" style="width: 80px;">Records</th>
            <th class="text-left" style="width: 220px;">Trend</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($results['monthly'] as $month)
            <tr>
                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $month['month'])->format('M Y') }}</td>
                <td class="text-right">{{ number_format($month['count']) }}</td>
                <td>
                    <div class="bar-track"><div class="bar-fill bar-blue" style="width: {{ $maxMonthlyCount ? round(($month['count'] / $maxMonthlyCount) * 100, 1) : 0 }}%;"></div></div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@else
<p class="empty-note">No records in the last 6 months.</p>
@endif