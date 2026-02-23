<?php

namespace Modules\Core\Traits;

use Illuminate\Http\Request;
use Exception;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Modules\Core\Models\MediaStore;
use Modules\Core\Models\MediaStoreImageSize;

trait MediaTrait
{
    private $correctedMimeTypes = [
        'image/svg' => 'image/svg+xml'
    ];

    /**
     * Upload a file
     * @param Request $request
     * @param array $file
     * @param array $entity_data
     * @param array $options
     * 
     * @throws \Exception
     */
    public function uploadFile(Request $request, array $file, array $entity_data, ?array $options): ?MediaStore
    {
        try {
            if (array_key_exists('multiple', $options) && $options['multiple']) {
                $files = $request->file($file['field_name']);
                $image = $files[$file['index']];
            } else {
                $image = $request->file($file['field_name']);
            }

            $ext = $image->getClientOriginalExtension();

            $mediaStore = MediaStore::firstOrNew([
                'entity_name' => $entity_data['name'],
                'entity_id' => (string)$entity_data['id']
            ]);
            $mediaStore->mime_type = $this->getCorrectedMimeType($image->getMimeType());
            $mediaStore->file_name = $file['file_name'] . '.' . $ext;
            $mediaStore->file_size = $image->getMaxFilesize();
            $mediaStore->file_extension = $ext;
            $mediaStore->entity_name = $entity_data['name'];
            $mediaStore->entity_id = $entity_data['id'];
            $mediaStore->content = file_get_contents($image->getRealPath());
            $mediaStore->save();

            $destinationPath = public_path('/tmp-media');
            if (!is_dir($destinationPath)) mkdir($destinationPath, 0777, true);

            if ($ext !== 'svg') {
                $manager = new ImageManager(new Driver());

                $img = $manager->read($image->getRealPath());
                $img->scale(48)->save($destinationPath . '/ms-' . $mediaStore->id);

                $mediaStoreImageThumb = MediaStoreImageSize::firstOrNew([
                    'media_store_id' => $mediaStore->id,
                    'width' => '48',
                    'height' => '48',
                ]);

                $mediaStoreImageThumb->content = file_get_contents($destinationPath . '/ms-' . $mediaStore->id);
                $mediaStoreImageThumb->save();

                unlink($destinationPath . '/ms-' . $mediaStore->id);
            }

            return $mediaStore;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function doUploadClientModuleMedia(Request $request, array $entityData, array $options = []): array
    {
        $result = [];

        try {
            $thumbnailExtensions = config('client.images.thumbnail_extensions');

            foreach ($request->file('media_files') as $uploadedFile) {
                $filename = $uploadedFile->getClientOriginalName();

                $ext = $uploadedFile->getClientOriginalExtension();
                $tempToken = $options['temp_token'] ?? null;

                $mediaStore = MediaStore::firstOrNew([
                    'entity_name' => $entityData['name'],
                    'entity_id' => (string)$entityData['id'] ?? null,
                    'file_name' => $filename,
                    'temp_token' => $tempToken,
                ]);
                $mediaStore->mime_type = $this->getCorrectedMimeType($uploadedFile->getMimeType());
                $mediaStore->file_size = $uploadedFile->getMaxFilesize();
                $mediaStore->file_extension = $ext;
                $mediaStore->content = file_get_contents($uploadedFile->getRealPath());
                $mediaStore->temp_token = $tempToken;
                $mediaStore->save();

                $destinationPath = public_path('/tmp-media');
                if (!is_dir($destinationPath)) mkdir($destinationPath, 0777, true);

                if (in_array(strtolower($ext), config('client.images.thumbnail_extensions'))) {
                    $manager = new ImageManager(new Driver());

                    $img = $manager->read($uploadedFile->getRealPath());
                    $img->scale(48)->save($destinationPath . '/ms-' . $mediaStore->id);

                    $mediaStoreImageThumb = MediaStoreImageSize::firstOrNew([
                        'media_store_id' => $mediaStore->id,
                        'width' => '48',
                        'height' => '48',
                    ]);

                    $mediaStoreImageThumb->content = file_get_contents($destinationPath . '/ms-' . $mediaStore->id);
                    $mediaStoreImageThumb->save();

                    unlink($destinationPath . '/ms-' . $mediaStore->id);
                }

                $_file = [
                    'id' => $mediaStore->id,
                    'file_name' => $mediaStore->file_name,
                    'file_extension' => $mediaStore->file_extension,
                    'url' => route('media.image.download', ['id' => $mediaStore->id]),
                ];

                if (in_array($mediaStore->file_extension, $thumbnailExtensions)) {
                    $_file['preview_url'] = route('media.image.download', ['id' => $mediaStore->id, 'resize_width' => 48]);
                }

                array_push($result, $_file);
            }
        } catch (Exception $ex) {
            throw $ex;
        }

        return $result;
    }

    public function getPlaceholderImageSrc($type = 'general')
    {
        switch ($type) {
            case 'profile':
                return '/images/profile.png';
            case 'general':
            default:
                return '/images/image-placeholder.png';
        }
    }

    private function getCorrectedMimeType($mime_type)
    {
        return in_array($mime_type, array_keys($this->correctedMimeTypes)) ? str_replace(array_keys($this->correctedMimeTypes), array_values($this->correctedMimeTypes), $mime_type) : $mime_type;
    }
}
