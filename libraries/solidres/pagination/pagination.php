<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2014 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die;

/**
 * Solidres Pagination class
 *
 * @package     Solidres
 * @subpackage	Pagination
 * @since		0.6.0
 */

class SRPagination extends JPagination
{
	/**
	 * Create and return the pagination data object.
	 *
	 * @return  object  Pagination data object.
	 *
	 * @since   1.5
	 */
	protected function _buildDataObject()
	{
		$data = new stdClass;

		// Build the additional URL parameters string.
		$params = '';

		if (!empty($this->additionalUrlParams))
		{
			foreach ($this->additionalUrlParams as $key => $value)
			{
				$params .= '&' . $key . '=' . $value;
			}
		}

		$data->all = new JPaginationObject(JText::_('JLIB_HTML_VIEW_ALL'), $this->prefix);

		if (!$this->viewall)
		{
			$data->all->base = '0';
			$data->all->link = self::removeQueryString(JRoute::_($params . '&' . $this->prefix . 'limitstart='), 'show');
		}

		// Set the start and previous data objects.
		$data->start = new JPaginationObject(JText::_('JLIB_HTML_START'), $this->prefix);
		$data->previous = new JPaginationObject(JText::_('JPREV'), $this->prefix);

		if ($this->pagesCurrent > 1)
		{
			$page = ($this->pagesCurrent - 2) * $this->limit;

			// Set the empty for removal from route
			// @todo remove code: $page = $page == 0 ? '' : $page;

			$data->start->base = '0';
			$data->start->link = self::removeQueryString(JRoute::_($params . '&' . $this->prefix . 'limitstart=0'), 'show');
			$data->previous->base = $page;
			$data->previous->link = self::removeQueryString(JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $page), 'show');
		}

		// Set the next and end data objects.
		$data->next = new JPaginationObject(JText::_('JNEXT'), $this->prefix);
		$data->end = new JPaginationObject(JText::_('JLIB_HTML_END'), $this->prefix);

		if ($this->pagesCurrent < $this->pagesTotal)
		{
			$next = $this->pagesCurrent * $this->limit;
			$end = ($this->pagesTotal - 1) * $this->limit;

			$data->next->base = $next;
			$data->next->link = self::removeQueryString(JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $next), 'show');
			$data->end->base = $end;
			$data->end->link = self::removeQueryString(JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $end), 'show');
		}

		$data->pages = array();
		$stop = $this->pagesStop;

		for ($i = $this->pagesStart; $i <= $stop; $i++)
		{
			$offset = ($i - 1) * $this->limit;

			$data->pages[$i] = new JPaginationObject($i, $this->prefix);

			if ($i != $this->pagesCurrent || $this->viewall)
			{
				$data->pages[$i]->base = $offset;
				$data->pages[$i]->link = self::removeQueryString(JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $offset), 'show');
			}
			else
			{
				$data->pages[$i]->active = true;
			}
		}

		return $data;
	}

	private function removeQueryString($url, $key)
	{
		$tmp = JUri::getInstance($url);
		$tmp->delVar($key);
		return $tmp->__toString();
	}
}