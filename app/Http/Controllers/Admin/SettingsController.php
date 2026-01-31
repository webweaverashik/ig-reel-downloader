<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Show general settings
     */
    public function general()
    {
        $settings = SiteSetting::where('group', 'general')->orderBy('order')->get();
        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $data = $request->except(['_token', 'site_logo', 'site_favicon', 'delete_logo', 'delete_favicon']);

        // Handle regular text settings
        foreach ($data as $key => $value) {
            SiteSetting::set($key, $value, 'text', 'general');
        }

        // Handle File Uploads
        $this->handleFileUpload($request, 'site_logo', 'logo');
        $this->handleFileUpload($request, 'site_favicon', 'favicon');

        // Handle File Deletions
        if ($request->has('delete_logo')) {
            $this->deleteFile('site_logo');
        }
        if ($request->has('delete_favicon')) {
            $this->deleteFile('site_favicon');
        }

        SiteSetting::clearCache();

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Handle file upload helper
     */
    private function handleFileUpload(Request $request, string $key, string $folder)
    {
        if ($request->hasFile($key)) {

            // Validation rules
            $rules = match ($key) {
                'site_favicon' => 'mimes:ico,png|max:1024',
                default        => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            };

            $request->validate([
                $key => $rules,
            ]);

            $file = $request->file($key);

            $path = public_path('uploads/' . $folder);
            if (! file_exists($path)) {
                mkdir($path, 0755, true);
            }

            $filename = $folder . '_' . time() . '.' . $file->getClientOriginalExtension();

            $oldSetting = SiteSetting::get($key);
            if ($oldSetting && file_exists(public_path('uploads/' . $oldSetting))) {
                @unlink(public_path('uploads/' . $oldSetting));
            }

            $file->move($path, $filename);

            SiteSetting::set($key, $folder . '/' . $filename, 'file', 'general');
        }
    }

    /**
     * Delete file helper
     */
    private function deleteFile(string $key)
    {
        $oldSetting = SiteSetting::get($key);
        if ($oldSetting && is_string($oldSetting) && file_exists(public_path('uploads/' . $oldSetting))) {
            @unlink(public_path('uploads/' . $oldSetting));
        }
        SiteSetting::set($key, null, 'file', 'general');
    }

    /**
     * Show SEO settings
     */
    public function seo()
    {
        $settings = SiteSetting::where('group', 'seo')->orderBy('order')->get();
        return view('admin.settings.seo', compact('settings'));
    }

    /**
     * Update SEO settings
     */
    public function updateSeo(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            SiteSetting::set($key, $value, 'text', 'seo');
        }

        SiteSetting::clearCache();

        return back()->with('success', 'SEO settings updated successfully.');
    }

    /**
     * Show contact settings
     */
    public function contact()
    {
        $settings = SiteSetting::where('group', 'contact')->orderBy('order')->get();
        return view('admin.settings.contact', compact('settings'));
    }

    /**
     * Update contact settings
     */
    public function updateContact(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            SiteSetting::set($key, $value, 'text', 'contact');
        }

        SiteSetting::clearCache();

        return back()->with('success', 'Contact settings updated successfully.');
    }

    /**
     * Show social settings
     */
    public function social()
    {
        $settings = SiteSetting::where('group', 'social')->orderBy('order')->get();
        return view('admin.settings.social', compact('settings'));
    }

    /**
     * Update social settings
     */
    public function updateSocial(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            SiteSetting::set($key, $value, 'text', 'social');
        }

        SiteSetting::clearCache();

        return back()->with('success', 'Social settings updated successfully.');
    }
}
