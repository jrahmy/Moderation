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
 * Extends \XF\Entity\ConversationMessage
 */
class ConversationMessage extends XFCP_ConversationMessage implements
    WarnableInterface
{
    use Reportable;

    /**
     * @param string|null $error
     *
     * @return bool
     */
    public function canWarn(&$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return false;
        }

        if (!$this->user_id) {
            return false;
        }

        if ($visitor->user_id == $this->user_id) {
            return false;
        }

        if (!$visitor->hasPermission('conversation', 'warn')) {
            return false;
        }

        if ($this->j_warning_id) {
            $error = \XF::phraseDeferred(
                'user_has_already_been_warned_for_this_content'
            );
            return false;
        }

        if (!$this->hasOpenReport()) {
            return false;
        }

        return $this->User && $this->User->isWarnable();
    }

    /**
     * @return string
     */
    public function getWarnUrl()
    {
        $router = $this->app()->router('public');
        return $router->buildLink('conversations/messages/warn', $this);
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

        $structure->columns['j_warning_id'] = [
            'type' => self::UINT,
            'default' => 0,
        ];
        $structure->columns['j_warning_message'] = [
            'type' => self::STR,
            'default' => '',
            'maxLength' => 255,
            'api' => true,
        ];

        return $structure;
    }
}
