<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Service\Report;

use XF\Entity\User;

/**
 * Extends \XF\Service\Report\Notifier
 */
class Notifier extends XFCP_Notifier
{
    /**
     * @var int[]
     */
    protected $notifyAssigned = [];

    /**
     * @var int[]
     */
    protected $notifyCommented = [];

    /**
     * @var int[]
     */
    protected $notifiableUsers = [];

    /**
     * @param int[] $users
     */
    public function setNotifyAssigned(array $users)
    {
        $this->notifyAssigned = array_unique($users);
    }

    /**
     * @return int[]
     */
    public function getNotifyAssigned()
    {
        return $this->notifyAssigned;
    }

    /**
     * @param int[] $users
     */
    public function setNotifyCommented(array $users)
    {
        $this->notifyCommented = array_unique($users);
    }

    /**
     * @return int[]
     */
    public function getNotifyCommented()
    {
        return $this->notifyCommented;
    }

    /**
     * Sends notifications to users.
     */
    public function notify()
    {
        $notifiableUsers = $this->getUsersForNotification();

        foreach ($this->notifyAssigned as $userId) {
            if (!isset($notifiableUsers[$userId])) {
                continue;
            }

            $this->sendAssignNotification($notifiableUsers[$userId]);
        }
        $this->notifyAssigned = [];

        parent::notify();

        foreach ($this->notifyCommented as $userId) {
            if (!isset($notifiableUsers[$userId])) {
                continue;
            }

            $this->sendCommentNotification($notifiableUsers[$userId]);
        }
        $this->notifyCommented = [];

        $this->notifiableUsers = [];
    }

    /**
     * @return User[]
     *
     * @noparent
     */
    protected function getUsersForNotification()
    {
        if (!$this->notifiableUsers) {
            $userIds = array_unique(
                array_merge(
                    $this->getNotifyAssigned(),
                    $this->getNotifyMentioned(),
                    $this->getNotifyCommented()
                )
            );

            /** @var \XF\Mvc\Entity\AbstractCollection $users */
            $users = $this->em()->findByIds('XF:User', $userIds, [
                'Profile',
                'Option',
            ]);
            if (!$users->count()) {
                return [];
            }

            $users = $users->filter(function (User $user) {
                return \XF::asVisitor($user, function () {
                    return $this->report->canView();
                });
            });

            $this->notifiableUsers = $users->toArray();
        }

        return $this->notifiableUsers;
    }

    /**
     * @param User $user
     *
     * @return bool
     *
     * @noparent
     */
    protected function sendAssignNotification(User $user)
    {
        return $this->sendNotification($user, 'assign_user');
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    protected function sendMentionNotification(User $user)
    {
        return $this->sendNotification($user, 'mention');
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    protected function sendCommentNotification(User $user)
    {
        return $this->sendNotification($user, $this->comment->action);
    }

    /**
     * @param User   $user
     * @param string $action
     *
     * @return bool
     */
    protected function sendNotification(User $user, $action)
    {
        if ($user->user_id == $this->comment->user_id) {
            return false;
        }

        if (!empty($this->usersAlerted[$user->user_id])) {
            return false;
        }

        /** @var \XF\Repository\UserAlert $alertRepo */
        $alertRepo = $this->repository('XF:UserAlert');
        $alert = $alertRepo->alert(
            $user,
            $this->comment->user_id,
            $this->comment->username,
            'report_comment',
            $this->comment->report_comment_id,
            $action
        );
        if (!$alert) {
            return false;
        }

        $this->usersAlerted[$user->user_id] = true;
        return true;
    }
}
