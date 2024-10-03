<?php
session_start();

include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secretaries Corner</title>

  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
 

</head>

<body class="bg-[#E8E8E7]">

  <?php include "../admin_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
    <div class="card">
    <div class="card-body">
          <div class="flex justify-between items-center mb-4">
            <div class="flex items-center">
              <div class="dilglogo">
              <img src="../img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
              </div>
              <h1 class="text-xl font-bold ml-4">Lupong Tagapamayapa Incentives Award (LTIA)</h1>
            </div>
            <div class="menu">
              <ul class="flex space-x-4">
                <li>
                  <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='admin_dashboard.php';" style="margin-left: 0;">
                  <i class="ti ti-building-community mr-2"> </i> 
                      Back
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <h2 class="text-left text-2xl font-semibold">FORM 1</h2>

<!-- Create a select input aligned with "FORM 1" -->
<div class="form-group mt-4">
  <label for="barangay_select" class="block text-lg font-medium text-gray-700">Select Barangay</label>
  <select id="barangay_select" name="barangay" class="form-control">
    <option value="Barangay 1">Barangay 1</option>
    <option value="Barangay 2">Barangay 2</option>
    <option value="Barangay 3">Barangay 3</option>
    <option value="Barangay 4">Barangay 4</option>
  </select>
</div>

