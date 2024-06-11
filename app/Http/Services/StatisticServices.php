<?php

namespace App\Http\Services;

use App\Models\ActivityUser;
use App\Models\Flow;
use App\Models\FlowUser;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class StatisticServices
{
    public function headerStatistics()
    {
        $header_statistic = [];
        $header_statistic['total_users'] = User::where('user_type', 'client')->count();
        $header_statistic['active_users'] = User::where('user_type', 'client')->where('is_active', true)->count();

        $start_date = Carbon::now()->startOfMonth();
        $end_date = Carbon::now()->endOfMonth();
        $header_statistic['users_this_month'] = User::where('user_type', 'client')
            ->whereDate('created_at', ">=", $start_date)->whereDate("created_at", "<=", $end_date)->count();

//        $header_statistic['total_activities'] = ActivityUser::count();
//        $header_statistic['total_finished_activities'] = ActivityUser::where('status', "finished")->count();
        //$header_statistic['completion_rate'] = $header_statistic['total_activities'] ? round($header_statistic['total_finished_activities'] / $header_statistic['total_activities'] * 100) : 0;
        $totalActivities = ActivityUser::count();
        $totalFinishedActivities = ActivityUser::where('status', "finished")->count();
        $header_statistic['completion_rate'] = $totalActivities ? round($totalFinishedActivities / $totalActivities * 100) : 0;

        return $header_statistic;
    }

    public function topUsersThisMonth()
    {
        $start_date = Carbon::now()->startOfMonth();
        $end_date = Carbon::now()->endOfMonth();
        $user_ids = FlowUser::whereDate('created_at', ">=", $start_date)->
        whereDate("created_at", "<=", $end_date)
            ->orderBy("score")->pluck('user_id');
        $users = User::where('user_type', 'client')->whereIn('id', $user_ids)->get()->take(5);
        return $users->sortByDesc("completion");
    }

    public function dailyEnrolment()
    {
        $start = Carbon::now()->subDays(6);
        $end = Carbon::now();
        $dates = CarbonPeriod::create($start, '1 day', $end);
        $x_labels = [];
        for ($i = 0; $i < 7; $i++) {
            $x_labels[] = $i;
        }
        $y_labels = [];

        foreach ($dates as $date) {
//            $finished_y_labels[] = FlowUser::whereDate('created_at', $date->format('Y-m-d'))->where('status', "finished")->count() ?
//                FlowUser::whereDate('created_at', $date->format('Y-m-d'))->count() / FlowUser::whereDate('created_at', $date->format('Y-m-d'))->where('status', "finished")->count() * 100
//                : 0;
            $finished_y_labels[] = FlowUser::whereDate('created_at', $date->format('Y-m-d'))->where('status', "finished")->count() ?
                ceil((FlowUser::whereDate('created_at', $date->format('Y-m-d'))->where('status', "finished")->count() / FlowUser::whereDate('created_at', $date->format('Y-m-d'))->count()) * 100)
                : 0;
            $y_labels[] = FlowUser::whereDate('created_at', $date->format('Y-m-d'))->count();

        }
        return ['x_labels' => $x_labels, 'series' => [["name" => "completion", "data" => $finished_y_labels], ["name" => "enrollment", "data" => $y_labels]]];
    }

    public function monthEnrolments()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();
        $dates = CarbonPeriod::create($start, '1 day', $end);
        $y_labels = [];
        foreach ($dates as $date) {
//            $finished_y_labels[] = FlowUser::whereDate('created_at', $date->format('Y-m-d'))->where('status', "finished")->count()
//                ? FlowUser::whereDate('created_at', $date->format('Y-m-d'))->count() / FlowUser::whereDate('created_at', $date->format('Y-m-d'))->where('status', "finished")->count() * 100
//                : 0;

            $finished_y_labels[] = FlowUser::whereDate('created_at', $date->format('Y-m-d'))->where('status', "finished")->count()
                ? ceil((FlowUser::whereDate('created_at', $date->format('Y-m-d'))->where('status', "finished")->count() / FlowUser::whereDate('created_at', $date->format('Y-m-d'))->count()) * 100)
                : 0;
            $y_labels[] = FlowUser::whereDate('created_at', $date->format('Y-m-d'))->count();

        }
        $x_labels = [];
        for ($i = 0; $i < Carbon::now()->daysInMonth; $i++) {
            $x_labels[] = $i;
        }
        return ['x_labels' => $x_labels, 'series' => [["name" => "completion", "data" => $finished_y_labels], ["name" => "enrollment", "data" => $y_labels]]];
    }

    public function sixMonthsEndolment()
    {
        $x_labels = [];
        $y_labels = [];
        $finshed_y_labels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::today()->startOfMonth()->subMonth($i);
            $end_month = Carbon::today()->startOfMonth()->subMonth($i)->endOfMonth();

            $x_labels [] = $month->shortMonthName;

//            $finshed_y_labels[] = FlowUser::whereDate('created_at', ">=", $month)->whereDate("created_at", "<=", $end_month)->where('status', "finished")->count() ?
//                FlowUser::whereDate('created_at', ">=", $month)->whereDate("created_at", "<=", $end_month)->count() / FlowUser::whereDate('created_at', ">=", $month)->whereDate("created_at", "<=", $end_month)->where('status', "finished")->count() * 100
//                : 0;

            $finshed_y_labels[] = FlowUser::whereDate('created_at', ">=", $month)->whereDate("created_at", "<=", $end_month)->where('status', "finished")->count() ?
                ceil((FlowUser::whereDate('created_at', ">=", $month)->whereDate("created_at", "<=", $end_month)->where('status', "finished")->count() / FlowUser::whereDate('created_at', ">=", $month)->whereDate("created_at", "<=", $end_month)->count()) * 100)
                : 0;
            $y_labels [] = FlowUser::whereBetween('created_at', [$month, $end_month])->count();

        }
        return ['x_labels' => $x_labels, 'series' => [["name" => "completion", "data" => $finshed_y_labels], ["name" => "enrollment", "data" => $y_labels]]];
    }

    public function twelveMonthsEndolment()
    {
        $x_labels = [];
        $y_labels = [];
        $finshed_y_labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::today()->startOfMonth()->subMonth($i);
            $end_month = Carbon::today()->startOfMonth()->subMonth($i)->endOfMonth();

            $x_labels [] = $month->shortMonthName;
//            $finshed_y_labels[] = FlowUser::whereMonth('created_at', $month)->where('status', "finished")->count() ?
//                FlowUser::whereMonth('created_at', $month)->count() / FlowUser::whereMonth('created_at', $month)->where('status', "finished")->count() * 100 :
//                0;
            $finshed_y_labels[] = FlowUser::whereMonth('created_at', $month)->where('status', "finished")->count() ?
                ceil((FlowUser::whereMonth('created_at', $month)->where('status', "finished")->count() / FlowUser::whereMonth('created_at', $month)->count()) * 100) :
                0;
            $y_labels [] = FlowUser::whereBetween('created_at', [$month, $end_month])->count();
        }
        return ['x_labels' => $x_labels, 'series' => [["name" => "completion", "data" => $finshed_y_labels], ["name" => "enrollment", "data" => $y_labels]]];
    }

