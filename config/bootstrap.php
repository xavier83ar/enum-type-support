<?php

use Cake\Database\Type;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Validation\Validator;

Type::map('enum', 'EnumTypeSupport\\Database\\Type\\EnumType');
EventManager::instance()->on('Model.buildValidator', function (Event $event, Validator $validator) {
    $validator->provider('enum', Type::build('enum'));
});
