<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 后台登录两步验证管理插件
 * 
 * @package ExtraVerification
 * @author Wis
 * @version 1.0.0
 * @link https://wislab.net/
 */
class ExtraVerification_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        $return = self::installDb();
        Helper::addPanel(4, 'ExtraVerification/set-extverf.php', '两步验证', '两步验证设置', 'subscriber');
        Helper::addAction('extverf-edit', 'ExtraVerification_Action');
        
        return $return;
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){
        Helper::removeAction('extverf-edit');
        Helper::removePanel(4, 'ExtraVerification/set-extverf.php');
        
        return _t('插件已被禁用，如需取消两步验证功能，请自行清理数据库及相关文件');
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){}
    
    public static function googleAuthenticator(){
        $ga = new ExtraVerification_GoogleAuthenticator();
        return $ga;
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    public static function editForm()
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $user = Typecho_Widget::widget('Widget_User');
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        
        $line = $db->fetchRow($db->select()->from($prefix.'users')->where('uid = ?', $user->uid));
        
        $form = new Typecho_Widget_Helper_Form(Typecho_Common::url('/action/extverf-edit', $options->index),
        Typecho_Widget_Helper_Form::POST_METHOD);

        /** 密钥 */
        $secret = new Typecho_Widget_Helper_Form_Element_Text('secretKey', NULL, isset($line['googleAuth']) ? $line['googleAuth'] : '', _t('密钥'), _t('请务必与 Google 身份验证器中的密钥保持一致，支持英文和数字，最长32位.<br>留空本项则关闭当前用户的两步验证功能.'));
        $form->addInput($secret);
        
        /** 动态密码 */
        $code = new Typecho_Widget_Helper_Form_Element_Text('authCode', NULL, NULL, _t('动态密码'), _t('更改密钥时需同时输入由新密钥生成的动态密码以确认修改.'));
        $code->input->setAttribute('class', 'w-20');
        $form->addInput($code);
        
        /** 随机密钥 */
        $secKey = self::googleAuthenticator()->createSecret(32);
        $issuer = str_replace(' ', '', ucwords(strtolower($options->title)));
        $qrcurl = self::googleAuthenticator()->getQRCodeGoogleUrl($user->name, $secKey, $issuer);
        $rand = new Typecho_Widget_Helper_Form_Element_Text('randSecret', NULL, $secKey, _t('随机参考密钥'), _t('如果需要使用该字符串作为密钥，请先将字符串 <a href="#" id="copyKey" class="copyKey">粘贴到密钥框</a> 中，然后使用 Google 身份验证器 <a href="'. $qrcurl .'" target="_blank">扫描二维码</a>.'));
        $rand->input->setAttribute('disabled', 'disabled');
        $form->addInput($rand);
        
        /** 提交 */
        $submit = new Typecho_Widget_Helper_Form_Element_Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $submit->value(_t('确认更改'));
        $form->addItem($submit);
        
        return $form;
    }
    
    public static function cancelForm(){}
    
    public static function installDb()
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        
        /** 检查数据表，适时建立字段 */
        try {
            $select = $db->select('table.users.googleAuth')->from('table.users');
            $db->query($select, Typecho_Db::READ);
            return _t('检测到密钥字段，插件已经被启用');
        }catch (Typecho_Db_Exception $e){
            try {
                $db->query('ALTER TABLE `'. $prefix .'users` ADD `googleAuth` varchar(32) NULL DEFAULT NULL');
                return _t('插件数据库初始化完成，插件已经被启用');
            }catch (Typecho_Db_Exception $e){
                throw new Typecho_Plugin_Exception(_t('插件数据库初始化失败，插件未被启用'));
            }
        }
    }
}
