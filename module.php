<?php

defined('IN_IA') or exit('Access Denied');

class Weihu_menpiaoModule extends WeModule {

    public function settingsDisplay($settings){
        global $_GPC, $_W;

        load()->func('tpl');
        if (checksubmit('submit')) {
//            $cfg = $settings;
//            $cfg['album']['listtype'] = $_GPC['album']['listtype'];
//            $cfg['album']['toppic'] = $_GPC['toppic'];
//            $cfg['album']['status'] = intval($_GPC['status']);
//
//            if ($this->saveSettings($cfg)) {
//                message('微相册参数保存成功', 'refresh');
//            }
        }

        include $this->template('setting');
    }

}
