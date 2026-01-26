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
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            SiteSetting::set($key, $value, 'text', 'general');
        }

        SiteSetting::clearCache();

        return back()->with('success', 'Settings updated successfully.');
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
