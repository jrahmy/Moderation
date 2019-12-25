<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Service\Report;

/**
 * Extends \XF\Service\Report\CommentPreparer
 */
class CommentPreparer extends XFCP_CommentPreparer
{
    /**
     * @var bool|string
     */
    protected $logIp = true;

    /**
     * @param bool|string $logIp
     */
    public function logIp($logIp)
    {
        $this->logIp = $logIp;
    }

    /**
     * Post-insert callback.
     */
    public function afterInsert()
    {
        if ($this->logIp) {
            $ip =
                $this->logIp === true
                    ? $this->app->request()->getIp()
                    : $this->logIp;

            $this->writeIpLog($ip);
        }
    }

    /**
     * @param string $ip
     */
    protected function writeIpLog($ip)
    {
        /** @var \XF\Repository\Ip $ipRepo */
        $ipRepo = $this->repository('XF:Ip');
        $ipEntity = $ipRepo->logIp(
            $this->comment->user_id,
            $ip,
            'report_comment',
            $this->comment->report_comment_id
        );
        if ($ipEntity) {
            $this->comment->fastUpdate('j_ip_id', $ipEntity->ip_id);
        }
    }
}
