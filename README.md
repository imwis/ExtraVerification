# ExtraVerification

一个为 Typecho 博客后台登录添加双因素认证管理功能的插件。

### 插件初始化

#### 部署插件

1. 下载插件包
2. 将插件压缩包内文件解压到 `/usr/plugins` 目录下
3. 登入后台，从菜单 “控制台 -> 插件” 进入插件管理页
4. 找到本插件后点击 “启用”
5. 在后台菜单 “设置 -> 两步验证” 进行相关设置

#### 修改源码

1. 用代码编辑器打开 `admin/login.php` 文件，在适当位置插入以下代码：

```html
<p>
    <label for="extAuth" class="sr-only"><?php _e('动态密码'); ?></label>
    <input type="text" id="extAuth" name="extAuth" class="text-l w-100" placeholder="<?php _e('动态密码'); ?>" />
</p>
```

2. 接着打开 `var/Widget/User.php` 文件，在 `/** 开始验证用户 **/` 相关语句的下方，大约在 `126` 行的位置，插入以下代码：

```php
if(Typecho_Common::isAvailableClass('ExtraVerification_Plugin')) {
    $extv = new ExtraVerification_Plugin();
    if(false == empty($user['googleAuth']) && 
    false == $extv->googleAuthenticator()->verifyCode($user['googleAuth'], $this->request->extAuth, 1)){
        return false;
    }
}
```

#### 设置密钥

当插件启用后，从 `后台主菜单 -> 设置 -> 两步验证` ，即可进入两步验证设置页面，输入新密钥同时将其添加到 Google Authenticator 中，再输入由新密钥生成的动态密码确认后，即可开启两步验证。

### 更新记录

#### 1.1.0 @ 2016-08-26
- 将两步验证密钥管理页中插件相关 Javascript 代码脱离 jQuery 依赖。

#### 1.0.0 @ 2016-02-14
- 初版发布