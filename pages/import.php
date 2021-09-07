<?php
$oMigrator = new nvContentMigrator;
$csrfToken = rex_csrf_token::factory('nv_contentmigrator');

/*
if (rex_post('download', 'string')) {

    if (!$csrfToken->isValid()) {
        echo rex_view::error("Ein Fehler ist aufgetreten. Bitte wenden Sie sich an den Webmaster.");
        return;
    }

    $aFileContent = json_decode(base64_decode(rex_post("filecontent")),1);

    dump($aFileContent);

    return;
}
*/
if (rex_post('import', 'string')) {

    if (!$csrfToken->isValid()) {
        echo rex_view::error("Ein Fehler ist aufgetreten. Bitte wenden Sie sich an den Webmaster.");
        return;
    }

    $aFileContent = json_decode(file_get_contents($_FILES["importfile"]["tmp_name"]), 1);
    $iArticlesId = rex_request('nv_articles_id', 'int');
    $oMigrator->import($iArticlesId, $aFileContent);
    echo rex_view::success($this->i18n('nv_contentmigrator_imported'));

    if (count($aFileContent["media"]) > 0) {
        $sContent = '<div class="container-fluid">';
        $sContent .= '<div class="row">';
        $sContent .= '<div class="col-lg-3"><strong>'.$this->i18n('nv_contentmigrator_label_filename').'</strong></div>';
        $sContent .= '<div class="col-lg-3"><strong>'.$this->i18n('nv_contentmigrator_label_category').'</strong></div>';
        $sContent .= '<div class="col-lg-2"><strong>'.$this->i18n('nv_contentmigrator_label_width').'</strong></div>';
        $sContent .= '<div class="col-lg-2"><strong>'.$this->i18n('nv_contentmigrator_label_height').'</strong></div>';
        $sContent .= '<div class="col-lg-2"><strong>'.$this->i18n('nv_contentmigrator_label_exists').'</strong></div>';

        $sContent .= '</div>';
        foreach ($aFileContent["media"] as $aMedia) {
            $sMediaExists = $this->i18n('nv_contentmigrator_no');
            $oExistingMedia = $oMigrator->checkMediaExists($aMedia["filename"],$aMedia["width"],$aMedia["height"],$aMedia["filesize"]);
            if ($oExistingMedia->getValue("filename")) {
                $sMediaExists = '<a href="'.rex::getServer().'media/'.$oExistingMedia->getValue("filename").'" target="_blank">'.$oExistingMedia->getValue("filename").'</a>';
            }


            $sContent .= '<div class="row">';
            $sContent .= '<div class="col-lg-3"><a href="' . $aFileContent["article"]["server"] . 'media/' . $aMedia["filename"] . '" target="_blank">' . $aMedia['filename'].'</a></div>';
            $sContent .= '<div class="col-lg-3">'.$aMedia["path"].'</div>';
            $sContent .= '<div class="col-lg-2">'.$aMedia["width"].'px</div>';
            $sContent .= '<div class="col-lg-2">'.$aMedia["height"].'px</div>';
            $sContent .= '<div class="col-lg-2">'.$sMediaExists.'</div>';
            $sContent .= '</div>';
        }
        $sContent .= '</div>';
       


        $fragment = new rex_fragment();
        $fragment->setVar("class", "edit");
        $fragment->setVar('title', $this->i18n('nv_contentmigrator_title_used_media'), false);
        $fragment->setVar('body', $sContent, false);
        $output = $fragment->parse('core/page/section.php');
        
        echo $output;
    }


    return;
}

echo rex_view::warning($this->i18n('nv_contentmigrator_warning_content_deleted'));

$aTree = $oMigrator->getTree();
$sContent = '<div class="container-fluid">';
$sContent .= $oMigrator->parseTreeList($aTree);
$sContent .= $oMigrator->getUploadField();
$sContent .= $oMigrator->getConfirmationField();
$sContent .= '</div>';

$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save" type="submit" name="import" value="1">' . $this->i18n('nv_contentmigrator_btn_import') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');
$buttons = '
<fieldset class="rex-form-action">
' . $buttons . '
</fieldset>
';

$fragment = new rex_fragment();
$fragment->setVar("class", "edit");
$fragment->setVar('title', $this->i18n('nv_contentmigrator_title_import'), false);
$fragment->setVar('body', $sContent, false);
$fragment->setVar("buttons", $buttons, false);
$output = $fragment->parse('core/page/section.php');

$output = '<form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data">'
    . $csrfToken->getHiddenField()
    . $output
    . '</form>';

echo $output;
