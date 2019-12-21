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
 * Extends \XF\Entity\Warning
 */
class Warning extends XFCP_Warning
{
    /**
     * @param Structure $structure
     *
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->relations['Report'] = [
            'entity' => 'XF:Report',
            'type' => self::TO_ONE,
            'conditions' => [
                ['content_type', '=', '$content_type'],
                ['content_id', '=', '$content_id'],
            ],
        ];

        return $structure;
    }
}