<form method="post" action="form2update.php" enctype="multipart/form-data">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>CRITERIA</th>
            <th>Assignee Points</th>
            <th>File</th>
            <th>Rate</th>
            <th>Remarks</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
            <details>
                <summary><b>1. a) Proper Recording of Every Dispute/Complaint - Evaluation Criteria</b></summary>
                <p><br>
                    <b>Scoring Details:</b> <br><br>
                    <b>5 points</b> - Submitted/presented the record book or logbook reflecting all the required details.<br>
                    <b>2.5 points</b> - Submitted/presented the record book or logbook reflecting some of the necessary details.<br>
                    <b>0 points</b> - No presented record book or logbook.<br><br>

                    <b>Note:</b> Check if the record contains the following:
                    <ul>
                    <li>Docket number</li>
                    <li>Names of the parties</li>
                    <li>Date and time filed</li>
                    <li>Nature of the case</li>
                    <li>Disposition</li>
                    </ul>
                </p>
                </details>
        </td>
            <td>20</td>
            <td>
              <?php if (!empty($row['IA_1a_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_1a_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
          </tr>
          <tr>
            <td><details>
          <summary><b> b. ) Sending of Notices/Summons to Parties - Evaluation Criteria</b></summary>
          <p>
            <b>Criteria:</b> Sending of Notices/Summons to parties within the prescribed period (within the next working day upon receipt of complaint).
          </p>

          <p><b>Scoring Breakdown:</b></p>
          <ul>
            <li><b>5 points</b> - Submitted/presented 80-100% of summons with complete and accurate information issued within the prescribed period.</li>
            <li><b>3 points</b> - Submitted/presented 50-79% of summons with complete and accurate information issued within the prescribed period.</li>
            <li><b>2 points</b> - Submitted/presented 1-49% of summons with complete and accurate information issued within the prescribed period.</li>
            <li><b>0 points</b> - No summons/notices submitted/presented.</li>
          </ul>

          <p><b>Note:</b> Scores will be given only when a file copy of the summons issued within the next working day is stamped with the date and time of receipt.</p>
        </details>
        </td>
            <td>10</td>
            <td>
              <?php if (!empty($row['IA_1b_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_1b_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
          </tr>
          <tr>
          <tr>
  <td>
    <details>
      <summary>
        2. Settlement and Award Period (with at least 10 settled cases within the assessment period)
      </summary>
      <p>10 points – 80-100% cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
      <p>8 points – 60-79% cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
      <p>6 points – 40-59% cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
      <p>4 points – 20-39% cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
      <p>2 points – 1-19% cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
      <p>0 points – 0 cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
    </details>
  </td>
  <td>10</td>
  <td></td>
  <td></td>
  <td></td>
</tr>

               <tr>
                <td>a) Mediation (within 15 days from initial confrontation with the Lupon Chairman)</td>
                <td></td>
                <td>
                <?php if (!empty($row['IA_2a_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2a_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>b) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td></td>
                <td>
                <?php if (!empty($row['IA_2b_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2b_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>c) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td></td>
                <td>
                <?php if (!empty($row['IA_2c_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2c_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>d) Arbitration (within 10 days from the date of the agreement to arbitrate)</td>
                <td></td>
                <td>
                <?php if (!empty($row['IA_2d_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2d_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>e) Conciliation beyond 46 days but not more than 60 days on a clearly meritorious case</td>
                <td></td>
                <td>
                <?php if (!empty($row['IA_2e_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2e_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>B. Systematic Maintenance of Records</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>1. Record of Cases </b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>For Cities - computer database with searchable case information</td>
                <td></td>
                <td>
                <?php if (!empty($row['IB_1forcities_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>For Municipalities:</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>a. Manual Records</td>
                <td></td>
                <td>
                <?php if (!empty($row['IB_1aformuni_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1aformuni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>b. Digital Record Filing</td>
                <td></td>
                <td>
                  <?php if (!empty($row['IB_1bformuni_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1bformuni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. Copies of Minutes of Lupon meetings with attendance sheets and notices</td>
                <td></td>
                <td>
                <?php if (!empty($row['IB_2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>3. Copies of reports submitted to the Court and to the DILG on file</td>
                <td></td>
                <td>
                <?php if (!empty($row['IB_3_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>4. All records are kept on file in a secured filing cabinet(s)</td>
                <td></td>
                <td>
                <?php if (!empty($row['IB_4_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_4_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>C. Timely Submissions to the Court and the DILG</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. <b>To the Court:</b> Submitted/ presented copies of settlement agreement to the Court from the lapse of the ten-day period repudiating the mediation/ conciliation settlement agreement, or within five (5) calendar days from the date of the arbitration award</td>
                <td></td>
                <td>
                <?php if (!empty($row['IC_1_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IC_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. To the DILG (Quarterly)</td>
                <td></td>
                <td>
                <?php if (!empty($row['IC_2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IC_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>D. Conduct of monthly meetings for administration of the Katarungang Pambarangay (KP)</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. Notice of Meeting</td>
                <td></td>
                <td>
                <?php if (!empty($row['ID_1_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['ID_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. Minutes of the Meeting</td>
                <td></td>
                <td>
                <?php if (!empty($row['ID_2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['ID_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>II. EFFECTIVENESS IN SECURING THE SETTLEMENT OF INTERPERSONAL DISPUTE OBJECTIVE OF THE KATARUNGANG PAMBARANGAY</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>A. Quantity of settled cases against filed</td>
                <td></td>
                <td>
                <?php if (!empty($row['IIA_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIA_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>B. Quality of Settlement of Cases</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>1. Zero cases repudiated</td>
                <td></td>
                <td>
                <?php if (!empty($row['IIB_1_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIB_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. Non-recurrence of cases settled</td>
                <td></td>
                <td>
                <?php if (!empty($row['IIB_2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIB_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>C. At least 80% compliance with the terms of settlement or award after the cases have been settled</td>
                <td></td>
                <td>
                <?php if (!empty($row['IIC_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIC_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>III. CREATIVITY AND RESOURCEFULNESS OF THE LUPONG TAGAPAMAYAPA</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>A. Settlement Technique utilized by the Lupon</td>
                <td></td>
                <td>
                <?php if (!empty($row['IIIA_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIA_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>B. Coordination with Concerned Agencies relating to disputes filed (PNP, DSWD, DILG, DAR, DENR, Office of the Prosecutor, Court, DOJ, CHR, etc.)</td>
                <td></td>
                <td>
                <?php if (!empty($row['IIIB_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIB_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>C. Sustained information drive to promote Katarungang Pambarangay</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>1. For Cities</td>
                <td></td>
              </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC materials developed</li>
                  </ul>
                </td>
                <td></td>
                <td>
                <?php if (!empty($row['IIIC_1forcities_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td></td>
                <td>
                <?php if (!empty($row['IIIC_1forcities2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td></td>
                <td>
                <?php if (!empty($row['IIIC_1forcities3_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>2. For Municipalities</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC materials developed</li>
                  </ul>
                </td>
                <td></td>
                <td>
                <?php if (!empty($row['IIIC_2formuni1_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td></td>
                <td>
                <?php if (!empty($row['IIIC_2formuni2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td></td>
                <td>
                <?php if (!empty($row['IIIC_2formuni3_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>D. KP Training or seminar within the assessment period<br />
                  Organized skills training participated by the Lupong Tagapamayapa</td>
                <td></td>
                <td>
                <?php if (!empty($row['IIID_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIID_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>IV. AREA OR FACILITY FOR KP ACTIVITIES</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>Building structure or space:</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>For Cities - the office or space should be exclusive for KP matters</td>
                <td></td>
                <td>
                <?php if (!empty($row['IV_forcities_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IV_forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>For Municipalities - KP office or space may be shared or used for other Barangay matters.</td>
                <td></td>
                <td>
                <?php if (!empty($row['IV_muni_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IV_muni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>V. FINANCIAL OR NON-FINANCIAL SUPPORT</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. From City, Municipal, Provincial or NGAs</td>
                <td></td>
                <td>
                <?php if (!empty($row['V_1_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['V_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>3 From People's Organizations, NGOs or Private Sector</td>
                <td></td>
                <td>
                <?php if (!empty($row['threepeoplesorg'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['threepeoplesorg']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
            </tbody>
          </table>
      <input type="submit" value="Okay" class="btn btn-dark mt-3" />
    </form>




        </div>
      </div>
    </div>
  </div>
</body>
      
   

    </div>
  </div>

</body>
</html>
