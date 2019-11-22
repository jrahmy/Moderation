<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * Extends \XF\Entity\Report
 */
class Report extends XFCP_Report
{
    /**
     * @return bool
     */
    public function wasOpen()
    {
        if ($this->first_report_date == $this->last_modified_date) {
            return false;
        }

        $previousState = $this->getPreviousValue('report_state');
        return $previousState == 'open' || $previousState == 'assigned';
    }

    /**
     * @return array
     */
    public function getCommentUserIds()
    {
        return $this->db()->fetchAllColumn(
            'SELECT DISTINCT user_id
                FROM xf_report_comment
                WHERE report_id = ?
                ORDER BY user_id',
            $this->report_id
        );
    }

    /**
     * @param Structure $structure
     *
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->getters['comment_user_ids'] = true;

        return $structure;
    }
}
