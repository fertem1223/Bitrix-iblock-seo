<?php
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Fertem\IblockSeo\PropertyInstaller;

$moduleId = 'fertem.iblockseo';
if (!$USER->IsAdmin() || !Loader::includeModule($moduleId) || !Loader::includeModule('iblock')) { return; }
$savedIds = array_values(array_filter(array_map('intval', explode(',', Option::get($moduleId, 'iblocks', '')))));
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $savedIds = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['iblocks'] ?? [])))));
    Option::set($moduleId, 'iblocks', implode(',', $savedIds));
    $errors = PropertyInstaller::install($savedIds);
    if (!$errors) { LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($moduleId) . '&lang=' . LANGUAGE_ID . '&saved=Y'); }
}
$iblocks = [];
$result = CIBlock::GetList(['IBLOCK_TYPE_ID' => 'ASC', 'SORT' => 'ASC', 'NAME' => 'ASC'], ['ACTIVE' => 'Y']);
while ($row = $result->Fetch()) { $iblocks[] = $row; }
?>
<?php if (($_GET['saved'] ?? '') === 'Y'): ?><div class="adm-info-message-wrap adm-info-message-green"><div class="adm-info-message">Настройки сохранены, SEO-свойства созданы.</div></div><?php endif ?>
<?php if ($errors): ?><div class="adm-info-message-wrap adm-info-message-red"><div class="adm-info-message"><?= implode('<br>', array_map('htmlspecialcharsbx', $errors)) ?></div></div><?php endif ?>
<form method="post"><?= bitrix_sessid_post() ?>
<table class="adm-detail-content-table edit-table"><tbody>
<tr><td width="40%" class="adm-detail-content-cell-l">Инфоблоки с SEO-правилами</td><td class="adm-detail-content-cell-r">
<select name="iblocks[]" multiple size="12" style="min-width:420px">
<?php foreach ($iblocks as $iblock): ?><option value="<?= (int)$iblock['ID'] ?>" <?= in_array((int)$iblock['ID'], $savedIds, true) ? 'selected' : '' ?>>[<?= htmlspecialcharsbx($iblock['IBLOCK_TYPE_ID']) ?>] <?= htmlspecialcharsbx($iblock['NAME']) ?> (ID <?= (int)$iblock['ID'] ?>)</option><?php endforeach ?>
</select><p>При сохранении недостающие свойства будут добавлены автоматически. Существующие свойства и данные не удаляются.</p>
</td></tr></tbody></table>
<input class="adm-btn-save" type="submit" value="Сохранить и создать SEO-поля">
</form>

