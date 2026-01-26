<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * Display the Privacy Policy page
     */
    public function privacyPolicy()
    {
        $page = Page::where('slug', 'privacy-policy')->first();
        
        if ($page && !$page->is_active) {
            return redirect()->route('home');
        }
        
        return view('privacy-policy', [
            'pageType' => 'privacy-policy',
            'page' => $page,
        ]);
    }

    /**
     * Display the Terms of Service page
     */
    public function terms()
    {
        $page = Page::where('slug', 'terms')->first();
        
        if ($page && !$page->is_active) {
            return redirect()->route('home');
        }
        
        return view('terms', [
            'pageType' => 'terms',
            'page' => $page,
        ]);
    }

    /**
     * Display the Contact page
     */
    public function contact()
    {
        return view('contact', [
            'pageType' => 'contact',
            'contactEmail' => SiteSetting::get('contact_email', 'support@igreeldownloader.net'),
            'dmcaEmail' => SiteSetting::get('dmca_email', 'dmca@igreeldownloader.net'),
            'privacyEmail' => SiteSetting::get('privacy_email', 'privacy@igreeldownloader.net'),
            'responseTimeGeneral' => SiteSetting::get('response_time_general', '24-48 hours'),
            'responseTimeSupport' => SiteSetting::get('response_time_support', '1-3 days'),
            'responseTimeDmca' => SiteSetting::get('response_time_dmca', '3-5 days'),
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
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:255',
                'subject' => 'required|string|max:50',
                'url' => 'nullable|url|max:500',
                'message' => 'required|string|min:10|max:5000',
                'consent' => 'required|accepted',
            ], [
                'name.required' => 'Please enter your name.',
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'subject.required' => 'Please select a subject.',
                'message.required' => 'Please enter your message.',
                'message.min' => 'Your message must be at least 10 characters.',
                'consent.required' => 'You must agree to the privacy policy.',
                'consent.accepted' => 'You must agree to the privacy policy.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Store in database
            $contactMessage = ContactMessage::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'url' => $validated['url'] ?? null,
                'message' => $validated['message'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'new',
            ]);

            Log::info('Contact form submission saved', [
                'id' => $contactMessage->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
            ]);

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
}