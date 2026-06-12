<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function agencyReport()
    {
        $agencyReport = Deal::select(
            'recruitment_agency',
            DB::raw('COUNT(*) as total_deals'),
            DB::raw('SUM(amount) as total_revenue'),
            DB::raw('AVG(margin_agreed) as average_margin'),
            DB::raw('SUM(agency_deal_value) as total_agency_value')
        )
            ->whereNotNull('recruitment_agency')
            ->groupBy('recruitment_agency')
            ->orderByDesc('total_revenue')
            ->get();

        return view('reports.agency_report', compact('agencyReport'));
    }

    public function mdaReport()
    {
        $mdaReport = Deal::select(
            'mda',
            DB::raw('COUNT(*) as total_deals'),
            DB::raw('SUM(amount) as total_revenue'),
            DB::raw('AVG(margin_agreed) as average_margin'),
            DB::raw('SUM(agency_deal_value) as total_agency_value')
        )
            ->whereNotNull('mda')
            ->groupBy('mda')
            ->orderByDesc('total_revenue')
            ->get();

        return view('reports.mda_report', compact('mdaReport'));
    }

    public function workerReport()
    {
        $workerReport = Deal::select(
            'first_name',
            'last_name',
            DB::raw('COUNT(*) as total_deals'),
            DB::raw('SUM(amount) as total_revenue'),
            DB::raw('AVG(margin_agreed) as average_margin'),
            DB::raw('SUM(agency_deal_value) as total_agency_value')
        )
            ->whereNotNull('first_name')
            ->whereNotNull('last_name')
            ->groupBy('first_name', 'last_name')
            ->orderByDesc('total_revenue')
            ->get();

        return view('reports.worker_report', compact('workerReport'));
    }

    public function companyReport()
    {
        $companyReport = Deal::select(
            'company_name',
            DB::raw('COUNT(*) as total_deals'),
            DB::raw('SUM(amount) as total_revenue'),
            DB::raw('AVG(margin_agreed) as average_margin'),
            DB::raw('SUM(agency_deal_value) as total_agency_value')
        )
            ->whereNotNull('company_name')
            ->groupBy('company_name')
            ->orderByDesc('total_revenue')
            ->get();

        return view('reports.company_report', compact('companyReport'));
    }

    public function ownerPipeline()
    {
        $ownerPipeline = User::with(['deals' => function ($query) {
            $query->select('id', 'user_id', 'amount', 'stage');
        }])
            ->get()
            ->map(fn ($user) => [
                'owner' => $user->name,
                'total_deals' => $user->deals->count(),
                'pipeline_value' => $user->deals->sum('amount'),
                'by_stage' => $user->deals->groupBy('stage.value')->map->sum('amount'),
            ]);

        return view('reports.owner_pipeline', compact('ownerPipeline'));
    }

    public function stagePipeline()
    {
        $stagePipeline = Deal::select('stage', DB::raw('COUNT(*) as total_deals'), DB::raw('SUM(amount) as pipeline_value'))
            ->groupBy('stage')
            ->orderByDesc('pipeline_value')
            ->get();

        return view('reports.stage_pipeline', compact('stagePipeline'));
    }

    public function dealMasterReport()
    {
        $dealsMasterReport = Deal::with([
            'user:id,name',
            'companies' => function ($query) {
                // Eager-load companies via pivot to isolate the primary corporate account
                $query->wherePivot('is_primary', true)->select('companies.id', 'companies.name');
            },
        ])
            ->select('id', 'name', 'amount', 'stage', 'user_id', 'margin_agreed', 'date_logged')
            ->orderByDesc('amount')
            ->paginate(50);

        return view('reports.deals_master_report', compact('dealsMasterReport'));
    }
}
