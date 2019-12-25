<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Service\Report;

/**
 * Extends \XF\Service\Report\Commenter
 */
class Commenter extends XFCP_Commenter
{
    /**
     * @param bool|string $logIp
     */
    public function logIp($logIp)
    {
        $this->commentPreparer->logIp($logIp);
    }

    /**
     * @return \XF\Entity\ReportComment
     */
    public function _save()
    {
        $comment = parent::_save();

        $this->commentPreparer->afterInsert();

        return $comment;
    }

    /**
     * @noparent
     */
    public function sendNotifications()
    {
        if ($this->comment->isClosureComment() && $this->sendAlert) {
            /** @var \XF\Service\Report\ClosureNotifier $closureNotifier */
            $closureNotifier = $this->service(
                'XF:Report\ClosureNotifier',
                $this->report
            );
            $closureNotifier->setAlertComment($this->alertComment);
            $closureNotifier->notify();
        }

        /** @var \Jrahmy\Moderation\XF\Service\Report\Notifier $notifier */
        $notifier = $this->service(
            'XF:Report\Notifier',
            $this->report,
            $this->comment
        );

        if ($this->comment->action == 'assign') {
            $notifier->setNotifyAssigned([$this->report->assigned_user_id]);
        }

        $notifier->setNotifyMentioned(
            $this->commentPreparer->getMentionedUserIds()
        );

        /** @var \Jrahmy\Moderation\XF\Repository\UserAlert $alertRepo */
        $alertRepo = $this->repository('XF:UserAlert');
        $alertCounts = $alertRepo->getUnreadAlertCountsForContent(
            'report',
            $this->report->report_id
        );
        $alertUserIds = array_diff(
            $this->report->comment_user_ids,
            array_keys($alertCounts)
        );
        $notifier->setNotifyCommented($alertUserIds);

        $notifier->notify();
    }
}
