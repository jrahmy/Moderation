<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Entity;

use Jrahmy\Moderation\Entity\Reportable;
use Jrahmy\Moderation\Entity\WarnableInterface;
use XF\Mvc\Entity\Structure;

/**
 * Extends \XF\Entity\ProfilePost
 */
class ProfilePost extends XFCP_ProfilePost implements WarnableInterface
{
    use Reportable;

    /**
     * @param string|null $error
     *
     * @return bool
     */
    public function canWarn(&$error = null)
    {
        $canWarn = parent::canWarn($error);

        if ($canWarn && !$this->hasOpenReport()) {
            return false;
        }

        return $canWarn;
    }

    /**
     * @return string
     */
    public function getWarnUrl()
    {
        $router = $this->app()->router('public');
        return $router->buildLink('profile-posts/warn', $this);
    }

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
