<?php

namespace App\Policies;

use App\Models\Bac\BacCalendarEvent;
use App\Models\User;

/**
 * BAC calendar events are simple scheduled records (no workflow state
 * machine), so authorization is a plain permission check against the
 * "bac-calendar" module keys.
 */
class BacCalendarEventPolicy extends BasePolicy
{
    use ModuleCrudPolicy;

    protected string $module = 'bac-calendar';
}
