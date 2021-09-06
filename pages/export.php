<?php
$oMigrator = new nvContentMigrator;
$csrfToken = rex_csrf_token::factory('nv_contentmigrator');

if (rex_post('export', 'string')) {
   
    if(!$csrfToken->isValid())  {
        echo rex_view::error("Ein Fehler ist aufgetreten. Bitte wenden Sie sich an den Webmaster.");
        return;
    }

    $iArticlesId = rex_request('nv_articles_id', 'int');
    $oMigrator->export($iArticlesId);

}


$aTree = $oMigrator->getTree();
$sContent = '<div class="container-fluid">';
$sContent .= $oMigrator->parseTreeList($aTree);
$sContent .= '</div>';

$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save" type="submit" name="export" value="1">'.$this->i18n('nv_contentmigrator_btn_export').'</button>';
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
$fragment->setVar('title', $this->i18n('nv_contentmigrator_title_export'), false);
$fragment->setVar('body', $sContent, false);
$fragment->setVar("buttons", $buttons, false);
$output = $fragment->parse('core/page/section.php');

$output = '<form action="' . rex_url::currentBackendPage() . '" method="post">'
. $csrfToken->getHiddenField() 
. $output 
. '</form>';

echo $output;