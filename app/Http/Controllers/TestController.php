<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
class TestController extends Controller
{
    //
    public function valid(){
        echo $_GET['echostr'];
    }

    //接收服务器推送
    public function wxEven(){
        $content=file_get_contents('php://input');
        $data=simplexml_load_string($content);
        $wx_id =$data->ToUserName;
        $event=$data->Event;
        $openid=$data->FromUserName;
        $userInfo=$this->getUserinfo($openid);
        $info=[
            'openid'=>$userInfo['openid'],
            'nickname'=>$userInfo['nickname'],
            'country'=>$userInfo['country'],
            'province'=>$userInfo['province'],
            'city'=>$userInfo['city'],
            'headimgurl'=>$userInfo['headimgurl'],
            'create_time'=>$userInfo['subscribe_time'],
        ];
        $res=DB::table('user')->insert($info);
        if($res){
            echo '添加用户成功';
        }else{
            echo '添加用户失败';
        }
        echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$wx_id.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. '欢迎关注 '. $userInfo['nickname'] .']]></Content></xml>';

        echo 'SUCCESS';
    }

    //获取assess_token
    public function getAccesstoken(){
        $access_token=Redis::get('access_token');
        if(!$access_token){
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET');
            $response=file_get_contents($url);
            $access_token=json_decode($response,true);
            $access_token=$access_token['access_token'];
            Redis::set('access_token',$access_token);
            Redis::expire('access_token',3600);
        }
        return $access_token;
    }

    //获取用户信息
    public function getUserinfo($openid){

        $Userurl='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccesstoken().'&openid='.$openid.'&lang=zh_CN';
        $userInfo=file_get_contents($Userurl);
        $userInfo=json_decode($userInfo,true);
        return $userInfo;
    }

    //自定义菜单
    public function getMenu(){
        $mUrl='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccesstoken();
        //接口数据
        $arr=[
          'button'=>[
                [
                    "type"=>"click",
                    "name"=>"今日",
                    "key"=>"V1001_TODAY_MUSIC"
                ],
                [
                    "name"=>'菜单',
                    "sub_button"=>[
                        [
                            "type"=>"view",
                            "name"=>"搜索",
                            "url"=>"http://www.soso.com/"
                        ],

                       [
                           "type"=>"pic_sysphoto",
                            "name"=>"系统拍照发图",
                            "key"=>"rselfmenu_1_0",
                           "sub_button"=>[ ]
                       ],

                        [
                            "name"=> "发送位置",
                            "type"=> "location_select",
                            "key"=> "rselfmenu_2_0"
                        ]
                    ]
                ]
          ]
        ];
        $json_arr=json_encode($arr,JSON_UNESCAPED_UNICODE);
        //发送请求
        $clinet=new Client();
        $response=$clinet->request('POST',$mUrl,[
            'body'=>$json_arr
        ]);
        //处理响应
        $res_str=$response->getBody();
        $arr=json_decode($res_str,true);
        echo '<pre>';print_r($arr);
    }
}
