<?php

return [

    /*
    | Token de verificación global del webhook (Meta lo envía en el GET de alta).
    | Cada bot puede tener su propio verify_token; si está vacío se usa este.
    */
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN', ''),

    /*
    | Versión del Graph API por defecto (cada bot puede sobreescribirla).
    */
    'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v21.0'),

    /*
    | Cola para el procesamiento diferido (solo la regla de espera). En hosting
    | compartido la respuesta inmediata usa app()->terminating(), no la cola.
    */
    'queue' => env('WHATSAPP_QUEUE', 'default'),

];
