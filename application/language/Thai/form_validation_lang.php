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

$lang['form_validation_required']		= '{field} จำเป็นต้องกรอก.';
$lang['form_validation_isset']			= '{field} ต้องมีค่า.';
$lang['form_validation_valid_email']		= '{field} ต้องเป็นรูปแบบอีเมลที่ถูกต้อง.';
$lang['form_validation_valid_emails']		= '{field} ต้องเป็นรูปแบบอีเมลที่ถูกต้องทั้งหมด.';
$lang['form_validation_valid_url']		= '{field} ต้องเป็นรูปแบบ URL ที่ถูกต้อง.';
$lang['form_validation_valid_ip']		= '{field} ต้องเป็นรูปแบบ IP ที่ถูกต้อง.';
$lang['form_validation_min_length']		= '{field} ต้องมีความยาวอย่างน้อย {param} ตัวอักษร.';
$lang['form_validation_max_length']		= '{field} ต้องมีความยาวไม่เกิน {param} ตัวอักษร.';
$lang['form_validation_exact_length']		= '{field} ต้องมีความยาวเท่ากับ {param} ตัวอักษรพอดี.';
$lang['form_validation_alpha']			= '{field} สามารถระบุได้เฉพาะตัวอักษรภาษาอังกฤษเท่านั้น.';
$lang['form_validation_alpha_numeric']		= '{field} สามารถระบุได้เฉพาะตัวอักษรภาษาอังกฤษและตัวเลขเท่านั้น.';
$lang['form_validation_alpha_numeric_spaces']	= '{field} สามารถระบุได้เฉพาะตัวอักษรภาษาอังกฤษ ตัวเลข และเว้นวรรคเท่านั้น.';
$lang['form_validation_alpha_dash']		= '{field} สามารถระบุได้เฉพาะตัวอักษรภาษาอังกฤษ ตัวเลข ขีดล่าง และขีดกลางเท่านั้น.';
$lang['form_validation_numeric']		= '{field} ต้องระบุเฉพาะตัวเลขเท่านั้น.';
$lang['form_validation_is_numeric']		= '{field} ต้องระบุเฉพาะค่าที่เป็นตัวเลขเท่านั้น.';
$lang['form_validation_integer']		= '{field} ต้องระบุเฉพาะตัวเลขจำนวนเต็มเท่านั้น.';
$lang['form_validation_regex_match']		= '{field} รูปแบบของข้อมูลไม่ถูกต้อง.';
$lang['form_validation_matches']		= '{field} ข้อมูลไม่ตรงกับฟิลด์ {param}.';
$lang['form_validation_differs']		= '{field} ข้อมูลต้องแตกต่างจากฟิลด์ {param}.';
$lang['form_validation_is_unique'] 		= 'ข้อมูลในฟิลด์ {field} นี้มีอยู่ในระบบแล้ว (ต้องไม่ซ้ำกัน).';
$lang['form_validation_is_natural']		= '{field} ต้องระบุเฉพาะตัวเลขบวกเท่านั้น.';
$lang['form_validation_is_natural_no_zero']	= '{field} ต้องระบุเฉพาะตัวเลขที่มากกว่าศูนย์เท่านั้น.';
$lang['form_validation_decimal']		= '{field} ต้องระบุเฉพาะตัวเลขทศนิยมเท่านั้น.';
$lang['form_validation_less_than']		= '{field} ต้องมีค่าน้อยกว่า {param}.';
$lang['form_validation_less_than_equal_to']	= '{field} ต้องมีค่าน้อยกว่าหรือเท่ากับ {param}.';
$lang['form_validation_greater_than']		= '{field} ต้องมีค่ามากกว่า {param}.';
$lang['form_validation_greater_than_equal_to']	= '{field} ต้องมีค่ามากกว่าหรือเท่ากับ {param}.';
$lang['form_validation_error_message_not_set']	= 'ไม่สามารถเข้าถึงข้อความแจ้งข้อผิดพลาดของฟิลด์ {field}.';
$lang['form_validation_in_list']		= '{field} ต้องเป็นหนึ่งในรายการดังนี้: {param}.';
