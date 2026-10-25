<?php
namespace Fertem\IblockSeo;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

final class SeoManager
{
    private const CACHE_TTL = 3600;

    public static function apply(): void
    {
        global $APPLICATION;
        if (defined('ADMIN_SECTION') && ADMIN_SECTION === true) { return; }
        if (!Loader::includeModule('iblock')) { return; }
        $iblocks = array_values(array_filter(array_map('intval', explode(',', Option::get('fertem.iblockseo', 'iblocks', '')))));
        if (!$iblocks) { return; }
        $request = Application::getInstance()->getContext()->getRequest();
        $path = self::normalizePath($request->getRequestedPage());
        $cache = Cache::createInstance(); $cacheId = md5($path . '|' . implode(',', $iblocks)); $rule = null;
        if ($cache->initCache(self::CACHE_TTL, $cacheId, '/fertem/iblockseo')) { $rule = $cache->getVars(); }
        elseif ($cache->startDataCache()) { $rule = self::findRule($path, $iblocks); $cache->endDataCache($rule); }
        if (!$rule) { return; }
        if ($rule['SEO_TITLE'] !== '') { $APPLICATION->SetPageProperty('title', $rule['SEO_TITLE']); }
        if ($rule['SEO_H1'] !== '') { $APPLICATION->SetTitle($rule['SEO_H1']); }
        if ($rule['SEO_DESCRIPTION'] !== '') { $APPLICATION->SetPageProperty('description', $rule['SEO_DESCRIPTION']); }
        if ($rule['SEO_KEYWORDS'] !== '') { $APPLICATION->SetPageProperty('keywords', $rule['SEO_KEYWORDS']); }
        if ($rule['SEO_ROBOTS'] !== '') { $APPLICATION->SetPageProperty('robots', $rule['SEO_ROBOTS']); }
        if ($rule['SEO_CANONICAL'] !== '') { Asset::getInstance()->addString('<link rel="canonical" href="' . htmlspecialcharsbx($rule['SEO_CANONICAL']) . '">', true); }
    }

    private static function findRule(string $path, array $iblocks): ?array
    {
        $result = \CIBlockElement::GetList(['ID' => 'DESC'], ['IBLOCK_ID' => $iblocks, 'ACTIVE' => 'Y', 'PROPERTY_SEO_URL' => [$path, $path . '/']], false, ['nTopCount' => 1], ['ID', 'PROPERTY_SEO_TITLE', 'PROPERTY_SEO_H1', 'PROPERTY_SEO_DESCRIPTION', 'PROPERTY_SEO_KEYWORDS', 'PROPERTY_SEO_CANONICAL', 'PROPERTY_SEO_ROBOTS']);
        if (!$row = $result->Fetch()) { return null; }
        $rule = [];
        foreach (['SEO_TITLE','SEO_H1','SEO_DESCRIPTION','SEO_KEYWORDS','SEO_CANONICAL','SEO_ROBOTS'] as $key) { $rule[$key] = trim((string)($row['PROPERTY_' . $key . '_VALUE'] ?? '')); }
        return $rule;
    }

    private static function normalizePath(string $path): string
    {
        $path = '/' . ltrim(rawurldecode(parse_url($path, PHP_URL_PATH) ?: '/'), '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public static function clearCache(): void
    {
        Cache::clearCache(true, '/fertem/iblockseo');
    }
}

