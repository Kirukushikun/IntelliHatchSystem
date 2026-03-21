<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InsightsController extends Controller
{
    public function index()
    {
        return view('admin.insights');
    }

    public function show(int $formTypeId)
    {
        abort_if(! FormType::where('id', $formTypeId)->exists(), 404);

        return view('admin.insights-detail', compact('formTypeId'));
    }

    public function printView(Request $request, int $formTypeId)
    {
        $formType = FormType::findOrFail($formTypeId);

        $context = in_array($request->input('context'), ['week', 'month'])
            ? $request->input('context')
            : 'week';

        $cached = Cache::get("insights:form:{$formTypeId}:{$context}");

        abort_if(! $cached, 404, 'No insight found for this context. Please generate insights first.');

        return view('admin.insights-print', [
            'formTypeName' => $formType->form_name,
            'context'      => $context,
            'insight'      => $cached['insight'],
            'generatedAt'  => $cached['generated_at'],
        ]);
    }
}

