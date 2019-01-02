<?php

namespace mattgrayisok\listbuilder\models;

use craft\base\Model;

class Settings extends Model
{
    public $syncOnSubscribe = true;

    public function rules()
    {
        return [
            [['syncOnSubscribe'], 'boolean'],
        ];
    }
}
