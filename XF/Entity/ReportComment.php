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
 * Extends \XF\Entity\ReportComment
 */
class ReportComment extends XFCP_ReportComment
{
    /**
     * @return string
     */
    protected function getAction()
    {
        if ($this->is_report) {
            return 'report';
        }

        switch ($this->state_change) {
            case 'open':
                return 'open';

            case 'assigned':
                return 'assign';

            case 'resolved':
                return 'resolve';

            case 'rejected':
                return 'reject';
        }

        return 'comment';
    }

    /**
     * @param Structure $structure
     *
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->getters['action'] = true;

        return $structure;
    }
}
