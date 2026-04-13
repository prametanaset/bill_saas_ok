<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'ไม่สามารถตรวจสอบการตั้งค่าฐานข้อมูลจากสตริงการเชื่อมต่อที่คุณส่งมาได้.';
$lang['db_unable_to_connect'] = 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ฐานข้อมูลโดยใช้การตั้งค่าที่กำหนดไว้ได้.';
$lang['db_unable_to_select'] = 'ไม่สามารถเลือกฐานข้อมูลที่กำหนดไว้ได้: %s';
$lang['db_unable_to_create'] = 'ไม่สามารถสร้างฐานข้อมูลที่กำหนดไว้ได้: %s';
$lang['db_invalid_query'] = 'คำสั่ง Query ที่ส่งมาไม่ถูกต้อง.';
$lang['db_must_set_table'] = 'คุณต้องกำหนดชื่อตารางฐานข้อมูลที่จะใช้ใน Query ของคุณ.';
$lang['db_must_use_set'] = 'คุณต้องใช้เมธอด "set" เพื่ออัปเดตข้อมูล.';
$lang['db_must_use_index'] = 'คุณต้องระบุอินเด็กซ์เพื่อให้ตรงกับการอัปเดตแบบกลุ่ม (Batch update).';
$lang['db_batch_missing_index'] = 'หนึ่งในแถวข้อมูลที่ส่งมาเพื่อการอัปเดตแบบกลุ่มไม่มีอินเด็กซ์ที่ระบุไว้.';
$lang['db_must_use_where'] = 'ไม่อนุญาตให้อัปเดตข้อมูลหากไม่มีเงื่อนไข "where" เนื่องด้วยเหตุผลด้านความปลอดภัย.';
$lang['db_del_must_use_where'] = 'ไม่อนุญาตให้ลบข้อมูลหากไม่มีเงื่อนไข "where" หรือ "like" เนื่องด้วยเหตุผลด้านความปลอดภัย.';
$lang['db_field_param_missing'] = 'การดึงข้อมูลฟิลด์จำเป็นต้องใช้ชื่อตารางเป็นพารามิเตอร์.';
$lang['db_unsupported_function'] = 'ฟีเจอร์นี้ไม่รองรับในฐานข้อมูลที่คุณกำลังใช้งาน.';
$lang['db_transaction_failure'] = 'ธุรกรรม (Transaction) ล้มเหลว: ย้อนกลับข้อมูล (Rollback) เรียบร้อยแล้ว.';
$lang['db_unable_to_drop'] = 'ไม่สามารถลบฐานข้อมูลที่กำหนดไว้ได้.';
$lang['db_unsupported_feature'] = 'แพลตฟอร์มฐานข้อมูลที่คุณใช้ไม่รองรับฟีเจอร์นี้.';
$lang['db_unsupported_compression'] = 'เซิร์ฟเวอร์ของคุณไม่รองรับรูปแบบการบีบอัดไฟล์ที่เลือกไว้.';
$lang['db_filepath_error'] = 'ไม่สามารถเขียนข้อมูลลงในเส้นทางไฟล์ที่คุณส่งมาได้.';
$lang['db_invalid_cache_path'] = 'เส้นทางแคชที่คุณส่งมาไม่ถูกต้องหรือไม่มีสิทธิ์ในการเขียนข้อมูล.';
$lang['db_table_name_required'] = 'จำเป็นต้องระบุชื่อตารางสำหรับการดำเนินการนี้.';
$lang['db_column_name_required'] = 'จำเป็นต้องระบุชื่อคอลัมน์สำหรับการดำเนินการนี้.';
$lang['db_column_definition_required'] = 'จำเป็นต้องมีการกำหนดรายละเอียดคอลัมน์สำหรับการดำเนินการนี้.';
$lang['db_unable_to_set_charset'] = 'ไม่สามารถตั้งค่าชุดตัวอักษรสำหรับการเชื่อมต่อของลูกค้าได้: %s';
$lang['db_error_heading'] = 'เกิดข้อผิดพลาดกับฐานข้อมูล';
