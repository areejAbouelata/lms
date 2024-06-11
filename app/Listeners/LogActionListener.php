<?php

namespace App\Listeners;

use App\Events\LogActionEvent;
use App\Models\LogingAction;
use App\Models\LogingActionTranslation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use function Psy\info;

class LogActionListener implements ShouldQueue
{ 
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\LogActionEvent  $event
     * @return void
     */
    public function handle(LogActionEvent $event)
    {
        $method = $event->method ;
        $title = $event->title ;
        $action_ar  ="" ;
        $action_en  ="" ;
        // DELETE GET POST  PUT|PATCH  , GET|HEAD
        \Log::info($method) ;
        switch ($method) {
            case 'GET':
                $action_ar = trans('dashboard.general.show') ;
                $action_en = trans('dashboard.general.show') ;
                break;
            case "DELETE":
                $action_ar = trans('dashboard.general.delete') ;
                $action_en = trans('dashboard.general.delete') ;
                break;
            case "POST":
                $action_ar = trans('dashboard.general.add') ;
                $action_en = trans('dashboard.general.add') ;
                break;
            case "PUT":
                $action_ar = trans('dashboard.general.edit') ;
                $action_en = trans('dashboard.general.edit') ;
                break;
            case "PATCH":
                $action_ar = trans('dashboard.general.edit') ;
                $action_en = trans('dashboard.general.edit') ;
                break;
            case "HEAD":
                $action_ar = trans('dashboard.general.show') ;
                $action_en = trans('dashboard.general.show') ;
                break;
            default:
        } 
        LogingAction::create([
            'en' => ['title' =>$action_ar. ' '. trans($title,[],'en')] , 
            'ar' => ['title' =>$action_ar. ' '.  trans($title,[],'ar')] , 
            'user_id' => $event->user->id ,
            'link' => $event->url
        ]) ;
    }
}