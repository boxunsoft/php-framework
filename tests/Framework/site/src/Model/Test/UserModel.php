<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 7:03 PM
 */

namespace TT\Model\Test;

use Alf\Model;

class UserModel extends Model
{
    protected $configKey = 'db/default';

    protected $primaryKey = 'id';
    protected $isAutoIncr = true;
    protected $table = 'user';
}