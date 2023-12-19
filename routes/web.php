<?php

use Inertia\Inertia;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ContactNumberController;
use App\Http\Controllers\ContactPersonController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\LinkedWithGoogleMiddleware;

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
    Route::inertia('/auth/google', 'Auth/GoogleAuth')->name('google.auth');

    Route::put('/user/availability', [UserController::class, 'updateAvailability'])->name('user-availability.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(LinkedWithGoogleMiddleware::class)->name('dashboard');
    Route::get('/dashboard/company-search', [DashboardController::class, 'searchCompany'])->name('dashboard.searchCompany');
    Route::post('/dashboard/company/{id}', [DashboardController::class, 'company'])->name('dashboard.company');
    Route::post('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');

    Route::resource('companies', CompanyController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::put('company/{company}/restore', [CompanyController::class, 'restore'])->name('companies.restore');
    Route::post('companies-import', [CompanyController::class, 'import'])->name('companies.import');
    Route::get('companies-export', [CompanyController::class, 'export'])->name('companies.export');

    Route::resource('calls', CallController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::get('calls-export', [CallController::class, 'export'])->name('calls.export');

    Route::resource('calendar-events', CalendarEventController::class)->only(['show', 'store', 'update', 'destroy']);
    Route::post('/user-calendar-events/{user_id}', [CalendarEventController::class, 'userMeetings'])->name('user-events.list');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    Route::resource('contact-persons', ContactPersonController::class)->only(['store', 'update', 'destroy']);
    Route::resource('contact-numbers', ContactNumberController::class)->only(['store', 'update', 'destroy']);
    Route::resource('comment', CommentController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/search-contacts', [ChatController::class, 'searchContacts'])->name('chat.search-contacts');
    Route::get('/chat/{id}', [ChatController::class, 'getMessagesFor'])->name('chat.messages');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/read/{id}', [ChatController::class, 'read'])->name('chat.read');
});

require_once __DIR__ . '/jetstream.php';
