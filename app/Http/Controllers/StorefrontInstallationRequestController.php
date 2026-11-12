<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StorefrontInstallationRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if ($request->filled('company_website')) {
            return response()->json(['message' => 'Thanks, your installation request has been received.'], 202);
        }

        $services = [
            'Android radio upgrade',
            'Car speaker or subwoofer upgrade',
            'Amplifier installation',
            'Reverse camera or dashcam installation',
            'Alarm or parking sensor installation',
            'Other',
        ];

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+() .-]{7,40}$/'],
            'vehicle_make' => ['required', 'string', 'max:100'],
            'vehicle_model' => ['required', 'string', 'max:120'],
            'vehicle_year' => ['nullable', 'integer', 'min:1950', 'max:'.(now()->year + 1)],
            'service' => ['required', 'string', Rule::in($services)],
            'service_detail' => ['required_if:service,Other', 'nullable', 'string', 'max:140'],
            'preferred_at' => ['nullable', 'date', 'after_or_equal:now'],
            'location' => ['nullable', 'string', 'max:190'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $service = $data['service'] === 'Other'
            ? 'Other: '.$data['service_detail']
            : $data['service'];
        $notes = collect([
            filled($data['preferred_at'] ?? null)
                ? 'Preferred date and time: '.Carbon::parse($data['preferred_at'])->format('D, M j, Y · g:i A')
                : null,
            filled($data['notes'] ?? null) ? 'Customer message: '.$data['notes'] : null,
        ])->filter()->implode("\n\n");

        DB::table('installation_bookings')->insert([
            'customer_name' => $data['customer_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'vehicle_make' => $data['vehicle_make'],
            'vehicle_model' => $data['vehicle_model'],
            'vehicle_year' => $data['vehicle_year'] ?? null,
            'service' => $service,
            'scheduled_at' => null,
            'location' => $data['location'] ?? null,
            'technician' => null,
            'service_charge' => 0,
            'status' => 'pending',
            'notes' => $notes !== '' ? $notes : null,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Thanks, your request has been sent. Our installation team will contact you to confirm fitment and timing.',
        ], 201);
    }
}
