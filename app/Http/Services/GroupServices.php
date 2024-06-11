<?php

namespace App\Http\Services;

use App\Models\User;

class GroupServices
{
    public function updateAssignUsersToGroup($user_ids, $group_id)
    {
        $old_users = User::where("group_id", $group_id)->get()->pluck("id")->toArray();
        //     $deleted_users = array_diff($old_users , $user_ids);
        //     $new_users = array_intersect($user_ids, $old_users);
        // dd( $new_users)         ;
        foreach ($user_ids as $id) {
            User::whereIn('id', $old_users)->update([
                'group_id' => null
            ]);
        }
        $this->assignUsersToGroup($user_ids, $group_id);
        return true;
    }

    public function assignUsersToGroup($user_ids, $group_id)
    {
        foreach ($user_ids as $id) {
            User::where('id', $id)->update([
                'group_id' => $group_id
            ]);
        }
        return true;
    }

    public function unassignUsersToGroup($group_id)
    {
        User::where('group_id', $group_id)->update([
            'group_id' => null
        ]);
        return true;
    }
}
