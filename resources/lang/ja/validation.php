<?php

return [
    'required' => ':attribute は必須です。',
    'required_if' => ':otherが:valueの場合、:attributeは必須項目です。',
    'after' => ':attributeには、:dateより後の時間を指定してください。',
    'after_or_equal' => ':attributeには、:date以降の日付を指定してください。',

    // --- 以下を追加 ---
    'integer' => ':attributeには、整数を指定してください。',
    'string' => ':attributeには、文字列を指定してください。',
    'min' => [
        'numeric' => ':attributeには、:min以上の数字を指定してください。',
    ],
    'max' => [
        'string' => ':attributeは、:max文字以下で指定してください。',
    ],
    'email' => ':attributeには、有効なメールアドレスを指定してください。',
    'date' => ':attributeには、有効な日付を指定してください。',
    'regex' => ':attributeの形式が正しくありません。',
    'unique' => ':attributeの値は既に存在しています。',
    'date_format' => ':attributeの形式は、:formatと一致しません。',
];
