<?php

namespace App\Http\Controllers\Api\Dashboard\Statictics;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Dashboard\Client\ClientResource;
use App\Http\Resources\Api\Dashboard\Flow\FlowResource;
use App\Http\Services\StatisticServices;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public $statisticServices;

    public function __construct(StatisticServices $statisticServices)
    {
        $this->statisticServices = $statisticServices;
    }

    public function home(Request $request)
    {
//
        $data = [];
        $data['header_statistic'] = $this->statisticServices->headerStatistics();
        $data['users'] = ClientResource::collection($this->statisticServices->topUsersThisMonth());
        $data['daily_enrollment'] = $this->statisticServices->dailyEnrolment();
        $data['month_enrollment'] = $this->statisticServices->monthEnrolments();
        $data['six_months_enrollment'] = $this->statisticServices->sixMonthsEndolment();
        $data['twelve_months_enrollment'] = $this->statisticServices->twelveMonthsEndolment();
//        $data['completionRateByFlow'] = FlowResource::collection($this->statisticServices->completionRateByFlow($request));


        $data['daily_completionRateByFlow'] = FlowResource::collection($this->statisticServices->dailyCompletionRateByFlow($request));
        $data['month_completionRateByFlow'] = FlowResource::collection($this->statisticServices->monthCompletionRateByFlow($request));
        $data['six_months_completionRateByFlow'] = FlowResource::collection($this->statisticServices->sixMonthsCompletionRateByFlow($request));
        $data['twelve_months_completionRateByFlow'] = FlowResource::collection($this->statisticServices->twelveMonthsCompletionRateByFlow($request));
        return $data;
    }

    public function headerStatistics()
    {
        return $this->statisticServices->headerStatistics();
    }
}
