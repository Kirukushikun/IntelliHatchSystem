<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_type_id',
        'form_inputs',
        'date_submitted',
        'uploaded_by',
    ];

    protected $casts = [
        'form_inputs' => 'array',
        'date_submitted' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function formType(): BelongsTo
    {
        return $this->belongsTo(FormType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get machine information from form inputs
     */
    public function getMachineInfoAttribute(): array
    {
        $formInputs = $this->form_inputs;
        $machineInfo = [
            'table' => null,
            'id' => null,
            'name' => null
        ];

        // Check for different machine types in form_inputs
        if (isset($formInputs['incubator']) && !empty($formInputs['incubator'])) {
            $machineId = $formInputs['incubator'];
            $machine = DB::table('incubator-machines')
                ->where('id', $machineId)
                ->first();
            
            if ($machine) {
                $machineInfo = [
                    'table' => 'incubator-machines',
                    'id' => $machineId,
                    'name' => $machine->incubatorName
                ];
            }
        } elseif (isset($formInputs['hatcher']) && !empty($formInputs['hatcher'])) {
            $machineId = $formInputs['hatcher'];
            $machine = DB::table('hatcher-machines')
                ->where('id', $machineId)
                ->first();
            
            if ($machine) {
                $machineInfo = [
                    'table' => 'hatcher-machines',
                    'id' => $machineId,
                    'name' => $machine->hatcherName
                ];
            }
        }

        return $machineInfo;
    }
}
