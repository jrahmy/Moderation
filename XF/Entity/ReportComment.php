<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Entity;

use XF\Entity\ReactionTrait;
use XF\Mvc\Entity\Structure;

/**
 * Extends \XF\Entity\ReportComment
 */
class ReportComment extends XFCP_ReportComment
{
    use ReactionTrait;

    /**
     * @return bool
     */
    public function canView()
    {
        $visitor = \XF::visitor();

        /** @var \XF\Entity\Report $report */
        $report = $this->Report;
        if (!$report) {
            return false;
        }

        return $report->canView();
    }

    /**
     * @param string|null $error
     *
     * @return bool
     */
    public function canEdit(&$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return false;
        }

        if ($visitor->hasPermission('report', 'editAnyComment')) {
            return true;
        }

        if ($visitor->user_id == $this->user_id) {
            if (!$visitor->hasPermission('report', 'editOwnComment')) {
                return false;
            }

            $editLimit = $visitor->hasPermission(
                'report',
                'editOwnCommentTimeLimit'
            );
            if (
                $editLimit != -1 &&
                (!$editLimit ||
                    $this->comment_date < \XF::$time - 60 * $editLimit)
            ) {
                $error = \XF::phraseDeferred(
                    'message_edit_time_limit_expired',
                    ['minutes' => $editLimit]
                );
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param string|null $error
     *
     * @return bool
     */
    public function canDelete(&$error = null)
    {
        if ($this->action != 'comment') {
            return false;
        }

        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return false;
        }

        if ($visitor->hasPermission('report', 'deleteAnyComment')) {
            return true;
        }

        if ($visitor->user_id == $this->user_id) {
            if (!$visitor->hasPermission('report', 'deleteOwnComment')) {
                return false;
            }

            $editLimit = $visitor->hasPermission(
                'report',
                'editOwnCommentTimeLimit'
            );
            if (
                $editLimit != -1 &&
                (!$editLimit ||
                    $this->comment_date < \XF::$time - 60 * $editLimit)
            ) {
                $error = \XF::phraseDeferred(
                    'message_edit_time_limit_expired',
                    ['minutes' => $editLimit]
                );
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param string|null $error
     *
     * @return bool
     */
    public function canReact(&$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return false;
        }

        if ($this->user_id == $visitor->user_id) {
            $error = \XF::phraseDeferred(
                'reacting_to_your_own_content_is_considered_cheating'
            );
            return false;
        }

        return $visitor->hasPermission('report', 'react');
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getBreadcrumbs()
    {
        $router = $this->app()->router('public');

        return [
            [
                'href' => $router->buildLink('reports'),
                'value' => \XF::phrase('reports'),
            ],
            [
                'href' => $router->buildLink('reports', $this->Report),
                'value' => $this->Report->title,
            ],
        ];
    }

    /**
     * @return \XF\Phrase
     */
    public function getTitle()
    {
        return \XF::phrase('j_m_comment_on_report_x', [
            'report' => $this->Report->title,
        ]);
    }

    /**
     * @return string
     */
    public function getAction()
    {
        if ($this->is_report) {
            return 'report';
        }

        switch ($this->state_change) {
            case 'open':
                return 'open';

            case 'assigned':
                return 'assign';

            case 'resolved':
                return 'resolve';

            case 'rejected':
                return 'reject';
        }

        return 'comment';
    }

    /**
     * @param Structure $structure
     *
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['j_ip_id'] = [
            'type' => self::UINT,
            'default' => 0,
        ];
        $structure->behaviors['XF:Reactable'] = ['stateField' => ''];
        $structure->behaviors['XF:NewsFeedPublishable'] = [
            'usernameField' => 'username',
            'dateField' => 'comment_date',
        ];
        $structure->getters['title'] = true;
        $structure->getters['action'] = true;

        static::addReactableStructureElements($structure);

        return $structure;
    }
}
