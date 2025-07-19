<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\CmsManageRequest;

class FeatureController extends Controller
{
    /**
     * show feature page hero section
     */
    public function showFeatureHeroSection()
    {
        $data = CMS::where('page', 'feature-page')->where('section', 'hero')->first();
        return view("backend.layouts.cms.feature-page.hero", compact("data"));
    }

    /**
     * update feature hero section
     **/
    public function updateFeatureHeroSection(CmsManageRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'feature-page')
                ->where('section', 'hero')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'feature-page',
                    'section' => 'hero',
                ],
                $validated_data
            );

            return back()->with('t-success', 'Feature content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * show feature item hero section
     */
    public function showFeatureItemSection(Request $request)
    {
        if ($request->ajax()) {
            $feature_items = CMS::where('page', 'feature-page')->where('section', 'card')->get();

            return DataTables::of($feature_items)
                ->addIndexColumn()
                ->addColumn('page', fn($item) => ucfirst(str_replace('-', ' ', $item->page)))
                ->addColumn('section', fn($item) => ucfirst($item->section))
                ->addColumn('title', fn($item) => $item->title)
                ->addColumn('description', function ($item) {
                    return strlen($item->description) > 100 ? substr($item->description, 0, 100) . '...' : $item->description;
                })
                ->addColumn('image', function ($item) {
                    $image = $item->image && file_exists(public_path($item->image))
                        ? asset($item->image)
                        : asset('default/placeholder-image.avif');
                    return '<img src="' . $image . '" alt="Image" width="60">';
                })
                ->addColumn('status', function ($item) {
                    $checked = $item->status == 'active' ? 'checked' : '';

                    return '<div style="display: flex; justify-content: center; align-items: center;">
                                <input onclick="showStatusChangeAlert(' . $item->id . ')"
                                    type="checkbox"
                                    class="form-check-input status-toggle"
                                    style="cursor: pointer; width: 40px; height: 20px;"
                                    ' . $checked . '>
                            </div>';
                })

                ->addColumn('action', function ($item) {
                    return '<div class="d-flex justify-content-start align-items-center gap-1">
                    <button type="button" onclick="goToEdit(' . $item->id . ')" class="btn btn-primary btn-sm">
                        <i class="fe fe-edit"></i>
                    </button>
                    <button type="button" onclick="showDeleteConfirm(' . $item->id . ')" class="btn btn-danger btn-sm">
                        <i class="fe fe-trash"></i>
                    </button>
                </div>';
                })
                ->rawColumns(['description', 'image', 'status', 'action'])
                ->make();
        }

        $item = CMS::where('page', 'feature-page')->where('section', 'text')->first();

        return view('backend.layouts.cms.feature-page.feature-item', compact('item'));
    }

    /**
     * update feature hero section
     **/
    public function updateFeatureItemHeroSection(CmsManageRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            CMS::where('page', 'feature-page')
                ->where('section', 'text')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'feature-page',
                    'section' => 'text',
                ],
                $validated_data
            );

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * store feature item
     **/
    public function storeFeatureItem(CmsManageRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'feature-page')
                ->where('section', 'card')
                ->first();

            // handle image
            if ($request->hasFile('image')) {
                $image_path = Helper::uploadImage($request->file('image'), 'cms/feature-item');
                $validated_data['image'] = $image_path;
            }

            CMS::updateOrCreate(
                [
                    'page' => 'feature-page',
                    'section' => 'card',
                ] +
                    $validated_data
            );

            return response()->json([
                'success' => true,
                'message' => 'Item added successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating item!',
            ], 200);
        }
    }

    /**
     * edit feature item
     **/
    public function editFeatureItem(int $id)
    {
        $data = CMS::find($id);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Establishment not found.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status Changed successfully!',
        ]);
    }


    /**
     * update feature item status
     **/
    public function updateItemStatus(int $id)
    {
        $data = CMS::find($id);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found.',
            ]);
        }
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Status Changed successfully!',
        ]);
    }


    /**
     * delete item
     **/
    public function deleteFeatureItem($id)
    {
        $item = CMS::find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        // delete image
        if ($item && $item->image) {
            Helper::deleteImage($item->image);
        }

        $item->delete();

        return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
    }
}
