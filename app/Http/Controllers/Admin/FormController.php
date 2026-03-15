<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormType;

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
     * Display the hatchery sullair form page.
     */
    public function hatcherySullair()
    {
        return view('shared.forms.hatchery-sullair');
    }

    /**
     * Display the hatcher machine accuracy temperature checking form page.
     */
    public function hatcherMachineAccuracy()
    {
        return view('shared.forms.hatcher-machine-accuracy');
    }

    /**
     * Display the incubator machine accuracy temperature checking form page.
     */
    public function incubatorMachineAccuracy()
    {
        return view('shared.forms.incubator-machine-accuracy');
    }

    /**
     * Display the plenum temperature and humidity monitoring form page.
     */
    public function plenumTempHumidity()
    {
        return view('shared.forms.plenum-temp-humidity');
    }

    /**
     * Display the entrance damper spacing monitoring form page.
     */
    public function entranceDamperSpacing()
    {
        return view('shared.forms.entrance-damper-spacing');
    }

    /**
     * Display the incubator entrance temperature monitoring form page.
     */
    public function incubatorEntranceTemp()
    {
        return view('shared.forms.incubator-entrance-temp');
    }

    /**
     * Display the incubator temperature calibration form page.
     */
    public function incubatorTempCalibration()
    {
        return view('shared.forms.incubator-temp-calibration');
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
