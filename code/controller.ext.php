<?php

class module_controller extends ctrl_module
{


    static function ExecuteUpdateTheme($uid, $theme)
    {
        global $zdbh;
        /* Set CSS back to default */
        self::ExecuteUpdateCSS($uid, 'default');
        /* Set new theme */
        $sql = $zdbh->prepare("
            UPDATE x_accounts
            SET ac_usertheme_vc = :theme
            WHERE ac_id_pk = :uid");
        $sql->bindParam(':theme', $theme);
        $sql->bindParam(':uid', $uid);
        $sql->execute();
        return true;
    }
    static function ExecuteUpdateCSS($uid, $css)
    {
        global $zdbh;
        $sql = $zdbh->prepare("
            UPDATE x_accounts
            SET ac_usercss_vc = :css
            WHERE ac_id_pk = :uid");
        $sql->bindParam(':css', $css);
        $sql->bindParam(':uid', $uid);
        $sql->execute();
        return true;
    }
    static function ExecuteShowCurrentTheme($uid)
    {
        return ui_template::GetUserTemplate();
    }
    static function ExecuteShowCurrentCSS($uid)
    {
        global $zdbh;
        $numrows = $zdbh->prepare("SELECT ac_usercss_vc FROM x_accounts WHERE ac_id_pk = :uid");
        $numrows->bindParam(':uid', $uid);
        $numrows->execute();
        $result = $numrows->fetch();
        if ($result) {
            return $result['ac_usercss_vc'];
        } else {
            return false;
        }
    }
    static function ExecuteStylesList()
    {
        return ui_template::ListAvaliableTemplates();
    }
    static function ExecuteCSSList()
    {
        $currentuser = ctrl_users::GetUserDetail();
        return ui_template::ListAvaliableCSS(self::ExecuteShowCurrentTheme($currentuser['userid']));
    }

    static function getCurrentTheme()
    {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        return self::ExecuteShowCurrentTheme($currentuser['userid']);
    }
    static function getCurrentCSS()
    {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        return self::ExecuteShowCurrentCSS($currentuser['userid']);
    }
    static function getSelectThemeMenu()
    {
        $html = "";
        foreach (self::ExecuteStylesList() as $theme) {
            if ($theme['name'] != self::getCurrentTheme()) {
                $html .="<option value = \"" . $theme['name'] . "\">" . $theme['name'] . "</option>\n";
            } else {
                $html .="<option value = \"" . $theme['name'] . "\" selected=\"selected\">" . $theme['name'] . "</option>\n";
            }
        }
        return $html;
    }
    static function getSelectCSSMenu()
    {
        $html = "";
        foreach (self::ExecuteCSSList() as $css) {
            if ($css['name'] != self::getCurrentCSS()) {
                $html .="<option value = \"" . $css['name'] . "\">" . $css['name'] . "</option>\n";
            } else {
                $html .="<option value = \"" . $css['name'] . "\" selected=\"selected\">" . $css['name'] . "</option>\n";
            }
        }
        return $html;
    }
    static function getIsSelectCSS()
    {
        global $controller;
        $getvars = $controller->GetAllControllerRequests('URL');
        if (isset($getvars['selectcss']))
            return true;
        return false;
    }
    static function doSaveTheme()
    {
        global $controller;
        runtime_csfr::Protect();
        $currentuser = ctrl_users::GetUserDetail();
        $formvars = $controller->GetAllControllerRequests('FORM');
        self::ExecuteUpdateTheme($currentuser['userid'], $formvars['inTheme']);
        if (count(self::ExecuteCSSList($formvars['inTheme'])) > 1) {
            header("location: ./?module=" . $controller->GetCurrentModule() . "&selectcss=true");
        } else {
            self::ExecuteUpdateCSS($currentuser['userid'], "");
            header("location: ./?module=" . $controller->GetCurrentModule() . "&saved=true");
        }
        exit;
    }
    static function doSaveCSS()
    {
        global $controller;
        runtime_csfr::Protect();
        $currentuser = ctrl_users::GetUserDetail();
        $formvars = $controller->GetAllControllerRequests('FORM');
        self::ExecuteUpdateCSS($currentuser['userid'], $formvars['inCSS']);
        header("location: ./?module=" . $controller->GetCurrentModule() . "&saved=true");
        exit;
    }
    static function getResult()
    {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        $urlvars = $controller->GetAllControllerRequests('URL');
        if (isset($urlvars['saved'])) {
            return ui_sysmessage::shout(ui_language::translate("Your theme configuration has been saved"), "zannounceok");
        }
        if (isset($urlvars['selectcss'])) {
            return ui_sysmessage::shout(ui_language::translate("This theme has more than one variation, please choose a variation you'd like to use.."), "zannounceerror");
        }
        return false;
    }

	}
?>