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
     * @param array  $userIds
     * @param string $contentType
     * @param int    $contentId
     *
     * @return array
     */
    public function getUnreadContentAlertCountsForUsers(
        array $userIds,
        $contentType,
        $contentId
    ) {
        $db = $this->db();
        $userIds = $db->quote($userIds);

        return $db->fetchPairs(
            "SELECT alerted_user_id, COUNT(*)
                FROM xf_user_alert
                WHERE alerted_user_id IN ({$userIds})
                    AND content_type = ?
                    AND content_id = ?
                    AND view_date = 0
                GROUP BY user_id",
            [$contentType, $contentId]
        );
    }
}
