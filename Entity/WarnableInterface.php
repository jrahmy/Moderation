<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\Entity;

/**
 * An interface for warnable entities.
 */
interface WarnableInterface
{
    /**
     * @param string|null $error
     *
     * @return bool
     */
    public function canWarn(&$error = null);

    /**
     * @return string
     */
    public function getWarnUrl();
}
