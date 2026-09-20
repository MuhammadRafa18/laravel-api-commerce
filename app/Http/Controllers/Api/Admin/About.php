<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\About as ResourcesAbout;
use App\Models\About as ModelsAbout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class About extends Controller
{
    public function index()
    {

        $about = ModelsAbout::with('powers')->orderBy('created_at', 'desc')->get();
        if ($about->isEmpty()) {
            return response()->json([
                'message' => 'About Not Found',
            ], 404);
        }

        return ResourcesAbout::collection($about);
    }

    public function store(Request $request)
    {

        if (ModelsAbout::count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'About sudah ada, tidak bisa menambah lagi',
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'headline' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string',
            'image' => 'nullable|image|mimes:wep,png,jpg|max:2048',
            'paragraf' => 'required|string',
            'image_visi' => 'nullable|image|mimes:wep,png,jpg|max:2048',
            'visi_misi' => 'required|string',

            'powers' => 'nullable|array',
            'powers.*.label' => 'required|string|max:255',
            'powers.*.icon' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->safe()->except(['powers']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('about', 'public');
        }
        if ($request->hasFile('image_visi')) {
            $data['image_visi'] = $request->file('image_visi')->store('about', 'public');
        }

        $about = ModelsAbout::create($data);

        if ($request->has('powers')) {
            foreach ($request->powers as $index => $power) {
                $iconPath = null;
                if ($request->hasFile("powers.{$index}.icon")) {
                    $iconPath = $request->file("powers.{$index}.icon")
                        ->store('about/powers', 'public');
                }

                $about->powers()->create([
                    'label' => $power['label'],
                    'icon' => $iconPath,
                    'order' => $index,
                ]);
            }
        }

        return response()->json([
            'message' => 'About berhasil dibuat',
            'data' => new ResourcesAbout($about),
        ], 201);
    }

    public function show($slug)
    {

        $about = ModelsAbout::with('powers')->where('slug', $slug)->firstOrFail();

        return response()->json([
            'data' => new ResourcesAbout($about),
        ], 200);
    }

    public function update(Request $request, ModelsAbout $about)
    {
        $validator = Validator::make($request->all(), [
            'headline' => 'sometimes|string|max:255',
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'sometimes|string',
            'image' => 'sometimes|image|mimes:wep,png,jpg|max:2048',
            'paragraf' => 'sometimes|string',
            'image_visi' => 'sometimes|image|mimes:wep,png,jpg|max:2048',
            'visi_misi' => 'sometimes|string',

            'powers' => 'sometimes|array',
            'powers.*.id' => 'sometimes|exists:powers,id',
            'powers.*.label' => 'required_with:powers|string|max:255',
            'powers.*.icon' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }
        $data = $validator->safe()->except(['powers']);

        if ($request->hasFile('image')) {
            if ($about->image && Storage::disk('public')->exists($about->image)) {
                Storage::disk('public')->delete($about->image);
            }
            $data['image'] = $request->file('image')->store('about', 'public');
        }
        if ($request->hasFile('image_visi')) {
            if ($about->image_visi && Storage::disk('public')->exists($about->image_visi)) {
                Storage::disk('public')->delete($about->image_visi);
            }
            $data['image_visi'] = $request->file('image_visi')->store('about', 'public');
        }

        $about->update($data);
        if ($request->has('powers')) {
            $incomingIds = collect($request->powers)
                ->pluck('id')
                ->filter()
                ->toArray();
            $about->powers()
                ->whereNotIn('id', $incomingIds)
                ->get()
                ->each(function ($power) {
                    if ($power->icon) {
                        Storage::disk('public')->delete($power->icon);
                    }

                    $power->delete();
                });

            foreach ($request->powers as $index => $power) {
                $iconPath = null;

                if ($request->hasFile("powers.{$index}.icon")) {

                    if (! empty($power['id'])) {
                        $old = $about->powers()->find($power['id']);
                        if ($old?->icon) {
                            Storage::disk('public')->delete($old->icon);
                        }
                    }
                    $iconPath = $request->file("powers.{$index}.icon")
                        ->store('about/powers', 'public');
                }

                if (! empty($power['id'])) {

                    $existing = $about->powers()->find($power['id']);
                    if ($existing) {
                        $existing->update([
                            'label' => $power['label'] ?? $existing->label,
                            'icon' => $iconPath ?? $existing->icon,
                            'order' => (int) $index,
                        ]);
                    }
                } else {

                    $about->powers()->create([
                        'label' => $power['label'],
                        'icon' => $iconPath,
                        'order' => (int) $index,
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'About berhasil diupadte',
            'data' => new ResourcesAbout($about),
        ], 200);
    }

    public function destroy(ModelsAbout $about)
    {
        if (! empty($about->image) && Storage::disk('public')->exists($about->image)) {
            Storage::disk('public')->delete($about->image);
        }
        if (! empty($about->image_visi) && Storage::disk('public')->exists($about->image_visi)) {
            Storage::disk('public')->delete($about->image_visi);
        }
        foreach ($about->powers as $power) {
            if (! empty($power->icon)) {
                Storage::disk('public')->delete($power->icon);
            }
        }

        $about->powers()->delete();

        $about->delete();

        return response()->json([
            'message' => 'About berhasil dihapus',
        ], 200);
    }
}
