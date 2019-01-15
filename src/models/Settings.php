<?php

namespace mattgrayisok\listbuilder\models;

use craft\base\Model;

class Settings extends Model
{
    public $syncOnSubscribe = true;
    public $syncOnSubscribeStep = 10;

    public function rules()
    {
        return [
            [['syncOnSubscribe'], 'boolean'],
            [['syncOnSubscribeStep'], 'integer']
        ];
    }
}
