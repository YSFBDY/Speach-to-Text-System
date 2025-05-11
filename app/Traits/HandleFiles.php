<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait HandleFiles
{
    public function handleFileUpload(Request $request, $fileField, $disk)
    {
        if ($request->hasFile($fileField)) {
            // Store new file
            $file = $request->file($fileField);
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('', $fileName, $disk);

            return $fileName;
        }

        return null;
    }

    public function deleteOldFile($oldFile, $disk)
    {
        if ($oldFile) {
            Storage::disk($disk)->delete(basename($oldFile));
        }
    }
}
