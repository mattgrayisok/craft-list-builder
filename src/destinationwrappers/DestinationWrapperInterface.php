<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Email mailing list popup notification exit
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\destinationwrappers;

use mattgrayisok\listbuilder\ListBuilder;

use Craft;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
interface DestinationWrapperInterface
{

    public function syncSignups($signups, $destination = null);

}
