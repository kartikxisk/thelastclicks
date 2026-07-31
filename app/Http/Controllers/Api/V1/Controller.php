<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller as BaseController;

/**
 * Base for every /api/v1 controller. Exists so cross-cutting V1 concerns have
 * one home, and so `Api\V1\Controller` is unambiguous at the import site.
 */
abstract class Controller extends BaseController {}
