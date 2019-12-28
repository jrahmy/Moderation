<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

/**
 * Extends \XF\Pub\Controller\Conversation
 */
class Conversation extends XFCP_Conversation
{
    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionMessagesWarn(ParameterBag $params)
    {
        $message = $this->assertMessageExists($params->message_id);
        if (!$message->canWarn($error)) {
            return $this->noPermission($error);
        }

        $breadcrumbs = [
            [
                'value' => \XF::phrase('conversations'),
                'href' => $this->buildLink('conversations'),
            ],
            [
                'value' => $message->Conversation->title,
                'href' => $this->buildLink(
                    'conversations',
                    $message->Conversation
                ),
            ],
        ];

        /** @var \XF\ControllerPlugin\Warn $warnPlugin */
        $warnPlugin = $this->plugin('XF:Warn');
        return $warnPlugin->actionWarn(
            'conversation_message',
            $message,
            $this->buildLink('conversations/messages/warn', $message),
            $breadcrumbs
        );
    }

    /**
     * @param int      $messageId
     * @param string[] $extraWith
     *
     * @return \XF\Entity\ConversationMessage
     *
     * @throws \XF\Mvc\Reply\Exception
     */
    public function assertMessageExists($messageId, array $extraWith = [])
    {
        $extraWith[] = 'Conversation';

        $visitor = \XF::visitor();
        if ($visitor->user_id) {
            $extraWith[] = 'Conversation.Recipients|' . $visitor->user_id;
            $extraWith[] = 'Conversation.Users|' . $visitor->user_id;
        }

        array_unique($extraWith);

        /** @var \XF\Entity\ConversationMessage $message */
        $message = $this->em()->find(
            'XF:ConversationMessage',
            $messageId,
            $extraWith
        );
        if (!$message) {
            throw $this->exception(
                $this->notFound(\XF::phrase('requested_message_not_found'))
            );
        }

        return $message;
    }
}
