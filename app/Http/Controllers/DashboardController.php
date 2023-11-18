<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

use App\Models\Company;
use Nnjeim\World\World;
use App\Events\UserMetadataUpdate;
use App\Services\AnalyticsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(AnalyticsService $analyticsService)
    {
        $analytics = $analyticsService->getAnalytics();
        $selectedCompany = request()->has('company') ? $this->selectCompany(request()->input('company')) : null;

        $companies = $this->searchCompany();

        if (request()->wantsJson() && isset(request()->page)) {
            // Handle pagination for infinite scroll of companies
            return $companies;
        }

        $action = World::countries(['fields' => 'iso2']);
        $countries = [];

        if ($action->success) {
            $countries = Cache::rememberForever('countries', function () use ($action) {
                return $action->data;
            });
        }

        return Inertia::render('Dashboard', [
            'analytics' => $analytics,
            'company' => $selectedCompany,
            'companies' => $companies,
            'filters' => [
                // 'keyword' => request()->input('search_keyword') ?? (auth()->user()->metadata['search_keyword'] ?? ''),
                'keyword' => request()->input('search_keyword'),
                'status' => request()->input('call_status'),
                'city' => request()->input('filtered_city'),
                'state' => request()->input('filtered_state'),
                'country' => request()->input('filtered_country'),
            ],
            'countries' => $countries
        ]);
    }

    public function searchCompany()
    {
        $limit = isset(request()->limit) ? request()->input('limit') : 100;
        if (isset(request()->filter) && request()->input('filter') == 1) {
            // $limit = 1000; // meilisearch max limit
            $user = auth()->user();
            $metadata = $user->metadata;

            $metadata['search_keyword'] = null;
            $metadata['filtered_city'] = null;
            $metadata['filtered_state'] = null;
            $metadata['filtered_country'] = null;
            $metadata['call_status'] = null;

            if (isset(request()->query)) {
                $metadata['search_keyword'] = request()->input('query');
            }
            if (isset(request()->filtered_city)) {
                $metadata['filtered_city'] = request()->input('filtered_city');
            }
            if (isset(request()->filtered_state)) {
                $metadata['filtered_state'] = request()->input('filtered_state');
            }
            if (isset(request()->filtered_country)) {
                $metadata['filtered_country'] = request()->input('filtered_country');
            }
            if (isset(request()->call_status)) {
                $metadata['call_status'] = request()->input('call_status');
            }

            $user->metadata = $metadata;
            // UPDATE USER METADATA
            $user->save();
            UserMetadataUpdate::dispatch();
        }

        $company = Company::search(request()->input('query'), function ($meilisearch, $query, $options) {
            if (isset(request()->filtered_city)) {
                $options['filter'][] = 'address_city = "' . request()->input('filtered_city') . '"';
            }
            if (isset(request()->filtered_state)) {
                $options['filter'][] = 'address_state = "' . request()->input('filtered_state') . '"';
            }
            if (isset(request()->filtered_country)) {
                $options['filter'][] = 'address_country = "' . request()->input('filtered_country') . '"';
            }
            if (isset(request()->call_status)) {
                $options['filter'][] = 'call_status = "' . request()->input('call_status') . '"';
            }
            return $meilisearch->search($query, $options);
        });

        $companies = $company->query(fn (Builder $query) => $query->with([
            'contactPersons',
            'contactNumbers',
            'calls',
            'comments'
        ]))->paginate($limit)->withQueryString();

        return $companies;
    }

    public function company($company_id)
    {
        return $this->selectCompany($company_id);
    }

    private function selectCompany($company_id)
    {
        $companyRelationships = ['actionLogs', 'calendarEvents', 'calls', 'comments', 'contactPersons', 'contactNumbers'];
        return Company::with($companyRelationships)->find($company_id);
    }

    public function analytics(AnalyticsService $analyticsService)
    {
        return $analyticsService->getAnalytics();
    }
}
