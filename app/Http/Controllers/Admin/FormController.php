<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormType;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * Display the forms listing page.
     */
    public function index()
    {
        $forms = Form::with(['formType', 'user', 'incubator'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $formTypes = FormType::all();

        return view('shared.forms', compact('forms', 'formTypes'));
    }

    /**
     * Display the incubator routine form page.
     */
    public function incubatorRoutine()
    {
        return view('shared.forms.incubator-routine');
    }

    /**
     * Display the blower air form page.
     */
    public function blowerAir()
    {
        return view('shared.forms.blower-air');
    }

    /**
     * Store a newly submitted form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_type_id' => 'required|exists:form_types,id',
            'form_inputs' => 'required|array',
            'uploaded_by' => 'nullable|exists:users,id',
            'incubator_id' => 'nullable|exists:incubator-machines,id',
        ]);

        Form::create([
            'form_type_id' => $validated['form_type_id'],
            'form_inputs' => json_encode($validated['form_inputs']),
            'date_submitted' => now(),
            'uploaded_by' => $validated['uploaded_by'] ?? null,
            'incubator_id' => $validated['incubator_id'] ?? null,
        ]);

        return redirect()->back()
            ->with('success', 'Form submitted successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Form $form)
    {
        $formName = $form->formType->form_name ?? 'Unknown form';
        $form->delete();

        return redirect()->route('admin.forms')
            ->with('success', "Form '{$formName}' deleted successfully.");
    }
}
