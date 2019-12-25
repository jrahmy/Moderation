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

    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionComments(ParameterBag $params)
    {
        $comment = $this->assertViewableComment($params->report_comment_id);

        $anchor = "#report-comment-{$comment->report_comment_id}";
        return $this->redirectPermanently(
            $this->buildLink('reports', $comment->Report) . $anchor
        );
    }

    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionCommentsEdit(ParameterBag $params)
    {
        $comment = $this->assertViewableComment($params->report_comment_id);
        if (!$comment->canEdit($error)) {
            return $this->noPermission($error);
        }

        if ($this->isPost()) {
            $editor = $this->setupCommentEdit($comment);

            if (!$editor->validate($errors)) {
                return $this->error($errors);
            }

            $editor->save();
            $this->finalizeCommentEdit($editor);

            if (
                $this->filter('_xfWithData', 'bool') &&
                $this->filter('_xfInlineEdit', 'bool')
            ) {
                $viewParams = [
                    'report' => $comment->Report,
                    'comment' => $comment,
                ];
                $reply = $this->view(
                    'XF:Report\Comments\EditNewComment',
                    'j_m_report_comment_edit_new_comment',
                    $viewParams
                );
                $reply->setJsonParam(
                    'message',
                    \XF::phrase('your_changes_have_been_saved')
                );
                return $reply;
            }

            return $this->redirect(
                $this->buildLink('reports/comments', $comment)
            );
        }

        $viewParams = [
            'comment' => $comment,
            'report' => $comment->Report,
            'quickEdit' => $this->responseType() == 'json',
        ];
        return $this->view(
            'XF:Report\Comments\Edit',
            'j_m_report_comment_edit',
            $viewParams
        );
    }

    /**
     * @param \XF\Entity\ReportComment $comment
     *
     * @return \Jrahmy\Moderation\Service\Report\CommentEditor
     */
    protected function setupCommentEdit(\XF\Entity\ReportComment $comment)
    {
        $message = $this->plugin('XF:Editor')->fromInput('message');

        /** @var \Jrahmy\Moderation\Service\Report\CommentEditor $editor */
        $editor = $this->service(
            'Jrahmy\Moderation:Report\CommentEditor',
            $comment
        );
        $editor->setMessage($message);

        return $editor;
    }

    /**
     * @param \Jrahmy\Moderation\Service\Report\CommentEditor $editor
     */
    protected function finalizeCommentEdit(
        \Jrahmy\Moderation\Service\Report\CommentEditor $editor
    ) {
    }

    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionCommentsDelete(ParameterBag $params)
    {
        $comment = $this->assertViewableComment($params->report_comment_id);
        if (!$comment->canDelete($error)) {
            return $this->noPermission($error);
        }

        if ($this->isPost()) {
            /** @var \Jrahmy\Moderation\Service\Report\CommentDeleter $deleter */
            $deleter = $this->service(
                'Jrahmy\Moderation:Report\CommentDeleter',
                $comment
            );
            $deleter->delete();

            return $this->redirect(
                $this->getDynamicRedirect(
                    $this->buildLink('reports', $comment->Report),
                    false
                )
            );
        }

        $viewParams = [
            'comment' => $comment,
            'report' => $comment->Report,
        ];
        return $this->view(
            'XF:Report\Comments\Delete',
            'j_m_report_comment_delete',
            $viewParams
        );
    }

    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionCommentsIp(ParameterBag $params)
    {
        /** @var \Jrahmy\Moderation\XF\Entity\ReportComment $comment */
        $comment = $this->assertViewableComment($params->report_comment_id);

        /** @var \XF\ControllerPlugin\Ip $ipPlugin */
        $ipPlugin = $this->plugin('XF:Ip');
        return $ipPlugin->actionIp($comment, $comment->getBreadcrumbs(), [
            'id' => 'j_ip_id',
        ]);
    }

    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionCommentsReact(ParameterBag $params)
    {
        $comment = $this->assertViewableComment($params->report_comment_id);

        /** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
        $reactionPlugin = $this->plugin('XF:Reaction');
        return $reactionPlugin->actionReactSimple($comment, 'reports/comments');
    }

    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionCommentsReactions(ParameterBag $params)
    {
        /** @var \Jrahmy\Moderation\XF\Entity\ReportComment $comment */
        $comment = $this->assertViewableComment($params->report_comment_id);

        /** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
        $reactionPlugin = $this->plugin('XF:Reaction');
        return $reactionPlugin->actionReactions(
            $comment,
            'reports/comments/reactions',
            null,
            $comment->getBreadcrumbs()
        );
    }

    /**
     * @param       $commentId
     * @param array $extraWith
     *
     * @return \XF\Entity\ReportComment
     *
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertViewableComment($commentId, array $extraWith = [])
    {
        $extraWith[] = 'Report';
        $extraWith[] = 'User';
        array_unique($extraWith);

        /** @var \Jrahmy\Moderation\XF\Entity\ReportComment $comment */
        $comment = $this->em()->find(
            'XF:ReportComment',
            $commentId,
            $extraWith
        );
        if (!$comment) {
            throw $this->exception(
                $this->notFound(\XF::phrase('requested_comment_not_found'))
            );
        }
        if (!$comment->canView()) {
            throw $this->exception($this->noPermission());
        }

        return $comment;
    }
}
