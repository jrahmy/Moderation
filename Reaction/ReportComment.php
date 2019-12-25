<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\Reaction;

use XF\Mvc\Entity\Entity;
use XF\Reaction\AbstractHandler;

/**
 * An reaction handler for report comments.
 */
class ReportComment extends AbstractHandler
{
    /**
     * @param Entity $entity
     *
     * @return bool
     */
    public function reactionsCounted(Entity $entity)
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function getEntityWith()
    {
        return ['Report'];
    }
}
