<?php

namespace App\Livewire\Bac;

use App\Models\Bac\PhilgepsPosting;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PhilgepsShow extends Component
{
    public PhilgepsPosting $posting;

    public function mount(PhilgepsPosting $posting): void
    {
        Gate::authorize('view', $posting);
        $this->posting = $posting->load(['procurement.modeOfProcurement', 'postedBy']);
    }

    public function render()
    {
        return view('livewire.bac.philgeps-show')
            ->layout('components.layouts.app', [
                'title' => 'PhilGEPS Posting · '.($this->posting->reference_no ?: 'Record'),
            ]);
    }
}
