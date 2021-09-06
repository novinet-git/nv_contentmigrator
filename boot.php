<?
$oMigrator = new nvContentMigrator;

$addon = rex_addon::get("nv_contentmigrator");
if (file_exists($addon->getAssetsPath("css/style.css"))) {
    rex_view::addCssFile($addon->getAssetsUrl("css/style.css"));
}