<?php

namespace App\Http\Controllers;

use App\Exports\GaaTemplateExport;
use App\Models\Budget\GeneralAppropriationsAct;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Downloads the GAA Excel template so end-users know the exact column
 * layout the importer expects, along with a reference sheet of every
 * valid code currently configured. Authorized against gaa.upload (the same
 * permission required to submit a filled-in file on the Upload GAA page).
 */
class GaaTemplateController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('upload', GeneralAppropriationsAct::class);

        return Excel::download(new GaaTemplateExport, 'GAA-Excel-Template.xlsx');
    }
}
