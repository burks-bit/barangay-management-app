<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>{{ $serviceRequest->tracking_number }}</title>
	<style>
		@page { margin: 22mm 18mm 20mm; }
		body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
		.pdf-header { text-align: center; margin-bottom: 20px; }
		.barangay-name { font-size: 17px; font-weight: bold; text-transform: uppercase; }
		.barangay-detail { color: #4b5563; margin-top: 3px; }
		.header-rule { border-bottom: 2px solid #1f2937; margin-top: 10px; }
		.subtitle { text-align: center; color: #6b7280; margin-bottom: 24px; }
		.row { margin: 8px 0; }
		.label { display: inline-block; width: 140px; font-weight: bold; }
		.content { border: 1px solid #d1d5db; padding: 16px; margin-top: 20px; min-height: 180px; }
		.signatures { margin-top: 45px; width: 100%; }
		.signature { display: inline-block; width: 42%; border-top: 1px solid #374151; text-align: center; padding-top: 6px; margin-right: 7%; }
		.signature:last-child { margin-right: 0; }
		.pdf-footer { border-top: 1px solid #d1d5db; color: #6b7280; font-size: 9px; margin-top: 35px; padding-top: 7px; text-align: center; }
	</style>
</head>
<body>
	@include('pdf.header', ['barangay' => $barangay])

	<div class="subtitle">{{ $serviceRequest->requestType?->name }}<br>{{ $serviceRequest->tracking_number }}</div>
	<div class="row"><span class="label">Requester:</span>{{ $serviceRequest->requester?->memberProfile?->full_name ?: $serviceRequest->resident?->full_name ?: $serviceRequest->requester?->name }}</div>
	<div class="row"><span class="label">Purpose:</span>{{ $serviceRequest->purpose }}</div>
	<div class="row"><span class="label">Released:</span>{{ $serviceRequest->released_at?->format('F d, Y') }}</div>
	<div class="content">{!! strip_tags($serviceRequest->document_content, '<p><br><strong><b><em><i><u><ol><ul><li><a><span>') !!}</div>
	<div class="signatures"><div class="signature">{{ $serviceRequest->encoder?->name }}<br>Encoded by</div><div class="signature">{{ $serviceRequest->approverOfficial?->full_name }}<br>Approved and released by</div></div>

	@include('pdf.footer', ['serviceRequest' => $serviceRequest])
</body>
</html>