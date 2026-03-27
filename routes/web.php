<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Shared\Management\HatcherController;
use App\Http\Controllers\Shared\Management\GetSetController;
use App\Http\Controllers\Shared\Management\HouseNumberController;
use App\Http\Controllers\Shared\Management\IncubatorController;
use App\Http\Controllers\Shared\Management\PlenumController;
use App\Http\Controllers\Shared\Management\PsNumberController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\FormsPrintController;
use App\Http\Controllers\Admin\InsightsController;

Route::get('/', function () {
    return view('landing');
});

// Public form routes
Route::get('/forms/incubator-routine', function () {
    return view('shared.forms.incubator-routine');
})->name('forms.incubator-routine');

Route::get('/forms/blower-air-hatcher', function () {
    return view('shared.forms.blower-air-hatcher');
})->name('forms.blower-air-hatcher');

Route::get('/forms/blower-air-incubator', function () {
    return view('shared.forms.blower-air-incubator');
})->name('forms.blower-air-incubator');

Route::get('/forms/hatchery-sullair', function () {
    return view('shared.forms.hatchery-sullair');
})->name('forms.hatchery-sullair');

Route::get('/forms/hatcher-machine-accuracy', function () {
    return view('shared.forms.hatcher-machine-accuracy');
})->name('forms.hatcher-machine-accuracy');

Route::get('/forms/plenum-temp-humidity', function () {
    return view('shared.forms.plenum-temp-humidity');
})->name('forms.plenum-temp-humidity');

Route::get('/forms/incubator-machine-accuracy', function () {
    return view('shared.forms.incubator-machine-accuracy');
})->name('forms.incubator-machine-accuracy');

Route::get('/forms/entrance-damper-spacing', function () {
    return view('shared.forms.entrance-damper-spacing');
})->name('forms.entrance-damper-spacing');

Route::get('/forms/incubator-entrance-temp', function () {
    return view('shared.forms.incubator-entrance-temp');
})->name('forms.incubator-entrance-temp');

Route::get('/forms/incubator-temp-calibration', function () {
    return view('shared.forms.incubator-temp-calibration');
})->name('forms.incubator-temp-calibration');

Route::get('/forms/hatcher-temp-calibration', function () {
    return view('shared.forms.hatcher-temp-calibration');
})->name('forms.hatcher-temp-calibration');

Route::get('/forms/pasgar-score', function () {
    return view('shared.forms.pasgar-score');
})->name('forms.pasgar-score');

Route::get('/forms/incubator-rack-pm', function () {
    return view('shared.forms.incubator-rack-pm');
})->name('forms.incubator-rack-pm');

Route::get('/forms/weekly-volt-ampere', function () {
    return view('shared.forms.weekly-volt-ampere');
})->name('forms.weekly-volt-ampere');

Route::get('/forms/diesel-generator-weekly', function () {
    return view('shared.forms.diesel-generator-weekly');
})->name('forms.diesel-generator-weekly');

