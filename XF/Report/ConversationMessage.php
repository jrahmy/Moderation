<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Report;

use XF\Entity\Report;

/**
 * Extends \XF\Report\ConversationMessage
 */
class ConversationMessage extends XFCP_ConversationMessage
{
    /**
     * @param Report $report
     *
     * @return bool
     */
    public function canView(Report $report)
    {
        return $this->canActionContent($report);
    }

    /**
     * @param Report $report
     *
     * @return bool
     */
    protected function canViewContent(Report $report)
    {
        /** @var \XF\Entity\ConversationMessage $content */
        $content = $report->Content;
        return $content ? $content->canView() : false;
    }

    /**
     * @param Report $report
     *
     * @return string
     */
    public function getContentLink(Report $report)
    {
        if (!$this->canViewContent($report)) {
            return '';
        }

        $router = \XF::app()->router('public');
        return $router->buildLink('canonical:conversations/messages', [
            'message_id' => $report->content_id,
        ]);
    }
}
