<?php

namespace App\Http\Controllers;

use App\Models\ReportDefinition;
use App\Services\ReportBuilderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Census / report builder.
 *
 * Lets admin and moderators build analytics over raised service requests,
 * complaints and assistance requests, filtered by status (and other
 * dimensions), grouped by a chosen breakdown, and save them as reusable
 * named reports.
 */
class ReportBuilderController extends Controller
{
    public function __construct(private ReportBuilderService $reports)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Reports/Census/Index', [
            'reports' => $this->reports->listReports(),
        ]);
    }

    /**
     * Ad-hoc builder page. Accepts the current configuration via query
     * string so "Run" simply navigates with new params (partial reload).
     */
    public function builder(Request $request): Response
    {
        return Inertia::render('Reports/Census/Builder', $this->reports->builder($request));
    }

    public function store(Request $request)
    {
        return $this->handle(
            fn () => $this->reports->create($request, $request->user()->id),
            fn ($report) => redirect()->route('reports.census.show', $report)->with('success', 'Report saved successfully.'),
            'ReportBuilderController::store'
        );
    }

    public function show(ReportDefinition $report_definition): Response
    {
        return Inertia::render('Reports/Census/Show', $this->reports->show($report_definition));
    }

    public function update(Request $request, ReportDefinition $report_definition)
    {
        return $this->handle(
            fn () => $this->reports->update($request, $report_definition),
            fn () => redirect()->route('reports.census.show', $report_definition)->with('success', 'Report updated successfully.'),
            'ReportBuilderController::update'
        );
    }

    public function destroy(ReportDefinition $report_definition)
    {
        return $this->handle(
            fn () => $this->reports->delete($report_definition),
            fn () => redirect()->route('reports.census.index')->with('success', 'Report deleted successfully.'),
            'ReportBuilderController::destroy'
        );
    }

    /**
     * Print an ad-hoc builder configuration as a PDF (opens in a new tab).
     */
    public function printBuilder(Request $request)
    {
        return $this->reports->printBuilder($request);
    }

    /**
     * Print a saved report definition as a PDF (opens in a new tab).
     */
    public function print(Request $request, ReportDefinition $report_definition)
    {
        return $this->reports->print($report_definition);
    }
}