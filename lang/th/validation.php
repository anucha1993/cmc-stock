<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'ฟิลด์ :attribute จะต้องยอมรับ',
    'accepted_if' => 'ฟิลด์ :attribute จะต้องยอมรับเมื่อ :other เป็น :value',
    'active_url' => 'ฟิลด์ :attribute ไม่ใช่ URL ที่ถูกต้อง',
    'after' => 'ฟิลด์ :attribute จะต้องเป็นวันที่หลังจาก :date',
    'after_or_equal' => 'ฟิลด์ :attribute จะต้องเป็นวันที่หลังจากหรือเท่ากับ :date',
    'alpha' => 'ฟิลด์ :attribute จะต้องประกอบด้วยตัวอักษรเท่านั้น',
    'alpha_dash' => 'ฟิลด์ :attribute จะต้องประกอบด้วยตัวอักษร ตัวเลข เส้นประ และ underscore เท่านั้น',
    'alpha_num' => 'ฟิลด์ :attribute จะต้องประกอบด้วยตัวอักษรและตัวเลขเท่านั้น',
    'array' => 'ฟิลด์ :attribute จะต้องเป็น array',
    'ascii' => 'ฟิลด์ :attribute จะต้องประกอบด้วยตัวอักษรและสัญลักษณ์แบบไบต์เดียวเท่านั้น',
    'before' => 'ฟิลด์ :attribute จะต้องเป็นวันที่ก่อน :date',
    'before_or_equal' => 'ฟิลด์ :attribute จะต้องเป็นวันที่ก่อนหรือเท่ากับ :date',
    'between' => [
        'array' => 'ฟิลด์ :attribute จะต้องมีระหว่าง :min ถึง :max รายการ',
        'file' => 'ฟิลด์ :attribute จะต้องมีขนาดระหว่าง :min ถึง :max กิโลไบต์',
        'numeric' => 'ฟิลด์ :attribute จะต้องอยู่ระหว่าง :min ถึง :max',
        'string' => 'ฟิลด์ :attribute จะต้องมีความยาวระหว่าง :min ถึง :max ตัวอักษร',
    ],
    'boolean' => 'ฟิลด์ :attribute จะต้องเป็น true หรือ false',
    'can' => 'ฟิลด์ :attribute มีค่าที่ไม่ได้รับอนุญาต',
    'confirmed' => 'การยืนยันฟิลด์ :attribute ไม่ตรงกัน',
    'contains' => 'ฟิลด์ :attribute ไม่มีค่าที่จำเป็น',
    'current_password' => 'รหัสผ่านไม่ถูกต้อง',
    'date' => 'ฟิลด์ :attribute ไม่ใช่วันที่ที่ถูกต้อง',
    'date_equals' => 'ฟิลด์ :attribute จะต้องเป็นวันที่เท่ากับ :date',
    'date_format' => 'ฟิลด์ :attribute ไม่ตรงกับรูปแบบ :format',
    'decimal' => 'ฟิลด์ :attribute จะต้องมี :decimal ตำแหน่งทศนิยม',
    'declined' => 'ฟิลด์ :attribute จะต้องถูกปฏิเสธ',
    'declined_if' => 'ฟิลด์ :attribute จะต้องถูกปฏิเสธเมื่อ :other เป็น :value',
    'different' => 'ฟิลด์ :attribute และ :other จะต้องแตกต่างกัน',
    'digits' => 'ฟิลด์ :attribute จะต้องเป็น :digits หลัก',
    'digits_between' => 'ฟิลด์ :attribute จะต้องอยู่ระหว่าง :min ถึง :max หลัก',
    'dimensions' => 'ฟิลด์ :attribute มีขนาดรูปภาพที่ไม่ถูกต้อง',
    'distinct' => 'ฟิลด์ :attribute มีค่าที่ซ้ำกัน',
    'doesnt_end_with' => 'ฟิลด์ :attribute จะต้องไม่ลงท้ายด้วย: :values',
    'doesnt_start_with' => 'ฟิลด์ :attribute จะต้องไม่เริ่มต้นด้วย: :values',
    'email' => 'ฟิลด์ :attribute จะต้องเป็นอีเมลที่ถูกต้อง',
    'ends_with' => 'ฟิลด์ :attribute จะต้องลงท้ายด้วย: :values',
    'enum' => ':attribute ที่เลือกไม่ถูกต้อง',
    'exists' => ':attribute ที่เลือกไม่ถูกต้อง',
    'extensions' => 'ฟิลด์ :attribute จะต้องมีนามสกุล: :values',
    'file' => 'ฟิลด์ :attribute จะต้องเป็นไฟล์',
    'filled' => 'ฟิลด์ :attribute จะต้องมีค่า',
    'gt' => [
        'array' => 'ฟิลด์ :attribute จะต้องมีมากกว่า :value รายการ',
        'file' => 'ฟิลด์ :attribute จะต้องมีขนาดมากกว่า :value กิโลไบต์',
        'numeric' => 'ฟิลด์ :attribute จะต้องมากกว่า :value',
        'string' => 'ฟิลด์ :attribute จะต้องมีความยาวมากกว่า :value ตัวอักษร',
    ],
    'gte' => [
        'array' => 'ฟิลด์ :attribute จะต้องมี :value รายการหรือมากกว่า',
        'file' => 'ฟิลด์ :attribute จะต้องมีขนาด :value กิโลไบต์หรือมากกว่า',
        'numeric' => 'ฟิลด์ :attribute จะต้องมีค่า :value หรือมากกว่า',
        'string' => 'ฟิลด์ :attribute จะต้องมีความยาว :value ตัวอักษรหรือมากกว่า',
    ],
    'hex_color' => 'ฟิลด์ :attribute จะต้องเป็นสีแบบเลขฐานสิบหกที่ถูกต้อง',
    'image' => 'ฟิลด์ :attribute จะต้องเป็นรูปภาพ',
    'in' => ':attribute ที่เลือกไม่ถูกต้อง',
    'in_array' => 'ฟิลด์ :attribute ไม่มีอยู่ใน :other',
    'integer' => 'ฟิลด์ :attribute จะต้องเป็นจำนวนเต็ม',
    'ip' => 'ฟิลด์ :attribute จะต้องเป็น IP address ที่ถูกต้อง',
    'ipv4' => 'ฟิลด์ :attribute จะต้องเป็น IPv4 address ที่ถูกต้อง',
    'ipv6' => 'ฟิลด์ :attribute จะต้องเป็น IPv6 address ที่ถูกต้อง',
    'json' => 'ฟิลด์ :attribute จะต้องเป็น JSON string ที่ถูกต้อง',
    'list' => 'ฟิลด์ :attribute จะต้องเป็นรายการ',
    'lowercase' => 'ฟิลด์ :attribute จะต้องเป็นตัวพิมพ์เล็ก',
    'lt' => [
        'array' => 'ฟิลด์ :attribute จะต้องมีน้อยกว่า :value รายการ',
        'file' => 'ฟิลด์ :attribute จะต้องมีขนาดน้อยกว่า :value กิโลไบต์',
        'numeric' => 'ฟิลด์ :attribute จะต้องน้อยกว่า :value',
        'string' => 'ฟิลด์ :attribute จะต้องมีความยาวน้อยกว่า :value ตัวอักษร',
    ],
    'lte' => [
        'array' => 'ฟิลด์ :attribute จะต้องไม่มีมากกว่า :value รายการ',
        'file' => 'ฟิลด์ :attribute จะต้องมีขนาดน้อยกว่าหรือเท่ากับ :value กิโลไบต์',
        'numeric' => 'ฟิลด์ :attribute จะต้องน้อยกว่าหรือเท่ากับ :value',
        'string' => 'ฟิลด์ :attribute จะต้องมีความยาวน้อยกว่าหรือเท่ากับ :value ตัวอักษร',
    ],
    'mac_address' => 'ฟิลด์ :attribute จะต้องเป็น MAC address ที่ถูกต้อง',
    'max' => [
        'array' => 'ฟิลด์ :attribute จะต้องไม่มีมากกว่า :max รายการ',
        'file' => 'ฟิลด์ :attribute จะต้องไม่มีขนาดมากกว่า :max กิโลไบต์',
        'numeric' => 'ฟิลด์ :attribute จะต้องไม่มากกว่า :max',
        'string' => 'ฟิลด์ :attribute จะต้องไม่เกิน :max ตัวอักษร',
    ],
    'max_digits' => 'ฟิลด์ :attribute จะต้องไม่เกิน :max หลัก',
    'mimes' => 'ฟิลด์ :attribute จะต้องเป็นไฟล์ประเภท: :values',
    'mimetypes' => 'ฟิลด์ :attribute จะต้องเป็นไฟล์ประเภท: :values',
    'min' => [
        'array' => 'ฟิลด์ :attribute จะต้องมีอย่างน้อย :min รายการ',
        'file' => 'ฟิลด์ :attribute จะต้องมีขนาดอย่างน้อย :min กิโลไบต์',
        'numeric' => 'ฟิลด์ :attribute จะต้องอย่างน้อย :min',
        'string' => 'ฟิลด์ :attribute จะต้องมีอย่างน้อย :min ตัวอักษร',
    ],
    'min_digits' => 'ฟิลด์ :attribute จะต้องมีอย่างน้อย :min หลัก',
    'missing' => 'ฟิลด์ :attribute จะต้องหายไป',
    'missing_if' => 'ฟิลด์ :attribute จะต้องหายไปเมื่อ :other เป็น :value',
    'missing_unless' => 'ฟิลด์ :attribute จะต้องหายไปเว้นแต่ :other เป็น :value',
    'missing_with' => 'ฟิลด์ :attribute จะต้องหายไปเมื่อมี :values',
    'missing_with_all' => 'ฟิลด์ :attribute จะต้องหายไปเมื่อมี :values',
    'multiple_of' => 'ฟิลด์ :attribute จะต้องเป็นผลคูณของ :value',
    'not_in' => ':attribute ที่เลือกไม่ถูกต้อง',
    'not_regex' => 'รูปแบบฟิลด์ :attribute ไม่ถูกต้อง',
    'numeric' => 'ฟิลด์ :attribute จะต้องเป็นตัวเลข',
    'password' => [
        'letters' => 'ฟิลด์ :attribute จะต้องมีตัวอักษรอย่างน้อยหนึ่งตัว',
        'mixed' => 'ฟิลด์ :attribute จะต้องมีตัวอักษรใหญ่และเล็กอย่างน้อยหนึ่งตัว',
        'numbers' => 'ฟิลด์ :attribute จะต้องมีตัวเลขอย่างน้อยหนึ่งตัว',
        'symbols' => 'ฟิลด์ :attribute จะต้องมีสัญลักษณ์อย่างน้อยหนึ่งตัว',
        'uncompromised' => ':attribute ที่ให้มาปรากฏในการรั่วไหลของข้อมูล กรุณาเลือก :attribute อื่น',
    ],
    'present' => 'ฟิลด์ :attribute จะต้องมีอยู่',
    'present_if' => 'ฟิลด์ :attribute จะต้องมีอยู่เมื่อ :other เป็น :value',
    'present_unless' => 'ฟิลด์ :attribute จะต้องมีอยู่เว้นแต่ :other เป็น :value',
    'present_with' => 'ฟิลด์ :attribute จะต้องมีอยู่เมื่อมี :values',
    'present_with_all' => 'ฟิลด์ :attribute จะต้องมีอยู่เมื่อมี :values',
    'prohibited' => 'ฟิลด์ :attribute ถูกห้าม',
    'prohibited_if' => 'ฟิลด์ :attribute ถูกห้ามเมื่อ :other เป็น :value',
    'prohibited_unless' => 'ฟิลด์ :attribute ถูกห้ามเว้นแต่ :other อยู่ใน :values',
    'prohibits' => 'ฟิลด์ :attribute ห้าม :other จากการแสดง',
    'regex' => 'รูปแบบฟิลด์ :attribute ไม่ถูกต้อง',
    'required' => 'ฟิลด์ :attribute จำเป็นต้องกรอก',
    'required_array_keys' => 'ฟิลด์ :attribute จะต้องมีค่าสำหรับ: :values',
    'required_if' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อ :other เป็น :value',
    'required_if_accepted' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อ :other ได้รับการยอมรับ',
    'required_if_declined' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อ :other ถูกปฏิเสธ',
    'required_unless' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเว้นแต่ :other อยู่ใน :values',
    'required_with' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อมี :values',
    'required_with_all' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อมี :values',
    'required_without' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อไม่มี :values',
    'required_without_all' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อไม่มี :values ใดๆ',
    'same' => 'ฟิลด์ :attribute และ :other จะต้องตรงกัน',
    'size' => [
        'array' => 'ฟิลด์ :attribute จะต้องมี :size รายการ',
        'file' => 'ฟิลด์ :attribute จะต้องมีขนาด :size กิโลไบต์',
        'numeric' => 'ฟิลด์ :attribute จะต้องเป็น :size',
        'string' => 'ฟิลด์ :attribute จะต้องมี :size ตัวอักษร',
    ],
    'starts_with' => 'ฟิลด์ :attribute จะต้องเริ่มต้นด้วย: :values',
    'string' => 'ฟิลด์ :attribute จะต้องเป็นข้อความ',
    'timezone' => 'ฟิลด์ :attribute จะต้องเป็นเขตเวลาที่ถูกต้อง',
    'unique' => ':attribute นี้ถูกใช้ไปแล้ว',
    'uploaded' => ':attribute อัปโหลดไม่สำเร็จ',
    'uppercase' => 'ฟิลด์ :attribute จะต้องเป็นตัวพิมพ์ใหญ่',
    'url' => 'ฟิลด์ :attribute จะต้องเป็น URL ที่ถูกต้อง',
    'ulid' => 'ฟิลด์ :attribute จะต้องเป็น ULID ที่ถูกต้อง',
    'uuid' => 'ฟิลด์ :attribute จะต้องเป็น UUID ที่ถูกต้อง',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'ชื่อ',
        'email' => 'อีเมล',
        'password' => 'รหัสผ่าน',
        'password_confirmation' => 'ยืนยันรหัสผ่าน',
        'phone' => 'เบอร์โทรศัพท์',
        'first_name' => 'ชื่อจริง',
        'last_name' => 'นามสกุล',
        'birth_date' => 'วันเกิด',
        'gender' => 'เพศ',
        'address' => 'ที่อยู่',
        'roles' => 'บทบาท',
        'display_name' => 'ชื่อแสดง',
        'description' => 'คำอธิบาย',
        'level' => 'ระดับ',
    ],

];