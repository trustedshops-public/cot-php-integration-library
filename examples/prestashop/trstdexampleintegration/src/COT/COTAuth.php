<?php

namespace TRSTDExampleIntegration\COT;

use ObjectModel;

class COTAuth extends ObjectModel
{
    /**
     * @var string
     */
    public $id_sub;

    /**
     * @var string
     */
    public $id_token;

    /**
     * @var string
     */
    public $refresh_token;

    /**
     * @var string
     */
    public $access_token;

    public static $definition = [
        'table' => 'trstd_cot_auth',
        'primary' => 'id_sub',
        'fields' => [
            'id_sub' => [
                'type' => self::TYPE_STRING,
                'required' => true,
                'size' => 36,
            ],
            'id_token' => [
                'type' => self::TYPE_HTML,
                'required' => true,
            ],
            'refresh_token' => [
                'type' => self::TYPE_HTML,
                'required' => true,
            ],
            'access_token' => [
                'type' => self::TYPE_HTML,
                'required' => true,
            ]
        ],
    ];
}
