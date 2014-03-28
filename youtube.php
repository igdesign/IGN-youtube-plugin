<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.loadmodule
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plug-in to enable loading modules into content (e.g. articles)
 * This uses the {loadmodule} syntax
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.loadmodule
 * @since       1.5
 */
class PlgContentYoutube extends JPlugin
{
	protected static $modules = array();

	protected static $mods = array();

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  mixed   true if there is an error. Void otherwise.
	 *
	 * @since   1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer')
		{
			return true;
		}


		// Simple performance check to determine whether bot should process further
		if (strpos($article->text, '{youtube') === false && strpos($article->text, '{youtube') === false)
		{
			return true;
		}

		// Expression to search for (positions)
		$regex		= '/{youtube\s(.*?)}/i';
		$style		= $this->params->def('style', 'iframe');

		// Find all instances of plugin and put in $matches for loadposition
		// $matches[0] is full pattern match, $matches[1] is the position
		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);



		// No matches, skip this
		if ($matches)
		{
			foreach ($matches as $match)
			{
				$matcheslist = explode(',', $match[1]);

				// We may not have a module style so fall back to the plugin default.
				if (!array_key_exists(1, $matcheslist))
				{
					$matcheslist[1] = $style;
				}

				$identifier = trim($matcheslist[0]);
				$style      = trim($matcheslist[1]);


				$output = $this->_load($identifier, $style);

				// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
				$article->text = preg_replace("|$match[0]|", addcslashes($output, '\\$'), $article->text, 1);
				$style = $this->params->def('style', 'none');
			}
		}
  }


	/**
	 * Loads and renders the video embed
	 *
	 * @param   string  $position  The position assigned to the module
	 * @param   string  $style     The style assigned to the module
	 *
	 * @return  mixed
	 *
	 * @since   1.6
	 */
	protected function _load($identifier, $style = 'none')
	{

/*     <iframe src="http://www.youtube.com/embed/jINRx_HMdaw?rel=0&amp;fs=1&amp;wmode=transparent" width="464" height="261" allowfullscreen="allowfullscreen" title="JoomlaWorks AllVideos Player" style="background-color: transparent; border-width: 0px; margin: 0px; outline: 0px; padding: 0px; vertical-align: baseline; font-style: inherit; font-family: inherit;"></iframe> */
    $videoSrc = 'http://youtube.com/embed/'.$identifier;

    $output = null;

    if ($style == 'object') {
      $videoSrc .= '?version=3&amp;hl=en_US';
      $output = '<object data="'.$videoSrc.'" type="application/x-shockwave-flash" ><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="allowfullscreen" value="true" /><param name="movie" value="'.$videoSrc.'" /></object>';
    } else {
      $videoSrc .= '?rel=0&amp;fs=1&amp;wmode=transparent';
      $output = '<iframe class="youtube-embed" src="'.$videoSrc.'" allowfullscreen="allowfullscreen" ></iframe>';
    }


		return $output;
	}
}
