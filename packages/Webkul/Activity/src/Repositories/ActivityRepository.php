<?php

namespace Webkul\Activity\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\User\Repositories\UserRepository;

class ActivityRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Activity\Contracts\Activity';
    }

    /**
     * @param  string  $dateRange
     * @return mixed
     */
    public function getActivities($dateRange)
    {
        return $this->select(
                'activities.id',
                'activities.created_at',
                'activities.title',
                'activities.schedule_from as start',
                'activities.schedule_to as end',
                'users.name as user_name',
            )
            ->addSelect(\DB::raw('IF(activities.is_done, "done", "") as class'))
            ->leftJoin('activity_participants', 'activities.id', '=', 'activity_participants.activity_id')
            ->leftJoin('users', 'activities.user_id', '=', 'users.id')
            ->whereIn('type', ['call', 'meeting', 'lunch'])
            ->whereBetween('activities.schedule_from', $dateRange)
            ->where(function ($query) {
                $currentUser = auth()->guard('user')->user();

                if ($currentUser->view_permission != 'global') {
                    if ($currentUser->view_permission == 'group') {
                        $userIds = app(UserRepository::class)->getCurrentUserGroupsUserIds();

                        $query->whereIn('activities.user_id', $userIds)
                            ->orWhereIn('activity_participants.user_id', $userIds);
                    } else {
                        $query->where('activities.user_id', $currentUser->id)
                            ->orWhere('activity_participants.user_id', $currentUser->id);
                    }
                }
            })
            ->distinct()
            ->get();
    }
}