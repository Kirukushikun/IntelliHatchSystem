<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_types', function (Blueprint $table) {
            $table->string('impact_level')->nullable()->after('form_name');
        });

        $classifications = [
            'Incubator Routine Checklist Per Shift'                    => 'direct',
            'Hatcher Blower Air Speed Monitoring'                      => 'direct_indirect',
            'Incubator Blower Air Speed Monitoring'                    => 'direct_indirect',
            'Hatchery Sullair Air Compressor Weekly PMS Checklist'     => 'direct_indirect',
            'Hatcher Machine Accuracy Temperature Checking'            => 'direct',
            'Plenum Temperature and Humidity Monitoring'               => 'indirect',
            'Incubator Machine Accuracy Temperature Checking'          => 'direct',
            'Entrance Damper Spacing Monitoring'                       => 'indirect',
            'Incubator Entrance Temperature Monitoring'                => 'indirect',
            'Incubator Temperature Calibration'                        => 'direct',
            'Hatcher Temperature Calibration'                          => 'direct',
            'PASGAR Score'                                             => 'direct',
            'Incubator Rack Preventive Maintenance Checklist'          => 'support',
            'Weekly Voltage and Ampere Monitoring'                     => 'direct_indirect',
            'Hatchery Diesel Generator Weekly Maintenance Checklist'   => 'direct',
        ];

        foreach ($classifications as $formName => $level) {
            DB::table('form_types')
                ->where('form_name', $formName)
                ->update(['impact_level' => $level]);
        }
    }

    public function down(): void
    {
        Schema::table('form_types', function (Blueprint $table) {
            $table->dropColumn('impact_level');
        });
    }
};
