<?php
include '../../php/ConnectToDb.php';

function dateToMysql($dateStr)
{
    $dateStr = trim($dateStr);
    if ($dateStr === '') return '';
    $d = DateTime::createFromFormat('Y-m-d', $dateStr);
    if ($d) return $d->format('Y-m-d');
    $d = DateTime::createFromFormat('d/m/Y', $dateStr);
    if ($d) return $d->format('Y-m-d');
    $d = DateTime::createFromFormat('d/m/y', $dateStr);
    if ($d) return $d->format('Y-m-d');
    return $dateStr;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enrollee_id = intval($_POST['enrollee_id']);

    $lrn_no = mysqli_real_escape_string($conn, $_POST['lrn_no']);
    $application_date = dateToMysql($_POST['date_application'] ?? '');
    $application_date = mysqli_real_escape_string($conn, $application_date);
    $pwd_id = mysqli_real_escape_string($conn, $_POST['pwd_id']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $age = intval($_POST['age']);
    $birthday = dateToMysql($_POST['birthday'] ?? '');
    $birthday = mysqli_real_escape_string($conn, $birthday);
    $sex = mysqli_real_escape_string($conn, $_POST['sex']);
    $disability_type = mysqli_real_escape_string($conn, $_POST['disability_diagnosis']);
    $disability_cause = mysqli_real_escape_string($conn, $_POST['congenital_inborn']);
    $specific_disability = mysqli_real_escape_string($conn, $_POST['specific_disability']);
    $civil_status = mysqli_real_escape_string($conn, $_POST['civil_status']);
    $address = mysqli_real_escape_string($conn, $_POST['complete_address']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no']);
    $educational_attainment = mysqli_real_escape_string($conn, $_POST['education_type']);
    $employment_status = mysqli_real_escape_string($conn, $_POST['employment_status']);
    $employment_category = mysqli_real_escape_string($conn, $_POST['category_of_employment']);
    $employment_nature = mysqli_real_escape_string($conn, $_POST['nature_of_employment']);
    $occupation = mysqli_real_escape_string($conn, $_POST['occupation']);
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $parent_status = mysqli_real_escape_string($conn, $_POST['parent_status']);
    $siblings_count = intval($_POST['siblings_no']);
    $working_family_members = intval($_POST['working_family_no']);
    $monthly_income_head = floatval($_POST['monthly_income_head']);
    $total_family_income = floatval($_POST['total_family_income']);
    $previous_level = mysqli_real_escape_string($conn, $_POST['previous_level']);
    $family_type = mysqli_real_escape_string($conn, $_POST['family_type']);
    $comelec_registered = mysqli_real_escape_string($conn, $_POST['comelec_registered']);
    $four_ps_member = mysqli_real_escape_string($conn, $_POST['four_ps_member']);
    $covid_vaccinated = mysqli_real_escape_string($conn, $_POST['covid_vaccine']);
    $guardian_name = mysqli_real_escape_string($conn, $_POST['guardian']);
    $guardian_contact = mysqli_real_escape_string($conn, $_POST['guardian_contact']);
    $house_tagging = mysqli_real_escape_string($conn, $_POST['house_tagging']);
    $teacher_name = mysqli_real_escape_string($conn, $_POST['teacher_name']);

    $current_sql = "SELECT photo FROM enrollees WHERE enrollee_id = $enrollee_id";
    $current_result = mysqli_query($conn, $current_sql);
    $current_row = mysqli_fetch_assoc($current_result);
    $photoPath = $current_row['photo'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        if ($photoPath && file_exists('../' . $photoPath)) {
            unlink('../' . $photoPath);
        }

        $targetDir = '../images/';
        $fileName = time() . '_' . basename($_FILES['photo']['name']);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
            $photoPath = 'images/' . $fileName;
        } else {
            echo "<script>
                alert('Error uploading photo!');
                window.history.back();
            </script>";
            exit;
        }
    }

    $sql = "UPDATE enrollees SET
        lrn_no = '$lrn_no',
        application_date = '$application_date',
        pwd_id = '$pwd_id',
        last_name = '$last_name',
        first_name = '$first_name',
        middle_name = '$middle_name',
        age = $age,
        birthday = '$birthday',
        sex = '$sex',
        disability_type = '$disability_type',
        disability_cause = '$disability_cause',
        specific_disability = '$specific_disability',
        civil_status = '$civil_status',
        address = '$address',
        barangay = '$barangay',
        contact_no = '$contact_no',
        educational_attainment = '$educational_attainment',
        employment_status = '$employment_status',
        employment_category = '$employment_category',
        employment_nature = '$employment_nature',
        occupation = '$occupation',
        father_name = '$father_name',
        mother_name = '$mother_name',
        parent_status = '$parent_status',
        siblings_count = $siblings_count,
        working_family_members = $working_family_members,
        monthly_income_head = $monthly_income_head,
        total_family_income = $total_family_income,
        family_type = '$family_type',
        previous_level = '$previous_level',
        comelec_registered = '$comelec_registered',
        four_ps_member = '$four_ps_member',
        covid_vaccinated = '$covid_vaccinated',
        guardian_name = '$guardian_name',
        guardian_contact = '$guardian_contact',
        house_tagging = '$house_tagging',
        teacher_name = '$teacher_name',
        photo = '$photoPath'
    WHERE enrollee_id = $enrollee_id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
            alert('Enrollee successfully updated!');
            window.location.href = '../new.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "');
            window.history.back();
        </script>";
    }

    mysqli_close($conn);
}
