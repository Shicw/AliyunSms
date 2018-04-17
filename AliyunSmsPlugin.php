<?php
namespace plugins\aliyun_sms;
use cmf\lib\Plugin;
ini_set("display_errors", "on");

require_once 'sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendBatchSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;

// 加载区域结点配置
Config::load();
/**
 * AliyunSmsPlugin阿里云短信验证码插件
 */
class AliyunSmsPlugin extends Plugin
{

    public $info = [
        'name'        => 'AliyunSms',
        'title'       => '阿里云短信插件',
        'description' => '阿里云短信插件',
        'status'      => 1,
        'author'      => 'Shicw',
        'version'     => '1.0'
    ];

    public $has_admin = 0;//插件是否有后台管理界面

    public function install() //安装方法必须实现
    {
        return true;
    }

    public function uninstall() //卸载方法必须实现
    {
        return true;
    }
    static $acsClient = null;

    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public function getAcsClient() {
        $config = $this->getConfig();
        //产品名称:云通信流量服务API产品,无需替换
        $product = "Dysmsapi";
        //产品域名,无需替换
        $domain = "dysmsapi.aliyuncs.com";
        $accessKeyId = $config['access_key']; // AccessKeyId
        $accessKeySecret = $config['access_secret']; // AccessKeySecret
        // 暂时不支持多Region
        $region = "cn-hangzhou";
        // 服务结点
        $endPointName = "cn-hangzhou";
        if(static::$acsClient == null) {
            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送短信
     * @return
     */
    public function sendMobileVerificationCode($param) {
        //从plugin表中读取config值
        $config = $this->getConfig();
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();
        //可选-启用https协议
        //$request->setProtocol("https");
        // 必填，设置短信接收号码
        $request->setPhoneNumbers($param['mobile']);
        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($config['sign_name']);
        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($config['template_code']);
        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode(array(  // 短信模板中字段的值
            "code"=>$param['code'],
            "product"=>"dsd"
        ), JSON_UNESCAPED_UNICODE));
        // 可选，设置流水号
        $request->setOutId("yourOutId");
        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        $request->setSmsUpExtendCode("1234567");
        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        //设置过期时间
        $expire_minute = $config['expire_minute'];
        $expire_minute = empty($expire_minute) ? 30 : $expire_minute;
        $expire_time   = time() + $expire_minute * 60;

        $result = [
            'error'     => 0,
            'message' => '成功发送验证码',
            'expire_time' => $expire_time
        ];
        return $result;
    }
}