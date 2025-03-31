<?php

namespace App\Http\Controllers;

use App\HomeDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomeDescriptionController extends Controller
{
    public function index()
    {
        $homeDescriptions = Cache::remember('home_descriptions_cache', 60, function () {
            return HomeDescription::latest()->first();
        });

        if ($homeDescriptions) {
            if ($homeDescriptions->image) {
                $homeDescriptions->image_url = asset('storage/' . $homeDescriptions->image);
            }
            if ($homeDescriptions->logo) {
                $homeDescriptions->logo_url = asset('storage/' . $homeDescriptions->logo);
            }
        }

        return response()->json($homeDescriptions);
    }

    public function show($id)
    {
        $description = HomeDescription::findOrFail($id);

        if ($description->image) {
            $description->image_url = asset('storage/' . $description->image);
        }

        if ($description->logo) {
            $description->logo_url = asset('storage/' . $description->logo);
        }

        return response()->json($description);
    }

    public function store(Request $request)
    {
        // Validate request data
        $data = $request->validate([
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/banner', $imageName);
            $data['image'] = 'banner/' . $imageName;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_logo_' . $logo->getClientOriginalName();
            $logo->storeAs('public/logo', $logoName);
            $data['logo'] = 'logo/' . $logoName;
        }

        // Clear cache
        Cache::forget('home_descriptions_cache');

        // Create new description
        $homeDescription = HomeDescription::create($data);

        // Add full URLs
        $homeDescription->image_url = isset($data['image']) ? asset('storage/' . $data['image']) : null;
        $homeDescription->logo_url = isset($data['logo']) ? asset('storage/' . $data['logo']) : null;

        return response()->json($homeDescription);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $description = HomeDescription::findOrFail($id);

        // Update image if provided
        if ($request->hasFile('image')) {
            if ($description->image) {
                Storage::delete('public/' . $description->image);
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/banner', $imageName);
            $data['image'] = 'banner/' . $imageName;
        }

        // Update logo if provided
        if ($request->hasFile('logo')) {
            if ($description->logo) {
                Storage::delete('public/' . $description->logo);
            }
            $logo = $request->file('logo');
            $logoName = time() . '_logo_' . $logo->getClientOriginalName();
            $logo->storeAs('public/logo', $logoName);
            $data['logo'] = 'logo/' . $logoName;
        }

        // Clear cache
        Cache::forget('home_descriptions_cache');

        // Update description
        $description->update($data);

        // Add full URLs
        $description->image_url = isset($data['image']) ? asset('storage/' . $data['image']) : asset('storage/' . $description->image);
        $description->logo_url = isset($data['logo']) ? asset('storage/' . $data['logo']) : asset('storage/' . $description->logo);

        return response()->json($description);
    }

    public function destroy($id)
    {
        $description = HomeDescription::findOrFail($id);

        // Delete associated image
        if ($description->image) {
            Storage::delete('public/' . $description->image);
        }

        // Delete associated logo
        if ($description->logo) {
            Storage::delete('public/' . $description->logo);
        }

        $description->delete();

        return response()->json(null, 204);
    }
}
