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
        $forms = Form::with(['formType', 'user'])
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
     * Display the blower air hatcher form page.
     */
    public function blowerAirHatcher()
    {
        return view('shared.forms.blower-air-hatcher');
    }

    /**
     * Display the blower air incubator form page.
     */
    public function blowerAirIncubator()
    {
        return view('shared.forms.blower-air-incubator');
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
