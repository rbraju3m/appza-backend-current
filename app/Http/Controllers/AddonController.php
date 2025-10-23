<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddonRequest;
use App\Http\Requests\VersionAddedRequest;
use App\Models\Addon;
use App\Models\AddonVersion;
use App\Models\FluentInfo;
use App\Traits\HandlesFileUploads;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AddonController extends Controller
{
    use ValidatesRequests, HandlesFileUploads;

    /**
     * Display a listing of the resource (latest active version per addon).
     */
    public function index(): Renderable
    {
        $addons = AddonVersion::query()
            ->select([
                'appza_product_addons_versions.id',
                'appza_product_addons_versions.addon_id',
                'appza_product_addons_versions.version',
                'appza_product_addons.addon_name',
                'appza_product_addons.addon_slug',
                'appza_product_addons.addon_json_info',
                'appza_fluent_informations.product_slug',
                'appza_fluent_informations.product_name',
                'appza_product_addons_versions.is_edited',
            ])
            ->join('appza_product_addons', 'appza_product_addons.id', '=', 'appza_product_addons_versions.addon_id')
            ->join('appza_fluent_informations', 'appza_fluent_informations.id', '=', 'appza_product_addons.product_id')
            ->where('appza_product_addons.is_active', 1)
            ->where('appza_product_addons_versions.is_active', 1)
            ->whereRaw('appza_product_addons_versions.id = (
                SELECT MAX(av.id)
                FROM appza_product_addons_versions av
                WHERE av.addon_id = appza_product_addons.id
                  AND av.is_active = 1
            )')
            ->orderByDesc('appza_product_addons_versions.id')
            ->paginate(20);

        return view('addon-version.index', compact('addons'));
    }

    /**
     * Show the form for creating a new addon.
     */
    public function create(): Renderable
    {
        $products = FluentInfo::pluck('product_name', 'id')->all();
        return view('addon-version.add', compact('products'));
    }

    /**
     * Store a new addon and its first version.
     */
    public function store(AddonRequest $request): RedirectResponse
    {
        $inputs = $request->validated();

        try {
            DB::beginTransaction();

            // Handle ZIP Upload (preserve original name)
            $inputs['addon_file'] = $this->handleFileUploadWithOriginalName(
                $request,
                null,
                'addon_file',
                'addons',
                'r2'
            );

            // Build addon JSON info
            $jsonData = json_decode($inputs['addon_json_info'], true) ?? [];
            $jsonData['version']      = $inputs['version'];
            $jsonData['download_url'] = config('app.image_public_path') . $inputs['addon_file'];

            $inputs['addon_json_info'] = json_encode($jsonData);

            // Create addon
            $addon = Addon::create($inputs);

            // Create addon version
            AddonVersion::create([
                'addon_id' => $addon->id,
                'version'  => $inputs['version'],
                'addon_file'  => $inputs['addon_file'],
                'is_edited'  => true,
                'version_json_info'  => json_encode($jsonData),
            ]);

            DB::commit();

            return redirect()->route('addon_version_list')->with('message', 'Addon created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating addon: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('addon_version_list')
                ->with('validate', 'Failed to create addon. Please try again.');
        }
    }

    /**
     * Show all versions of a given addon.
     */
    public function addedVersion($addonId): Renderable
    {
        $versions = AddonVersion::query()
            ->select([
                'appza_product_addons_versions.id',
                'appza_product_addons_versions.addon_id',
                'appza_product_addons_versions.version',
                'appza_product_addons_versions.addon_file',
                'appza_product_addons_versions.is_edited',
                'appza_product_addons_versions.created_at',
                'appza_product_addons.addon_name',
                'appza_product_addons.addon_slug',
                'appza_fluent_informations.product_slug',
                'appza_fluent_informations.product_name',
            ])
            ->join('appza_product_addons', 'appza_product_addons.id', '=', 'appza_product_addons_versions.addon_id')
            ->join('appza_fluent_informations', 'appza_fluent_informations.id', '=', 'appza_product_addons.product_id')
            ->where('appza_product_addons.is_active', 1)
            ->where('appza_product_addons_versions.is_active', 1)
            ->where('appza_product_addons_versions.addon_id', $addonId)
            ->orderByDesc('appza_product_addons_versions.id')
            ->paginate(20);

        return view('addon-version.added-version', compact('versions', 'addonId'));
    }

    /**
     * Store a new version for an existing addon.
     */
    public function addedVersionStore(VersionAddedRequest $request, $addonId): RedirectResponse
    {
        $inputs = $request->validated();
        $addon  = Addon::findOrFail($addonId);

        $latestVersion = optional(
            AddonVersion::where('addon_id', $addonId)->latest()->first()
        )->version;

        if ($latestVersion && version_compare($inputs['version'], $latestVersion, '<=')) {
            return back()->with('validate', "Version must be greater than {$latestVersion}");
        }

        try {
            DB::beginTransaction();

            // Handle ZIP Upload
            $addonFile = $this->handleFileUploadWithOriginalName(
                $request,
                null,
                'addon_file',
                'addons',
                'r2'
            );

            // Decode addon JSON
            $jsonData = json_decode($addon->addon_json_info, true) ?? [];

            // Update fields
            $jsonData['version']      = $inputs['version'];
            $jsonData['download_url'] = config('app.image_public_path') . $addonFile;

            // Update Addon JSON info
            $addon->update([
                'addon_json_info' => json_encode($jsonData),
            ]);

            // Create new version record
            $version = AddonVersion::create([
                'addon_id'          => $addon->id,
                'version'           => $inputs['version'],
                'is_edited'         => true,
                'addon_file'        => $addonFile,
                'version_json_info' => json_encode($jsonData),
            ]);

            // Reset all others to false
            AddonVersion::where('id', '<>', $version->id)
                ->where('addon_id', $addon->id)
                ->update(['is_edited' => false]);

            DB::commit();

            return redirect()
                ->route('addon_version_added', $addonId)
                ->with('message', 'New version upload successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating addon version: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('addon_version_added', $addonId)
                ->with('validate', 'Failed to add new version. Please try again.');
        }
    }

    public function addedVersionUpdate(VersionAddedRequest $request, $addonId): RedirectResponse
    {
        $inputs = $request->validated();
        $addonVersion = AddonVersion::findOrFail($addonId);
        $latestVersion = optional(
            AddonVersion::where('addon_id', $addonId)->latest()->first()
        )->version;

        if ($latestVersion && version_compare($inputs['version'], $latestVersion, '=')) {
            return back()->with('validate', "Version must be different from {$latestVersion}");
        }

        DB::beginTransaction();

        try {
            // Handle ZIP Upload
            $updateDownloadUrl = $this->handleFileUploadWithOriginalName(
                $request,
                null,
                'addon_file',
                'addons',
                'r2'
            );

            // Decode addon JSON
            $jsonData = json_decode($addonVersion->version_json_info, true) ?? [];

            $jsonData['download_url'] = config('app.image_public_path') . $updateDownloadUrl;

            // Update AddonVersion JSON info
            $addonVersion->update([
                'addon_file' => $updateDownloadUrl,
                'version_json_info' => json_encode($jsonData),
            ]);

            // Update main Addon JSON info
            $findAddon = Addon::findOrFail($addonVersion->addon_id);
            $findAddon->update(['addon_json_info' => json_encode($jsonData)]);

            DB::commit();

            return redirect()
                ->back()
                ->with('message', 'Upload successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error creating addon version: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('addon_version_added', $addonId)
                ->with('validate', 'Failed to add new version. Please try again.');
        }
    }
}
