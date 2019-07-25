<?php
/**
 * User
 *
 * PHP version 7
 *
 * @category    User
 * @package     Xpressengine\User
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Freezer\Models;

use Xpressengine\User\Models\User as OriginUser;

/**
 * @category    User
 * @package     Xpressengine\User
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class User extends OriginUser
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @deprecated since rc.8 instead use freezeLogs()
     */
    public function freeze_logs()
    {
        return $this->hasMany(Log::class, 'user_id');
    }

    /**
     * freeze logs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function freezeLogs()
    {
        return $this->hasMany(Log::class, 'user_id');
    }
}
