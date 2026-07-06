@props(['value', 'size' => 100])
<img src="data:image/png;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size($size)->margin(1)->generate($value)) }}"
     alt="QR Verification Code" width="{{ $size }}" height="{{ $size }}" {{ $attributes }}>
