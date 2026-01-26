<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * Display the Privacy Policy page
     */
    public function privacyPolicy()
    {
        return view('privacy-policy', [
            'pageType' => 'privacy-policy',
        ]);
    }

    /**
     * Display the Terms of Service page
     */
    public function terms()
    {
        return view('terms', [
            'pageType' => 'terms',
        ]);
    }

    /**
     * Display the Contact page
     */
    public function contact()
    {
        return view('contact', [
            'pageType' => 'contact',
        ]);
    }

    /**
     * Handle contact form submission
     */
    public function submitContact(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name'    => 'required|string|max:100',
                'email'   => 'required|email|max:255',
                'subject' => 'required|string|max:50',
                'url'     => 'nullable|url|max:500',
                'message' => 'required|string|min:10|max:5000',
                'consent' => 'required|accepted',
            ], [
                'name.required'    => 'Please enter your name.',
                'email.required'   => 'Please enter your email address.',
                'email.email'      => 'Please enter a valid email address.',
                'subject.required' => 'Please select a subject.',
                'message.required' => 'Please enter your message.',
                'message.min'      => 'Your message must be at least 10 characters.',
                'consent.required' => 'You must agree to the privacy policy.',
                'consent.accepted' => 'You must agree to the privacy policy.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Get subject label
            $subjectLabels = [
                'general'     => 'General Inquiry',
                'support'     => 'Technical Support',
                'bug'         => 'Bug Report',
                'feature'     => 'Feature Request',
                'feedback'    => 'Feedback',
                'partnership' => 'Partnership/Business',
                'dmca'        => 'DMCA/Copyright',
                'privacy'     => 'Privacy Concern',
                'other'       => 'Other',
            ];

            $subjectLabel = $subjectLabels[$validated['subject']] ?? $validated['subject'];

            // Log the contact form submission
            Log::info('Contact form submission', [
                'name'           => $validated['name'],
                'email'          => $validated['email'],
                'subject'        => $subjectLabel,
                'url'            => $validated['url'] ?? null,
                'message_length' => strlen($validated['message']),
                'ip'             => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);

            // Store the contact message (you can implement database storage here)
            $contactData = [
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'subject'      => $subjectLabel,
                'url'          => $validated['url'] ?? null,
                'message'      => $validated['message'],
                'ip'           => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'submitted_at' => now()->toIso8601String(),
            ];

            // Option 1: Store in a JSON file (simple approach)
            $this->storeContactMessage($contactData);

            // Option 2: Send email notification (uncomment if mail is configured)
            // $this->sendContactNotification($contactData);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message! We\'ll get back to you within 24-48 hours.',
            ]);

        } catch (\Exception $e) {
            Log::error('Contact form error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending your message. Please try again or email us directly.',
            ], 500);
        }
    }

    /**
     * Store contact message to a JSON file
     */
    private function storeContactMessage(array $data): void
    {
        $storagePath = storage_path('app/contacts');

        // Create directory if it doesn't exist
        if (! file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.json';
        $filepath = $storagePath . '/' . $filename;

        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Send email notification for contact form
     * Uncomment and configure if you have mail settings
     */
    private function sendContactNotification(array $data): void
    {
        // Example using Laravel Mail
        // Mail::raw(
        //     "New contact form submission:\n\n" .
        //     "Name: {$data['name']}\n" .
        //     "Email: {$data['email']}\n" .
        //     "Subject: {$data['subject']}\n" .
        //     "URL: " . ($data['url'] ?? 'N/A') . "\n\n" .
        //     "Message:\n{$data['message']}\n\n" .
        //     "---\n" .
        //     "IP: {$data['ip']}\n" .
        //     "Submitted: {$data['submitted_at']}",
        //     function ($message) use ($data) {
        //         $message->to('support@igreeldownloader.net')
        //             ->subject('[IGReelDownloader] ' . $data['subject'])
        //             ->replyTo($data['email'], $data['name']);
        //     }
        // );
    }
}
