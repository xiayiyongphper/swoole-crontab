<?php

namespace generator\controllers;

use framework\components\ToolsAbstract;
use generator\models\LeCustomers;
use generator\models\LeMerchant;
use generator\models\LeMerchantStore;
use yii\console\Controller;

/**
 * Site controller
 */
class CustomerController extends Controller
{
    public function actionCreate()
    {
        $data = [
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170935', 'store_name' => '赣州小店1', 'password' => '20170935', 'storekeeper' => '赣州1号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170936', 'store_name' => '赣州小店2', 'password' => '20170936', 'storekeeper' => '赣州2号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170937', 'store_name' => '赣州小店3', 'password' => '20170937', 'storekeeper' => '赣州3号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170938', 'store_name' => '赣州小店4', 'password' => '20170938', 'storekeeper' => '赣州4号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170939', 'store_name' => '赣州小店5', 'password' => '20170939', 'storekeeper' => '赣州5号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170940', 'store_name' => '赣州小店6', 'password' => '20170940', 'storekeeper' => '赣州6号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170941', 'store_name' => '赣州小店7', 'password' => '20170941', 'storekeeper' => '赣州7号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170942', 'store_name' => '赣州小店8', 'password' => '20170942', 'storekeeper' => '赣州8号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170943', 'store_name' => '赣州小店9', 'password' => '20170943', 'storekeeper' => '赣州9号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170944', 'store_name' => '赣州小店10', 'password' => '20170944', 'storekeeper' => '赣州10号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170945', 'store_name' => '赣州小店11', 'password' => '20170945', 'storekeeper' => '赣州11号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'phone' => '13520170946', 'store_name' => '赣州小店12', 'password' => '20170946', 'storekeeper' => '赣州12号', 'contractor' => '曾庆超', 'contractor_id' => 226, 'area_id' => 343, 'created_at' => '2017-3-30',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170947', 'store_name' => '衡阳小店1', 'password' => '20170947', 'storekeeper' => '衡阳1号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170948', 'store_name' => '衡阳小店2', 'password' => '20170948', 'storekeeper' => '衡阳2号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170949', 'store_name' => '衡阳小店3', 'password' => '20170949', 'storekeeper' => '衡阳3号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170950', 'store_name' => '衡阳小店4', 'password' => '20170950', 'storekeeper' => '衡阳4号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170951', 'store_name' => '衡阳小店5', 'password' => '20170951', 'storekeeper' => '衡阳5号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170952', 'store_name' => '衡阳小店6', 'password' => '20170952', 'storekeeper' => '衡阳6号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170953', 'store_name' => '衡阳小店7', 'password' => '20170953', 'storekeeper' => '衡阳7号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170954', 'store_name' => '衡阳小店8', 'password' => '20170954', 'storekeeper' => '衡阳8号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170955', 'store_name' => '衡阳小店9', 'password' => '20170955', 'storekeeper' => '衡阳9号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170956', 'store_name' => '衡阳小店10', 'password' => '20170956', 'storekeeper' => '衡阳10号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170957', 'store_name' => '衡阳小店11', 'password' => '20170957', 'storekeeper' => '衡阳11号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170958', 'store_name' => '衡阳小店12', 'password' => '20170958', 'storekeeper' => '衡阳12号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170959', 'store_name' => '衡阳小店13', 'password' => '20170959', 'storekeeper' => '衡阳13号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170960', 'store_name' => '衡阳小店14', 'password' => '20170960', 'storekeeper' => '衡阳14号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170961', 'store_name' => '衡阳小店15', 'password' => '20170961', 'storekeeper' => '衡阳15号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170962', 'store_name' => '衡阳小店16', 'password' => '20170962', 'storekeeper' => '衡阳16号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170963', 'store_name' => '衡阳小店17', 'password' => '20170963', 'storekeeper' => '衡阳17号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170964', 'store_name' => '衡阳小店18', 'password' => '20170964', 'storekeeper' => '衡阳18号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'phone' => '13520170965', 'store_name' => '衡阳小店19', 'password' => '20170965', 'storekeeper' => '衡阳19号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 346, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520170966', 'store_name' => '江门小店1', 'password' => '20170966', 'storekeeper' => '江门1号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520170967', 'store_name' => '江门小店2', 'password' => '20170967', 'storekeeper' => '江门2号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520170968', 'store_name' => '江门小店3', 'password' => '20170968', 'storekeeper' => '江门3号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520170969', 'store_name' => '江门小店4', 'password' => '20170969', 'storekeeper' => '江门4号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520170970', 'store_name' => '江门小店5', 'password' => '20170970', 'storekeeper' => '江门5号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520170971', 'store_name' => '江门小店6', 'password' => '20170971', 'storekeeper' => '江门6号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520170972', 'store_name' => '江门小店7', 'password' => '20170972', 'storekeeper' => '江门7号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520170973', 'store_name' => '江门小店8', 'password' => '20170973', 'storekeeper' => '江门8号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520170974', 'store_name' => '江门小店9', 'password' => '20170974', 'storekeeper' => '江门9号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520171136', 'store_name' => '门小店10', 'password' => '20171136', 'storekeeper' => '江门10号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520171137', 'store_name' => '门小店11', 'password' => '20171137', 'storekeeper' => '江门11号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520171138', 'store_name' => '门小店12', 'password' => '20171138', 'storekeeper' => '江门12号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520171139', 'store_name' => '门小店13', 'password' => '20171139', 'storekeeper' => '江门13号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520171140', 'store_name' => '门小店14', 'password' => '20171140', 'storekeeper' => '江门14号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520171141', 'store_name' => '门小店15', 'password' => '20171141', 'storekeeper' => '江门15号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'phone' => '13520171142', 'store_name' => '门小店16', 'password' => '20171142', 'storekeeper' => '江门16号', 'contractor' => '常灿', 'contractor_id' => 4, 'area_id' => 349, 'created_at' => '2016-9-29',],
            ['province' => 420000, 'city' => 421000, 'phone' => '13520170975', 'store_name' => '荆州小店1', 'password' => '20170975', 'storekeeper' => '荆州1号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 345, 'created_at' => '2016-12-14',],
            ['province' => 420000, 'city' => 421000, 'phone' => '13520170976', 'store_name' => '荆州小店2', 'password' => '20170976', 'storekeeper' => '荆州2号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 345, 'created_at' => '2016-12-14',],
            ['province' => 420000, 'city' => 421000, 'phone' => '13520170977', 'store_name' => '荆州小店3', 'password' => '20170977', 'storekeeper' => '荆州3号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 345, 'created_at' => '2016-12-14',],
            ['province' => 420000, 'city' => 421000, 'phone' => '13520170978', 'store_name' => '荆州小店4', 'password' => '20170978', 'storekeeper' => '荆州4号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 345, 'created_at' => '2016-12-14',],
            ['province' => 420000, 'city' => 421000, 'phone' => '13520170979', 'store_name' => '荆州小店5', 'password' => '20170979', 'storekeeper' => '荆州5号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 345, 'created_at' => '2016-12-14',],
            ['province' => 420000, 'city' => 421000, 'phone' => '13520170980', 'store_name' => '荆州小店6', 'password' => '20170980', 'storekeeper' => '荆州6号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 345, 'created_at' => '2016-12-14',],
            ['province' => 420000, 'city' => 421000, 'phone' => '13520170981', 'store_name' => '荆州小店7', 'password' => '20170981', 'storekeeper' => '荆州7号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 345, 'created_at' => '2016-12-14',],
            ['province' => 420000, 'city' => 421000, 'phone' => '13520170982', 'store_name' => '荆州小店8', 'password' => '20170982', 'storekeeper' => '荆州8号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 345, 'created_at' => '2016-12-14',],
            ['province' => 420000, 'city' => 421000, 'phone' => '13520170983', 'store_name' => '荆州小店9', 'password' => '20170983', 'storekeeper' => '荆州9号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 345, 'created_at' => '2016-12-14',],
            ['province' => 420000, 'city' => 421000, 'phone' => '13520170984', 'store_name' => '荆州小店10', 'password' => '20170984', 'storekeeper' => '荆州10号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 345, 'created_at' => '2016-12-14',],
            ['province' => 440000, 'city' => 440900, 'phone' => '13520170985', 'store_name' => '茂名小店1', 'password' => '20170985', 'storekeeper' => '茂名1号', 'contractor' => '朱康', 'contractor_id' => 181, 'area_id' => 351, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440900, 'phone' => '13520170986', 'store_name' => '茂名小店2', 'password' => '20170986', 'storekeeper' => '茂名2号', 'contractor' => '朱康', 'contractor_id' => 181, 'area_id' => 351, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440900, 'phone' => '13520170987', 'store_name' => '茂名小店3', 'password' => '20170987', 'storekeeper' => '茂名3号', 'contractor' => '朱康', 'contractor_id' => 181, 'area_id' => 351, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440900, 'phone' => '13520170988', 'store_name' => '茂名小店4', 'password' => '20170988', 'storekeeper' => '茂名4号', 'contractor' => '朱康', 'contractor_id' => 181, 'area_id' => 351, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440900, 'phone' => '13520170989', 'store_name' => '茂名小店5', 'password' => '20170989', 'storekeeper' => '茂名5号', 'contractor' => '朱康', 'contractor_id' => 181, 'area_id' => 351, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440900, 'phone' => '13520170990', 'store_name' => '茂名小店6', 'password' => '20170990', 'storekeeper' => '茂名6号', 'contractor' => '朱康', 'contractor_id' => 181, 'area_id' => 351, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440900, 'phone' => '13520170991', 'store_name' => '茂名小店7', 'password' => '20170991', 'storekeeper' => '茂名7号', 'contractor' => '朱康', 'contractor_id' => 181, 'area_id' => 351, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440900, 'phone' => '13520170992', 'store_name' => '茂名小店8', 'password' => '20170992', 'storekeeper' => '茂名8号', 'contractor' => '朱康', 'contractor_id' => 181, 'area_id' => 351, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440900, 'phone' => '13520170993', 'store_name' => '茂名小店9', 'password' => '20170993', 'storekeeper' => '茂名9号', 'contractor' => '朱康', 'contractor_id' => 181, 'area_id' => 351, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440900, 'phone' => '13520170994', 'store_name' => '茂名小店10', 'password' => '20170994', 'storekeeper' => '茂名10号', 'contractor' => '朱康', 'contractor_id' => 181, 'area_id' => 351, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520170995', 'store_name' => '梅州小店1', 'password' => '20170995', 'storekeeper' => '梅州1号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520170996', 'store_name' => '梅州小店2', 'password' => '20170996', 'storekeeper' => '梅州2号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520170997', 'store_name' => '梅州小店3', 'password' => '20170997', 'storekeeper' => '梅州3号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520170998', 'store_name' => '梅州小店4', 'password' => '20170998', 'storekeeper' => '梅州4号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520170999', 'store_name' => '梅州小店5', 'password' => '20170999', 'storekeeper' => '梅州5号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520171000', 'store_name' => '梅州小店6', 'password' => '20171000', 'storekeeper' => '梅州6号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520171001', 'store_name' => '梅州小店7', 'password' => '20171001', 'storekeeper' => '梅州7号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520171002', 'store_name' => '梅州小店8', 'password' => '20171002', 'storekeeper' => '梅州8号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520171003', 'store_name' => '梅州小店9', 'password' => '20171003', 'storekeeper' => '梅州9号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520171130', 'store_name' => '梅州小店10', 'password' => '20171130', 'storekeeper' => '梅州10号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520171131', 'store_name' => '梅州小店11', 'password' => '20171131', 'storekeeper' => '梅州11号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520171132', 'store_name' => '梅州小店12', 'password' => '20171132', 'storekeeper' => '梅州12号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520171133', 'store_name' => '梅州小店13', 'password' => '20171133', 'storekeeper' => '梅州13号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520171134', 'store_name' => '梅州小店14', 'password' => '20171134', 'storekeeper' => '梅州14号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'phone' => '13520171135', 'store_name' => '梅州小店15', 'password' => '20171135', 'storekeeper' => '梅州15号', 'contractor' => '邓云', 'contractor_id' => 5, 'area_id' => 353, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171004', 'store_name' => '清远小店1', 'password' => '20171004', 'storekeeper' => '清远1号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171005', 'store_name' => '清远小店2', 'password' => '20171005', 'storekeeper' => '清远2号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171006', 'store_name' => '清远小店3', 'password' => '20171006', 'storekeeper' => '清远3号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171007', 'store_name' => '清远小店4', 'password' => '20171007', 'storekeeper' => '清远4号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171008', 'store_name' => '清远小店5', 'password' => '20171008', 'storekeeper' => '清远5号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171009', 'store_name' => '清远小店6', 'password' => '20171009', 'storekeeper' => '清远6号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171010', 'store_name' => '清远小店7', 'password' => '20171010', 'storekeeper' => '清远7号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171011', 'store_name' => '清远小店8', 'password' => '20171011', 'storekeeper' => '清远8号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171012', 'store_name' => '清远小店9', 'password' => '20171012', 'storekeeper' => '清远9号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171013', 'store_name' => '清远小店10', 'password' => '20171013', 'storekeeper' => '清远10号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171014', 'store_name' => '清远小店11', 'password' => '20171014', 'storekeeper' => '清远11号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171015', 'store_name' => '清远小店12', 'password' => '20171015', 'storekeeper' => '清远12号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171016', 'store_name' => '清远小店13', 'password' => '20171016', 'storekeeper' => '清远13号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171017', 'store_name' => '清远小店14', 'password' => '20171017', 'storekeeper' => '清远14号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171018', 'store_name' => '清远小店15', 'password' => '20171018', 'storekeeper' => '清远15号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171019', 'store_name' => '清远小店16', 'password' => '20171019', 'storekeeper' => '清远16号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171020', 'store_name' => '清远小店17', 'password' => '20171020', 'storekeeper' => '清远17号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171122', 'store_name' => '清远小店18', 'password' => '20171122', 'storekeeper' => '清远18号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171123', 'store_name' => '清远小店19', 'password' => '20171123', 'storekeeper' => '清远19号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171124', 'store_name' => '清远小店20', 'password' => '20171124', 'storekeeper' => '清远20号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171125', 'store_name' => '清远小店21', 'password' => '20171125', 'storekeeper' => '清远21号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171126', 'store_name' => '清远小店22', 'password' => '20171126', 'storekeeper' => '清远22号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171127', 'store_name' => '清远小店23', 'password' => '20171127', 'storekeeper' => '清远23号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171128', 'store_name' => '清远小店24', 'password' => '20171128', 'storekeeper' => '清远24号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'phone' => '13520171129', 'store_name' => '清远小店25', 'password' => '20171129', 'storekeeper' => '清远25号', 'contractor' => '温则庆', 'contractor_id' => 135, 'area_id' => 355, 'created_at' => '2016-9-28',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171021', 'store_name' => '泉州小店1', 'password' => '20171021', 'storekeeper' => '泉州1号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171022', 'store_name' => '泉州小店2', 'password' => '20171022', 'storekeeper' => '泉州2号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171023', 'store_name' => '泉州小店3', 'password' => '20171023', 'storekeeper' => '泉州3号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171024', 'store_name' => '泉州小店4', 'password' => '20171024', 'storekeeper' => '泉州4号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171025', 'store_name' => '泉州小店5', 'password' => '20171025', 'storekeeper' => '泉州5号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171026', 'store_name' => '泉州小店6', 'password' => '20171026', 'storekeeper' => '泉州6号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171027', 'store_name' => '泉州小店7', 'password' => '20171027', 'storekeeper' => '泉州7号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171028', 'store_name' => '泉州小店8', 'password' => '20171028', 'storekeeper' => '泉州8号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171029', 'store_name' => '泉州小店9', 'password' => '20171029', 'storekeeper' => '泉州9号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171030', 'store_name' => '泉州小店10', 'password' => '20171030', 'storekeeper' => '泉州10号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171031', 'store_name' => '泉州小店11', 'password' => '20171031', 'storekeeper' => '泉州11号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171032', 'store_name' => '泉州小店12', 'password' => '20171032', 'storekeeper' => '泉州12号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171033', 'store_name' => '泉州小店13', 'password' => '20171033', 'storekeeper' => '泉州13号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171034', 'store_name' => '泉州小店14', 'password' => '20171034', 'storekeeper' => '泉州14号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171035', 'store_name' => '泉州小店15', 'password' => '20171035', 'storekeeper' => '泉州15号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171036', 'store_name' => '泉州小店16', 'password' => '20171036', 'storekeeper' => '泉州16号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171037', 'store_name' => '泉州小店17', 'password' => '20171037', 'storekeeper' => '泉州17号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171038', 'store_name' => '泉州小店18', 'password' => '20171038', 'storekeeper' => '泉州18号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171039', 'store_name' => '泉州小店19', 'password' => '20171039', 'storekeeper' => '泉州19号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'phone' => '13520171040', 'store_name' => '泉州小店20', 'password' => '20171040', 'storekeeper' => '泉州20号', 'contractor' => '廖文辉', 'contractor_id' => 138, 'area_id' => 341, 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 440200, 'phone' => '13520171041', 'store_name' => '韶关小店1', 'password' => '20171041', 'storekeeper' => '韶关1号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 347, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440200, 'phone' => '13520171042', 'store_name' => '韶关小店2', 'password' => '20171042', 'storekeeper' => '韶关2号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 347, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440200, 'phone' => '13520171043', 'store_name' => '韶关小店3', 'password' => '20171043', 'storekeeper' => '韶关3号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 347, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440200, 'phone' => '13520171044', 'store_name' => '韶关小店4', 'password' => '20171044', 'storekeeper' => '韶关4号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 347, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440200, 'phone' => '13520171045', 'store_name' => '韶关小店5', 'password' => '20171045', 'storekeeper' => '韶关5号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 347, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440200, 'phone' => '13520171046', 'store_name' => '韶关小店6', 'password' => '20171046', 'storekeeper' => '韶关6号', 'contractor' => '朱祥', 'contractor_id' => 339, 'area_id' => 347, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440300, 'phone' => '13520161047', 'store_name' => '深圳小店1', 'password' => '20171047', 'storekeeper' => '深圳1号', 'contractor' => '张洲龙', 'contractor_id' => 146, 'area_id' => 348, 'created_at' => '2017-7-12',],
            ['province' => 440000, 'city' => 440300, 'phone' => '13520161048', 'store_name' => '深圳小店2', 'password' => '20171048', 'storekeeper' => '深圳2号', 'contractor' => '张洲龙', 'contractor_id' => 146, 'area_id' => 348, 'created_at' => '2017-7-12',],
            ['province' => 440000, 'city' => 440300, 'phone' => '13520161049', 'store_name' => '深圳小店3', 'password' => '20171049', 'storekeeper' => '深圳3号', 'contractor' => '张洲龙', 'contractor_id' => 146, 'area_id' => 348, 'created_at' => '2017-7-12',],
            ['province' => 440000, 'city' => 440300, 'phone' => '13520161050', 'store_name' => '深圳小店4', 'password' => '20171050', 'storekeeper' => '深圳4号', 'contractor' => '张洲龙', 'contractor_id' => 146, 'area_id' => 348, 'created_at' => '2017-7-12',],
            ['province' => 440000, 'city' => 440300, 'phone' => '13520171051', 'store_name' => '深圳小店5', 'password' => '20171051', 'storekeeper' => '深圳5号', 'contractor' => '张洲龙', 'contractor_id' => 146, 'area_id' => 348, 'created_at' => '2017-7-12',],
            ['province' => 440000, 'city' => 441700, 'phone' => '13520171052', 'store_name' => '阳江小店1', 'password' => '20171052', 'storekeeper' => '阳江1号', 'contractor' => '杨熹', 'contractor_id' => 94, 'area_id' => 354, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441700, 'phone' => '13520171053', 'store_name' => '阳江小店2', 'password' => '20171053', 'storekeeper' => '阳江2号', 'contractor' => '杨熹', 'contractor_id' => 94, 'area_id' => 354, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441700, 'phone' => '13520171054', 'store_name' => '阳江小店3', 'password' => '20171054', 'storekeeper' => '阳江3号', 'contractor' => '杨熹', 'contractor_id' => 94, 'area_id' => 354, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441700, 'phone' => '13520171055', 'store_name' => '阳江小店4', 'password' => '20171055', 'storekeeper' => '阳江4号', 'contractor' => '杨熹', 'contractor_id' => 94, 'area_id' => 354, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441700, 'phone' => '13520171056', 'store_name' => '阳江小店5', 'password' => '20171056', 'storekeeper' => '阳江5号', 'contractor' => '杨熹', 'contractor_id' => 94, 'area_id' => 354, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441700, 'phone' => '13520171057', 'store_name' => '阳江小店6', 'password' => '20171057', 'storekeeper' => '阳江6号', 'contractor' => '杨熹', 'contractor_id' => 94, 'area_id' => 354, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441700, 'phone' => '13520171058', 'store_name' => '阳江小店7', 'password' => '20171058', 'storekeeper' => '阳江7号', 'contractor' => '杨熹', 'contractor_id' => 94, 'area_id' => 354, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441700, 'phone' => '13520171059', 'store_name' => '阳江小店8', 'password' => '20171059', 'storekeeper' => '阳江8号', 'contractor' => '杨熹', 'contractor_id' => 94, 'area_id' => 354, 'created_at' => '2016-9-29',],
            ['province' => 420000, 'city' => 420500, 'phone' => '13520171060', 'store_name' => '宜昌小店1', 'password' => '20171060', 'storekeeper' => '宜昌1号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 344, 'created_at' => '2016-12-13',],
            ['province' => 420000, 'city' => 420500, 'phone' => '13520171061', 'store_name' => '宜昌小店2', 'password' => '20171061', 'storekeeper' => '宜昌2号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 344, 'created_at' => '2016-12-13',],
            ['province' => 420000, 'city' => 420500, 'phone' => '13520171062', 'store_name' => '宜昌小店3', 'password' => '20171062', 'storekeeper' => '宜昌3号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 344, 'created_at' => '2016-12-13',],
            ['province' => 420000, 'city' => 420500, 'phone' => '13520171063', 'store_name' => '宜昌小店4', 'password' => '20171063', 'storekeeper' => '宜昌4号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 344, 'created_at' => '2016-12-13',],
            ['province' => 420000, 'city' => 420500, 'phone' => '13520171064', 'store_name' => '宜昌小店5', 'password' => '20171064', 'storekeeper' => '宜昌5号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 344, 'created_at' => '2016-12-13',],
            ['province' => 420000, 'city' => 420500, 'phone' => '13520171065', 'store_name' => '宜昌小店6', 'password' => '20171065', 'storekeeper' => '宜昌6号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 344, 'created_at' => '2016-12-13',],
            ['province' => 420000, 'city' => 420500, 'phone' => '13520171066', 'store_name' => '宜昌小店7', 'password' => '20171066', 'storekeeper' => '宜昌7号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 344, 'created_at' => '2016-12-13',],
            ['province' => 420000, 'city' => 420500, 'phone' => '13520171067', 'store_name' => '宜昌小店8', 'password' => '20171067', 'storekeeper' => '宜昌8号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 344, 'created_at' => '2016-12-13',],
            ['province' => 420000, 'city' => 420500, 'phone' => '13520171068', 'store_name' => '宜昌小店9', 'password' => '20171068', 'storekeeper' => '宜昌9号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 344, 'created_at' => '2016-12-13',],
            ['province' => 420000, 'city' => 420500, 'phone' => '13520171069', 'store_name' => '宜昌小店10', 'password' => '20171069', 'storekeeper' => '宜昌10号', 'contractor' => '胡志龙', 'contractor_id' => 229, 'area_id' => 344, 'created_at' => '2016-12-13',],
            ['province' => 350000, 'city' => 350600, 'phone' => '13520171080', 'store_name' => '漳州小店1', 'password' => '20171080', 'storekeeper' => '漳州1号', 'contractor' => '陈志强', 'contractor_id' => 319, 'area_id' => 342, 'created_at' => '2016-12-24',],
            ['province' => 350000, 'city' => 350600, 'phone' => '13520171081', 'store_name' => '漳州小店2', 'password' => '20171081', 'storekeeper' => '漳州2号', 'contractor' => '陈志强', 'contractor_id' => 319, 'area_id' => 342, 'created_at' => '2016-12-24',],
            ['province' => 350000, 'city' => 350600, 'phone' => '13520171082', 'store_name' => '漳州小店3', 'password' => '20171082', 'storekeeper' => '漳州3号', 'contractor' => '陈志强', 'contractor_id' => 319, 'area_id' => 342, 'created_at' => '2016-12-24',],
            ['province' => 350000, 'city' => 350600, 'phone' => '13520171083', 'store_name' => '漳州小店4', 'password' => '20171083', 'storekeeper' => '漳州4号', 'contractor' => '陈志强', 'contractor_id' => 319, 'area_id' => 342, 'created_at' => '2016-12-24',],
            ['province' => 350000, 'city' => 350600, 'phone' => '13520171084', 'store_name' => '漳州小店5', 'password' => '20171084', 'storekeeper' => '漳州5号', 'contractor' => '陈志强', 'contractor_id' => 319, 'area_id' => 342, 'created_at' => '2016-12-24',],
            ['province' => 350000, 'city' => 350600, 'phone' => '13520171085', 'store_name' => '漳州小店6', 'password' => '20171085', 'storekeeper' => '漳州6号', 'contractor' => '陈志强', 'contractor_id' => 319, 'area_id' => 342, 'created_at' => '2016-12-24',],
            ['province' => 350000, 'city' => 350600, 'phone' => '13520171086', 'store_name' => '漳州小店7', 'password' => '20171086', 'storekeeper' => '漳州7号', 'contractor' => '陈志强', 'contractor_id' => 319, 'area_id' => 342, 'created_at' => '2016-12-24',],
            ['province' => 350000, 'city' => 350600, 'phone' => '13520171087', 'store_name' => '漳州小店8', 'password' => '20171087', 'storekeeper' => '漳州8号', 'contractor' => '陈志强', 'contractor_id' => 319, 'area_id' => 342, 'created_at' => '2016-12-24',],
            ['province' => 350000, 'city' => 350600, 'phone' => '13520171088', 'store_name' => '漳州小店9', 'password' => '20171088', 'storekeeper' => '漳州9号', 'contractor' => '陈志强', 'contractor_id' => 319, 'area_id' => 342, 'created_at' => '2016-12-24',],
            ['province' => 350000, 'city' => 350600, 'phone' => '13520171089', 'store_name' => '漳州小店10', 'password' => '20171089', 'storekeeper' => '漳州10号', 'contractor' => '陈志强', 'contractor_id' => 319, 'area_id' => 342, 'created_at' => '2016-12-24',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171090', 'store_name' => '中山小店1', 'password' => '20171090', 'storekeeper' => '中山1号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171091', 'store_name' => '中山小店2', 'password' => '20171091', 'storekeeper' => '中山2号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171092', 'store_name' => '中山小店3', 'password' => '20171092', 'storekeeper' => '中山3号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171093', 'store_name' => '中山小店4', 'password' => '20171093', 'storekeeper' => '中山4号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171094', 'store_name' => '中山小店5', 'password' => '20171094', 'storekeeper' => '中山5号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171095', 'store_name' => '中山小店6', 'password' => '20171095', 'storekeeper' => '中山6号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171096', 'store_name' => '中山小店7', 'password' => '20171096', 'storekeeper' => '中山7号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171097', 'store_name' => '中山小店8', 'password' => '20171097', 'storekeeper' => '中山8号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171098', 'store_name' => '中山小店9', 'password' => '20171098', 'storekeeper' => '中山9号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171099', 'store_name' => '中山小店10', 'password' => '20171099', 'storekeeper' => '中山10号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171100', 'store_name' => '中山小店11', 'password' => '20171100', 'storekeeper' => '中山11号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171101', 'store_name' => '中山小店12', 'password' => '20171101', 'storekeeper' => '中山12号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171102', 'store_name' => '中山小店13', 'password' => '20171102', 'storekeeper' => '中山13号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171103', 'store_name' => '中山小店14', 'password' => '20171103', 'storekeeper' => '中山14号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'phone' => '13520171104', 'store_name' => '中山小店15', 'password' => '20171104', 'storekeeper' => '中山15号', 'contractor' => '林秋霞', 'contractor_id' => 13, 'area_id' => 356, 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171105', 'store_name' => '肇庆小店1', 'password' => '20171105', 'storekeeper' => '肇庆1号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171106', 'store_name' => '肇庆小店2', 'password' => '20171106', 'storekeeper' => '肇庆2号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171107', 'store_name' => '肇庆小店3', 'password' => '20171107', 'storekeeper' => '肇庆3号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171108', 'store_name' => '肇庆小店4', 'password' => '20171108', 'storekeeper' => '肇庆4号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171109', 'store_name' => '肇庆小店5', 'password' => '20171109', 'storekeeper' => '肇庆5号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171110', 'store_name' => '肇庆小店6', 'password' => '20171110', 'storekeeper' => '肇庆6号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171111', 'store_name' => '肇庆小店7', 'password' => '20171111', 'storekeeper' => '肇庆7号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171112', 'store_name' => '肇庆小店8', 'password' => '20171112', 'storekeeper' => '肇庆8号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171113', 'store_name' => '肇庆小店9', 'password' => '20171113', 'storekeeper' => '肇庆9号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171114', 'store_name' => '肇庆小店10', 'password' => '20171114', 'storekeeper' => '肇庆10号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171115', 'store_name' => '肇庆小店11', 'password' => '20171115', 'storekeeper' => '肇庆11号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171116', 'store_name' => '肇庆小店12', 'password' => '20171116', 'storekeeper' => '肇庆12号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171117', 'store_name' => '肇庆小店13', 'password' => '20171117', 'storekeeper' => '肇庆13号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171118', 'store_name' => '肇庆小店14', 'password' => '20171118', 'storekeeper' => '肇庆14号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171119', 'store_name' => '肇庆小店15', 'password' => '20171119', 'storekeeper' => '肇庆15号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171120', 'store_name' => '肇庆小店16', 'password' => '20171120', 'storekeeper' => '肇庆16号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520171121', 'store_name' => '肇庆小店17', 'password' => '20171121', 'storekeeper' => '肇庆17号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520161121', 'store_name' => '肇庆小店18', 'password' => '20161121', 'storekeeper' => '肇庆18号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520161122', 'store_name' => '肇庆小店19', 'password' => '20161122', 'storekeeper' => '肇庆19号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'phone' => '13520161123', 'store_name' => '肇庆小店20', 'password' => '20161123', 'storekeeper' => '肇庆20号', 'contractor' => '陈智淑', 'contractor_id' => 15, 'area_id' => 352, 'created_at' => '2016-9-29',],
        ];
        foreach ($data as $key => $item) {
            $this->updatePassword($item['phone'], $item['password']);
//            $day = $item['created_at'];
//            $timestamp = $this->getCreatedAtTimestamp($day);
//            $customer = new LeCustomers();
//            $customer->username = $item['phone'];
//            $customer->setPassword($item['password']);
//            $customer->province = $item['province'];
//            $customer->city = $item['city'];
//            $customer->district = 0;
//            $customer->area_id = $item['area_id'];
//            $customer->address = '乐来科技';
//            $customer->store_name = $item['store_name'];
//            $customer->storekeeper = $item['storekeeper'];
//            $customer->phone = $item['phone'];
//            $customer->reject_count = 0;
//            $customer->auth_token = '';
//            $customer->status = LeCustomers::STATUS_PASSED;
//            $customer->contractor = $item['contractor'];
//            $customer->contractor_id = $item['contractor_id'];
//            $customer->created_at = date('Y-m-d H:i:s', $timestamp);
//            $customer->apply_at = date('Y-m-d H:i:s', strtotime("+5minute", $timestamp));
//            $customer->updated_at = $customer->apply_at;
//            $customer->first_order_id = 0;
//            $customer->orders_total_price = 0;
//            $customer->level = 0;
//            $customer->state = 2;
//            $customer->tag_id = 7;
//            $customer->save();
        }
    }

    protected function updatePassword($phone, $password)
    {
        $customer = LeCustomers::findOne(['phone' => $phone]);
        $customer->setPassword(md5($password));
        $customer->save();
    }


    /**
     * 更改用户的注册时间
     */
    public function actionUpdateCustomerCreatedAt()
    {
        $data = [
            ['entity_id' => 41507, 'created_at' => '2016-12-3',],
            ['entity_id' => 41508, 'created_at' => '2016-12-3',],
            ['entity_id' => 41509, 'created_at' => '2016-12-3',],
            ['entity_id' => 41510, 'created_at' => '2016-12-3',],
            ['entity_id' => 41511, 'created_at' => '2016-12-3',],
            ['entity_id' => 41758, 'created_at' => '2016-12-3',],
            ['entity_id' => 41758, 'created_at' => '2016-12-3',],
            ['entity_id' => 41761, 'created_at' => '2016-12-3',],
            ['entity_id' => 41762, 'created_at' => '2016-12-3',],
            ['entity_id' => 41764, 'created_at' => '2016-12-3',],
            ['entity_id' => 41768, 'created_at' => '2016-12-3',],
            ['entity_id' => 41768, 'created_at' => '2016-12-3',],
            ['entity_id' => 41771, 'created_at' => '2016-12-3',],
            ['entity_id' => 41772, 'created_at' => '2016-12-3',],
            ['entity_id' => 41773, 'created_at' => '2016-12-3',],
            ['entity_id' => 41774, 'created_at' => '2016-12-3',],
            ['entity_id' => 41775, 'created_at' => '2016-12-3',],
            ['entity_id' => 41582, 'created_at' => '2016-9-30',],
            ['entity_id' => 41583, 'created_at' => '2016-9-30',],
            ['entity_id' => 41584, 'created_at' => '2016-9-30',],
            ['entity_id' => 41585, 'created_at' => '2016-9-30',],
            ['entity_id' => 41755, 'created_at' => '2016-9-30',],
            ['entity_id' => 41756, 'created_at' => '2016-9-30',],
            ['entity_id' => 41757, 'created_at' => '2016-9-30',],
            ['entity_id' => 41760, 'created_at' => '2016-9-30',],
            ['entity_id' => 41763, 'created_at' => '2016-9-30',],
            ['entity_id' => 41765, 'created_at' => '2016-9-30',],
        ];
        foreach ($data as $key => $item) {
            $day = $item['created_at'];
            $timestamp = $this->getCreatedAtTimestamp($day);
            $customer = LeCustomers::findOne(['entity_id' => $item['entity_id']]);
            $customer->created_at = date('Y-m-d H:i:s', $timestamp);
            $customer->updated_at = date('Y-m-d H:i:s', $timestamp);
            $customer->apply_at = date('Y-m-d H:i:s', strtotime("+30minute", $timestamp));
            $customer->save();
        }
    }

    /**
     *
     * @param $day
     * @return false|int
     */
    protected function getCreatedAtTimestamp($day)
    {
        $time = strtotime($day);
        $day = date('j', $time);
        $month = date('n', $time);
        $year = date('Y', $time);
        return mktime(mt_rand(0, 15), mt_rand(0, 59), mt_rand(0, 59), $month, $day, $year);
    }

    public function actionCreateMerchantStore()
    {
        $data = [
            ['province' => 440000, 'city' => 442000, 'region_id' => 2, 'name' => '中山小店1', 'phone' => '13520170921', 'password' => '20170921', 'area_id' => '356', 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 442000, 'region_id' => 2, 'name' => '中山小店2', 'phone' => '13520170922', 'password' => '20170922', 'area_id' => '356', 'created_at' => '2016-12-20',],
            ['province' => 440000, 'city' => 441200, 'region_id' => 2, 'name' => '肇庆小店1', 'phone' => '13520170968', 'password' => '20170968', 'area_id' => '352', 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'region_id' => 2, 'name' => '肇庆小店2', 'phone' => '13520170969', 'password' => '20170969', 'area_id' => '352', 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441200, 'region_id' => 2, 'name' => '肇庆小店3', 'phone' => '13520170970', 'password' => '20170970', 'area_id' => '352', 'created_at' => '2016-9-29',],
            ['province' => 350000, 'city' => 350600, 'region_id' => 2, 'name' => '漳州小店1', 'phone' => '13520170928', 'password' => '20170928', 'area_id' => '342', 'created_at' => '2016-12-24',],
            ['province' => 350000, 'city' => 350600, 'region_id' => 2, 'name' => '漳州小店2', 'phone' => '13520170929', 'password' => '20170929', 'area_id' => '342', 'created_at' => '2016-12-24',],
            ['province' => 420000, 'city' => 420500, 'region_id' => 2, 'name' => '宜昌小店1', 'phone' => '13520170934', 'password' => '20170934', 'area_id' => '344', 'created_at' => '2016-12-13',],
            ['province' => 420000, 'city' => 420500, 'region_id' => 2, 'name' => '宜昌小店2', 'phone' => '13520170935', 'password' => '20170935', 'area_id' => '344', 'created_at' => '2016-12-13',],
            ['province' => 440000, 'city' => 441700, 'region_id' => 2, 'name' => '阳江小店1', 'phone' => '13520170937', 'password' => '20170937', 'area_id' => '354', 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 441700, 'region_id' => 2, 'name' => '阳江小店2', 'phone' => '13520170938', 'password' => '20170938', 'area_id' => '354', 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440200, 'region_id' => 2, 'name' => '韶关小店1', 'phone' => '13520170940', 'password' => '20170940', 'area_id' => '347', 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440200, 'region_id' => 2, 'name' => '韶关小店2', 'phone' => '13520170941', 'password' => '20170941', 'area_id' => '347', 'created_at' => '2016-9-29',],
            ['province' => 350000, 'city' => 350500, 'region_id' => 2, 'name' => '泉州小店1', 'phone' => '13520170943', 'password' => '20170943', 'area_id' => '341', 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'region_id' => 2, 'name' => '泉州小店2', 'phone' => '13520170944', 'password' => '20170944', 'area_id' => '341', 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'region_id' => 2, 'name' => '泉州小店3', 'phone' => '13520170971', 'password' => '20170971', 'area_id' => '341', 'created_at' => '2016-12-17',],
            ['province' => 350000, 'city' => 350500, 'region_id' => 2, 'name' => '泉州小店4', 'phone' => '13520170972', 'password' => '20170972', 'area_id' => '341', 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441800, 'region_id' => 2, 'name' => '清远小店1', 'phone' => '13520170947', 'password' => '20170947', 'area_id' => '355', 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'region_id' => 2, 'name' => '清远小店2', 'phone' => '13520170948', 'password' => '20170948', 'area_id' => '355', 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'region_id' => 2, 'name' => '清远小店3', 'phone' => '13520170973', 'password' => '20170973', 'area_id' => '355', 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441800, 'region_id' => 2, 'name' => '清远小店4', 'phone' => '13520170974', 'password' => '20170974', 'area_id' => '355', 'created_at' => '2016-9-28',],
            ['province' => 440000, 'city' => 441400, 'region_id' => 2, 'name' => '梅州小店1', 'phone' => '13520170951', 'password' => '20170951', 'area_id' => '353', 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'region_id' => 2, 'name' => '梅州小店2', 'phone' => '13520170975', 'password' => '20170975', 'area_id' => '353', 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 441400, 'region_id' => 2, 'name' => '梅州小店3', 'phone' => '13520170976', 'password' => '20170976', 'area_id' => '353', 'created_at' => '2016-12-17',],
            ['province' => 440000, 'city' => 440900, 'region_id' => 2, 'name' => '茂名小店1', 'phone' => '13520170954', 'password' => '20170954', 'area_id' => '351', 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440900, 'region_id' => 2, 'name' => '茂名小店2', 'phone' => '13520170955', 'password' => '20170955', 'area_id' => '351', 'created_at' => '2016-9-29',],
            ['province' => 420000, 'city' => 421000, 'region_id' => 2, 'name' => '荆州小店1', 'phone' => '13520170956', 'password' => '20170956', 'area_id' => '345', 'created_at' => '2016-12-14',],
            ['province' => 420000, 'city' => 421000, 'region_id' => 2, 'name' => '荆州小店2', 'phone' => '13520170957', 'password' => '20170957', 'area_id' => '345', 'created_at' => '2016-12-14',],
            ['province' => 440000, 'city' => 440700, 'region_id' => 2, 'name' => '江门小店1', 'phone' => '13520170958', 'password' => '20170958', 'area_id' => '349', 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'region_id' => 2, 'name' => '江门小店2', 'phone' => '13520170977', 'password' => '20170977', 'area_id' => '349', 'created_at' => '2016-9-29',],
            ['province' => 440000, 'city' => 440700, 'region_id' => 2, 'name' => '江门小店3', 'phone' => '13520170978', 'password' => '20170978', 'area_id' => '349', 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'region_id' => 2, 'name' => '衡阳小店1', 'phone' => '13520170961', 'password' => '20170961', 'area_id' => '346', 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'region_id' => 2, 'name' => '衡阳小店2', 'phone' => '13520170962', 'password' => '20170962', 'area_id' => '346', 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'region_id' => 2, 'name' => '衡阳小店3', 'phone' => '13520170963', 'password' => '20170963', 'area_id' => '346', 'created_at' => '2016-9-29',],
            ['province' => 430000, 'city' => 430400, 'region_id' => 2, 'name' => '衡阳小店4', 'phone' => '13520170964', 'password' => '20170964', 'area_id' => '346', 'created_at' => '2016-9-29',],
            ['province' => 360000, 'city' => 360700, 'region_id' => 2, 'name' => '赣州小店1', 'phone' => '13520170965', 'password' => '20170965', 'area_id' => '343', 'created_at' => '2017-3-30',],
            ['province' => 360000, 'city' => 360700, 'region_id' => 2, 'name' => '赣州小店2', 'phone' => '13520170966', 'password' => '20170966', 'area_id' => '343', 'created_at' => '2017-3-30',],
            ['province' => 440000, 'city' => 440300, 'region_id' => 2, 'name' => '深圳小店1', 'phone' => '13520170967', 'password' => '20170967', 'area_id' => '348', 'created_at' => '2017-7-12',],
        ];

        foreach ($data as $item) {
            $day = $item['created_at'];
            $timestamp = $this->getCreatedAtTimestamp($day);
            $merchant = new LeMerchant();
            $merchant->setPassword(md5($item['password']));
            $merchant->name = $item['name'];
            $merchant->real_name = $item['name'];
            $merchant->phone = $item['phone'];
            $merchant->region_id = 2;
            $merchant->status = 1;
            $merchant->created_at = date('Y-m-d H:i:s', $timestamp);
            $merchant->updated_at = date('Y-m-d H:i:s', $timestamp);
            $merchant->is_recommend = 0;
            $merchant->save();
            $store = new LeMerchantStore();
            $store->merchant_id = $merchant->entity_id;
            $store->user_name = $merchant->name;
            $store->password = md5($item['password']);
            $store->store_name = $merchant->name;
            $store->min_trade_amount = 100;
            $store->promised_delivery_time = 72;
            $store->contact_phone = $item['phone'];
            $store->province = $item['province'];
            $store->business_license_code = '';
            $store->city = $item['city'];
            $store->district = 0;
            $store->area_id = $item['area_id'];
            $store->store_address = '乐来科技';
            $store->store_description = '';
            $store->created_at = date('Y-m-d H:i:s', strtotime("+10minute", $timestamp));
            $store->updated_at = date('Y-m-d H:i:s', strtotime("+10minute", $timestamp));
            $store->commission = -1;
            $store->is_info_complete = 1;
            $store->status = 1;
            $store->store_type = 7;
            $store->shop_images = '-';
            $store->business_category = '|2|31|80|103|127|';
            if (!$store->save()) {
                ToolsAbstract::log($store->getErrors());
            }
        }
    }

    /**
     * 更新用户信息
     */
    public function actionUpdateCustomer()
    {
        $data = [
            ['src_id' => 39344, 'dst_id' => 42177,],
            ['src_id' => 39342, 'dst_id' => 42178,],
            ['src_id' => 39338, 'dst_id' => 42179,],
            ['src_id' => 39333, 'dst_id' => 42180,],
            ['src_id' => 39332, 'dst_id' => 42181,],
            ['src_id' => 38984, 'dst_id' => 42182,],
            ['src_id' => 38983, 'dst_id' => 42183,],
            ['src_id' => 38978, 'dst_id' => 42184,],
            ['src_id' => 38975, 'dst_id' => 42185,],
            ['src_id' => 38974, 'dst_id' => 42186,],
            ['src_id' => 38973, 'dst_id' => 42187,],
            ['src_id' => 38502, 'dst_id' => 42188,],
            ['src_id' => 38501, 'dst_id' => 42189,],
            ['src_id' => 38500, 'dst_id' => 42190,],
            ['src_id' => 38499, 'dst_id' => 42191,],
            ['src_id' => 38497, 'dst_id' => 42192,],
            ['src_id' => 38496, 'dst_id' => 42193,],
            ['src_id' => 38495, 'dst_id' => 42194,],
            ['src_id' => 38494, 'dst_id' => 42195,],
            ['src_id' => 38492, 'dst_id' => 42196,],
            ['src_id' => 37666, 'dst_id' => 42077,],
            ['src_id' => 37516, 'dst_id' => 42078,],
            ['src_id' => 37515, 'dst_id' => 42079,],
            ['src_id' => 3217, 'dst_id' => 42080,],
            ['src_id' => 3063, 'dst_id' => 42081,],
            ['src_id' => 2963, 'dst_id' => 42082,],
            ['src_id' => 2861, 'dst_id' => 42083,],
            ['src_id' => 2528, 'dst_id' => 42084,],
            ['src_id' => 38969, 'dst_id' => 42062,],
            ['src_id' => 38967, 'dst_id' => 42063,],
            ['src_id' => 36723, 'dst_id' => 42064,],
            ['src_id' => 26716, 'dst_id' => 42065,],
            ['src_id' => 24687, 'dst_id' => 42066,],
            ['src_id' => 41845, 'dst_id' => 42026,],
            ['src_id' => 39236, 'dst_id' => 42027,],
            ['src_id' => 39227, 'dst_id' => 42028,],
            ['src_id' => 39224, 'dst_id' => 42029,],
            ['src_id' => 9441, 'dst_id' => 42030,],
            ['src_id' => 8841, 'dst_id' => 42031,],
            ['src_id' => 4965, 'dst_id' => 42032,],
            ['src_id' => 4855, 'dst_id' => 42033,],
            ['src_id' => 26399, 'dst_id' => 42067,],
        ];

        foreach ($data as $item) {
            print_r($item);
            echo PHP_EOL;
            $srcCustomer = LeCustomers::findOne(['entity_id' => $item['src_id']]);
            $srcPhone = $srcCustomer->phone;
            $srcCustomer->phone = $srcPhone . '-批发';
            if ($srcCustomer->save()) {
                $dstCustomer = LeCustomers::findOne(['entity_id' => $item['dst_id']]);
                $dstCustomer->store_name = $srcCustomer->store_name;
                $dstCustomer->address = $srcCustomer->address;
                $dstCustomer->detail_address = $srcCustomer->detail_address;
                $dstCustomer->phone = $srcPhone;
                $dstCustomer->username = $srcCustomer->username;
                if (!$dstCustomer->username) {
                    $dstCustomer->username = $dstCustomer->phone;
                }
                $dstCustomer->storekeeper = $srcCustomer->storekeeper;
                $dstCustomer->business_license_img = $srcCustomer->business_license_img;
                $dstCustomer->is_login_white_list = 1;
                $dstCustomer->setPassword(md5('123456'));
                $dstCustomer->save();
            }
        }
    }

    public function actionUpdateMerchantStore()
    {
        $data = [
            ['src_id' => 594, 'dst_id' => 673,],
            ['src_id' => 582, 'dst_id' => 672,],
            ['src_id' => 573, 'dst_id' => 671,],
            ['src_id' => 596, 'dst_id' => 685,],
            ['src_id' => 567, 'dst_id' => 689,],
            ['src_id' => 565, 'dst_id' => 688,],
            ['src_id' => 595, 'dst_id' => 692,],
            ['src_id' => 587, 'dst_id' => 691,],
            ['src_id' => 599, 'dst_id' => 699,],
            ['src_id' => 592, 'dst_id' => 698,],
        ];

        foreach ($data as $item) {
            print_r($item);
            echo PHP_EOL;
            $srcMerchant = LeMerchant::findOne(['entity_id' => $item['src_id']]);
            $srcMerchantPhone = $srcMerchant->phone;
            $srcMerchant->phone = $srcMerchantPhone . '-批发';
            //修改商家电话
            if ($srcMerchant->save()) {
                $dstMerchant = LeMerchant::findOne(['entity_id' => $item['dst_id']]);
                $dstMerchant->phone = $srcMerchantPhone;
                $dstMerchant->name = $srcMerchant->name;
                $dstMerchant->real_name = $srcMerchant->real_name;
                $dstMerchant->setPassword(md5('123456'));
                //修改目标上级电话、名称、密码
                if ($dstMerchant->save()) {
                    $srcMerchantStore = LeMerchantStore::findOne(['merchant_id' => $item['src_id']]);
                    $srcMerchantStoreUserName = $srcMerchantStore->user_name;
                    $srcMerchantStore->user_name = $srcMerchantStoreUserName . '-批发';
                    //修改原商家店铺用户名
                    if ($srcMerchantStore->save()) {
                        $dstMerchantStore = LeMerchantStore::findOne(['merchant_id' => $item['dst_id']]);
                        $dstMerchantStore->user_name = $srcMerchantStoreUserName;
                        if (!$dstMerchantStore->user_name) {
                            $dstMerchantStore->user_name = $srcMerchantPhone;
                        }
                        $dstMerchantStore->store_name = $srcMerchantStore->store_name;
                        $dstMerchantStore->store_address = $srcMerchantStore->store_address;
                        $dstMerchantStore->contact_phone = $srcMerchantStore->contact_phone;
                        $dstMerchantStore->business_license_img = $srcMerchantStore->business_license_img;
                        $dstMerchantStore->business_license_code = $srcMerchantStore->business_license_code;
                        $dstMerchantStore->compensation_service = 0;
                        $dstMerchantStore->password = md5('123456');
                        if (!$dstMerchantStore->save()) {
                            print_r($dstMerchantStore->getErrors());
                        }
                    } else {
                        print_r($srcMerchantStore->getErrors());
                    }
                } else {
                    print_r($dstMerchant->getErrors());
                }
            } else {
                print_r($srcMerchant->getErrors());
            }
        }
    }
}
