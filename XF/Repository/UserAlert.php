<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Repository;

/**
 * Extends \XF\Repository\UserAlert
 */
class UserAlert extends XFCP_UserAlert
{
    /**
     * @param string $contentType
     * @param int    $contentId
     *
     * @return int[][]
     */
    public function getUnreadAlertCountsForContent($contentType, $contentId)
    {
        return $this->db()->fetchPairs(
            'SELECT alerted_user_id, COUNT(*)
                FROM xf_user_alert
                    WHERE content_type = ?
                        AND content_id = ?
                        AND view_date = 0
                GROUP BY alerted_user_id',
            [$contentType, $contentId]
        );
    }
}
