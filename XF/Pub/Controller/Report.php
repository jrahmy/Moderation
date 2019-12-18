<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Pub\Controller;

use Jrahmy\Moderation\Entity\WarnableInterface;
use XF\Mvc\ParameterBag;

/**
 * Extends \XF\Pub\Controller\Report
 */
class Report extends XFCP_Report
{
    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionView(ParameterBag $params)
    {
        $reply = parent::actionView($params);

        if ($reply instanceof \XF\Mvc\Reply\View) {
            /** @var \XF\Entity\Report $report */
            $report = $reply->getParam('report');

            $content = $report->Content;
            $warnable = $content && $content instanceof WarnableInterface;

            /** @var \XF\Mvc\Entity\AbstractCollection */
            $warnings = $report->Warnings;
            $warning = $warnings->count() === 1 ? $warnings->first() : null;

            $reply->setParams([
                'warnable' => $warnable,
                'warning' => $warning,
            ]);
        }

        return $reply;
    }
}
