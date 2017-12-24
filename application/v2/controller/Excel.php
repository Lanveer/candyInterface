<?php

namespace app\v2\controller;

// 引入三方库
require_once VENDOR_PATH . 'autoload.php';


/**
 * Excel文件操作
 * 需要 "PHPoffice/phpexcel": "^1.8" 的支持
 *
 * @author 赵晓天
 */
class Excel extends Base
{
    /**
     * 读取Excel文件返回数据
     *
     * @return array
     */
    public static function getDataByExcelFile($file, $title)
    {
        if(!file_exists($file) || empty($title)) {
            return null;
        }

        // 创建一个特定的读取类
        $filetype = \PHPExcel_IOFactory::identify($file);
        $excelread = \PHPExcel_IOFactory::createReader($filetype);

        // 加载文件
        $phpexcel = $excelread->load($file);
        // 读取第一个工作表
        $sheet = $phpexcel->getSheet(0);
        if (empty($sheet)) {
            return null;
        }

        // 获取当前工作表的行数
        $rows = $sheet->getHighestRow();
        // 用来存Excel数据
        $arr = [];

        // 读取数据
        for ($i = 2; $i <= $rows; $i++){
            $arr_col = [];
            for ($col = 0; $col < count($title); $col++){
                // 把数字列转换成字母列，这里是通的列索引获取到对应的字母列
                $columnname = \PHPExcel_Cell::stringFromColumnIndex($col);
                $arr_col[$title[$col]] = $sheet->getCell($columnname . $i)->getValue();
            }
            $arr[] = $arr_col;
        }

        return $arr;
    }

    /**
     * 数据导出到Excel
     *
     * @return string
     */
    public static function getExcelByData(array $data = [], $keys = [], $excelName = '')
    {
        if (empty($data) || empty($keys)) {
            return null;
        }

        // 创建文件
        $phpexcel = new \PHPExcel();
        $phpexcel->setActiveSheetIndex(0);

        // 写入表头
        $c = 0;
        foreach ($keys as $key => $value) {
            $columnname = \PHPExcel_Cell::stringFromColumnIndex($c++);
            $phpexcel->getActiveSheet()->setCellValue($columnname . '1', $value);
        }

        // 写入数据
        for ($i = 0; $i < count($data); $i++) {
            $c = 0;
            foreach ($keys as $key => $value) {
                // 把数字列转换成字母列，这里是通的列索引获取到对应的字母列
                $columnname = \PHPExcel_Cell::stringFromColumnIndex($c++);
                $phpexcel->getActiveSheet()->setCellValue($columnname . ($i + 2), $data[$i][$key]);
            }
        }

        // 保存文件
        $phpwriter = new \PHPExcel_Writer_Excel2007($phpexcel);
        $excelName = (empty($excelName) ? md5(time()) : $excelName) . '.xlsx';
        $savePath =  DS . 'tmp' . DS . $excelName;
        $phpwriter->save(ROOT_PATH . 'public' . $savePath);

        return $savePath;
    }
}
