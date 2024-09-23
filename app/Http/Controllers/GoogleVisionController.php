<?php

namespace App\Http\Controllers;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\AnnotateImageRequest;

class GoogleVisionController extends Controller
{
    public function analyzeImage()
    {
        // مسیر فایل تصویری که در پوشه public قرار دارد
        $imagePath = public_path('ax.jpg');

        // بررسی اینکه آیا فایل کلید Google Cloud JSON در مسیر وجود دارد یا نه
        $credentialsPath = storage_path('app/google-cloud-keys/google-cloud-key.json');

        if (!file_exists($credentialsPath)) {
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
        $image = (new Image())->setContent($imageData);

        // تنظیم ویژگی‌های مختلف برای تحلیل جامع تصویر
        $features = [
            (new Feature())->setType(1)->setMaxResults(10), // 1: تشخیص لیبل‌ها (LABEL_DETECTION)
            (new Feature())->setType(2)->setMaxResults(10), // 2: تشخیص چهره‌ها (FACE_DETECTION)
            (new Feature())->setType(16)->setMaxResults(10), // 16: تشخیص اشیاء (OBJECT_LOCALIZATION)
            (new Feature())->setType(3)->setMaxResults(10), // 3: تشخیص متن (TEXT_DETECTION)
            (new Feature())->setType(4)->setMaxResults(10), // 4: تشخیص لوگوها (LOGO_DETECTION)
            (new Feature())->setType(6)->setMaxResults(10), // 6: تشخیص محتوای حساس (SAFE_SEARCH_DETECTION)
            (new Feature())->setType(12)->setMaxResults(10), // 12: تحلیل خصوصیات تصویر (IMAGE_PROPERTIES)
        ];

        // ایجاد درخواست با استفاده از AnnotateImageRequest
        $request = (new AnnotateImageRequest())
            ->setImage($image)
            ->setFeatures($features);

        // ارسال درخواست به Google Vision API
        $response = $imageAnnotator->batchAnnotateImages([$request]);

        // گرفتن نتایج از پاسخ API
        $labelAnnotations = $response->getResponses()[0]->getLabelAnnotations();
        $faceAnnotations = $response->getResponses()[0]->getFaceAnnotations();
        $objectAnnotations = $response->getResponses()[0]->getLocalizedObjectAnnotations();
        $textAnnotations = $response->getResponses()[0]->getTextAnnotations();
        $logoAnnotations = $response->getResponses()[0]->getLogoAnnotations();
        $safeSearchAnnotations = $response->getResponses()[0]->getSafeSearchAnnotation();
        $imagePropertiesAnnotations = $response->getResponses()[0]->getImagePropertiesAnnotation();

        // بستن کلاینت بعد از اتمام کار
        $imageAnnotator->close();

        // ایجاد آرایه برای نتایج
        $results = [];

        // چک کردن و نمایش لیبل‌ها
        if ($labelAnnotations) {
            $labelDescriptions = [];
            foreach ($labelAnnotations as $label) {
                $labelDescriptions[] = $label->getDescription();
            }
            $results['labels'] = $labelDescriptions;
        }

        // چک کردن و نمایش چهره‌ها
        if ($faceAnnotations) {
            $results['faces'] = count($faceAnnotations) . ' faces detected';
        }

        // چک کردن و نمایش اشیاء
        if ($objectAnnotations) {
            $objectDescriptions = [];
            foreach ($objectAnnotations as $object) {
                $objectDescriptions[] = $object->getName();
            }
            $results['objects'] = $objectDescriptions;
        }

        // چک کردن و نمایش متن‌ها
        if ($textAnnotations) {
            $results['texts'] = $textAnnotations[0]->getDescription(); // نمایش اولین بلوک متنی
        }

        // چک کردن و نمایش لوگوها
        if ($logoAnnotations) {
            $logoDescriptions = [];
            foreach ($logoAnnotations as $logo) {
                $logoDescriptions[] = $logo->getDescription();
            }
            $results['logos'] = $logoDescriptions;
        }

        // چک کردن و نمایش محتوای حساس
        if ($safeSearchAnnotations) {
            $results['safe_search'] = [
                'adult' => $safeSearchAnnotations->getAdult(),
                'violence' => $safeSearchAnnotations->getViolence(),
                'racy' => $safeSearchAnnotations->getRacy(),
            ];
        }

        // چک کردن و نمایش خصوصیات تصویر
        if ($imagePropertiesAnnotations) {
            $colors = $imagePropertiesAnnotations->getDominantColors()->getColors();
            $colorInfo = [];
            foreach ($colors as $color) {
                $colorInfo[] = [
                    'color' => sprintf('rgb(%d, %d, %d)', $color->getColor()->getRed(), $color->getColor()->getGreen(), $color->getColor()->getBlue()),
                    'score' => $color->getScore(),
                ];
            }
            $results['image_properties'] = $colorInfo;
        }

        // برگرداندن نتایج به صورت JSON
        if (!empty($results)) {
            return response()->json($results, 200);
        } else {
            return response()->json([
                'message' => 'No significant labels, faces, objects, or properties found.'
            ], 200);
        }
    }
}
