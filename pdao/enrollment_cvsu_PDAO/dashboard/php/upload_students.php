<?php

session_start();
include '../../php/ConnectToDb.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileUpload'])) {

    $file = $_FILES['fileUpload'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];
    $fileSize = $file['size'];

    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileError !== 0) {
        $_SESSION['upload_message'] = 'Error uploading file!';
        header('Location: ../admin.php');
        exit();
    }

    if ($fileExt !== 'xlsx' && $fileExt !== 'xls') {
        $_SESSION['upload_message'] = 'Please upload an Excel file (.xlsx or .xls)!';
        header('Location: ../admin.php');
        exit();
    }

    if ($fileSize > 5000000) {
        $_SESSION['upload_message'] = 'File size is too large! Maximum 5MB allowed.';
        header('Location: ../admin.php');
        exit();
    }

    require_once '../../vendor/autoload.php';

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmpName);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, false);

        if (empty($rows)) {
            $_SESSION['upload_message'] = 'Excel file is empty!';
            header('Location: ../admin.php');
            exit();
        }

        $header = array_shift($rows);

        $columnMap = [];
        $requiredFields = ['lrn_no', 'last_name', 'first_name'];

        $headerMapping = [
            'lrn no.'                       => 'lrn_no',
            'date of application'           => 'application_date',
            'pwd id number'                 => 'pwd_id',
            'last name'                     => 'last_name',
            'first name'                    => 'first_name',
            'middle name'                   => 'middle_name',
            'age'                           => 'age',
            'birthday'                      => 'birthday',
            'sex'                           => 'sex',
            'disability'                    => 'disability_type',
            'congenital/inborn'             => 'disability_cause',
            'specific disability'           => 'specific_disability',
            'civil status'                  => 'civil_status',
            'house no. & street name'       => 'address',
            'barangay'                      => 'barangay',
            'contact no.'                   => 'contact_no',
            'educational attainment'        => 'educational_attainment',
            'employment status'             => 'employment_status',
            'category of employment'        => 'employment_category',
            'nature of employment'          => 'employment_nature',
            'occupation'                    => 'occupation',
            'father name'                   => 'father_name',
            'mother name'                   => 'mother_name',
            'parent status'                 => 'parent_status',
            'no. of sibling'                => 'siblings_count',
            'previous level/grade'          => 'previous_level',
            'no. of working in the family'  => 'working_family_members',
            'monthly income head'           => 'monthly_income_head',
            'total family income'           => 'total_family_income',
            'type of family'                => 'family_type',
            'comelec registered'            => 'comelec_registered',
            '4ps member'                    => 'four_ps_member',
            'received covid 19 vaccine'     => 'covid_vaccinated',
            'guardian'                      => 'guardian_name',
            'guardian contact no.'          => 'guardian_contact',
            'house tagging'                 => 'house_tagging',
            "teacher's name"                => 'teacher_name',
        ];

        foreach ($header as $index => $headerName) {
            if ($headerName === null) continue;
            $normalizedHeader = strtolower(trim($headerName));
            $normalizedHeader = preg_replace('/[^a-z0-9\/\. &\']/', '', $normalizedHeader);
            if (isset($headerMapping[$normalizedHeader])) {
                $columnMap[$headerMapping[$normalizedHeader]] = $index;
            }
        }

        $missingRequired = [];
        foreach ($requiredFields as $field) {
            if (!isset($columnMap[$field])) {
                $missingRequired[] = $field;
            }
        }

        if (!empty($missingRequired)) {
            $_SESSION['upload_message'] = 'Excel file is missing required columns: ' . implode(', ', $missingRequired);
            header('Location: ../admin.php');
            exit();
        }

        $parseDate = function ($value) {
            if (empty($value)) return '';

            if (is_numeric($value)) {
                $unixDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp((float)$value);
                return date('Y-m-d', $unixDate);
            }

            $value = trim(strval($value));

            $dt = DateTime::createFromFormat('d/m/Y', $value);
            if ($dt !== false) {
                return $dt->format('Y-m-d');
            }

            $dt = DateTime::createFromFormat('d/m/y', $value);
            if ($dt !== false) {
                return $dt->format('Y-m-d');
            }

            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }

            return null; 
        };

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        $rowNum = 1;
        foreach ($rows as $data) {
            $rowNum++;

            if (empty(array_filter($data))) {
                continue;
            }

            $getValue = function ($fieldName, $type = 'string') use ($data, $columnMap) {
                if (!isset($columnMap[$fieldName])) {
                    return $type === 'int' ? 0 : ($type === 'float' ? 0.0 : '');
                }
                $index = $columnMap[$fieldName];
                $value = trim(strval($data[$index] ?? ''));

                if ($type === 'int') {
                    return intval($value);
                } elseif ($type === 'float') {
                    return floatval($value);
                }
                return mysqli_real_escape_string($GLOBALS['conn'], $value);
            };

            $lrn_no                 = $getValue('lrn_no');
            $pwd_id                 = $getValue('pwd_id');
            $last_name              = $getValue('last_name');
            $first_name             = $getValue('first_name');
            $middle_name            = $getValue('middle_name');
            $age                    = $getValue('age', 'int');
            $sex                    = $getValue('sex');
            $disability_type        = $getValue('disability_type');
            $disability_cause       = $getValue('disability_cause');
            $specific_disability    = $getValue('specific_disability');
            $civil_status           = $getValue('civil_status');
            $address                = $getValue('address');
            $barangay               = $getValue('barangay');
            $contact_no             = $getValue('contact_no');
            $educational_attainment = $getValue('educational_attainment');
            $employment_status      = $getValue('employment_status');
            $employment_category    = $getValue('employment_category');
            $employment_nature      = $getValue('employment_nature');
            $occupation             = $getValue('occupation');
            $father_name            = $getValue('father_name');
            $mother_name            = $getValue('mother_name');
            $parent_status          = $getValue('parent_status');
            $siblings_count         = $getValue('siblings_count', 'int');
            $previous_level         = $getValue('previous_level');
            $working_family_members = $getValue('working_family_members', 'int');
            $monthly_income_head    = $getValue('monthly_income_head', 'float');
            $total_family_income    = $getValue('total_family_income', 'float');
            $family_type            = $getValue('family_type');
            $comelec_registered     = $getValue('comelec_registered');
            $four_ps_member         = $getValue('four_ps_member');
            $covid_vaccinated       = $getValue('covid_vaccinated');
            $guardian_name          = $getValue('guardian_name');
            $guardian_contact       = $getValue('guardian_contact');
            $house_tagging          = $getValue('house_tagging');
            $teacher_name           = $getValue('teacher_name');

            $application_date = '';
            if (isset($columnMap['application_date'])) {
                $raw_app_date = $data[$columnMap['application_date']] ?? '';
                $parsed_app_date = $parseDate($raw_app_date);
                if ($parsed_app_date === null && !empty($raw_app_date)) {
                    $errors[] = "Row $rowNum: Invalid application date format (got: $raw_app_date)";
                    $errorCount++;
                    continue;
                }
                $application_date = $parsed_app_date ?? '';
            }

            $birthday = '';
            if (isset($columnMap['birthday'])) {
                $raw_birthday = $data[$columnMap['birthday']] ?? '';
                $parsed_birthday = $parseDate($raw_birthday);
                if ($parsed_birthday === null && !empty($raw_birthday)) {
                    $errors[] = "Row $rowNum: Invalid birthday format (got: $raw_birthday)";
                    $errorCount++;
                    continue;
                }
                $birthday = $parsed_birthday ?? '';
            }

            $application_date = mysqli_real_escape_string($conn, $application_date);
            $birthday         = mysqli_real_escape_string($conn, $birthday);

            if (empty($lrn_no) || empty($last_name) || empty($first_name)) {
                $errors[] = "Row $rowNum: Missing required fields (LRN, Last Name, First Name)";
                $errorCount++;
                continue;
            }

            if (!empty($age) && !is_numeric($age)) {
                $errors[] = "Row $rowNum: Age must be numeric";
                $errorCount++;
                continue;
            }

            if (!empty($monthly_income_head) && !is_numeric($monthly_income_head)) {
                $errors[] = "Row $rowNum: Monthly income must be numeric";
                $errorCount++;
                continue;
            }

            if (!empty($total_family_income) && !is_numeric($total_family_income)) {
                $errors[] = "Row $rowNum: Total family income must be numeric";
                $errorCount++;
                continue;
            }

            if (!empty($contact_no) && !preg_match('/^[0-9\s\-\+\(\)]*$/', $contact_no)) {
                $errors[] = "Row $rowNum: Invalid contact number format";
                $errorCount++;
                continue;
            }

            $sql = "INSERT INTO enrollees (
                lrn_no, application_date, pwd_id, last_name, first_name, middle_name,
                age, birthday, sex, disability_type, disability_cause, specific_disability,
                civil_status, address, barangay, contact_no, educational_attainment,
                employment_status, employment_category, employment_nature, occupation,
                father_name, mother_name, parent_status, siblings_count, working_family_members,
                monthly_income_head, total_family_income, family_type, comelec_registered,
                four_ps_member, covid_vaccinated, guardian_name, guardian_contact,
                house_tagging, previous_level, teacher_name
            ) VALUES (
                '$lrn_no', '$application_date', '$pwd_id', '$last_name', '$first_name', '$middle_name',
                $age, '$birthday', '$sex', '$disability_type', '$disability_cause', '$specific_disability',
                '$civil_status', '$address', '$barangay', '$contact_no', '$educational_attainment',
                '$employment_status', '$employment_category', '$employment_nature', '$occupation',
                '$father_name', '$mother_name', '$parent_status', $siblings_count, $working_family_members,
                $monthly_income_head, $total_family_income, '$family_type', '$comelec_registered',
                '$four_ps_member', '$covid_vaccinated', '$guardian_name', '$guardian_contact',
                '$house_tagging', '$previous_level', '$teacher_name'
            )";

            if (mysqli_query($conn, $sql)) {
                $successCount++;
            } else {
                $errors[] = "Row $rowNum: " . mysqli_error($conn);
                $errorCount++;
            }
        }

        $message = "Upload Complete!\n";
        $message .= "Successfully imported: $successCount students\n";

        if ($errorCount > 0) {
            $message .= "Failed: $errorCount students\n\n";
            $message .= "Errors:\n";
            foreach ($errors as $error) {
                $message .= "- $error\n";
            }
        }

        $_SESSION['upload_message'] = $message;
    } catch (Exception $e) {
        $_SESSION['upload_message'] = 'Error reading Excel file: ' . $e->getMessage();
    }

    mysqli_close($conn);
    header('Location: ../admin.php');
    exit();
} else {
    $_SESSION['upload_message'] = 'No file uploaded!';
    header('Location: ../admin.php');
    exit();
}
