<?php

namespace Modules\Client\Services;

use Modules\Client\Models\ClientIdentityTheme;
use Modules\Core\Models\Colour;
use Modules\Core\Models\MediaStore;

class ClientIdentityThemeService
{
    public function getClientIdentityDefaultTheme(): array
    {
        $clientIdentity = ClientIdentityTheme::where(['default' => true])->first();

        if (!$clientIdentity) {
            return [
                'colors' => [
                    'primary' => [
                        'red' => 148,
                        'green' => 148,
                        'blue' => 148,
                        'hex' => '#949494',
                    ],
                    'secondary' => [
                        'red' => 0,
                        'green' => 0,
                        'blue' => 0,
                        'hex' => '#000000',
                    ],
                ],
                'logos' => [
                    'logo_coloured_light' => null,
                    'logo_coloured_dark' => null,
                ]
            ];
        }

        $primaryColour = Colour::select('name', 'red', 'green', 'blue', 'hex')->where('id', $clientIdentity->primary_colour_id)->first();
        $secondaryColour = Colour::select('name', 'red', 'green', 'blue', 'hex')->where('id', $clientIdentity->secondary_colour_id)->first();

        $logoColouredLightImg = MediaStore::find($clientIdentity->logo_coloured_light_id);
        $logoColouredDarkImg = MediaStore::find($clientIdentity->logo_coloured_dark_id);

        return [
            'colors' => [
                'primary' => $primaryColour ? $primaryColour->toArray() : null,
                'secondary' => $secondaryColour ? $secondaryColour->toArray() : null,
            ],
            'logos' => [
                'logo_coloured_light' => $logoColouredLightImg ? route('media.image.download', ['id' => $logoColouredLightImg->id]) : null,
                'logo_coloured_dark' => $logoColouredDarkImg ? route('media.image.download', ['id' => $logoColouredDarkImg->id]) : null,
            ]
        ];
    }

    public function getColourIdFromHex(string $hex, string $name = null): int
    {
        list($red, $green, $blue) = sscanf($hex, "#%02x%02x%02x");

        $colour = Colour::updateOrCreate([
            'red' => $red,
            'green' => $green,
            'blue' => $blue,
            'hex' => $hex
        ], [
            'name' => $name ?? 'No Name',
        ]);

        return $colour->id;
    }
}
