<?php

use projectorangebox\model\Model;

if (!function_exists('model')) {
  function model(string $name): Model
  {
    return service(service('config')->get('models.model prefix', 'model->') . $name);
  }
}