//    public function completionRateByFlow($request)
//    {
//        $start_duration = !$request->start_date ? Carbon::now()->subDays(7) : $request->start_date;
//        $flows = Flow::whereDate('created_at', ">=", $start_duration)->get()->sortByDesc("completion");
//        return $flows;
//    }

    public function dailyCompletionRateByFlow($request)
    {
        $start_duration = !$request->start_date ? Carbon::now()->subDays(7) : $request->start_date;
        $flows = Flow::whereDate('created_at', ">=", Carbon::now()->subDays(7))->get()->sortByDesc("completion");
        return $flows;
    }

    public function monthCompletionRateByFlow($request)
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();
        $flows = Flow::where('created_at', '>', $start )->where('created_at' , '<' , $end)->get()->sortByDesc("completion");
        return $flows;
    }

        public function sixMonthsCompletionRateByFlow($request)
    {
        $month = Carbon::today()->startOfMonth()->subMonth(1);
         
        $end_month =null ;
        
        for ($i = 5; $i >= 0; $i--) {
        
            $end_month = Carbon::today()->startOfMonth()->subMonth($i)->endOfMonth();
            
        }
        // info([$end_month , $month]) ;
         $flows = Flow::whereDate('created_at', ">",Carbon::today()->startOfMonth()->subMonth(5)->endOfMonth())->whereDate('created_at','<',$month)->get()->sortByDesc("completion");
           
         return $flows;
    }

    public function twelveMonthsCompletionRateByFlow($request)
    {
          $month = Carbon::today()->startOfMonth()->subMonth(1);
            $end_month = null ;
        for ($i = 11; $i >= 0; $i--) {
            $end_month = Carbon::today()->startOfMonth()->subMonth($i)->endOfMonth();
        }
         $flows = Flow::whereBetween('created_at', [Carbon::today()->startOfMonth()->subMonth(11)->endOfMonth(), $month])->get()->sortByDesc("completion");
        return $flows;
    }
}
