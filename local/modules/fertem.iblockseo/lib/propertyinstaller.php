<?php
namespace Fertem\IblockSeo;

use Bitrix\Main\Loader;

final class PropertyInstaller
{
    public const PROPERTIES = [
        'SEO_URL' => ['NAME' => 'SEO: URL страницы', 'MULTIPLE' => 'Y', 'SORT' => 100],
        'SEO_TITLE' => ['NAME' => 'SEO: Title', 'MULTIPLE' => 'N', 'SORT' => 110],
        'SEO_H1' => ['NAME' => 'SEO: H1', 'MULTIPLE' => 'N', 'SORT' => 120],
        'SEO_DESCRIPTION' => ['NAME' => 'SEO: Description', 'MULTIPLE' => 'N', 'SORT' => 130, 'ROW_COUNT' => 3],
        'SEO_KEYWORDS' => ['NAME' => 'SEO: Keywords', 'MULTIPLE' => 'N', 'SORT' => 140],
        'SEO_CANONICAL' => ['NAME' => 'SEO: Canonical URL', 'MULTIPLE' => 'N', 'SORT' => 150],
        'SEO_ROBOTS' => ['NAME' => 'SEO: Robots', 'MULTIPLE' => 'N', 'SORT' => 160],
    ];

    public static function install(array $iblockIds): array
    {
        if (!Loader::includeModule('iblock')) { return ['Модуль инфоблоков недоступен.']; }
        $errors = [];
        foreach ($iblockIds as $iblockId) {
            $iblockId = (int)$iblockId; if ($iblockId <= 0) { continue; }
            foreach (self::PROPERTIES as $code => $definition) {
                if (\CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, '=CODE' => $code])->Fetch()) { continue; }
                $property = new \CIBlockProperty();
                $id = $property->Add(array_merge(['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'PROPERTY_TYPE' => 'S', 'CODE' => $code, 'FILTRABLE' => $code === 'SEO_URL' ? 'Y' : 'N', 'SEARCHABLE' => 'N'], $definition));
                if (!$id) { $errors[] = 'Инфоблок ' . $iblockId . ', ' . $code . ': ' . $property->LAST_ERROR; }
            }
        }
        return $errors;
    }
}

