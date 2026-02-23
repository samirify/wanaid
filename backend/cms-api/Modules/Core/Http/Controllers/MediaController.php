<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Routing\Controller;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Modules\Core\Models\MediaStore;
use Modules\Core\Traits\MediaTrait;

class MediaController extends Controller
{
    use SAAApiResponse, MediaTrait, AppHelperTrait;

    /**
     * Show the specified resource.
     * @param int $id
     * @param int|null $resize_width
     * @return Response
     */
    public function show(Request $request, $id, $resize_width = null)
    {
        $file = MediaStore::findOrFail($id);

        $resizeWidth = $resize_width ?? $request->get('width', null);
        $quality = (int)$request->get('quality', 90);
        $webpFormat = strtolower($request->get('webp', 'y'));
        $mimeType = $file->mime_type;

        if (in_array($file->file_extension, config('client.images.thumbnail_extensions'))) {
            $manager = new ImageManager(new Driver());

            $image = $manager->read($file->content);
            if (!is_null($resizeWidth) && !empty($resizeWidth)) {
                $image->scale($resizeWidth);
            }

            if ($webpFormat === 'y') {
                $imageContent = $image->toWebp(90);
                $mimeType = 'image/webp';
            } else {
                switch (strtolower($file->file_extension)) {
                    case 'png':
                        $imageContent = $image->toPng($quality);
                        break;
                    case 'jpg':
                        $imageContent = $image->toJpeg($quality);
                        break;
                    case 'jpeg':
                        $imageContent = $image->toJpeg($quality);
                        break;
                    case 'bmp':
                        $imageContent = $image->toBitmap($quality);
                        break;
                    case 'webp':
                        $imageContent = $image->toWebp(90);
                        break;
                }
            }

            $response = (new Response())->setContent($imageContent);
        } else {
            $response = FacadeResponse::make($file->content, 200);
        }

        $response
            ->header('Content-Type', $mimeType)
            // ->header('Cache-Control', 'max-age=2592000, public')
            ->header('Content-Disposition', 'inline')
            ->header('Content-Transfer-Encoding', 'binary')
            ->header('Content-Encoding', 'compress')
            ->header('ETag', md5($file->content));

        return $response;
    }
}
