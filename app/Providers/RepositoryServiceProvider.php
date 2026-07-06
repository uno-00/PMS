<?php

namespace App\Providers;

use App\Repositories\Contracts\GaaRepositoryInterface;
use App\Repositories\Eloquent\GaaRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Central place that wires every {Contract} => {Eloquent implementation}
 * repository binding. New modules register their pair here, keeping
 * business/service classes decoupled from Eloquent specifics and easy to
 * mock in unit tests. Each concrete repository type-hints its own model
 * in its constructor, so the container can autowire it with no extra
 * factory closures.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        GaaRepositoryInterface::class => GaaRepository::class,
    ];
}
