<?php
    namespace models\common\error;

    /**
     * 信息校验错误
     * Class InfoError
     * @package models\common\error
     */
    class InfoError extends CommonError {

        //通用错误
        const VERIFY_FAIL          = 8000;
        const NULL                 = 8001;
        const COIN_NOT_ENOUGH      = 8101;
        const ORDER_CLOSED         = 8201;
        const TIME_OUT             = 8301;
        const DATA_ERROR           = 8400;

        const ALBUM_NOT_EXIST      = 10401;//专辑不存在
        const ALBUM_IS_DEL         = 10404;//视频 被下线了
        const ALBUM_IS_FREE        = 10300;//专辑是免费的
        const ALBUM_IS_TVOD        = 10301;//专辑是只能点播的,应该跳到购买点播
        const ALBUM_NEED_VIP       = 10302;//只能会员看，应该跳到会员购买
        const ALBUM_NEED_PAY       = 10303;//专辑是需要付费的,应该跳到点播和会员两个购买页面
        const USER_NOT_EXIST       = 11401;//用户不存在
        const USER_NOT_VIP         = 11303;//用户不是VIP了，应该跳转到会员购买页
        const USER_COIN_NOT_ENOUGH = 11302;//用户金币不足,跳转到购买金币页
        const USER_NOT_ALLOWED     = 11404;//用户已经被封禁
        const USER_TOKEN_TIME_OUT  = 11502;//用户token超时
        const PACKAGE_NOT_EXIST    = 12401;//套餐不存在
        const GOODS_NOT_EXIST      = 12402;//商品不存在
        const GOODS_ERROR          = 12403;//商品信息有误
        const EVENT_EXPIRED        = 12502;//活动已经结束
        const FREQ_CTRL            = 13302;//frequency_control 频率控制
        const DEVICE_INFO_ERROR    = 14400;//device 设备信息有误
        const DEVICE_SN_EMPTY      = 14401;//设备信息序列号为空
        const CODE_EXPIRED         = 15501;//激活码已经失效/到期

        public function __construct($msg, $code, $debugMsg = '', $infos = []) {
            parent::__construct($msg, $code, $debugMsg, $infos, $this);
        }
    }

    ?>