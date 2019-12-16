<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Entity;

use Jrahmy\Moderation\Entity\Reportable;
use XF\Mvc\Entity\Structure;

/**
 * Extends \XF\Entity\ProfilePostComment
 */
class ProfilePostComment extends XFCP_ProfilePostComment
{
    use Reportable;

    /**
     * @param Structure $structure
     *
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        static::setupReportableStructure($structure);

        return $structure;
    }
}
