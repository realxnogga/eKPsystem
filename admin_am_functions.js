
function loadSecretarysCornerContent() {
  return `
   <section class="flex flex-col gap-y-2">
      <h3 class="text-[2rem]">1. Secretary's Corner</h3>
      <p>Here, all of the verified barangay accounts will be shown, and you can search for specific barangay and view reports that they have made.</p>
      <img class="w-[60rem]" src="user_manual_pic/secretarys_corner.png" alt="secretary's corner picture">

      <p>1. Overview of barangay's report.</p>
      <p>Overview of reports that have created by the barangay. You can filter it by year and months of that year.</p>
      <p>A year will not appear in the select year dropdown if no report has been created by that year (for example, 2025).</p>
      <img class="w-[60rem]" src="user_manual_pic/brgy_report.png" alt="barangay report picture">  

        <p>By default, if a barangay has created any reports, the most recent report will automatically be displayed.</p>
        <img class="w-[40rem]" src="user_manual_pic/noreport.png" alt="no report picture"> 

        <p>If no report has been created by any barangay for the selected year or month, the dropdown will display "<i>No Report Yet</i>" to indicate that no data is available.</p>
        <img class="w-[40rem]" src="user_manual_pic/withreport.png" alt="no report picture"> 

        <p>When you select a year (e.g., 2024) from the "Select Year" dropdown, the "Select Month" dropdown will display only the months where reports have been created for that year. Note that both a year and its corresponding months will appear in their respective dropdowns only if a barangay has generated a report during that specific year or month.</p>
    </section>
`;
}

function loadAccountRequestContent() {
  return `
    <section class="flex flex-col gap-y-2">
      <h3 class="text-[2rem]">1. Account Request</h3>
      <p>Overview of the barangay accounts that want to be unlocked.</p>
      <p><span class="text-green-500">Unlock</span>:  Granting access to the System.</p>
      <p><span class="text-red-500">Deny</span>:  Deleting the barangay account and all related data in the database</p>
      <p><span class="text-black">Manage</span>:  Updating the information of the barangay account.</p>
      <img class="w-[40rem]" src="user_manual_pic/acc_request.png" alt="secretary's corner picture">

      <p>If you click the <span class="text-green-500">Unlock</span> button, it will appear in Secretaries corner'table making that barangay able to login.</p>

      <img class="w-[20rem]" src="user_manual_pic/accountrequesttab.png" alt="account request tab picture">
      <p>The green circle with a number represents the total count of barangay accounts awaiting admin verification. It will only be visible when there are barangay accounts requesting to be unlock.</p>

      <img class="w-[60rem]" src="user_manual_pic/manageaccountrequest.png" alt="manage account request picture">
      <p>All the fields here are editable except the Municipality Name and Barangay Name fields.</p>

    </section>
`;
}

function loadAssessorRequestContent() {
  return `
   <section class="flex flex-col gap-y-2">
      <h3 class="text-[2rem]">1. Assessor Request</h3>
      <p>Overview of the assessor accounts that want to try to log in.</p>
      <p><span class="text-green-500">Unlock</span>:  Granting access to the System.</p>
      <p><span class="text-red-500">Deny</span>:  Deleting the assessor account and all related data in the database.</p>
      <p><span class="text-black">Manage</span>:  Updating the information of the assessor account, including the password.</p>
      <img class="w-[40rem]" src="user_manual_pic/assessor_request.png" alt="assessor account picture">
      <p>If you click the Unlock button, it will appear in Secretaries corner'table making that barangay able to login.</p>
      <img class="w-[20rem]" src="user_manual_pic/assessorrequesttab.png" alt="assessor request tab picture">
      <p>The green circle with a number represents the total count of assessor accounts awaiting admin verification. It will only be visible when there are assessor accounts requesting to be unlock.</p>
      <img class="w-[60rem]" src="user_manual_pic/manageassessorrequest.png" alt="manage assessor request picture">
       <p>All the fields here are editable except the Municipality Name fields.</p>
   </section>
`;
}

function loadSettingContent() {
  return `
     <section class="flex flex-col gap-y-2">
      <h3 class="text-[2rem]">1. Update Information</h3>
      <p>All the fields here are editable. Leave the password field empty to keep the current password. After inputting your desired changes, click the <span class="text-green-500">Save Changes</span> button.</p>
      <img class="w-[20rem]" src="user_manual_pic/account_setting.png" alt="account setting picture">

      <p>Click the "Upload" button to select a photo from your device, choose either a square or dynamic aspect ratio, and then click the "Crop" button; once cropping is successful, a notification will pop up, and the cropped image will automatically update in the sidebar, header, KPs.</p>
      <img class="w-[60rem]" src="user_manual_pic/cropimage.png" alt="crop image picture">
    </section>

    <section class="flex flex-col gap-y-2">
      <h3 class="text-[2rem]">2. Update Security Setting</h3>
      <p>You can update the three answers and questions that you have provided before</p>
      <img class="w-[20rem]" src="user_manual_pic/security_setting.png" alt="security setting picture">
    </section>
`;
}

function loadLTIAContent() {
  return `
 <section class="h-full text-center">
    <p>No data yet!</p>
 </section>
`;
}
