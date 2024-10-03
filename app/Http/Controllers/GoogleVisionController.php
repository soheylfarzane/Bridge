<?php

namespace App\Http\Controllers;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class GoogleVisionController extends Controller
{
//    public function analyzeImage(Request $request)
//    {
//        // بررسی اینکه آیا فایل ارسال شده است یا نه
//        if (!$request->hasFile('image')) {
//            return response()->json([
//                'message' => 'No image file found'
//            ], 400);
//        }
//
//        // بررسی اینکه فایل تصویر است یا نه
//        $file = $request->file('image');
//        if (!$file->isValid() || !$file->isFile()) {
//            return response()->json([
//                'message' => 'Invalid image file'
//            ], 400);
//        }
//
//        // تولید یک نام فایل رندوم برای ذخیره
//        $randomFileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
//        $imagePath = public_path('images/' . $randomFileName);
//
//        // ذخیره تصویر در پوشه public/images
//        $file->move(public_path('images'), $randomFileName);
//
//        // بررسی اینکه آیا فایل کلید Google Cloud JSON در مسیر وجود دارد یا نه
//        $credentialsPath = storage_path('app/google-cloud-keys/google-cloud-key.json');
//
//        if (!file_exists($credentialsPath)) {
//            // حذف فایل تصویر قبل از برگرداندن پاسخ
//            unlink($imagePath);
//
//            return response()->json([
//                'message' => 'Google Cloud JSON credentials not found. Please upload the file first.'
//            ], 404);
//        }
//
//        // ایجاد کلاینت برای اتصال به Vision API با استفاده از فایل JSON ذخیره شده
//        $imageAnnotator = new ImageAnnotatorClient([
//            'credentials' => $credentialsPath,
//        ]);
//
//        // خواندن محتوای تصویر
//        $imageData = file_get_contents($imagePath);
//
//        // ارسال درخواست به Google Vision API برای شناسایی لیبل‌ها
//        $response = $imageAnnotator->labelDetection($imageData);
//        $labels = $response->getLabelAnnotations();
//
//        // بستن کلاینت بعد از اتمام کار
//        $imageAnnotator->close();
//
//        // حذف تصویر از سرور بعد از پردازش
//        unlink($imagePath);
//
//        // چک کردن و نمایش نتایج
//        if ($labels) {
//            $labelDescriptions = [];
//            foreach ($labels as $label) {
//                $labelDescriptions[] = $label->getDescription();
//            }
//
//            // برگرداندن نتایج به صورت JSON
//            return response()->json([
//                'labels' => $labelDescriptions
//            ], 200);
//        } else {
//            return response()->json([
//                'message' => 'No labels found'
//            ], 200);
//        }
//    }

    public function analyzeImage(Request $request)
    {
        // بررسی اینکه آیا فایل ارسال شده است یا نه
        if (!$request->hasFile('image')) {
            return response()->json([
                'message' => 'No image file found'
            ], 400);
        }

        // بررسی اینکه فایل تصویر است یا نه
        $file = $request->file('image');
        if (!$file->isValid() || !$file->isFile()) {
            return response()->json([
                'message' => 'Invalid image file'
            ], 400);
        }

        // تولید یک نام فایل رندوم برای ذخیره
        $randomFileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $imagePath = public_path('images/' . $randomFileName);

        // ذخیره تصویر در پوشه public/images
        $file->move(public_path('images'), $randomFileName);

        // بررسی اینکه آیا فایل کلید Google Cloud JSON در مسیر وجود دارد یا نه
        $credentialsPath = storage_path('app/google-cloud-keys/google-cloud-key.json');

        if (!file_exists($credentialsPath)) {
            // حذف فایل تصویر قبل از برگرداندن پاسخ
            unlink($imagePath);

            return response()->json([
                'message' => 'Google Cloud JSON credentials not found. Please upload the file first.'
            ], 404);
        }

        // ایجاد کلاینت برای اتصال به Vision API با استفاده از فایل JSON ذخیره شده
        $imageAnnotator = new ImageAnnotatorClient([
            'credentials' => $credentialsPath,
        ]);

        // خواندن محتوای تصویر
        $imageData = file_get_contents($imagePath);

        // ارسال درخواست به Google Vision API برای شناسایی لیبل‌ها
        $labelResponse = $imageAnnotator->labelDetection($imageData);
        $labels = $labelResponse->getLabelAnnotations();

        // ارسال درخواست به Google Vision API برای شناسایی متون
        $textResponse = $imageAnnotator->textDetection($imageData);
        $texts = $textResponse->getTextAnnotations();

        // بستن کلاینت بعد از اتمام کار
        $imageAnnotator->close();

        // حذف تصویر از سرور بعد از پردازش
        unlink($imagePath);

        // آماده‌سازی پاسخ JSON برای لیبل‌ها
        $labelDescriptions = [];
        if ($labels) {
            foreach ($labels as $label) {
                $labelDescriptions[] = $label->getDescription();
            }
        }

        // آماده‌سازی پاسخ JSON برای متون
        $textDescriptions = [];
        if ($texts) {
            foreach ($texts as $text) {
                $textDescriptions[] = $text->getDescription();
            }
        }

        // برگرداندن نتایج به صورت JSON
        return response()->json([
            'labels' => $labelDescriptions,
            'texts' => $textDescriptions
        ], 200);
    }


}
