<?php

return [
    'required' => ':attribute は必須です。',
    'required_if' => ':otherが:valueの場合、:attributeは必須項目です。',
    'after' => ':attributeには、:dateより後の時間を指定してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'business_hours.*.is_open' => '営業日',
        'business_hours.*.start_time' => '開始時刻',
        'business_hours.*.end_time' => '終了時刻',
    ],
];
