<?php

namespace App\Services;

class SMSService
{

    public static function send(array $sender, array $data,array $date_time , $service_provider = 'hisms')
    {
        switch ($service_provider) {
            case 'hisms':
                $validate_msg = self::sendHisms($sender , $data , $date_time );
                break;
            case 'sms_gateway':
                $validate_msg = self::sendSMSGateway($sender , $data , $date_time );
                break;
            case 'net_powers':
                $validate_msg = self::sendNetPowers($sender , $data , $date_time );
                break;

            default:
                $validate_msg = self::sendHisms($sender , $data , $date_time );
                break;
        }
        return $validate_msg;
    }


    public static function sendHisms($sender , $data , $date_time)
    {
        $url = "http://hisms.ws/api.php?send_sms&username=" . $sender['username'] . "&password=" . $sender['password'] . "&numbers=" . $data['numbers'] . "&sender=" . $sender['sender_name'] . "&message=" . $data['message'] . "&date=" . $date_time['date'] . "&time=" . $date_time['time'];

        $response = (int) file_get_contents($url);
        $result = self::validate_response($response);
        return  $msg = ['response' => $response, 'result' => $result];
    }

    public static function sendSMSGateway($sender , $data , $date_time)
    {
        $url = "https://apps.gateway.sa/vendorsms/pushsms.aspx?user=" . $sender['username'] . "&password=" . $sender['password'] . "&msisdn=" . $data['numbers'] . "&sid=" . $sender['sender_name'] . "&msg=" . $data['message'] . "&fl=0&dc=8";

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url);
        $response = json_decode($response->getBody()->getContents(),true);
        $result = self::validate_SMS_response($response['ErrorCode']);
        return  $msg = ['response' => $response, 'result' => $result];
    }

    public static function sendNetPowers($sender , $data , $date_time)
    {
        $url = "Http://sms.netpowers.net/http/api.php?id=" . $sender['username'] . "&password=" . $sender['password'] . "&to=" . $data['numbers'] . "&sender=" . $sender['sender_name'] . "&msg=" . $data['message'];

        $response = file_get_contents($url);
        $result =  "لم يتم الارسال حاول مرة اخرى عن طريق تسجيل الدخول";
        if(str_contains($response,'Sent')){
            $result =  "تم الارسال بنجاح";
        }
        return  $msg = ['response' => $response, 'result' => $result];
    }

    protected static function validate_response($response)
    {
      $result = '';
      switch ($response) {
        case 1:
          $result = 'اسم المستخدم غير صحيح';
          break;
        case 2:
          $result = 'كلمة المرور غير صحيحة';
          break;
        case 3:
          $result = 'تم ارسال كود التحقق';
          break;
        case 4:
          $result = 'لايوجد ارقام';
          break;
        case 5:
          $result = 'لايوجد رسالة';
          break;
        case 6:
          $result = 'رقم الهاتف غير صحيح';
          break;
        case 7:
          $result = 'رقم الهاتف غير مفعل';
          break;
        case 8:
          $result = 'الرسالة تحتوى على كلمة ممنوعة';
          break;
        case 9:
          $result = 'لايوجد رصيد';
          break;
        case 10:
          $result = 'صيغة التاريخ غير صحيحة';
          break;
        case 10:
          $result = 'صيغة الوقت غير صحيحة';
          break;
        case 404:
          $result = 'لم يتم ادخال جميع البرمترات المطلوبة';
          break;
        case 403:
          $result = 'تم تجاوز عدد المحاولات المسموحة';
          break;
        case 504:
          $result = 'الحساب معطل';
          break;
      }
      return $result;
    }

    protected static function validate_SMS_response($response)
    {
       return trans('dashboard.sms.response_code.'.$response);
    }


}
