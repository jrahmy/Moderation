<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\ControllerPlugin;

/**
 * Extends \XF\ControllerPlugin\Report
 */
class Report extends XFCP_Report
{
    /**
     * @param string                $contentType
     * @param \XF\Mvc\Entity\Entity $content
     * @param string                $confirmUrl
     * @param string                $returnUrl
     * @param array                 $options
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionReport(
        $contentType,
        \XF\Mvc\Entity\Entity $content,
        $confirmUrl,
        $returnUrl,
        $options = []
    ) {
        $reply = parent::actionReport(
            $contentType,
            $content,
            $confirmUrl,
            $returnUrl,
            $options
        );

        if (!($reply instanceof \XF\Mvc\Reply\Redirect)) {
            return $reply;
        }

        $visitor = \XF::visitor();
        if (!$visitor->is_moderator) {
            return $reply;
        }

        $report = $content->Report;
        if (!$report || !$report->canView()) {
            return $reply;
        }

        return $this->redirect(
            $this->buildLink('reports', $report),
            \XF::phrase('thank_you_for_reporting_this_content')
        );
    }
}
