<?php
$oMigrator = new nvContentMigrator;
$csrfToken = rex_csrf_token::factory('nv_contentmigrator');

if (rex_post('import', 'string')) {
   
    if(!$csrfToken->isValid())  {
        echo rex_view::error("Ein Fehler ist aufgetreten. Bitte wenden Sie sich an den Webmaster.");
        return;
    }

    $aFileContent = json_decode(file_get_contents($_FILES["importfile"]["tmp_name"]),1);
    $iArticlesId = rex_request('nv_articles_id', 'int');
    $oMigrator->import($iArticlesId,$aFileContent);
    echo rex_view::success($this->i18n('nv_contentmigrator_imported'));

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
$n['field'] = '<button class="btn btn-save" type="submit" name="import" value="1">'.$this->i18n('nv_contentmigrator_btn_import').'</button>';
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