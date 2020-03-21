<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation\XF\Pub\View\Report;

/**
 * Extends \XF\Pub\View\Report\View
 */
class View extends XFCP_View
{
	/**
	 * @return string
	 */
	public function renderHtml()
	{
		$options = \XF::options();
		$censorWords = $options->censorWords;

		try {
			$options->censorWords = [];
			$output = $this->renderTemplate($this->templateName, $this->params);
		} finally {
			$options->censorWords = $censorWords;
		}

		return $output;
	}
}