// Guest routes (no authentication required)
Route::middleware('guest')->group(function () {
    // Unified login route
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    
    // Legacy admin login routes (redirect to unified login)
    Route::get('/admin/login', function () {
        return redirect('/login');
    })->name('admin.login');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin/forms', [FormController::class, 'index'])->name('admin.forms');

        Route::get('/admin/forms/incubator-routine', [FormController::class, 'incubatorRoutine'])->name('admin.forms.incubator-routine');
        
        Route::get('/admin/forms/blower-air-hatcher-routine', [FormController::class, 'blowerAirHatcher'])->name('admin.forms.blower-air-hatcher-routine');

        Route::get('/admin/forms/blower-air-incubator-routine', [FormController::class, 'blowerAirIncubator'])->name('admin.forms.blower-air-incubator-routine');

        Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/admin/insights', [InsightsController::class, 'index'])->name('admin.insights');
        Route::get('/admin/insights/{formTypeId}', [InsightsController::class, 'show'])->name('admin.insights.show');

        Route::get('/admin/incubator-routine-dashboard', [DashboardController::class, 'incubatorRoutine'])->name('admin.incubator-routine-dashboard');

        Route::get('/admin/blower-air-hatcher-dashboard', [DashboardController::class, 'blowerAirHatcher'])->name('admin.blower-air-hatcher-dashboard');

        Route::get('/admin/blower-air-incubator-dashboard', [DashboardController::class, 'blowerAirIncubator'])->name('admin.blower-air-incubator-dashboard');

        Route::get('/admin/hatchery-sullair-dashboard', [DashboardController::class, 'hatcherySullair'])->name('admin.hatchery-sullair-dashboard');

        Route::get('/admin/plenum-temp-humidity-dashboard', [DashboardController::class, 'plenumTempHumidity'])->name('admin.plenum-temp-humidity-dashboard');

        Route::get('/admin/hatcher-machine-accuracy-dashboard', [DashboardController::class, 'hatcherMachineAccuracy'])->name('admin.hatcher-machine-accuracy-dashboard');

        Route::get('/admin/incubator-machine-accuracy-dashboard', [DashboardController::class, 'incubatorMachineAccuracy'])->name('admin.incubator-machine-accuracy-dashboard');

        Route::get('/admin/entrance-damper-spacing-dashboard', [DashboardController::class, 'entranceDamperSpacing'])->name('admin.entrance-damper-spacing-dashboard');

        Route::get('/admin/incubator-entrance-temp-dashboard', [DashboardController::class, 'incubatorEntranceTemp'])->name('admin.incubator-entrance-temp-dashboard');

        Route::get('/admin/incubator-temp-calibration-dashboard', [DashboardController::class, 'incubatorTempCalibration'])->name('admin.incubator-temp-calibration-dashboard');

        Route::get('/admin/hatcher-temp-calibration-dashboard', [DashboardController::class, 'hatcherTempCalibration'])->name('admin.hatcher-temp-calibration-dashboard');

        Route::get('/admin/forms/hatcher-machine-accuracy', [FormController::class, 'hatcherMachineAccuracy'])->name('admin.forms.hatcher-machine-accuracy');

        Route::get('/admin/forms/plenum-temp-humidity', [FormController::class, 'plenumTempHumidity'])->name('admin.forms.plenum-temp-humidity');

        Route::get('/admin/forms/incubator-machine-accuracy', [FormController::class, 'incubatorMachineAccuracy'])->name('admin.forms.incubator-machine-accuracy');

        Route::get('/admin/forms/entrance-damper-spacing', [FormController::class, 'entranceDamperSpacing'])->name('admin.forms.entrance-damper-spacing');

        Route::get('/admin/forms/incubator-entrance-temp', [FormController::class, 'incubatorEntranceTemp'])->name('admin.forms.incubator-entrance-temp');

        Route::get('/admin/forms/incubator-temp-calibration', [FormController::class, 'incubatorTempCalibration'])->name('admin.forms.incubator-temp-calibration');

        Route::get('/admin/forms/hatcher-temp-calibration', [FormController::class, 'hatcherTempCalibration'])->name('admin.forms.hatcher-temp-calibration');

        Route::get('/admin/forms/pasgar-score', [FormController::class, 'pasgarScore'])->name('admin.forms.pasgar-score');

        Route::get('/admin/forms/incubator-rack-pm', [FormController::class, 'incubatorRackPm'])->name('admin.forms.incubator-rack-pm');

        Route::get('/admin/forms/weekly-volt-ampere', [FormController::class, 'weeklyVoltAmpere'])->name('admin.forms.weekly-volt-ampere');

        Route::get('/admin/forms/diesel-generator-weekly', [FormController::class, 'dieselGeneratorWeekly'])->name('admin.forms.diesel-generator-weekly');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');

        Route::get('/admin/incubator-machines', [IncubatorController::class, 'index'])->name('admin.incubator-machines');

        Route::get('/admin/hatcher-machines', [HatcherController::class, 'index'])->name('admin.hatcher-machines');

        Route::get('/admin/plenum-machines', [PlenumController::class, 'index'])->name('admin.plenum-machines');

        Route::get('/admin/ps-numbers', [PsNumberController::class, 'index'])->name('admin.ps-numbers');

        Route::get('/admin/house-numbers', [HouseNumberController::class, 'index'])->name('admin.house-numbers');

        Route::get('/admin/get-sets', [GetSetController::class, 'index'])->name('admin.get-sets');

        Route::get('/admin/print/forms/blower-air-hatcher', [FormsPrintController::class, 'blowerAirHatcher'])
            ->middleware('signed')
            ->name('admin.print.forms.blower-air-hatcher');

        Route::get('/admin/print/forms/blower-air-incubator', [FormsPrintController::class, 'blowerAirIncubator'])
            ->middleware('signed')
            ->name('admin.print.forms.blower-air-incubator');

        Route::get('/admin/print/forms/hatchery-sullair', [FormsPrintController::class, 'hatcherySullair'])
            ->middleware('signed')
            ->name('admin.print.forms.hatchery-sullair');

        Route::get('/admin/print/forms/incubator-routine', [FormsPrintController::class, 'incubatorRoutine'])
            ->middleware('signed')
            ->name('admin.print.forms.incubator-routine');

        Route::get('/admin/print/performance/incubator-routine', [FormsPrintController::class, 'incubatorRoutinePerformance'])
            ->middleware('signed')
            ->name('admin.print.performance.incubator-routine');

        Route::get('/admin/print/forms/plenum-temp-humidity', [FormsPrintController::class, 'plenumTempHumidity'])
            ->middleware('signed')
            ->name('admin.print.forms.plenum-temp-humidity');

        Route::get('/admin/print/forms/hatcher-machine-accuracy', [FormsPrintController::class, 'hatcherMachineAccuracy'])
            ->middleware('signed')
            ->name('admin.print.forms.hatcher-machine-accuracy');

        Route::get('/admin/print/forms/incubator-machine-accuracy', [FormsPrintController::class, 'incubatorMachineAccuracy'])
            ->middleware('signed')
            ->name('admin.print.forms.incubator-machine-accuracy');

        Route::get('/admin/print/forms/entrance-damper-spacing', [FormsPrintController::class, 'entranceDamperSpacing'])
            ->middleware('signed')
            ->name('admin.print.forms.entrance-damper-spacing');

        Route::get('/admin/print/forms/incubator-entrance-temp', [FormsPrintController::class, 'incubatorEntranceTemp'])
            ->middleware('signed')
            ->name('admin.print.forms.incubator-entrance-temp');

        Route::get('/admin/print/forms/incubator-temp-calibration', [FormsPrintController::class, 'incubatorTempCalibration'])
            ->middleware('signed')
            ->name('admin.print.forms.incubator-temp-calibration');

        Route::get('/admin/print/forms/hatcher-temp-calibration', [FormsPrintController::class, 'hatcherTempCalibration'])
            ->middleware('signed')
            ->name('admin.print.forms.hatcher-temp-calibration');

        Route::get('/admin/print/insights/{formTypeId}', [InsightsController::class, 'printView'])
            ->middleware('signed')
            ->name('admin.print.insights');

        Route::get('/admin/print/ai-chat/{id}', function (int $id) {
            $chat = \App\Models\AiChat::with('formType')->find($id);
            abort_if(! $chat, 404);
            abort_if($chat->user_id !== auth()->id(), 403);
            abort_if($chat->status !== 'done', 404);
            return view('admin.ai-chat-print', ['chat' => $chat]);
        })->middleware('signed')->name('admin.print.ai-chat');

        Route::get('/admin/change-password', [UserController::class, 'changePassword'])->name('admin.change-password');

        Route::get('/admin/ai-chat', function () {
            return view('admin.ai-chat');
        })->name('admin.ai-chat');

        Route::get('/admin/ai-chat/{id}', function (int $id) {
            return view('admin.ai-chat-view', ['chatId' => $id]);
        })->name('admin.ai-chat.view');
    });

    // Superadmin-only routes
    Route::middleware('superadmin')->group(function () {
        Route::get('/admin/system-prompts', function () {
            return view('admin.system-prompts');
        })->name('admin.system-prompts');

        Route::get('/admin/admin-management', function () {
            return view('admin.admin-management');
        })->name('admin.admin-management');

        Route::get('/admin/activity-logs', function () {
            return view('admin.activity-logs');
        })->name('admin.activity-logs');

        Route::get('/admin/activity-logs/export/csv', [\App\Http\Controllers\Admin\ActivityLogExportController::class, 'csv'])->name('admin.activity-logs.export.csv');
        Route::get('/admin/activity-logs/export/pdf', [\App\Http\Controllers\Admin\ActivityLogExportController::class, 'pdf'])->name('admin.activity-logs.export.pdf');

        Route::get('/admin/form-types', function () {
            return view('admin.form-types');
        })->name('admin.form-types');
    });
    
    // User routes
    Route::middleware('user')->group(function () {
        Route::get('/user/forms', function () {
            return view('shared.forms');
        })->name('user.forms');

        Route::get('/user/forms/incubator-routine', [FormController::class, 'incubatorRoutine'])->name('user.forms.incubator-routine');
        
        Route::get('/user/forms/blower-air-hatcher', [FormController::class, 'blowerAirHatcher'])->name('user.forms.blower-air-hatcher');
        
        Route::get('/user/forms/blower-air-incubator', [FormController::class, 'blowerAirIncubator'])->name('user.forms.blower-air-incubator');
        
        Route::get('/user/forms/hatchery-sullair', [FormController::class, 'hatcherySullair'])->name('user.forms.hatchery-sullair');

        Route::get('/user/forms/hatcher-machine-accuracy', [FormController::class, 'hatcherMachineAccuracy'])->name('user.forms.hatcher-machine-accuracy');

        Route::get('/user/forms/plenum-temp-humidity', [FormController::class, 'plenumTempHumidity'])->name('user.forms.plenum-temp-humidity');

        Route::get('/user/forms/incubator-machine-accuracy', [FormController::class, 'incubatorMachineAccuracy'])->name('user.forms.incubator-machine-accuracy');

        Route::get('/user/forms/entrance-damper-spacing', [FormController::class, 'entranceDamperSpacing'])->name('user.forms.entrance-damper-spacing');

        Route::get('/user/forms/incubator-entrance-temp', [FormController::class, 'incubatorEntranceTemp'])->name('user.forms.incubator-entrance-temp');

        Route::get('/user/forms/incubator-temp-calibration', [FormController::class, 'incubatorTempCalibration'])->name('user.forms.incubator-temp-calibration');

        Route::get('/user/forms/hatcher-temp-calibration', [FormController::class, 'hatcherTempCalibration'])->name('user.forms.hatcher-temp-calibration');

        Route::get('/user/forms/pasgar-score', [FormController::class, 'pasgarScore'])->name('user.forms.pasgar-score');

        Route::get('/user/forms/incubator-rack-pm', [FormController::class, 'incubatorRackPm'])->name('user.forms.incubator-rack-pm');

        Route::get('/user/forms/weekly-volt-ampere', [FormController::class, 'weeklyVoltAmpere'])->name('user.forms.weekly-volt-ampere');

        Route::get('/user/forms/diesel-generator-weekly', [FormController::class, 'dieselGeneratorWeekly'])->name('user.forms.diesel-generator-weekly');

        Route::get('/user/incubator-machines', [IncubatorController::class, 'index'])->name('user.incubator-machines');

        Route::get('/user/hatcher-machines', [HatcherController::class, 'index'])->name('user.hatcher-machines');

        Route::get('/user/plenum-machines', [PlenumController::class, 'index'])->name('user.plenum-machines');

        Route::get('/user/ps-numbers', [PsNumberController::class, 'index'])->name('user.ps-numbers');

        Route::get('/user/house-numbers', [HouseNumberController::class, 'index'])->name('user.house-numbers');

        Route::get('/user/get-sets', [GetSetController::class, 'index'])->name('user.get-sets');

        Route::get('/user/change-password', [UserController::class, 'changePassword'])->name('user.change-password');
    });
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});