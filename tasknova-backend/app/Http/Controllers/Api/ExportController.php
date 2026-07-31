<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function tasksJson(Request $request, ExportService $exports): JsonResponse
    {
        return response()->json(['data' => $exports->tasks($request->user())->values()]);
    }

    public function tasksCsv(Request $request, ExportService $exports): StreamedResponse
    {
        $tasks = $exports->tasks($request->user());

        return response()->streamDownload(function () use ($tasks): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['id', 'title', 'description', 'subject', 'category_id', 'category', 'priority', 'status', 'due_date', 'due_time', 'created_at', 'updated_at']);

            foreach ($tasks as $task) {
                fputcsv($output, $task);
            }

            fclose($output);
        }, 'tasks.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function categoriesJson(Request $request, ExportService $exports): JsonResponse
    {
        return response()->json(['data' => $exports->categories($request->user())->values()]);
    }

    public function profileJson(Request $request, ExportService $exports): JsonResponse
    {
        return response()->json(['data' => $exports->profile($request->user())]);
    }
}
