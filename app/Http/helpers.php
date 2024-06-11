<?php
// namespace App\Http;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as Image;
use Illuminate\Support\Facades\File as File;
use Illuminate\Support\Facades\Notification as Notification;
use LaravelFCM\Facades\FCM as FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use App\Models\{Device, Setting,User , Driver , PointOffer};
use App\Jobs\{SendOrderRequestToDriver , SendFCMNotification};
use App\Notifications\General\{FCMNotification};
use App\Services\SMSService;
use GuzzleHttp\Client;
use App\Jobs\{UpdateTempWallet};
//return Settings
function setting($attr)
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

// Get Distance
function distance($startLat, $startLng, $endLat, $endLng, $unit = "K")
{
    // $unit = M --> Miles
    // $unit = K --> Kilometers
    // $unit = N --> Nautical Miles

    $startLat = (float) $startLat;
    $startLng = (float) $startLng;
    $endLat = (float) $endLat;
    $endLng = (float) $endLng;

    $theta = $startLng - $endLng;
    $dist = sin(deg2rad($startLat)) * sin(deg2rad($endLat)) + cos(deg2rad($startLat)) * cos(deg2rad($endLat)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
        return ($miles * 1.609344);
    } else if ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}

function generate_unique_code($length, $model, $col = 'code', $type = 'numbers' , $letter_type = 'all')
{
    if($type == 'numbers'){
        $characters = '0123456789';
    }else{
        switch ($letter_type) {
            case 'all':
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'lower':
                $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
                break;
            case 'upper':
                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;

            default:
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }
    }
    $generate_random_code = '';
    $charactersLength = strlen($characters);
    for ($i = 0; $i < $length; $i++) {
        $generate_random_code .= $characters[rand(0, $charactersLength - 1)];
    }
    if($model::where($col, $generate_random_code)->exists()){
        generate_unique_code($length,$model,$col,$type);
    }
    return $generate_random_code;
}


// Get Drivers
function getOtherDrivers($order , $notified_drivers , $number_of_drivers = 0)
{
    $number_of_drivers = $number_of_drivers + (int)convertArabicNumber(setting('number_drivers_to_notify'));


    $drivers = Driver::whereHas('user',function ($q) use($order){
        $q->available()->whereHas('profile',function ($q) {
            $q->whereNotNull('profiles.last_login_at');
        })->whereHas('devices')/*->withCount(['driverOrders','driverOrders as driver_orders_count' => function ($q) {
            $q->whereNotNull('orders.finished_at');
        }])*/
        // ->whereIn('users.id',online_users()->pluck('id'))
        /*->whereHas('car',function ($q) use($order) {
            $q->where('cars.car_type_id',$order->car_type_id);
        })*/->whereHas('car')->whereDoesntHave('driverOffers',function ($q) use($order) {
            $q->where('order_offers.order_id',$order->id);
        });
    })->whereIn('driver_type',[$order->order_type,'both'])->where(function ($q) {
        $q->where(function ($q) {
            $q->where('is_on_default_package',false)->whereHas('subscribedPackage',function ($q) {
                $q->whereDate('end_at',">=",date("Y-m-d"))->where('is_paid',1);
            });
        })/*->orWhere(function ($q) {
            $q->where(function ($q) {
                $q->where('is_on_default_package',true)->where('free_order_counter',"<",((int)setting('number_of_free_orders_on_default_package')))->orWhere(function ($q) {
                   $q->where('is_on_default_package',true)->whereHas('user',function ($q) {
                       $q->where('wallet',">",-(setting('min_wallet_to_recieve_order') ?? 10));
                   });
               });
            });
        })*/->orWhereHas('user',function ($q) {
            $q->where('is_with_special_needs',true);
        });
    })->when($order->start_lat && $order->start_lng,function ($q) use($order) {
        $q->nearest($order->start_lat ,$order->start_lng);
    })->when($number_of_drivers > 0,function ($q) use($number_of_drivers){
        $q->take($number_of_drivers);
    })->get();

    if ($drivers) {
        $drivers_ids_array = $drivers->pluck('user_id')->toArray();
        $db_drivers = User::whereIn('id',$drivers_ids_array)->get();
        $notified_drivers = $db_drivers->mapWithKeys(function ($item) use($order) {
            $count = @optional($item->orderNotifiedDrivers()->firstWhere('driver_order.order_id',$order->id))->pivot->notify_number ?? 0;
            // dump($count);
            $total_drivers = [];
            if ($count >= ((int)convertArabicNumber(setting('driver_notify_count_to_refuse')) ?? 2)) {
                $total_drivers[$item['id']] = ['status' => 'refuse_reply', 'notify_number' => $count];
            }else{
                $total_drivers[$item['id']] = ['status' => 'notify', 'notify_number' => $count];
            }
            return $total_drivers;
         })->toArray();

        $order->driverNotifiedOrders()->syncWithoutDetaching($notified_drivers);
        $new_drivers = $order->driverNotifiedOrders()->where('driver_order.status','notify')->pluck('users.id')->toArray();
        $db_drivers = User::whereIn('id',$new_drivers)->get();
        $minutes = ((int)convertArabicNumber(setting('waiting_time_for_driver_response'))) ? ((int)convertArabicNumber(setting('waiting_time_for_driver_response'))) : 1;
        $fcm_data = [
            'title' => trans('dashboard.fcm.new_order_title'),
            'body' => trans('dashboard.fcm.new_order_body',['client' => $order->fullname,'order_type' => trans('dashboard.order.order_types.'.$order->order_type)]),
            'notify_type' => 'new_order',
            'order_id' => $order->id,
            'order_type' => $order->order_type,
        ];
        // pushFcmNotes($fcm_data,$drivers_ids_array,'\\App\\Models\\Driver');
        SendFCMNotification::dispatch($fcm_data , $new_drivers)->onQueue('wallet');
        Notification::send($db_drivers,new FCMNotification($fcm_data,['database']));
        SendOrderRequestToDriver::dispatch($order, array_merge($new_drivers,$notified_drivers) ,  $number_of_drivers)->delay(now()->addMinutes($minutes));
    }
}


