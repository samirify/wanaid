<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Modules\Core\Services\LanguageService;
use Modules\Core\Services\TranslationService;
use Modules\Core\Traits\MediaTrait;

class TranslationController extends Controller
{
    use MediaTrait;
    use AppHelperTrait;
    use SAAApiResponse;

    private $resetDBAllowedHosts;

    public function __construct(
        private readonly TranslationService $translationService,
        private readonly LanguageService $languageService,
    ) {
        $this->resetDBAllowedHosts = config('client.admin_panel.reset_db.allowed_hosts');
    }

    public function translateLang($lang)
    {
        $languageTranslations = $this->translationService->getTranslations($lang);
        return response()->json($languageTranslations['translations']);
    }
}
