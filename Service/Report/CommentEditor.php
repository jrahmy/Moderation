<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\Service\Report;

use XF\Entity\ReportComment;
use XF\Service\AbstractService;
use XF\Service\ValidateAndSavableTrait;

/**
 * A service for editing a report comment.
 */
class CommentEditor extends AbstractService
{
    use ValidateAndSavableTrait;

    /**
     * @var ReportComment
     */
    protected $comment;

    /**
     * @var \XF\Service\Report\CommentPreparer
     */
    protected $preparer;

    /**
     * @param \XF\App       $app
     * @param ReportComment $comment
     */
    public function __construct(\XF\App $app, ReportComment $comment)
    {
        parent::__construct($app);
        $this->setComment($comment);
    }

    /**
     * @param ReportComment $comment
     */
    protected function setComment(ReportComment $comment)
    {
        $this->comment = $comment;
        $this->commentPreparer = $this->service(
            'XF:Report\CommentPreparer',
            $comment
        );
    }

    /**
     * @return ReportComment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return \XF\Service\Report\CommentPreparer
     */
    public function getCommentPreparer()
    {
        return $this->commentPreparer;
    }

    /**
     * @param string $message
     * @param bool $format
     *
     * @return bool
     */
    public function setMessage($message, $format = true)
    {
        return $this->commentPreparer->setMessage($message, $format);
    }

    /**
     * Service pre-validation events.
     */
    protected function finalSetup()
    {
    }

    /**
     * @return array
     */
    protected function _validate()
    {
        $this->finalSetup();

        $this->comment->preSave();
        return $this->comment->getErrors();
    }

    /**
     * @return \XF\Entity\ReportComment
     */
    protected function _save()
    {
        $this->comment->save();
        return $this->comment;
    }
}
