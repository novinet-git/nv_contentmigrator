<?
$oMigrator = new nvContentMigrator;

$addon = rex_addon::get("nv_contentmigrator");
if (file_exists($addon->getAssetsPath("css/style.css"))) {
    rex_view::addCssFile($addon->getAssetsUrl("css/style.css"));
}
if (file_exists($this->getAssetsPath("js/script.js"))) {
    rex_view::addJSFile($this->getAssetsUrl('js/script.js'));
}

rex_extension::register('PACKAGES_INCLUDED', function ($ep) {
    rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', 'nvContentMigrator::getPanel', rex_extension::LATE);
}, rex_extension::LATE);