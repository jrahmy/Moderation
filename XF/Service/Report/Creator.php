<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Service\Report;

/**
 * Extends \XF\Service\Report\Creator
 */
class Creator extends XFCP_Creator
{
    /**
     * @noparent
     */
    public function sendNotifications()
    {
        if ($this->threadCreator) {
            $this->threadCreator->sendNotifications();
            return;
        }

        /** @var \Jrahmy\Moderation\XF\Service\Report\Notifier $notifier */
        $notifier = $this->service(
            'XF:Report\Notifier',
            $this->report,
            $this->comment
        );

        if (
            ($this->report->report_state == 'assigned')
            && ($this->report->getPreviousValue('report_state') != 'assigned')
        ) {
            $notifier->setNotifyAssigned([$this->report->assigned_user_id]);
        }

        $notifier->setNotifyMentioned(
            $this->commentPreparer->getMentionedUserIds()
        );

        /** @var \Jrahmy\Moderation\XF\Entity\Report $report */
        $report = $this->report;
        if ($report->wasOpen()) {
            /** @var \Jrahmy\Moderation\XF\Repository\UserAlert $alertRepo */
            $alertRepo = $this->repository('XF:UserAlert');
            $alertCounts = $alertRepo->getUnreadContentAlertCountsForUsers(
                $report->comment_user_ids,
                'report',
                $report->report_id
            );
            $alertUserIds = array_diff(
                $report->comment_user_ids,
                array_keys($alertCounts)
            );
        } else {
            /** @var \XF\Repository\Report $reportRepo */
            $reportRepo = $this->repository('XF:Report');
            /** @var \XF\Mvc\Entity\AbstractCollection $moderators */
            $moderators = $reportRepo->getModeratorsWhoCanHandleReport($report);
            $alertUserIds = $moderators->pluckNamed('user_id');
        }

        $notifier->setNotifyCommented($alertUserIds);

        $notifier->notify();
    }
}
