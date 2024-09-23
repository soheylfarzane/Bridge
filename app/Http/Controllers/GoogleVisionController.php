<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Handle the file upload.
     */
    public function uploadJson(Request $request)
    {
        // اعتبارسنجی فایل ورودی
        $request->validate([
            'json_file' => 'required|file|mimes:json|max:2048',
        ]);

        // دریافت فایل و ذخیره آن در پوشه storage/app
        $file = $request->file('json_file');
        $path = $file->storeAs('google-cloud-keys', 'google-cloud-key.json');

        // بررسی موفقیت‌آمیز بودن آپلود
        if ($path) {
            return response()->json([
                'message' => 'File uploaded successfully',
                'path' => $path,
            ], 200);
        }

        return response()->json([
            'message' => 'File upload failed'
        ], 500);
    }
}
