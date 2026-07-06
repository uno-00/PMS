<?php

namespace App\Http\Controllers\Verify;

use App\Http\Controllers\Controller;
use App\Support\DocumentVerification;
use Illuminate\View\View;

/**
 * Public, unauthenticated document verification page. Every printed form
 * carries a QR code pointing here so any third party (COA auditor,
 * supplier, or citizen) can confirm a document is genuine and see its
 * current, authoritative status without needing system access.
 */
class DocumentVerificationController extends Controller
{
    public function __invoke(string $type, string $code): View
    {
        $result = DocumentVerification::resolve($type, $code);

        return view('verify.show', [
            'type' => $type,
            'code' => $code,
            'label' => $result['label'] ?? null,
            'record' => $result['record'] ?? null,
        ]);
    }
}