function sendNotify($user)
{
  try {
    if ($user->roles()->exists()) {
      $user->notify(new RegisterUser($user));
    } else {
      $user->notify(new VerifyApiMail($user));
    }
    $msg = [trans('dashboard.messages.success_add_send'), 1];
  } catch (\Exception $e) {
    $msg = [trans('dashboard.messages.success_add_not_send'), 0];
  }
  return $msg;
}


function uploadImg($files, $url = 'images', $key = 'image', $width = null, $height = null)
{
    $dist = storage_path('app/public/' . $url . "/");
    if ($url != 'images' && !File::isDirectory(storage_path('app/public/images/' . $url . "/"))){
        File::makeDirectory(storage_path('app/public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$url.DIRECTORY_SEPARATOR), 0777, true);
        $dist = storage_path('app/public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$url.DIRECTORY_SEPARATOR);
    }elseif (File::isDirectory(storage_path('app/public/images/' . $url . "/"))) {
        $dist = storage_path('app/public/images/' . $url . "/");
    }
    $image ="";
    if (!is_array($files)) {
      $dim = getimagesize($files);
      $width = $width ?? $dim[0];
      $height = $height ?? $dim[1];
    }

  if (gettype($files) == 'array') {
    $image = [];
    foreach ($files as $img) {
      $dim = getimagesize($img);
      $width = $width ?? $dim[0];
      $height = $height ?? $dim[1];

      if ($img && $dim['mime'] != "image/gif") {
        Image::make($img)->resize($width, $height, function ($cons) {
          $cons->aspectRatio();
        })->save($dist . $img->hashName());
        $image[][$key] = $img->hashName();
     }elseif ($img && $dim['mime'] == "image/gif") {
        $image = uploadGIFImg($img,$dist);
      }
    }
   }elseif ($dim && $dim['mime'] == "image/gif") {
     $image = uploadGIFImg($files,$dist);
   } else {
    Image::make($files)->resize($width, $height, function ($cons) {
      $cons->aspectRatio();
    })->save($dist . $files->hashName());
    $image = $files->hashName();
  }
  return $image;
}

function uploadGIFImg($gif_image,$dist) {
    $file_name = Str::uuid() ."___". $gif_image->getClientOriginalName();
    if ($gif_image->move($dist, $file_name)) {
      return $file_name;
    }
}

function uploadFile($files, $url = 'files', $key = 'file', $model = null)
{
  $dist = storage_path('app/public/' . $url);
  if ($url != 'images' && !File::isDirectory(storage_path('app/public/files/' . $url . "/"))){
      File::makeDirectory(storage_path('app/public'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$url.DIRECTORY_SEPARATOR), 0777, true);
      $dist = storage_path('app/public'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$url.DIRECTORY_SEPARATOR);
  }elseif (File::isDirectory(storage_path('app/public/files/' . $url . "/"))) {
      $dist = storage_path('app/public/files/' . $url . "/");
  }
  $file = '';

  if (gettype($files) == 'array') {
    $file = [];
    foreach ($files as $new_file) {
      $file_name = time() . "___file_" . $new_file->getClientOriginalName();
      if ($new_file->move($dist, $file_name)) {
        $file[][$key] = $file_name;
      }
    }
  } else {
    $file = $files;
    $file_name = time() . "___file_" . $file->getClientOriginalName();
    if ($file->move($dist, $file_name)) {
      $file =  $file_name;
    }
  }

  return $file;
}

function convertArabicNumber($number)
{
    $arabic_array = ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'];
    return strtr($number,$arabic_array);
}

function filter_mobile_number($mob_num)
{
    $mob_num = convertArabicNumber($mob_num);
    $first_3_val = substr($mob_num, 0, 3);
    $first_4_val = substr($mob_num, 0, 4);
    $sixth_val = substr($mob_num, 0, 6);
    $first_val = substr($mob_num, 0, 1);
    $mob_number = 0;
    $val = 0;
    if ($sixth_val == "009665") {
        $val = null;
        $mob_number = substr($mob_num, 2, 12);
    } elseif ($sixth_val == "009660") {
        $val = 966;
        $mob_number = substr($mob_num, 6, 14);
    } elseif ($first_3_val == "+96") {
        $val = "966";
        $mob_number = substr($mob_num, 4);
    } elseif ($first_4_val == "9660") {
        $val = "966";
        $mob_number = substr($mob_num, 4);
    }elseif ($first_3_val == "966") {
        $val = null;
        $mob_number = $mob_num;
    } elseif ($first_val == "5") {
        $val = "966";
        $mob_number = $mob_num;
    } elseif ($first_3_val == "009") {
        $val = "9";
        $mob_number = substr($mob_num, 4);
    } elseif ($first_val == "0") {
        $val = "966";
        $mob_number = substr($mob_num, 1, 9);
    } else {
        $val = "966";
        $mob_number = $mob_num;
    }

    $real_mob_number = $val . $mob_number;
    return $real_mob_number;
}


/**
 * Push Notifications to phone FCM
 *
 * @param  array $fcmData
 * @param  array $userIds
 */
function pushFcmNotes($fcmData, $userIds,$model = '\\App\\Models\\Device')
{
  $send_process = [];
  $fail_process = [];

  if (is_array($userIds) && !empty($userIds)) {
      $number_of_drivers = null;
    if ($model == '\\App\\Models\\Driver') {
         $model = '\\App\\Models\\Device';
         // $number_of_drivers = 1;
    }
    $devices = $model::whereIn('user_id',$userIds)/*->distinct('device_token')*/->latest()->when($number_of_drivers,function ($q) use($number_of_drivers) {
        $q->take($number_of_drivers);
    })->get();
    $ios_devices =array_filter($devices->where('type','ios')->pluck('device_token')->toArray());
    $android_devices = array_filter($devices->where('type','android')->pluck('device_token')->toArray());

    $optionBuilder = new OptionsBuilder();
    $optionBuilder->setTimeToLive(60*20);

    $notificationBuilder = new PayloadNotificationBuilder($fcmData['title']);
    $notificationBuilder->setBody($fcmData['body'])
    ->setSound('default');

    $dataBuilder = new PayloadDataBuilder();
    $dataBuilder->addData($fcmData);

    $option       = $optionBuilder->build();
    $data         = $dataBuilder->build();
    if (count($ios_devices)) {
        $notification = $notificationBuilder->build();
        // You must change it to get your tokens
        $downstreamResponse = FCM::sendTo($ios_devices, $option, $notification, $data);
        Device::whereIn('device_token',$downstreamResponse->tokensToDelete()+array_keys($downstreamResponse->tokensWithError()))->delete();
        // return $downstreamResponse;
        $send_process[] = $downstreamResponse->numberSuccess();
     }
     if (count($android_devices)) {
         $notification = null;
         // You must change it to get your tokens
         $downstreamResponse = FCM::sendTo($android_devices, $option, $notification, $data);
         Device::whereIn('device_token',$downstreamResponse->tokensToDelete()+array_keys($downstreamResponse->tokensWithError()))->delete();
         // return $downstreamResponse;
         $send_process[] = $downstreamResponse->numberSuccess();
         // code...
     }
     return count($send_process);
  }
  return "No Users";
}

// HISMS
function send_sms($mobile, $msg)
{
    $sender_name = str_replace(' ', '%20', setting('project_name'));
    $msg = str_replace(' ', '%20', $msg);
    $sender_data = [
        'username' => setting('sms_username'),
        'password' => setting('sms_password'),
        'sender_name' => setting('sms_sender_name') ?? $sender_name,
    ];
    $send_data = [
        'message' => $msg,
        'numbers' => $mobile
    ];
    $date_time = [
        'date' => date('Y-m-d'),
        'time' => date("H:i")
    ];
    return SMSService::send($sender_data , $send_data , $date_time , setting('sms_provider'));
}


function online_users()
{
    $client = new Client([
        'verify' => false
    ]);
    $online_users = $client->request('GET', setting('url_echo') . '/apps/' . setting('echo_app_id') . '/channels/presence-online/users', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . setting('echo_auth_key')
        ],
    ]);
    return collect(json_decode($online_users->getBody()->getContents(), true)['users']);
}

function channel_users($channel_name)
{
    $client = new Client([
        'verify' => false
    ]);
    $online_users = $client->request('GET', setting('url_echo') . '/apps/' . setting('echo_app_id') . '/channels/' . $channel_name . '/users', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . setting('echo_auth_key')
        ],
    ]);
    return collect(json_decode($online_users->getBody()->getContents(), true)['users']);
}


