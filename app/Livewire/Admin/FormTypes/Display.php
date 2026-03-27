<?php

namespace App\Livewire\Admin\FormTypes;

use App\Models\FormType;
use App\Services\ActivityLogger;
use Livewire\Component;

class Display extends Component
{
    public function updateImpactLevel(int $id, ?string $level): void
    {
        $formType = FormType::findOrFail($id);

        $normalizedLevel = in_array($level, ['direct', 'direct_indirect', 'indirect', 'support'])
            ? $level
            : null;

        $formType->update(['impact_level' => $normalizedLevel]);

        ActivityLogger::log(
            action: 'form_type_tag_updated',
            description: "Impact level for \"{$formType->form_name}\" set to " . ($normalizedLevel ?? 'none'),
            subjectType: FormType::class,
            subjectId: $formType->id,
            properties: ['impact_level' => $normalizedLevel]
        );

        session()->flash('success', "Tag updated for \"{$formType->form_name}\".");
    }

    public function render()
    {
        return view('livewire.admin.form-types.display', [
            'formTypes' => FormType::orderBy('form_name')->get(),
        ]);
    }
}
