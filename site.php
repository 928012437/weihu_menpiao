<?php

defined('IN_IA') or exit('Access Denied');

class Weihu_menpiaoModuleSite extends WeModuleSite {

    public function doWebset()
    {
        global $_W, $_GPC;

        $uniacid=$_W['uniacid'];

        $set=pdo_get('weihu_menpiao_set',array('uniacid'=>$uniacid));

        if($_W['ispost']){
            $data=array(
                'uniacid'=>$uniacid,
                'tmpid'=>$_GPC['tmpid']
            );
            if(empty($set)){
                pdo_insert('weihu_menpiao_set',$data);
            }else{
                pdo_update('weihu_menpiao_set',$data,array('id'=>$set['id']));
            }
            message('修改成功',$this->createWebUrl('set'),'success');
        }

        include $this->template('set');
    }

    public function doWebadv()
    {
        global $_W, $_GPC;

        $uniacid=$_W['uniacid'];

        if(!empty($_GPC['del'])){
            pdo_delete('weihu_menpiao_adv',array('id'=>$_GPC['id']));
            message('删除成功',$this->createWebUrl('adv'),'success');die;
        }

        if($_W['ispost']){
            $data=array(
                'uniacid'=>$uniacid,
                'url'=>$_GPC['url'],
                'thumb'=>$_GPC['thumb']
            );
            if(empty($_GPC['id'])){
                pdo_insert('weihu_menpiao_adv',$data);
            }else{
                pdo_update('weihu_menpiao_adv',$data,array('id'=>$_GPC['id']));
            }
            message('操作成功',$this->createWebUrl('adv'),'success');
        }

        $list=pdo_getall('weihu_menpiao_adv',array('uniacid'=>$uniacid));

        include $this->template('adv');
    }
    public function doWebmember() {
        global $_W,$_GPC;

        if($_GPC['clearmember']==1){

            pdo_query("DELETE FROM ".tablename('weihu_menpiao_member')." WHERE uniacid = :uniacid and expiretime is null ", array(':uniacid' => $_W['uniacid']));
            message('清理成功',$this->createWebUrl('member'),'success');
        }

        if($_W['ispost']&&!empty($_GPC['mid'])){

            $data=array(
                'name'=>$_GPC['name'],
                'mobile'=>$_GPC['mobile'],
                'content'=>$_GPC['content'],
                'status'=>$_GPC['status'],
                'ismanage'=>$_GPC['ismanage'],
                'starttime'=>strtotime($_GPC['starttime']),
                'expiretime'=>strtotime($_GPC['expiretime']),
                'photo'=>$_GPC['photo'],
            );
            pdo_update('weihu_menpiao_member',$data,array('id'=>$_GPC['mid']));
            message('修改成功',$this->createWebUrl('member'),'success');
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' and uniacid=:uniacid ';
        $params = array(':uniacid' => $_W['uniacid']);

        if(!empty($_GPC['keyword'])){
            $condition.=" and (nickname like '%{$_GPC['keyword']}%' or name like '%{$_GPC['keyword']}%' or mobile like '%{$_GPC['keyword']}%' )";
        }
        if(!empty($_GPC['time'])){
            $condition.=" and createtime >= ".strtotime($_GPC['time']['start'])." and createtime < ".strtotime('+1 day',strtotime($_GPC['time']['end']));
        }

        if($_GPC['export']==1){
            $list = pdo_fetchall('SELECT * FROM ' . tablename('weihu_menpiao_member') . (' WHERE 1 ' . $condition . ' order by createtime desc  ') , $params);

            foreach ($list as &$v){
                $v['total1'] = pdo_fetchcolumn('SELECT sum(price) FROM ' . tablename('weihu_menpiao_membercard') . " where mid=:mid and price2=0 ", array(':mid'=>$v['id']));
                $v['total2'] = pdo_fetchcolumn('SELECT sum(price) FROM ' . tablename('weihu_menpiao_membercard') . " where mid=:mid and price2=-1 ", array(':mid'=>$v['id']));
                $v['total3'] = pdo_fetchcolumn('SELECT sum(price) FROM ' . tablename('weihu_menpiao_membercard') . " where mid=:mid ", array(':mid'=>$v['id']));
            }
            unset($v);

            require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';

            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("ctos")
                ->setLastModifiedBy("ctos")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);

            //设置行高度
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);

            //set font size bold
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);

            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            //设置水平居中
            $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            // set table header content
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '姓名')
                ->setCellValue('B1', '手机号')
                ->setCellValue('C1', '开始时间')
                ->setCellValue('D1', '过期时间')
                ->setCellValue('E1', '注册时间')
                ->setCellValue('F1', '购卡金额')
                ->setCellValue('G1', '后台充卡金额')
                ->setCellValue('H1', '总消费金额');

            // Miscellaneous glyphs, UTF-8

            for($i=0;$i<count($list);$i++){
                $objPHPExcel->getActiveSheet(0)
                    ->setCellValue('A'.($i+2), $list[$i]['name'])
                    ->setCellValue('B'.($i+2), $list[$i]['mobile'])
                    ->setCellValue('C'.($i+2), $list[$i]['starttime']?date('Y-m-d H:i',$list[$i]['starttime']):'未开通')
                    ->setCellValue('D'.($i+2), $list[$i]['expiretime']?date('Y-m-d H:i',$list[$i]['expiretime']):'未开通')
                    ->setCellValue('E'.($i+2), date('Y-m-d H:i',$list[$i]['createtime']))
                    ->setCellValue('F'.($i+2), $list[$i]['total1'])
                    ->setCellValue('G'.($i+2), $list[$i]['total2'])
                    ->setCellValue('H'.($i+2), $list[$i]['total3'])
                    ->getRowDimension($i+2)->setRowHeight(16);
            }

            //  sheet命名
            $objPHPExcel->getActiveSheet()->setTitle('用户记录');

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

            // excel头参数
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="用户记录('.date('Y年m月d日H时i分s秒').').xls"');  //日期为文件名后缀
            header('Cache-Control: max-age=0');

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式

            $objWriter->save('php://output');
        }

        $list = pdo_fetchall('SELECT * FROM ' . tablename('weihu_menpiao_member') . (' WHERE 1 ' . $condition . ' order by createtime desc limit ')  . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('weihu_menpiao_member') . (' WHERE 1 ' . $condition), $params);
        $pager = pagination($total, $pindex, $psize);

        include $this->template('member');
    }
    public function doWebmembercard(){
        global $_W,$_GPC;

        $member = pdo_get('weihu_menpiao_member',array('id'=>$_GPC['mid']));
        if($_W['ispost']){
            if(empty($_GPC['id'])&&(empty($_GPC['name'])||empty($_GPC['daynum']))){
                message('信息不完整');
            }

            if(empty($_GPC['id'])){
                $data=array(
                    'uniacid'=>$_W['uniacid'],
                    'mid'=>$_GPC['mid'],
                    'content'=>$_GPC['content'],
                    'price'=>$_GPC['price'],
                    'price2'=>-1,
                    'ckzhanghao'=>$_W['username']
                );
                $data['name']=$_GPC['name'];
                $data['daynum']=$_GPC['daynum'];
                $data['createtime']=time();

                $y = date("Y");

                $m = date("m");

                $d = date("d");

                $todayTime= mktime(0,0,0,$m,$d,$y);
                if(empty($member['expiretime'])){
                    $member['expiretime']=$todayTime;
                }
                if (empty($member['starttime'])){
                    $member['starttime']=$todayTime;
                }

                if(!empty($_GPC['starttime'])){
                    $starttime=strtotime($_GPC['starttime']);
                    $y = date("Y",$starttime);

                    $m = date("m",$starttime);

                    $d = date("d",$starttime);

                    $todayTime= mktime(0,0,0,$m,$d,$y);

                    $changetime=$member['expiretime']+$todayTime-$member['starttime']+($_GPC['daynum']*24*60*60);
                    $data['starttime']=strtotime($_GPC['starttime']);
                    $data['endtime']=$changetime;
                    pdo_update('weihu_menpiao_member',array('starttime'=>$todayTime,'expiretime'=>$changetime),array('id'=>$_GPC['mid']));
                }else{
                    $changetime=$member['expiretime']+($_GPC['daynum']*24*60*60);

                    $data['starttime']=0;
                    $data['endtime']=$changetime;
                    pdo_update('weihu_menpiao_member',array('starttime'=>$member['starttime'],'expiretime'=>$changetime),array('id'=>$_GPC['mid']));
                }
                pdo_insert('weihu_menpiao_membercard',$data);

                $mcid=pdo_insertid();
                $this->tpmessage($mcid);

            }else{
                pdo_update('weihu_menpiao_membercard',array('content'=>$_GPC['content']),array('id'=>$_GPC['id']));
            }

            message('操作成功',$this->createWebUrl('membercard',array('mid'=>$_GPC['mid'])),'success');
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' and uniacid=:uniacid ';
        $params = array(':uniacid' => $_W['uniacid']);

        if(!empty($_GPC['mid'])){
            $condition.=' and mid=:mid ';
            $params[':mid']=$_GPC['mid'];
        }

        $list = pdo_fetchall('SELECT * FROM ' . tablename('weihu_menpiao_membercard') . (' WHERE 1 ' . $condition . ' order by createtime desc limit ') . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('weihu_menpiao_membercard') . (' WHERE 1 ' . $condition), $params);
        $pager = pagination($total, $pindex, $psize);
        if(empty($_GPC['mid'])){
            foreach ($list as &$v){
                $v['member']=pdo_get('weihu_menpiao_member',array('id'=>$v['mid']));
            }
            unset($v);
        }

        include $this->template('membercard');
    }

    public function doWebmemberlog(){
        global $_W,$_GPC;

        if($_W['ispost']){
            $log=pdo_get('weihu_menpiao_member_log',array('id'=>$_GPC['id']));
            $mid=$log['mid'];
            $data=array(
                'uniacid'=>$_W['uniacid'],
                'content'=>$_GPC['content'],
            );
                pdo_update('weihu_menpiao_member_log',$data,array('id'=>$_GPC['id']));

            message('操作成功',$this->createWebUrl('memberlog',array('mid'=>$mid)),'success');
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' and uniacid=:uniacid ';
        $params = array(':uniacid' => $_W['uniacid']);

        if(!empty($_GPC['mid'])){
            $condition.=' and mid=:mid ';
            $params[':mid']=$_GPC['mid'];
        }

        $list = pdo_fetchall('SELECT * FROM ' . tablename('weihu_menpiao_member_log') . (' WHERE 1 ' . $condition . ' order by createtime desc limit ') . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('weihu_menpiao_member_log') . (' WHERE 1 ' . $condition), $params);
        $pager = pagination($total, $pindex, $psize);

        if(empty($_GPC['mid'])){
            foreach ($list as &$v){
                $v['member']=pdo_get('weihu_menpiao_member',array('id'=>$v['mid']));
            }
            unset($v);
        }

        include $this->template('memberlog');
    }

    public function doWebmemberlogexport(){
        global $_W,$_GPC;

        $condition = ' and uniacid=:uniacid ';
        $params = array(':uniacid' => $_W['uniacid']);
        if(!empty($_GPC['mid'])){
            $condition.=' and mid=:mid ';
            $params[':mid']=$_GPC['mid'];
        }

        $list = pdo_fetchall('SELECT * FROM ' . tablename('weihu_menpiao_member_log') . (' WHERE 1 ' . $condition . ' order by createtime desc ') , $params);

            foreach ($list as &$v){
                $v['member']=pdo_get('weihu_menpiao_member',array('id'=>$v['mid']));
                $v['member2']=pdo_get('weihu_menpiao_member',array('id'=>$v['manageid']));
                $v['createtime']=date('Y-m-d H:i',$v['createtime']);
            }
            unset($v);

        require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);

        //设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);


        //set font size bold
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        //设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        // set table header content
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '姓名')
            ->setCellValue('B1', '手机号')
            ->setCellValue('C1', 'ip')
            ->setCellValue('D1', '备注')
            ->setCellValue('E1', '扫码时间')
            ->setCellValue('F1', '管理员姓名')
            ->setCellValue('G1', '管理员手机号');


        // Miscellaneous glyphs, UTF-8

        for($i=0;$i<count($list);$i++){
            $objPHPExcel->getActiveSheet(0)
                ->setCellValue('A'.($i+2), $list[$i]['member']['name'])
                ->setCellValue('B'.($i+2), $list[$i]['member']['mobile'])
                ->setCellValue('C'.($i+2), $list[$i]['ip'])
                ->setCellValue('D'.($i+2), $list[$i]['content'])
                ->setCellValue('E'.($i+2), $list[$i]['createtime'])
                ->setCellValue('F'.($i+2), $list[$i]['member2']['name'])
                ->setCellValue('G'.($i+2), $list[$i]['member2']['mobile'])
                ->getRowDimension($i+2)->setRowHeight(16);
        }

        $member=pdo_get('weihu_menpiao_member',array('id'=>$_GPC['mid']));
        //  sheet命名
        $objPHPExcel->getActiveSheet()->setTitle((empty($member['name'])?'':$member['name']).'扫码记录');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // excel头参数
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.(empty($member['name'])?'':$member['name']).'扫码记录('.date('Y年m月d日H时i分s秒').').xls"');  //日期为文件名后缀
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式

        $objWriter->save('php://output');

    }

    public function doWebmembercardexport(){
        global $_W,$_GPC;

        $condition = ' and uniacid=:uniacid ';
        $params = array(':uniacid' => $_W['uniacid']);
        if(!empty($_GPC['mid'])){
            $condition.=' and mid=:mid ';
            $params[':mid']=$_GPC['mid'];
        }

        $list = pdo_fetchall('SELECT * FROM ' . tablename('weihu_menpiao_membercard') . (' WHERE 1 ' . $condition . ' order by createtime desc ') , $params);

        foreach ($list as &$v){
            $v['member']=pdo_get('weihu_menpiao_member',array('id'=>$v['mid']));
            $v['starttime']=$v['starttime']==0?'未改变':date('Y-m-d H:i:s',$v['starttime']);
            $v['endtime']=date('Y-m-d H:i',$v['endtime']);
            $v['createtime']=date('Y-m-d H:i',$v['createtime']);
            $v['price']=$v['price2']==-1?$v['price'].'(后台充卡)':$v['price'];
        }
        unset($v);

        require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(18);

        //设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);


        //set font size bold
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        //设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        // set table header content
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '姓名')
            ->setCellValue('B1', '手机号')
            ->setCellValue('C1', '卡名')
            ->setCellValue('D1', '天数')
            ->setCellValue('E1', '价格')
            ->setCellValue('F1', '备注')
            ->setCellValue('G1', '开始时间')
            ->setCellValue('H1', '结束时间')
            ->setCellValue('I1', '办卡时间')
            ->setCellValue('J1', '操作者');


        // Miscellaneous glyphs, UTF-8

        for($i=0;$i<count($list);$i++){
            $objPHPExcel->getActiveSheet(0)
                ->setCellValue('A'.($i+2), $list[$i]['member']['name'])
                ->setCellValue('B'.($i+2), $list[$i]['member']['mobile'])
                ->setCellValue('C'.($i+2), $list[$i]['name'])
                ->setCellValue('D'.($i+2), $list[$i]['daynum'])
                ->setCellValue('E'.($i+2), $list[$i]['price'])
                ->setCellValue('F'.($i+2), $list[$i]['content'])
                ->setCellValue('G'.($i+2), $list[$i]['starttime'])
                ->setCellValue('H'.($i+2), $list[$i]['endtime'])
                ->setCellValue('I'.($i+2), $list[$i]['createtime'])
                ->setCellValue('J'.($i+2), $list[$i]['ckzhanghao'])
                ->getRowDimension($i+2)->setRowHeight(16);
        }

        $member=pdo_get('weihu_menpiao_member',array('id'=>$_GPC['mid']));
        //  sheet命名
        $objPHPExcel->getActiveSheet()->setTitle((empty($member['name'])?'':$member['name']).'办卡记录');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // excel头参数
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.(empty($member['name'])?'':$member['name']).'办卡记录('.date('Y年m月d日H时i分s秒').').xls"');  //日期为文件名后缀
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式

        $objWriter->save('php://output');

    }

    public function doWebcard() {
        global $_W,$_GPC;

        if($_W['ispost']){
            if(empty($_GPC['name'])||empty($_GPC['daynum'])){
                message('信息不完整');
            }
            $data=array(
                'uniacid'=>$_W['uniacid'],
                'name'=>$_GPC['name'],
                'daynum'=>$_GPC['daynum'],
                'price'=>$_GPC['price'],
                'discount'=>$_GPC['discount'],
                'discountcolor'=>$_GPC['discountcolor'],
                'content'=>$_GPC['content'],
                'status'=>$_GPC['status'],
                'thumb'=>$_GPC['thumb'],
            );
            if(empty($_GPC['id'])){
                pdo_insert('weihu_menpiao_card',$data);
            }else{
                pdo_update('weihu_menpiao_card',$data,array('id'=>$_GPC['id']));
            }

            message('修改成功',$this->createWebUrl('card'),'success');
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' and uniacid=:uniacid ';
        $params = array(':uniacid' => $_W['uniacid']);

        $list = pdo_fetchall('SELECT * FROM ' . tablename('weihu_menpiao_card') . (' WHERE 1 ' . $condition . ' limit ') . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('weihu_menpiao_card') . (' WHERE 1 ' . $condition), $params);
        $pager = pagination($total, $pindex, $psize);

        include $this->template('card');
    }

    public function doWebwhiteip() {
        global $_W,$_GPC;

        if($_W['ispost']){
            if(empty($_GPC['ip'])){
                message('信息不完整');
            }
            $data=array(
                'uniacid'=>$_W['uniacid'],
                'ip'=>$_GPC['ip'],
                'content'=>$_GPC['content'],
            );
            if(empty($_GPC['id'])){
                $data['createtime']=time();
                pdo_insert('weihu_menpiao_whiteip',$data);
            }else{
                pdo_update('weihu_menpiao_whiteip',$data,array('id'=>$_GPC['id']));
            }

            message('修改成功',$this->createWebUrl('whiteip'),'success');
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' and uniacid=:uniacid ';
        $params = array(':uniacid' => $_W['uniacid']);

        $list = pdo_fetchall('SELECT * FROM ' . tablename('weihu_menpiao_whiteip') . (' WHERE 1 ' . $condition . '  ORDER BY createtime DESC limit ') . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('weihu_menpiao_whiteip') . (' WHERE 1 ' . $condition), $params);
        $pager = pagination($total, $pindex, $psize);

        include $this->template('whiteip');
    }

    public function doMobileindex2() {
        global $_W,$_GPC;
        if(empty($_W['openid'])){
            message('请在微信端打开');
        }

        $member=pdo_get('weihu_menpiao_member',array('openid'=>$_W['openid']));
//        $member=pdo_get('weihu_menpiao_member',array('id'=>4));

        if(empty($member)){
            header('location:'.$this->createMobileUrl('index'));
        }else if($member['status']==0){
            message('账号已禁用');
        }else if(empty($member['ismanage'])){
            message('账号无权限');
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 200;
        $condition = ' and uniacid=:uniacid and manageid=:manageid ';
        $params = array(':uniacid' => $_W['uniacid'],':manageid'=>$member['id']);

        $list = pdo_fetchall('SELECT * FROM ' . tablename('weihu_menpiao_member_log') . (' WHERE 1 ' . $condition . ' order by createtime desc limit ') . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('weihu_menpiao_member_log') . (' WHERE 1 ' . $condition), $params);
        $pager = pagination($total, $pindex, $psize);

        foreach ($list as &$v){
            $v['member']=pdo_get('weihu_menpiao_member',array('id'=>$v['mid']));
        }
        unset($v);

        include $this->template('index2');
    }

    public function doMobileindex() {
        global $_W,$_GPC;
        if(empty($_W['openid'])){
            message('请在微信端打开');
        }
        $member=pdo_get('weihu_menpiao_member',array('openid'=>$_W['openid']));
//        $member=pdo_get('weihu_menpiao_member',array('id'=>14));

        load()->model('mc');
        $fans=mc_oauth_userinfo();

        if(empty($member)){
            $data=array(
                'uniacid'=>$_W['uniacid'],
                'openid'=>$_W['openid'],
                'nickname'=>$fans['nickname'],
                'avatar'=>$fans['avatar'],
            );
            $data['createtime']=time();
            $data['status']=1;
            pdo_insert('weihu_menpiao_member',$data);
        }else if($member['status']==0){
            message('账号已禁用');
        }

        if($_W['ispost']){

            if(empty($_GPC['name'])||empty($_GPC['mobile'])){
//                message('信息不全');
                echo json_encode(array('status'=>0,'msg'=>'信息不全'));die;
            }

            if(!preg_match("/^1[3456789]{1}\d{9}$/",$_GPC['mobile'])){
                echo json_encode(array('status'=>0,'msg'=>'手机号格式错误'));die;
            }

            if(empty($member['photo'])&&empty($_FILES["imgfile1"]["tmp_name"])){
//                message('请上传照片');
                echo json_encode(array('status'=>0,'msg'=>'请上传照片'));die;
            }

            $data=array(
                'uniacid'=>$_W['uniacid'],
                'openid'=>$_W['openid'],
                'nickname'=>$_GPC['nickname'],
                'avatar'=>$_GPC['avatar'],
                'name'=>$_GPC['name'],
                'mobile'=>$_GPC['mobile'],
            );
            if(!empty($_FILES["imgfile1"]["tmp_name"])){
                $time=time();
                $sjtext1=rand(10,99);
                move_uploaded_file($_FILES["imgfile1"]["tmp_name"],dirname(__FILE__)."/imgfile/if1" . $sjtext1.$time .'.jpg');
                $data['photo']=$_W['siteroot'].'addons/weihu_menpiao/imgfile/if1'. $sjtext1.$time .'.jpg';

                if(intval($_FILES['imgfile1']['size']/1024)>300){
                    $url=$_W['siteroot'].'addons/weihu_menpiao/imgprocess.php';
                    $data2=array('file'=>'imgfile/if1'. $sjtext1.$time .'.jpg');
                    $this->imgprocesspost($url,$data2);
                }
            }
            if (empty($member)){
                $data['createtime']=time();
                $data['status']=1;
                pdo_insert('weihu_menpiao_member',$data);
            }else{
                pdo_update('weihu_menpiao_member',$data,array('id'=>$member['id']));
            }
//            message('修改成功','','success');
            echo json_encode(array('status'=>1));die;
        }

        $uniacid=$_W['uniacid'];
        $list=pdo_getall('weihu_menpiao_adv',array('uniacid'=>$uniacid));

        include $this->template('index');
    }

function imgprocesspost($url, $data) {

   //初使化init方法
   $ch = curl_init();

   //指定URL
   curl_setopt($ch, CURLOPT_URL, $url);

   //设定请求后返回结果
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

   //声明使用POST方式来进行发送
   curl_setopt($ch, CURLOPT_POST, 1);

   //发送什么数据呢
   curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


   //忽略证书
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

   //忽略header头信息
   curl_setopt($ch, CURLOPT_HEADER, 0);

   //设置超时时间
   curl_setopt($ch, CURLOPT_TIMEOUT, 10);

   //发送请求
   $output = curl_exec($ch);

   //关闭curl
   curl_close($ch);

   //返回数据
   return $output;
}

    public function doMobilegetqrcode(){
        global $_W,$_GPC;
        require_once IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
        $value=$this->createMobileUrl('qrcode',array('id'=>$_GPC['id'],'temptime'=>time()),true,true);
        $errorCorrectionLevel = 'L';  //容错级别
        $matrixPointSize = 5;      //生成图片大小
        //生成二维码图片
        $QR = QRcode::png($value,false,$errorCorrectionLevel, $matrixPointSize, 2);
    }

    public function doMobileqrcode(){
        global $_W,$_GPC;
        $ip=$this->getip();

        $whitejur=pdo_get('weihu_menpiao_whiteip',array('uniacid'=>$_W['uniacid'],'ip'=>$ip));


        $member=pdo_get('weihu_menpiao_member',array('id'=>$_GPC['id']));

        $member2=pdo_get('weihu_menpiao_member',array('openid'=>$_W['openid']));

        if(empty($whitejur)&&empty($member2['ismanage'])){
            if(empty($whitejur)){
                message("您的ip：{$ip}权限不足");
            }
            if(empty($member2['ismanage'])){
                message('账号权限不足');
            }
        }

        if(empty($member)){
            message('二维码错误');
        }

        if((time()-$_GPC['temptime'])>60){
            message('二维码超时');
        }

        $sclog=pdo_fetch("select * from ".tablename('weihu_menpiao_member_log')." where mid=:mid order by createtime desc",array(':mid'=>$member['id']));
        if((time()-$sclog['createtime'])<1){
            message('扫码频繁，请稍后再试');
        }

        $result=0;
        if(!empty($member['starttime'])&&$member['starttime']<time()&&!empty($member['expiretime'])&&$member['expiretime']>time()){
            $data=array(
                'uniacid'=>$_W['uniacid'],
                'mid'=>$member['id'],
                'manageid'=>$member2['id'],
                'ip'=>$whitejur['ip'],
                'createtime'=>time()
            );
            pdo_insert('weihu_menpiao_member_log',$data);
            $result=1;
        }

        include $this->template('qrcode');
    }

    function getip(){
        if (!empty($_SERVER['HTTP_CLIENT_IP']))

        {

            $ip_address = $_SERVER['HTTP_CLIENT_IP'];

        }

        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))

        {

            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];

        }

        else

        {

            $ip_address = $_SERVER['REMOTE_ADDR'];

        }

        return $ip_address;
    }

    public function doMobilemembercard() {
        global $_W,$_GPC;
        if(empty($_W['openid'])){
            message('请在微信端打开');
        }

        $member=pdo_get('weihu_menpiao_member',array('openid'=>$_W['openid']));
//        $member=pdo_get('weihu_menpiao_member',array('id'=>4));

        if(empty($member)){
           header('location:'.$this->createMobileUrl('index'));
        }else if($member['status']==0){
            message('账号已禁用');
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 200;
        $condition = ' and uniacid=:uniacid and mid=:mid ';
        $params = array(':uniacid' => $_W['uniacid'],':mid'=>$member['id']);

        $list = pdo_fetchall('SELECT * FROM ' . tablename('weihu_menpiao_membercard') . (' WHERE 1 ' . $condition . ' order by createtime desc limit ') . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('weihu_menpiao_membercard') . (' WHERE 1 ' . $condition), $params);
        $pager = pagination($total, $pindex, $psize);

        $cards=pdo_getall('weihu_menpiao_card',array('uniacid'=>$_W['uniacid'],'status'=>0));

        include $this->template('membercard');
    }

    public function doMobileWxpay(){
        global $_W,$_GPC;
        if(empty($_GPC['cardid'])){
            message('未选择卡');
        }

        $card=pdo_get('weihu_menpiao_card',array('id'=>$_GPC['cardid']));

        $params = array(
            'tid' => $card['id'].'|'.$_GPC['starttime'].'|MC'.time(),      //充值模块中的订单号，此号码用于业务模块中区分订单，交易的识别码
            'ordersn' => 'MC'.time(),  //收银台中显示的订单号
            'title' => '购卡：'.$card['name'],          //收银台中显示的标题
            'fee' => $card['price'],      //收银台中显示需要支付的金额,只能大于 0
            'user' => $_W['member']['uid'],     //付款用户, 付款的用户名(选填项)
        );

        include $this->template('pay');
    }

    public function payResult($params) {
        global $_W,$_GPC;

        if ($params['result'] == 'success' && $params['from'] == 'return') {
            $tidarr=explode('|',$params['tid']);
            $cardid=$tidarr[0];
            $starttime=strtotime($cardid[1]);
            $card=pdo_get('weihu_menpiao_card',array('id'=>$cardid));
            $member=pdo_get('weihu_menpiao_member',array('openid'=>$_W['openid']));

            $data=array(
                'uniacid'=>$_W['uniacid'],
                'mid'=>$member['id'],
                'content'=>$_GPC['content'],
                'name'=>$card['name'],
                'daynum'=>$card['daynum'],
                'price'=>$params['fee'],
                'price2'=>0,
                'createtime'=>time(),
            );

            $y = date("Y");

            $m = date("m");

            $d = date("d");

            $todayTime= mktime(0,0,0,$m,$d,$y);

            if(empty($member['expiretime'])){
                $member['expiretime']=$todayTime;
            }
            if (empty($member['starttime'])){
                $member['starttime']=$todayTime;
            }

            if(!empty($starttime)){
                $y = date("Y",$starttime);

                $m = date("m",$starttime);

                $d = date("d",$starttime);

                $todayTime= mktime(0,0,0,$m,$d,$y);
                $changetime=$member['expiretime']+$todayTime-$member['starttime']+($card['daynum']*24*60*60);
                $data['starttime']=strtotime($_GPC['starttime']);
                $data['endtime']=$changetime;
                pdo_update('weihu_menpiao_member',array('starttime'=>$todayTime,'expiretime'=>$changetime),array('id'=>$member['id']));
            }else{
                $changetime=$member['expiretime']+($card['daynum']*24*60*60);

                $data['starttime']=0;
                $data['endtime']=$changetime;
                pdo_update('weihu_menpiao_member',array('starttime'=>$member['starttime'],'expiretime'=>$changetime),array('id'=>$member['id']));
            }
            pdo_insert('weihu_menpiao_membercard',$data);

            $mcid=pdo_insertid();
            $this->tpmessage($mcid);

            message('支付成功',$this->createMobileUrl('index',array('paysuccess'=>1)),'success');
        }else{
            message('支付出错，购卡失败',$this->createMobileUrl('index'));
        }
    }

    function tpmessage($mcid)
    {
        global $_W;

        $uniacid=$_W['uniacid'];

        $set=pdo_get('weihu_menpiao_set',array('uniacid'=>$uniacid));
        $template_id=$set['tmpid'];
        $topcolor = '#FF0000';

        $mc=pdo_get('weihu_menpiao_membercard',array('id'=>$mcid));
        $member=pdo_get('weihu_menpiao_member',array('id'=>$mc['mid']));
        $url=$this->createMobileUrl('index',array(),true,true);

        if (!empty($template_id))
        {
            $datas = array(
                'first' => array(
                    'value' => '购卡通知', 'color' => '#173177'
                ),
                'keyword1' => array('value' => date('Y-m-d H:i:s',$mc['createtime']), 'color' => '#173177'),
                'keyword2' => array('value' => $mc['daynum'].'天', 'color' => '#173177'),
                'keyword3' => array('value' => intval(($mc['endtime']-$member['starttime'])/86400).'天', 'color' => '#173177'),
                'keyword4' => array('value' => date('Y-m-d H:i:s',$mc['endtime']), 'color' => '#173177'),
                'remark' => array(
                    'value' => '卡名：'.$mc['name'].',价格：'.$mc['price'].'，方式：'.($mc['price2']==-1?'后台充卡':'自助充卡'), 'color' => '#173177'
                ),
            );

            $data = json_encode($datas);

            load()->func('communication');

                $account_api = WeAccount::create();
                $tokens = $account_api->getAccessToken();

                if (empty($tokens))
                {
                    $account_api->clearAccessToken();

                    $tokens = $account_api->getAccessToken();
                }
                $postarr = '{"touser":"' . $member['openid'] . '","template_id":"' . $template_id . '","url":"' . $url . '","topcolor":"' . $topcolor . '","data":' . $data . '}';
                $res = ihttp_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $tokens, $postarr);

        }
    }
    function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

}