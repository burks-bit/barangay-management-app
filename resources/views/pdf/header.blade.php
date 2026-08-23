<div class="pdf-header">
    <div class="barangay-name">{{ $barangay?->name ?? config('app.name', 'Barangay Management System') }}</div>
    @if ($barangay?->address)
        <div class="barangay-detail">{{ $barangay->address }}</div>
    @endif
    @if ($barangay?->contact_number)
        <div class="barangay-detail">{{ $barangay->contact_number }}</div>
    @endif
    <div class="header-rule"></div>
</div>
