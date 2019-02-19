<?php
/**
 * Created by PhpStorm.
 * User: jomon
 * Date: 5/10/18
 * Time: 11:08 AM
 */

return [
    'mailjet' => [
        'class' => \codexten\yii\mailjet\Mailer::class,
        'apikey' => $params['mailjet.apikey'],
        'secret' => $params['mailjet.secret'],
    ],
];