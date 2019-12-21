<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\Entity;

use XF\Mvc\Entity\Structure;

/**
 * A trait for reportable entities.
 */
trait Reportable
{
    /**
     * @return bool
     */
    public function hasOpenReport()
    {
        /** @var \XF\Entity\Report $report */
        $report = $this->Report;
        return $report && !$report->isClosed();
    }

    /**
     * @param Structure $structure
     */
    public static function setupReportableStructure(Structure $structure)
    {
        static::setupReportableRelationStructure($structure);
        static::setupReportableWithAliasStructure($structure);
    }

    /**
     * @param Structure $structure
     */
    public static function setupReportableRelationStructure(
        Structure $structure
    ) {
        $structure->relations['Report'] = [
            'entity' => 'XF:Report',
            'type' => self::TO_ONE,
            'conditions' => [
                ['content_type', '=', $structure->contentType],
                ['content_id', '=', "\${$structure->primaryKey}"],
            ],
        ];
    }

    /**
     * @param Structure $structure
     */
    public static function setupReportableWithAliasStructure(
        Structure $structure
    ) {
        $structure->withAliases['full'][] = function () {
            $visitor = \XF::visitor();
            if ($visitor->is_moderator) {
                return 'Report';
            }
        };
    }
}
