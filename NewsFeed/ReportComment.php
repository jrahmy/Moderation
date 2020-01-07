<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\NewsFeed;

use XF\NewsFeed\AbstractHandler;

/**
 * A news feed handler for report comments.
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
}
