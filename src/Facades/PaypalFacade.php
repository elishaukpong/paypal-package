<?php

/**
 *
 *  This file is part of the Paypal Laravel package.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @version         1.0
 *
 *  @author          Elisha Ukpong
 *  @license         MIT Licence
 *  @copyright       (c) Elisha Ukpong <ishukpong418@gmail.com>
 *
 *  @link            https://github.com/drumzminister
 *
 */

namespace Drumzminister\Paypal\Facades;

use Illuminate\Support\Facades\Facade;


/**
 * Class PaypalFacade
 * @package Drumzminister\Paypal\Facades
 */
class PaypalFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Paypal';
    }
}
