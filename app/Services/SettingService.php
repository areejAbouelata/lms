<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{ 
    public static function setting($attr)
    {
      
      
        if (\Schema::hasTable('settings')) {
            $phone = $attr;
            if ($attr == 'phone') {
                $attr = 'phones';
            }
          $setting=Setting::where('key',$attr)->first() ??[];
      
          if ($attr == 'project_name') {
            return ! empty($setting) ? $setting->value : 'Alamyia';
          }
          if ($attr == 'logo') {
            return ! empty($setting) ? asset('storage/images/setting')."/".$setting->value : asset('dashboardAssets/images/icons/logo_sm.png');
          }
          if ($phone == 'phone') {
            return ! empty($setting) && $setting->value ? json_decode($setting->value)[0] : null;
            }elseif ($phone == 'phones') {
                return ! empty($setting) && $setting->value ? implode(",",json_decode($setting->value)) : null;
            }
          if (! empty($setting)) {
            
            return $setting->value;
      
          }
          return false;
        }
        return false;
      }
}
