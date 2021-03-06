<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Notifications\Notifiable;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Utils\RPC;
use App\Notifications\{UserRegisterCode, UserLoginCode};
use App\Models\{SmsProject, Setting ,Users};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SmsController extends Controller
{
    use Notifiable;
    private $_sms_ip_check_expire_time = 60;
    /**
     * 发送短信
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        $ALIYUN_SMS_AK = env("ALIYUN_SMS_AK");
        $ALIYUN_SMS_AS = env("ALIYUN_SMS_AS");
        $ALIYUN_SMS_SIGN_NAME = env("ALIYUN_SMS_SIGN_NAME");
        $ALIYUN_SMS_VARIABLE = env("ALIYUN_SMS_VARIABLE");  //内容变量
        $tplId = env('ALIYUN_SMS_CODE');                //模版ID 模版CODE 格式为 SMS_140736882

        if (empty($tplId) || empty($ALIYUN_SMS_AK) || empty($ALIYUN_SMS_AS) || empty($ALIYUN_SMS_SIGN_NAME) || empty($ALIYUN_SMS_VARIABLE)) {
            return $this->error('系统配置错误，请联系系统管理员');
        }
        Config::set("aliyunsms.access_key", $ALIYUN_SMS_AK);
        Config::set("aliyunsms.access_secret", $ALIYUN_SMS_AS);
        Config::set("aliyunsms.sign_name", $ALIYUN_SMS_SIGN_NAME);
        $mobile = Input::get('mobile', '');
        if (empty($mobile)) {
            return $this->error('手机号不能为空');
        }
        //检查1分钟内该ip是否发送过验证码
        // if ($this->checkSmsIp($request->ip().$mobile)) {
        //     return $this->error('验证码发送过于频繁');
        // }
        $verification_code = $this->createSmsCode(6);
        $params = [
            $ALIYUN_SMS_VARIABLE => $verification_code
        ];

        try {
            $smsService = \App::make('Curder\LaravelAliyunSms\AliyunSms');
            $return = $smsService->send(strval($mobile), $tplId, $params);

            if ($return->Message == "OK") {
                //记入session
                session(['sms_captcha' => $verification_code]);
                session(['sms_mobile' => $mobile]);

                //设置缓存key
                //$this->setSmsIpKey($request->ip().$mobile, $mobile);
                return $this->success("发送成功");
            } else {
                return $this->error($return->Message);
            }
        } catch (\ErrorException $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 发送短信
     */
    public function smsSend(Request $request)
    {
        $mobile = $request->get('user_string', '');
        $country_code = $request->get('country_code', 86);
        $scene = $request->get('scene', '');
        $scene_list = SmsProject::enumScene();
        $region_list = SmsProject::enumRegion();
        $country_code = str_replace('+', '', $country_code);
        if (Cache::has("{$mobile}@$country_code")) {
            return $this->error('请勿重复点击');
        }
        if (empty($scene) || !in_array($scene, array_keys($scene_list))) {
            return $this->error('短信场景错误');
        }
        if (empty($country_code) || !in_array($country_code, array_keys($region_list))) {
            return $this->error('国际区域代码错误');
        }
        if (empty($mobile)) {
            return $this->error('电话号码不能为空');
        }
        $has_user_scene_list = [
            'login',
            'change_password',
            'reset_password',
            'withdraw',
        ];
        if (in_array($scene, $has_user_scene_list)) {
            // 修改密码直接从已经登录的session中取用户
            if ($scene == 'change_password') {
                $user_id = Users::getUserId();
                $user = Users::findOrFail($user_id);
                $country_code = $user->country_code;
            } else {
                $user = Users::getByString($mobile, $country_code);
            }
            if (empty($user)) {
                return $this->error('账号不存在');
            }
        } else {
            $user = Users::getByString($mobile, $country_code);
            if (!empty($user)) {
                return $this->error('账号已存在');
            }
        }
        //取短信模板,如果不存在则取默认
        $sms_project = SmsProject::where('scene', $scene)->get();
        if (count($sms_project) <= 0) {
            return $this->error('短信模板不存在');
        }
        $project = $sms_project->where('country_code', $country_code)->first();
        $project || $project = $sms_project->where('is_default', 1)->first();
        if (!$project) {
            return $this->error('短信模板不存在');
        }
        if (empty($mobile)) {
            return $this->error('请填写手机号');
        }
        $content = $project->project ?? $project->contents; //赛邮只需要模板id,不需要拼接内容
        $verification_code = $this->createSmsCode(6);
        session()->put('code@' . $country_code . $mobile, $verification_code);
        $class_name = '\\App\Notifications\\';
        if ($scene == 'register') {
            $class_name .= 'UserRegisterCode';
        } elseif ($scene == 'login') {
            $class_name .= 'UserLoginCode';
        } elseif ($scene == 'change_password') {
            $class_name .= 'ChangePasswordCode';
        } elseif ($scene == 'reset_password') {
            $class_name .= 'ResetPasswordCode';
        } elseif ($scene .= 'WithdrawCode') {
            $class_name .= 'WithdrawCode';
        }
        //暂时屏蔽 chinpoo
        $notification = new $class_name($mobile, $content, ['code' => $verification_code], $country_code);
        try {
            $this->notify($notification);
            Cache::put("{$mobile}@$country_code", 1, Carbon::now()->addSeconds(59));
            return $this->success('发送成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 短信宝发送短信
     */
    public function smsBaoSend(Request $request)
    {
        $mobile = $request->get('user_string');
        if (empty($mobile)) {
            return $this->error('电话不能为空');
        }
        $type = $request->get('type');//
        if ($type == 'forget' || $type == 'login') {
            $user = Users::getByString($mobile);
            if (empty($user)) {
                return $this->error('账号错误');
            }
        } else {
            $user = Users::getByString($mobile);
            if (!empty($user)) {
                return $this->error('账号已存在');
            }
        }

        /* $user = Users::getByString($mobile);
        if(!empty($user)) return $this->error('账号已存在'); */
        $username = Setting::getValueByKey('smsBao_username', '');
        $password = Setting::getValueByKey('password', '');
        $sms_signature = Setting::getValueByKey('sms_signature', '【签名】');
        if (empty($mobile)) {
            return $this->error('请填写手机号');
        }
        $verification_code = $this->createSmsCode(4);

        $content = $sms_signature . '您的验证码为 [' . $verification_code . ']，请勿泄漏。';
        $api = 'http://api.smsbao.com/sms';
        $send_url = $api . "?u=" . $username . "&p=" . md5($password) . "&m=" . $mobile . "&c=" . urlencode($content);
        $return_message = RPC::apihttp($send_url);
        if ($return_message == 0) {
            session(['code' => $verification_code]);
            return $this->success('发送成功');
        } else {
            $statusStr = array(
                "-1" => "参数不全",
                "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
                "30" => "密码错误",
                "40" => "账号不存在",
                "41" => "余额不足",
                "42" => "帐户已过期",
                "43" => "IP地址限制",
                "44" => "账号被禁用",
                "50" => "内容含有敏感词",
            );
            return $this->error($statusStr[$return_message]);
        }
    }

    /**
     * 检查1分钟内$ip是否发送过验证码
     * @param $ip
     * @return bool|\Illuminate\Http\JsonResponse
     */
    private function checkSmsIp($ip)
    {
        if (empty($ip)) {
            return $this->error('ip参数不正确');
        }
        return $this->checkSmsIpKey($ip);
    }

    /**
     * 生成短信验证码
     * @param int $num  验证码位数
     * @return string
     */
    public function createSmsCode($num = 6)
    {
        //验证码字符数组
        $n_array = range(0, 9);
        //随机生成$num位验证码字符
        $code_array = array_rand($n_array, $num);
        //重新排序验证码字符数组
        shuffle($code_array);
        //生成验证码
        $code = implode('', $code_array);
        return $code;
    }

    /**
     * 设置sms发送短信Ip缓存限制
     * @param $ip
     * @param $mobile
     */
    public function setSmsIpKey($ip, $mobile)
    {
        $key = Config::get('cache.keySmsIpCheck') . $ip;
        Redis::setex($key, $this->_sms_ip_check_expire_time, $mobile);//已发送

    }

    /**
     * 检查sms发送短信Ip缓存限制
     * @param $ip
     * @return bool
     */
    public function checkSmsIpKey($ip)
    {
        $key = Config::get('cache.keySmsIpCheck') . $ip;

        if (Redis::exists($key)) {
            return true;
        }
        return false;
    }

    /**
     * 发送邮箱验证 composer 安装的phpmailer
     */
    public function sendMail(Request $request)
    {
        $email = $request->get('user_string');
        $country_code = $request->get('country_code','86');
        $country_code = str_replace('+', '', $country_code);
        $scene = $request->get('scene');
        if (empty($email)) {
            return $this->error('邮箱不能为空');
        }
        $user = Users::getByString($email);
        if ($scene == 'login' || $scene == 'change_password' || $scene == 'reset_password') {
            if (empty($user)) {
                return $this->error('账号错误');
            }
        } else {
            if (!empty($user)) {
                return $this->error('账号已存在');
            }
        }
        //  从设置中取出值
        $username = Setting::getValueByKey('phpMailer_username', '');
        $host = Setting::getValueByKey('phpMailer_host', '');
        $password = Setting::getValueByKey('phpMailer_password', '');
        $port = Setting::getValueByKey('phpMailer_port', 465);
        $port == '' && $port = 465;
        //实例化phpMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->CharSet = "utf-8";
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "ssl";
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->Username = $username;
            $mail->Password = $password;//去开通的qq或163邮箱中找,这里用的不是邮箱的密码，而是开通之后的一个token
            $mail->setFrom($username, "[DAEX]");//设置邮件来源  //发件人
            $mail->Subject = "Verification code"; //邮件标题
            $code = $this->createSmsCode(4);
            $mail->MsgHTML('Your verification code is' . '【' . $code . '】');   //邮件内容
            $mail->addAddress($email);  //收件人（用户输入的邮箱）
            $res = $mail->send();
            if ($res) {
                session(['code@'. $country_code . $email => $code]);
                return $this->success('发送成功');
            } else {
                return $this->error('操作错误');
            }
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

    }
}