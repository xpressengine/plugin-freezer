<?php
/**
 * User
 *
 * PHP version 5
 *
 * @category    Freezer
 * @package     Xpressengine\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Freezer\Models;
use Xpressengine\Database\Eloquent\DynamicModel;

/**
 * @category    Freezer
 * @package     Xpressengine\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class PasswordSkip extends DynamicModel
{
    protected $table = 'freezer_password_skip';

    protected $dates = [
        'next_check_at',
    ];
}
