<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>{{ $title }}</title>
	<style>
		@page { margin: 22mm 18mm 20mm; }
		body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
		.pdf-header { text-align: center; margin-bottom: 20px; }
		.barangay-name { font-size: 17px; font-weight: bold; text-transform: uppercase; }
		.barangay-detail { color: #4b5563; margin-top: 3px; }
		.header-rule { border-bottom: 2px solid #1f2937; margin-top: 10px; }
		.subtitle { text-align: center; color: #6b7280; margin-bottom: 24px; }
		.report-title { font-size: 15px; font-weight: bold; color: #1f2937; text-transform: uppercase; }
		.report-description { font-size: 10px; margin-top: 4px; }
		.config-table { width: 100%; border: 1px solid #d1d5db; border-collapse: collapse; margin-bottom: 16px; font-size: 11px; }
		.config-table td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
		.config-table .label { font-weight: bold; width: 110px; color: #374151; white-space: nowrap; }
		.summary-table { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-bottom: 22px; }
		.summary-cell { width: 33.3%; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px 12px; text-align: center; }
		.summary-value { font-size: 15px; font-weight: bold; color: #1e3a8a; }
		.summary-caption { font-size: 9px; color: #6b7280; margin-top: 3px; }
		.section-title { font-size: 12px; font-weight: bold; color: #1f2937; border-bottom: 1px solid #d1d5db; padding-bottom: 5px; margin: 18px 0 10px; }
		.data-table { width: 100%; border-collapse: collapse; font-size: 11px; }
		.data-table th { background-color: #f3f4f6; border: 1px solid #d1d5db; padding: 6px 8px; font-weight: bold; color: #374151; }
		.data-table td { border: 1px solid #e5e7eb; padding: 6px 8px; }
		.total-row td { font-weight: bold; background-color: #f9fafb; }
		.text-left { text-align: left; }
		.text-right { text-align: right; }
		.bar-track { background-color: #e5e7eb; height: 10px; border-radius: 5px; overflow: hidden; }
		.bar-fill { background-color: #2563eb; height: 10px; }
		.bar-blue { background-color: #60a5fa; }
		.empty-note { text-align: center; color: #9ca3af; font-size: 11px; padding: 14px 0; }
		.pdf-footer { border-top: 1px solid #d1d5db; color: #6b7280; font-size: 9px; margin-top: 35px; padding-top: 7px; text-align: center; }
	</style>
</head>
<body>
	@include('pdfs.analytics.header', ['barangay' => $barangay])

	@include('pdfs.analytics.body')

	@include('pdfs.analytics.footer')
</body>
</html>