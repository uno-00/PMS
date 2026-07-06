<?php

namespace App\Support;

use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Planning\Ppmp;
use App\Models\Procurement\CertificateOfAvailabilityOfFunds;
use App\Models\Procurement\NoticeOfAward;
use App\Models\Procurement\NoticeToProceed;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;

/**
 * Central registry powering the public "Verify Document" page and the QR
 * codes printed on every generated form. Keeping the {type => [model,
 * column, label]} mapping in one place means new printable documents only
 * need one line here instead of a bespoke controller/route each.
 */
final class DocumentVerification
{
    public static function registry(): array
    {
        return [
            'gaa' => ['model' => GeneralAppropriationsAct::class, 'column' => 'reference_no', 'label' => 'General Appropriations Act'],
            'ppmp' => ['model' => Ppmp::class, 'column' => 'control_no', 'label' => 'Project Procurement Management Plan'],
            'pr' => ['model' => PurchaseRequest::class, 'column' => 'pr_no', 'label' => 'Purchase Request'],
            'caf' => ['model' => CertificateOfAvailabilityOfFunds::class, 'column' => 'verification_code', 'label' => 'Certificate of Availability of Funds'],
            'noa' => ['model' => NoticeOfAward::class, 'column' => 'noa_no', 'label' => 'Notice of Award'],
            'ntp' => ['model' => NoticeToProceed::class, 'column' => 'ntp_no', 'label' => 'Notice to Proceed'],
            'po' => ['model' => PurchaseOrder::class, 'column' => 'po_no', 'label' => 'Purchase Order'],
        ];
    }

    public static function urlFor(string $type, string $code): string
    {
        return route('verify.document', ['type' => $type, 'code' => $code]);
    }

    public static function resolve(string $type, string $code): ?array
    {
        $entry = self::registry()[$type] ?? null;

        if (! $entry) {
            return null;
        }

        $record = $entry['model']::query()->where($entry['column'], $code)->first();

        if (! $record) {
            return null;
        }

        return [
            'label' => $entry['label'],
            'record' => $record,
        ];
    }
}
