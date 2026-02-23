<?php

namespace Modules\Core\Services;

use Exception;
use Modules\Core\Models\Language;
use Modules\Core\Models\Navigation;

class NavigationService
{
    private ?Language $requestedLang = null;

    public function __construct(
        private readonly TranslationService $translationService
    ) {}

    public function getNavRecords(?Language $requestedLang = null, bool $mandatory = false): array
    {
        $this->requestedLang = $requestedLang;

        $items = Navigation::all()->toArray();

        if ($mandatory && count($items) <= 0) {
            throw new Exception("Navigation has not neen set!", 400);
        }

        return $this->formatNavRecords($items);
    }

    private function formatNavRecords(array $navItemRecords): array
    {
        $formattedNavRecords = [];

        foreach ($navItemRecords as $value) {
            $navData = $this->formatNavItems(json_decode($value['value'], true) ?? []);

            $formattedNavRecords[$value['code']] = [
                'code' => $value['code'],
                'name' => $value['name'],
                'items' => $navData['items'] ?? []
            ];
        }

        return $formattedNavRecords;
    }

    public function formatNavItems(array $menuItems): array
    {
        foreach ($menuItems as $key => $value) {
            $menuItems[$key] = [
                'key' => (string)$value['key'],
                'label' => (string)$value['label'],
                'pathId' => isset($value['pathId']) ? (int)$value['pathId'] : null,
                'nodeStyle' => isset($value['nodeStyle']) ? (string)$value['nodeStyle'] : 'link',
            ];

            if (empty($this->requestedLang)) {
                $menuItems[$key]['translations'] = getCodesTranslations([(string)$value['label']]);
            } else {
                $menuItems[$key]['label'] = getLanguageTranslation($menuItems[$key]['label'], $this->requestedLang->id);
            }

            if (isset($value['children'])) {
                unset($menuItems[$key]['path']);
                $navData = $this->formatNavItems($value['children']);
                $menuItems[$key]['children'] = $navData['items'];
            } elseif (isset($value['path'])) {
                $menuItems[$key]['path'] = (string)$value['path'];
                $menuItems[$key]['pathLocation'] = isset($value['pathLocation']) ? (string)$value['pathLocation'] : 'internal';
            } else {
                $menuItems[$key]['path'] = '/';
                $menuItems[$key]['pathLocation'] = 'internal';
            }
        }

        $result = [
            'items' => $menuItems,
        ];

        return  $result;
    }

    public function registerTranslations(array $navItems): array
    {
        $finalItems = $navItems;

        foreach ($finalItems as $k => $item) {
            if (isset($item['translations'])) {
                $translations = $item['translations'];

                $this->translationService->translateFieldsByLocale($translations);

                unset($item['translations']);
            }

            $finalItems[$k] = $item;

            if (isset($item['children'])) {
                $finalItems[$k]['children'] = $this->registerTranslations($item['children']);
            }
        }

        return $finalItems;
    }
}
