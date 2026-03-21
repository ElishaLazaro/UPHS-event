<?php
include '../../php/ConnectToDb.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $lrn_no = mysqli_real_escape_string($conn, $_POST['lrn_no']);
    $application_date = mysqli_real_escape_string($conn, $_POST['date_application']);
    $pwd_id = mysqli_real_escape_string($conn, $_POST['pwd_id']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $age = intval($_POST['age']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
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
    $previous_level = mysqli_real_escape_string($conn, $_POST['previous_level']);
    $working_family_members = intval($_POST['working_family_no']);
    $monthly_income_head = floatval($_POST['monthly_income_head']);
    $total_family_income = floatval($_POST['total_family_income']);
    $family_type = mysqli_real_escape_string($conn, $_POST['family_type']);
    $comelec_registered = mysqli_real_escape_string($conn, $_POST['comelec_registered']);
    $four_ps_member = mysqli_real_escape_string($conn, $_POST['four_ps_member']);
    $covid_vaccinated = mysqli_real_escape_string($conn, $_POST['covid_vaccine']);
    $guardian_name = mysqli_real_escape_string($conn, $_POST['guardian']);
    $guardian_contact = mysqli_real_escape_string($conn, $_POST['guardian_contact']);
    $house_tagging = mysqli_real_escape_string($conn, $_POST['house_tagging']);
    $teacher_name = mysqli_real_escape_string($conn, $_POST['teacher_name']);

    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {

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

    $sql = "INSERT INTO enrollees (
        lrn_no, application_date, pwd_id, last_name, first_name, middle_name,
        age, birthday, sex, disability_type, disability_cause, specific_disability,
        civil_status, address, barangay, contact_no, educational_attainment,
        employment_status, employment_category, employment_nature, occupation,
        father_name, mother_name, parent_status, siblings_count, working_family_members,
        monthly_income_head, total_family_income, family_type, comelec_registered,
        four_ps_member, covid_vaccinated, guardian_name, guardian_contact,
        house_tagging, teacher_name, photo
    ) VALUES (
        '$lrn_no', '$application_date', '$pwd_id', '$last_name', '$first_name', '$middle_name',
        $age, '$birthday', '$sex', '$disability_type', '$disability_cause', '$specific_disability',
        '$civil_status', '$address', '$barangay', '$contact_no', '$educational_attainment',
        '$employment_status', '$employment_category', '$employment_nature', '$occupation',
        '$father_name', '$mother_name', '$parent_status', $siblings_count, $working_family_members,
        $monthly_income_head, $total_family_income, '$family_type', '$comelec_registered',
        '$four_ps_member', '$covid_vaccinated', '$guardian_name', '$guardian_contact',
        '$house_tagging', '$teacher_name', '$photoPath'
    )";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
            alert('Enrollee successfully added!');
            window.location.href = '../new.php'; // redirect to new.php in dashboard
        </script>";
    } else {
        echo "<script>
            alert('Error: " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "');
            window.history.back();
        </script>";
    }

    mysqli_close($conn);
}