function wallet_transaction($user , $amount , $transaction_type , $morph = null , $reason = null) {
    if ($transaction_type == 'withdrawal') {
        $new_wallet = $user->wallet - $amount;
    }else{
        $new_wallet = $user->wallet + $amount;
    }
    $added_by = auth('api')->check() ? auth('api')->id() : (auth()->check() ? auth()->id() : null);

    $before_wallet_charge = [
        'wallet_before' => $user->wallet , 'wallet_after' => $new_wallet,
        'transaction_type' => $transaction_type , 'added_by_id' => $added_by,
        'amount' => $amount ,
        'transfer_status' => 'transfered',
        'reason' => $reason
    ];
    if ($morph) {
        $morph_type = get_class($morph);
        $morph_id = $morph->id;
        $before_wallet_charge += [
            'app_typeable_type' => $morph_type ,
            'app_typeable_id' => $morph_id
        ];
    }

    // $user->update(['wallet' => $new_wallet,'free_wallet_balance' => $free_wallet_balance]);

    $user->walletTransactions()->create($before_wallet_charge);
    return $new_wallet;
}

function use_point_offer($client , $driver) {
    // Point Offers
    $point_offers = PointOffer::active()->live()->get();

    if ($point_offers->count()) {
        $client_use_offer = false;
        $driver_use_offer = false;
        foreach ($point_offers as $point_offer) {
            $finished_client_count = 0;
            $finished_driver_count = 0;
            if (in_array($point_offer->user_type,['client','client_and_driver'])) {
                $finished_client_query = $client->clientOrders()->whereIn('order_status',['client_finish','driver_finish','admin_finish'])->whereBetween('created_at',[$point_offer->start_at,$point_offer->end_at]);
                $finished_client_count = $finished_client_query->count();
            }

            if (in_array($point_offer->user_type,['driver','client_and_driver'])) {
                $finished_driver_query = $driver->driverOrders()->whereIn('order_status',['client_finish','driver_finish','admin_finish'])->whereBetween('created_at',[$point_offer->start_at,$point_offer->end_at]);
                $finished_driver_count = $finished_driver_query->count();
            }

            if ($finished_client_count >= $point_offer->number_of_orders && !$client_use_offer && in_array($point_offer->user_type,['client','client_and_driver'])) {
                $client->userPoints()->create([
                    'points' => $point_offer->points,
                    'is_used' => false,
                    'status' => 'add',
                    'reason' => 'point_offer',
                    'transfer_type' => 'point',
                    'added_by_id' => auth('api')->id() ?? auth()->id(),
                ]);
                $client->pointOffers()->attach($point_offer->id);
                $client->update(['points' => ($client->points + $point_offer->points)]);
                if (isset($finished_client_count)) {
                    $finished_client_query->take($point_offer->number_of_orders)->update(['is_used_in_offer' => true]);
                }
                $client_use_offer = true;
            }

            if ($finished_driver_count >= $point_offer->number_of_orders && !$driver_use_offer && in_array($point_offer->user_type,['driver','client_and_driver'])) {

                $driver->userPoints()->create([
                    'points' => $point_offer->points,
                    'is_used' => false,
                    'status' => 'add',
                    'reason' => 'point_offer',
                    'transfer_type' => 'point',
                    'added_by_id' => auth('api')->id() ?? auth()->id(),
                ]);

                $driver->pointOffers()->attach($point_offer->id);
                $driver->update(['points' => ($driver->points + $point_offer->points)]);
                if (isset($finished_driver_count)) {
                    $finished_driver_query->take($point_offer->number_of_orders)->update(['is_used_in_offer' => true]);
                }
                $driver_use_offer = true;
            }
        }
    }
    return true;
}

