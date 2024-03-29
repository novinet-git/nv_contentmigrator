<?php class nvContentMigrator
{

    static $oAddon = "nv_contentmigrator";

    public function __construct()
    {
        $this->addon = rex_addon::get('nv_contentmigrator');
        $this->aDataColumns = array(
            "priority",
            "revision",
            "status",
            "value1",
            "value2",
            "value3",
            "value4",
            "value5",
            "value6",
            "value7",
            "value8",
            "value9",
            "value10",
            "value11",
            "value12",
            "value13",
            "value14",
            "value15",
            "value16",
            "value17",
            "value18",
            "value19",
            "value20",
            "media1",
            "media2",
            "media3",
            "media4",
            "media5",
            "media6",
            "media7",
            "media8",
            "media9",
            "media10",
            "medialist1",
            "medialist2",
            "medialist3",
            "medialist4",
            "medialist5",
            "medialist6",
            "medialist7",
            "medialist8",
            "medialist9",
            "medialist10",
            "link1",
            "link2",
            "link3",
            "link4",
            "link5",
            "link6",
            "link7",
            "link8",
            "link9",
            "link10",
            "linklist1",
            "linklist2",
            "linklist3",
            "linklist4",
            "linklist5",
            "linklist6",
            "linklist7",
            "linklist8",
            "linklist9",
            "linklist10"
        );
    }

    public function getTree($iParentId = 0, $iLevel = 0)
    {

        $aItems = array();
        $oItems = rex_sql::factory();
        $sQuery = "SELECT catname,id,parent_id,name,priority FROM " . rex::getTablePrefix() . "article WHERE parent_id = '$iParentId' && clang_id = '" . $this->getDefaultClangId() . "'  ORDER BY catpriority ASC";
        $oItems->setQuery($sQuery);

        foreach ($oItems as $oItem) {
            array_push($aItems, array("name" => $oItem->getValue("name"), "level" => $iLevel, "priority" => $oItem->getValue("catpriority"), "id" => $oItem->getValue("id"), "parent_id" => $oItem->getValue("parent_id"), "children" => $this->getTree($oItem->getValue("id"), $iLevel + 1)));
        }

        return $aItems;
    }

    public function getDefaultClangId()
    {
        $oItems = rex_sql::factory();
        $sQuery = "SELECT id,code,name FROM " . rex::getTablePrefix() . "clang ORDER BY priority ASC Limit 1";
        $oItems->setQuery($sQuery);
        return $oItems->getValue("id");
    }

    public function parseTreeList($aItems)
    {
        $aOut = array();

        $aOut[] = '<div class="row">';
        $aOut[] = '<div class="col-12"><strong>' . $this->addon->i18n('nv_contentmigrator_label_choose_article') . '</strong><br><select class="form-control selectpicker" data-live-search="true" name="nv_articles_id">' . $this->parseTreeSelection("nv_articles_id", $aItems) . '</select></div>';
        $aOut[] = '</div><br>';

        $sOut = implode("\n", $aOut);
        return $sOut;
    }

    public function getUploadField()
    {
        $aOut = array();
        $aOut = array();

        $aOut[] = '<div class="row">';
        $aOut[] = '<div class="col-12"><strong>' . $this->addon->i18n('nv_contentmigrator_label_jsonfile') . '</strong><br><input class="form-control" type="file" accept=".json" name="importfile" required="required"></div>';
        $aOut[] = '</div><br>';

        $sOut = implode("\n", $aOut);
        return $sOut;
    }
    public function getConfirmationField()
    {
        $aOut = array();
        $aOut = array();

        $aOut[] = '<div class="row">';
        $aOut[] = '<div class="col-12"><input type="checkbox" name="confirmation" value="1" required="required"> ' . $this->addon->i18n('nv_contentmigrator_label_confirm_import') . '</div>';
        $aOut[] = '</div><br>';

        $sOut = implode("\n", $aOut);
        return $sOut;
    }

    public function parseTreeSelection($sFieldname, $aItems)
    {
        $aOut = array();
        $sCheckValue = rex_request($sFieldname, 'int');
        foreach ($aItems as $aItem) {
            $aOut[] = '<option value="' . $aItem["id"] . '" ';
            if ($sCheckValue == $aItem["id"]) $aOut[] = 'selected';
            $aOut[] = '>';
            for ($x = 0; $x < $aItem["level"]; $x++) {
                $aOut[] = '&nbsp;&nbsp;';
            }

            $aOut[] = $aItem["name"] . ' (ID: ' . $aItem["id"] . ')</option>';
            if (count($aItem["children"])) {
                $aOut[] = $this->parseTreeSelection($sFieldname, $aItem["children"]);
            }
        }
        $sOut = implode("\n", $aOut);
        return $sOut;
    }

    public function export($iArticlesId)
    {
        $iArticlesId = (int) $iArticlesId;
        $oArticle = rex_article::get($iArticlesId);
        if (!$oArticle->getValue("id")) return;

        $aArr = array();
        $aArr["article"] = array(
            "server" => rex::getServer(),
            "id" => $oArticle->getValue("id"),
            "name" => $oArticle->getName(),
            "template_id" => $oArticle->getValue("template_id"),
            "clang_id" => $oArticle->getValue("clang_id"),
            "createdate" => $oArticle->getValue("createdate"),
            "createuser" => $oArticle->getValue("createuser"),
            "updatedate" => $oArticle->getValue("updatedate"),
            "updateuser" => $oArticle->getValue("updateuser"),
        );

        $aMediaUsedTotal = array();

        $aArr["slices"] = array();
        $oDbQ = rex_sql::factory();
        $sQuery = "SELECT s.* FROM " . rex::getTablePrefix() . "article_slice AS s LEFT JOIN " . rex::getTablePrefix() . "article AS a ON s.article_id = a.id WHERE (a.id = '$iArticlesId') && a.clang_id = '" . $this->getDefaultClangId() . "'  && s.clang_id = '" . $this->getDefaultClangId() . "' ORDER BY s.priority ASC";
        $oDbQ->setQuery($sQuery);
        foreach ($oDbQ as $oRow) {
            $aSlice = array();
            $aSlice["slice"] = array(
                "id" => $oRow->getValue("id"),
                "article_id" => $oRow->getValue("article_id"),
                "clang_id" => $oRow->getValue("clang_id"),
                "ctype_id" => $oRow->getValue("ctype_id"),
                "module_id" => $oRow->getValue("module_id"),
                "revision" => $oRow->getValue("revision"),
                "priority" => $oRow->getValue("priority"),
                "status" => $oRow->getValue("status"),
                "createdate" => $oRow->getValue("createdate"),
                "createuser" => $oRow->getValue("createuser"),
                "updatedate" => $oRow->getValue("updatedate"),
                "updateuser" => $oRow->getValue("updateuser"),
            );
            $aSlice["data"] = array();
            foreach ($this->aDataColumns as $sColumn) {
                $aSlice["data"][$sColumn] = $oRow->getValue($sColumn);
            }
            $aMediaUsed = $this->getUsedMedia($iArticlesId);
            $aSlice["media"] = $aMediaUsed;
            foreach ($aMediaUsed as $aMedia) {
                if (!in_array($aMedia["filename"], array_column($aMediaUsedTotal, 'filename'))) {
                    array_push($aMediaUsedTotal, $aMedia);
                }
            }
            array_push($aArr["slices"], $aSlice);
            $aArr["media"] = $aMediaUsedTotal;
        }

        /*
        dump($aArr);
        return;
*/

        $sFileContent = json_encode($aArr);

        $sFilename = 'nv_contentmigrator_export_article_' . $oArticle->getName() . '_' . $oArticle->getValue('id') . '_' . date('YmdHis') . '.json';
        header('Content-Disposition: attachment; filename="' . $sFilename . '"; charset=utf-8');
        rex_response::sendContent($sFileContent, 'application/octetstream');
        exit;
    }

    public function import(int $iArticlesId, $aFileContent = [])
    {

        $oArticle = rex_article::get($iArticlesId);
        if (!$oArticle->getValue("id")) return;

        // delete old content
        $oDb = rex_sql::factory();
        $oDb->setQuery("DELETE FROM " . rex::getTablePrefix() . "article_slice WHERE article_id = '$iArticlesId'");

        // update article data
        $oDb = rex_sql::factory();
        $oDb->setTable(rex::getTablePrefix() . 'article');
        $oDb->setValue('template_id', $aFileContent["article"]["template_id"]);
        $oDb->setWhere(['id' => $oArticle->getValue("id"), 'clang_id' => $aFileContent["article"]["clang_id"]]);
        $oDb->addGlobalUpdateFields();
        $oDb->update();


        // insert slices
        foreach ($aFileContent["slices"] as $aItem) {

            $aSlice = $aItem["slice"];
            $aData = $aItem["data"];
            $oRes = rex_content_service::addSlice($oArticle->getValue("id"), $oArticle->getValue("clang_id"), $aSlice["ctype_id"], $aSlice["module_id"], $aData);
        }

        rex_article_cache::delete($oArticle->getValue("id"), $aFileContent["article"]["clang_id"]);
    }

    public function getUsedMedia($iArticlesId)
    {
        $iArticlesId = (int) $iArticlesId;
        $oArticle = rex_article::get($iArticlesId);
        if (!$oArticle->getValue("id")) return;

        $aFiles = [];
        $sQuery = 'SELECT * FROM ' . rex::getTablePrefix() . 'media';

        $oDbQ = rex_sql::factory();
        $aItems = $oDbQ->getArray($sQuery);
        if (count($aItems)) {
            foreach ($aItems as $aItem) {
                $sFilename = $aItem['filename'];
                if ($this->checkMediaUsed($sFilename, $iArticlesId)) {
                    $aMediaPath = [];
                    if ($aItem["category_id"]) {
                        $aMediaPath = rex_media_category::get($aItem["category_id"])->getPathAsArray();
                        $aMediaPath[] = $aItem["category_id"];
                    }
                    $aMediaPathLabel = [];
                    foreach ($aMediaPath as $iCategoryId) {
                        $aMediaPathLabel[] = rex_media_category::get($iCategoryId)->getName();
                    }
                    $sMediaPath = implode(" > ", $aMediaPathLabel);

                    $aFiles[] = array(
                        "id" => $aItem["id"],
                        "filename" => $sFilename,
                        "originalname" => $aItem["originalname"],
                        "category_id" => $aItem["category_id"],
                        "path" => $sMediaPath,
                        "filesize" => $aItem["filesize"],
                        "width" => $aItem["width"],
                        "height" => $aItem["height"]
                    );
                }
            }
        }
        return $aFiles;
    }

    public function checkMediaUsed($sFilename, $iArticlesId)
    {
        $iArticlesId = (int) $iArticlesId;
        $oArticle = rex_article::get($iArticlesId);
        if (!$oArticle->getValue("id")) return;

        $oDbQ = rex_sql::factory();
        // FIXME move structure stuff into structure addon
        $values = [];
        for ($i = 1; $i < 21; ++$i) {
            $values[] = 'value' . $i . ' REGEXP ' . $oDbQ->escape('(^|[^[:alnum:]+_-])' . $sFilename);
        }

        $files = [];
        $filelists = [];
        $escapedFilename = $oDbQ->escape($sFilename);
        for ($i = 1; $i < 11; ++$i) {
            $files[] = 'media' . $i . ' = ' . $escapedFilename;
            $filelists[] = 'FIND_IN_SET(' . $escapedFilename . ', medialist' . $i . ')';
        }

        $where = '';
        $where .= implode(' OR ', $files) . ' OR ';
        $where .= implode(' OR ', $filelists) . ' OR ';
        $where .= implode(' OR ', $values);

        $from = '';
        if ($iArticlesId > 0) {
            $from = 'LEFT JOIN ' . rex::getTablePrefix() . 'article AS a ON s.article_id = a.id ';
            $where = '(a.id = "' . $iArticlesId . '" OR a.path LIKE "|' . $iArticlesId . '|%") AND (' . $where . ')';
        }

        $sQuery = 'SELECT DISTINCT s.article_id, s.clang_id FROM ' . rex::getTablePrefix() . 'article_slice AS s ' . $from . ' WHERE ' . $where;

        $oDbQ->getArray($sQuery);
        if ($oDbQ->getRows() > 0) {
            return true;
        }

        return false;
    }

    public function checkMediaExists($sFilename, $iWidth, $iHeight, $iFilesize)
    {
        $oDb = rex_sql::factory();
        $sQuery = 'SELECT * FROM ' . rex::getTablePrefix() . 'media WHERE filename = :filename && width = :width && height = :height && filesize = :filesize Limit 1';
        $oDb->setQuery($sQuery, ['filename' => $sFilename, 'width' => $iWidth, 'height' => $iHeight, 'filesize' => $iFilesize]);
        return $oDb;
    }

    static function getPanel($ep)
    {
        $oAddon = rex_addon::get(self::$oAddon);

        $oMigrator = new nvContentMigrator;

        //Vorgaben einlesen/setzen
        $op = $ep->getSubject();                                                //Content des ExtPoint (z.B. Seiteninhalt)
        $params = $ep->getParams();                                            //alle Parameter des ExtPoint holen (z.B. Article-ID)
        $article_id = $params['article_id'];                                            //ID des Artikels
        $clang = $params['clang'];                                                //ID der Sprachversion
        $ctype = $params['ctype'];

        $csrfToken = rex_csrf_token::factory('nv_contentmigrator');
        if (rex_post('export', 'string')) {

            if (!$csrfToken->isValid()) {
                echo rex_view::error("Ein Fehler ist aufgetreten. Bitte wenden Sie sich an den Webmaster.");
                return;
            }

            $iArticlesId = rex_request('nv_articles_id', 'int');
            $oMigrator->export($iArticlesId);
        }

        $panel = "";
        $panel .= '<form action="" method="post">
			<div class="nv_contentmigrator">
				' . $csrfToken->getHiddenField() . '
                <button class="btn btn-primary" type="submit" name="export" value="1">' . $oAddon->i18n('nv_contentmigrator_btn_export') . '</button>
                <a class="btn btn-primary" href="/redaxo/index.php?page=nv_contentmigrator/import&nv_articles_id=' . $article_id . '&nv_clang_id=' . $clang . '&nv_ctype_id=' . $ctype . '" onclick="newWindow(\'nv_contentmigrator\', this.href, 1000,800,\',status=yes,resizable=yes\');return false;">' . $oAddon->i18n('nv_contentmigrator_btn_import') . '</a>
			<input type="hidden" name="nv_articles_id" value="' . $article_id . '">
			<input type="hidden" name="nv_clang_id" value="' . $clang . '">
			<input type="hidden" name="nv_ctype_id" value="' . $ctype . '">
                </div>
            </form>';


        //SEO-Panel erstellen und ausgeben
        $collapsed = false;
        $frag = new rex_fragment();
        $frag->setVar('title', '<div class="seocu-title"><i class="rex-icon fa-copy"></i> nvContentMigrator<div class="seocu-resultbar-wrapper"><div class="seocu-resultbar"></div></div></div>', false);
        $frag->setVar('body', $panel, false);
        $frag->setVar('article_id', $article_id, false);
        $frag->setVar('clang', $clang, false);
        $frag->setVar('ctype', $ctype, false);
        $frag->setVar('collapse', true);                                //schließbares Panel - true|false
        $frag->setVar('collapsed', $collapsed);                            //Panel geschlossen starten - true|false
        $cnt = $frag->parse('core/page/section.php');

        return $op . $cnt;
    }
}
