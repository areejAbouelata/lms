<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/


Broadcast::channel('ma7ta_mobile-notification.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('ma7ta_mobile-chat.{chat_id}', function ($user, $chat_id) {
    return \App\Models\Chat::find($chat_id) ? true : false;
});

Broadcast::channel('ma7ta_mobile-update-location.driver', function ($user) {
    return true;
    // return \App\Models\User::where('user_type','driver')->find($driver_id);
});
// Broadcast::channel('ma7ta_mobile-update-location_driver_test', TestChannel::class);

Broadcast::channel('ma7ta_mobile-online', function ($user) {
    return [
        'id' => $user->id,
        'fullname' => $user->fullname,
        'user_type' => $user->user_type,
        'avatar' => $user->avatar,
        ];
});

Broadcast::channel('ma7ta_mobile-share-link.{uuid}', function ($user,$uuid) {
    $order = \App\Models\Order::whereIn('order_status',['shipped','start_trip'])->where(['share_link_uuid' => $uuid])->first();
    return ! is_null($order);
});
