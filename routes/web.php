<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SocialController;
use Spatie\GoogleCalendar\Event;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/auth/{provider}/redirect', [SocialController::class, 'redirect'])->where('provider', 'google|facebook|github');
Route::get('/auth/{provider}/callback', [SocialController::class, 'callback'])->where('provider', 'google|facebook|github');

Route::get('/gcalendar-events', function () {
    return Event::get();
});

Route::get('/gcalendar-events-add', function () {
    $event = new Event;

    $event->name = 'Appointment event';
    $event->description = 'Test new appointment event';
    $event->startDateTime = Carbon\Carbon::tomorrow();
    $event->endDateTime = Carbon\Carbon::tomorrow()->addHour();
    $event->addAttendee([
        'email' => 'guanlao.sherwin@gmail.com',
        'name' => 'Urban Gulaman',
        'comment' => 'Lorum ipsum',
        'responseStatus' => 'needsAction',
    ]);
    $event->addAttendee(['email' => 'sherwin.bunshin@gmail.com']);
    $event->addMeetLink();

    $event->save();
    return dd($event);
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/company-search', [DashboardController::class, 'searchCompany'])->name('dashboard.searchCompany');
    Route::post('/dashboard/company/{id}', [DashboardController::class, 'company'])->name('dashboard.company');
    Route::post('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');

    Route::resource('companies', CompanyController::class);
    Route::post('companies-import', [CompanyController::class, 'import'])->name('companies.import');
    Route::get('companies-export', [CompanyController::class, 'export'])->name('companies.export');

    Route::resource('calls', CallController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::resource('calendar-events', CalendarEventController::class)->only(['show', 'store', 'update', 'destroy']);
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
});

require_once __DIR__ . '/jetstream.php';
