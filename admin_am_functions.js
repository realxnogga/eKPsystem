
function loadSecretarysCornerContent() {
    return `
       <section class="flex flex-col gap-y-2">
          <h3 class="text-[2rem]">1. Secretary's Corner</h3>
          <p>Here, all of the verified barangay accounts will be shown, and you can search for and view reports for a particular barangay.</p>
          <img class="w-[60rem]" src="user_manual_pic/secretarys_corner.png" alt="secretary's corner picture">

          <p>1. Overview of barangay's report.</p>
          <p>Overview of reports that have created by the barangay. You can filter it by year and months of that year.</p>
          <p>A year will not appear in the select year dropdown if no report has been created by that year (for example, 2025).</p>
          <img class="w-[60rem]" src="user_manual_pic/brgy_report.png" alt="barangay report picture">  
        </section>
    `;
}

function loadAccountRequestContent() {
    return `
        <section class="flex flex-col gap-y-2">
          <h3 class="text-[2rem]">1. Account Request</h3>
          <p>Overview of the barangay accounts that want to try to log in.</p>
          <p><span class="text-green-500">Unlock</span>:  Granting access to the System.</p>
          <p><span class="text-red-500">Deny</span>:  Deleting the barangay account and all related data in the database</p>
          <p><span class="text-black">Manage</span>:  Updating the information of the barangay account, including the password.</p>
          <img class="w-[40rem]" src="user_manual_pic/account_request.png" alt="secretary's corner picture">
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
       </section>
    `;
}

function loadSettingContent() {
    return `
         <section class="flex flex-col gap-y-2">
          <h3 class="text-[2rem]">1. Update Information</h3>
          <p>You can update your information, such as your profile picture, username, first name, last name, etc. Leave the password field empty to keep the current password.</p>
          <img class="w-[20rem]" src="user_manual_pic/account_setting.png" alt="account setting picture">
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




