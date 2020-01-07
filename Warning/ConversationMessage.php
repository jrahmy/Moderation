<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\Warning;

use XF\Entity\Warning;
use XF\Mvc\Entity\Entity;
use XF\Warning\AbstractHandler;

/**
 * An warning handler for conversation messages.
 */
class ConversationMessage extends AbstractHandler
{
    /**
     * @param Entity $entity
     *
     * @return string
     */
    public function getStoredTitle(Entity $entity)
    {
        /** @var \XF\Entity\ConversationMessage $entity */
        return $entity->Conversation->title;
    }

    /**
     * @param string $title
     *
     * @return string
     */
    public function getDisplayTitle($title)
    {
        return \XF::phrase('conversation_message_in_x', ['title' => $title]);
    }

    /**
     * @param Entity $entity
     *
     * @return string
     */
    public function getContentForConversation(Entity $entity)
    {
        /** @var \XF\Entity\ConversationMessage $entity */
        return $entity->message;
    }

    /**
     * @param Entity $entity
     *
     * @return \XF\Entity\User
     */
    public function getContentUser(Entity $entity)
    {
        /** @var \XF\Entity\ConversationMessage $entity */
        return $entity->User;
    }

    /**
     * @param Entity $entity
     * @param bool   $canonical
     *
     * @return string
     */
    public function getContentUrl(Entity $entity, $canonical = false)
    {
        $router = \XF::app()->router('public');
        return $router->buildLink(
            ($canonical ? 'canonical:' : '') . 'conversations/messages',
            $entity
        );
    }

    /**
     * @param Entity      $entity
     * @param string|null $error
     *
     * @return bool
     */
    public function canViewContent(Entity $entity, &$error = null)
    {
        /** @var \XF\Entity\ConversationMessage $entity */
        return $entity->canView($error);
    }

    /**
     * @param Entity  $entity
     * @param Warning $warning
     */
    public function onWarning(Entity $entity, Warning $warning)
    {
        $entity->j_warning_id = $warning->warning_id;
        $entity->save();
    }

    /**
     * @param Entity  $entity
     * @param Warning $warning
     */
    public function onWarningRemoval(Entity $entity, Warning $warning)
    {
        $entity->j_warning_id = 0;
        $entity->j_warning_message = '';
        $entity->save();
    }

    /**
     * @param Entity $entity
     * @param string $action
     * @param array  $options
     */
    public function takeContentAction(Entity $entity, $action, array $options)
    {
        switch ($action) {
            case 'public':
                $message = isset($options['message'])
                    ? $options['message']
                    : '';
                if (is_string($message) && strlen($message)) {
                    $entity->j_warning_message = $message;
                    $entity->save();
                }
                break;
        }
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    public function canWarnPublicly(Entity $entity)
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function getEntityWith()
    {
        $visitor = \XF::visitor();
        return ['Conversation', "Conversation.Users|{$visitor->user_id}"];
    }
}
