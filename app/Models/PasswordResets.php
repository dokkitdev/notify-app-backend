<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 1/11/19
 * Time: 10:51 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PasswordResets extends Model
{
    protected $table = 'password_resets';
    protected $fillable = [
        'email',
        'token',
    ];

}