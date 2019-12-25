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

/**
 * A service for deleting a report comment.
 */
class CommentDeleter extends AbstractService
{
    /**
     * @var ReportComment
     */
    protected $comment;

    /**
     * @param \XF\App       $app
     * @param ReportComment $comment
     */
    public function __construct(\XF\App $app, ReportComment $comment)
    {
        parent::__construct($app);
        $this->comment = $comment;
    }

    /**
     * @return ReportComment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return $this->comment->delete();
    }
}
