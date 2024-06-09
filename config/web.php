<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'zvlibX7yj0Id0sLVdanEpbOI19FALbEx',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser'
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mongodb' => require(__DIR__ . '/db.php'),
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'POST libros/create' => 'libros/create',
                'GET libros/get-by-header-id' => 'libros/get-libros-by-id-from-header',
                'GET libros/get-by-header-genero' => 'libros/get-libros-by-genero-from-header',
                'GET libros/get-by-header-genero-autores-anio' => 'libros/get-libros-by-genero-autores-anio-from-header',
                'PUT libros/update-put' => 'libros/update-libros-put',
                'DELETE libros/delete/<id:\w+>' => 'libros/delete',
                'POST libros/login' => 'libros/login',
                'GET libros/jwt' => 'libros/jwt',
            ],
        ],
        
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
