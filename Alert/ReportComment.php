<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\Alert;

use XF\Alert\AbstractHandler;

/**
 * An alert handler for report comments.
 */
class ReportComment extends AbstractHandler
{
    /**
     * @return string[]
     */
    public function getEntityWith()
    {
        return ['Report'];
    }

    /**
     * @return string[]
     */
    public function getOptOutActions()
    {
        $visitor = \XF::visitor();
        if (!$visitor->is_moderator) {
            return [];
        }

        return ['reaction'];
    }

    /**
     * @return int
     */
    public function getOptOutDisplayOrder()
    {
        return 26000;
    }
}
