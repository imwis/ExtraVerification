<?php
/**
 * ExtraVerification 插件附属类
 * 
 * @author Wis Chu
 * @link https://wischu.com/
 */

class ExtraVerification_Action extends Typecho_Widget implements Widget_Interface_Do
{
	public function action()
	{
		$user = Typecho_Widget::widget('Widget_User');
        	$user->pass('subscriber');
		
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$options = Typecho_Widget::widget('Widget_Options');
		$user = Typecho_Widget::widget('Widget_User');
		$ga = new ExtraVerification_GoogleAuthenticator();
		
		$secret = trim($this->request->secretKey);
		$code = trim($this->request->authCode);
		$secret = !empty($secret) ? $secret : NULL;
		$seclen = strlen($secret);

		if(!empty($secret)){
			if(!preg_match("/^[A-Za-z0-9]+$/i", $secret)){
				$this->widget('Widget_Notice')->set(_t('密钥不支持特殊字符及中文，请重新输入'), NULL, 'error');
				$this->response->goBack();
			}elseif($seclen < 16){
				$this->widget('Widget_Notice')->set(_t('密钥长度太短，请重新输入'), NULL, 'error');
				$this->response->goBack();
			}elseif($seclen > 32){
				$this->widget('Widget_Notice')->set(_t('密钥长度请不要超过32位，请重新输入'), NULL, 'error');
				$this->response->goBack();
			}elseif(!$ga->verifyCode($secret, $code, 1)){
				$this->widget('Widget_Notice')->set(_t('密钥与动态密码不匹配，请确认后重新设置'), NULL, 'error');
				$this->response->goBack();
			}
			
			$db->query($db->update($prefix.'users')->rows(array('googleAuth' => $secret))->where('uid = ?', $user->uid));
			$this->widget('Widget_Notice')->set(_t('两步验证密钥已更改'), NULL, 'success');
		}else{
			$db->query($db->update($prefix.'users')->rows(array('googleAuth' => NULL))->where('uid = ?', $user->uid));
			$this->widget('Widget_Notice')->set(_t('两步验证已关闭'), NULL, 'success');
		}

		$this->response->goBack();
	}
}
