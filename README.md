# AliyunSms
此插件适用于ThinkCMF内容管理框架,框架内容详见https://www.thinkcmf.com/download.html
插件使用方法:
①将插件文件夹复制到ThinkCMF下的public/plugins中;
②进入后台管理中心--插件管理--插件列表,找到阿里云短信插件并安装;
③安装成功,点击设置,填写你在阿里云通信申请的Key,签名等信息;

当执行发送验证码操作时,会在user/VerificationCodeController/send()方法中通过hook_one("send_mobile_verification_code", $param)钩子执行插件中的方法;
同时将验证码和手机号两个参数传入插件.