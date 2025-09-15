<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesFileUploads
{
    /**
     * Handle file upload and storage for a given model and attribute.
     *
     * @param Request $request
     * @param mixed $model
     * @param string $attribute
     * @param string $directory
     * @param string|null $disk
     * @return string|null
     */
    public function handleFileUpload(Request $request, $model, $attribute, $directory, ?string $disk = 'r2'): ?string
    {
        if ($request->file($attribute)) {
            // Delete old file if it exists
            if (!empty($model->$attribute)) {
                Storage::disk($disk)->delete($model->$attribute);
            }

            // Store the new file
            return $request->file($attribute)->store($directory, $disk);
        }

        // If no upload, return the existing file path
        return $model->$attribute;
    }

    public function handleFileUploadWithOriginalName(Request $request, $model, $attribute, $directory = 'addons', ?string $disk = 'r2'): ?string
    {
        if ($request->hasFile($attribute)) {
            $file = $request->file($attribute);

            // Delete old file if it exists
            if (!empty($model->$attribute)) {
                Storage::disk($disk)->delete($model->$attribute);
            }

            $originalName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $extension    = $file->getClientOriginalExtension();
            $filename     = $originalName . '.' . $extension;

            return $file->storeAs($directory, $filename, $disk); // stored path
        }

        // No upload, keep old file
        return $model->$attribute;
    }

}
