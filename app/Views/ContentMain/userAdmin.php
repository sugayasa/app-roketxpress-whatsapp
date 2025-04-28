<div class="d-lg-flex content-menu px-4 py-4" id="content-userAdmin">
    <div class="w-100 overflow-hidden position-relative">
        <form id="editorUserAdmin-form" class="px-2" autocomplete="off">
            <div class="card bg-light2 mb-0 w-100 rounded-3">
				<div class="card-header d-flex align-items-center">
					<span class="user-chat-remove text-muted d-flex align-items-center">
						<i class="ri-arrow-left-s-line font-size-22 me-2 d-block d-lg-none"></i> 
						<h4 class="card-title mb-0">User Admin Details</h4>
					</span>
					<button type="button" class="btn btn-primary btn-sm ms-auto" id="userAdmin-btnAddNewUserAdmin"><i class="ri-add-line"></i> New User Admin</button>
				</div>
                <div class="card-body p-4" id="simpleScrollBar-detailUserAdmin">
					<div class="row">
						<div class="col-lg-8 col-sm-6 mb-3">
							<label for="editorUserAdmin-name" class="form-label">Name</label>
							<input type="text" class="form-control" id="editorUserAdmin-name" name="editorUserAdmin-name" placeholder="Name" required>
						</div>
						<div class="col-lg-4 col-sm-6 mb-3">
							<label for="editorUserAdmin-username" class="form-label">Username</label>
							<input type="text" class="form-control" id="editorUserAdmin-username" name="editorUserAdmin-username" placeholder="Username" autocomplete="new-username" required>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-8 col-sm-6 mb-3">
							<label for="editorUserAdmin-email" class="form-label">Email</label>
							<input type="email" class="form-control" id="editorUserAdmin-email" name="editorUserAdmin-email" placeholder="Email" required>
						</div>
						<div class="col-lg-4 col-sm-6 mb-3">
							<label for="editorUserAdmin-linkedAccountName" class="form-label">Linked Account</label>
							<div class="alert alert-danger mb-0 py-2" role="alert">
								<span id="editorUserAdmin-linkedAccountName"></span>
								<i class="ri-pencil-line float-end font-size-18" id="editorUserAdmin-btnChooseLinkedAccount"></i>
							</div>
						</div>
					</div>
					<hr class="mt-2">
					<div class="row">
						<div class="col-lg-6 col-sm-12 mt-3 mb-3">
							<label for="editorUserAdmin-optionLevelUserAdmin" class="form-label">User Level</label>
							<select class="form-select" id="editorUserAdmin-optionLevelUserAdmin" name="editorUserAdmin-optionLevelUserAdmin" required></select>
							<div class="mt-3">
								<h6 class="card-title mb-2">Available Menus</h6>
								<ul class="list-group list-group-flush" id="editorUserAdmin-menuListAvailable"></ul>
							</div>
						</div>
						<div class="col-lg-6 col-sm-12 mt-3 mb-3">
							<h6 class="card-title mb-0">Password Settings</h6>
							<div class="form-text mb-2">Leave the password blank if you don't want to change it</div>
							<hr class="my-2">
							<div class="mb-3" id="editorUserAdmin-containerCurrentPassword">
								<label for="editorUserAdmin-currentPassword" class="form-label">Current Password</label>
								<div class="input-group">
									<input type="password" class="form-control" id="editorUserAdmin-currentPassword" name="editorUserAdmin-currentPassword" autocomplete="new-password" placeholder="Current Password">
									<button type="button" class="btn btn-secondary inputPassword-toggleVisibility"><i class="ri-eye-off-line"></i></button>
								</div>
							</div>
							<div class="mb-3">
								<label for="editorUserAdmin-password" class="form-label">New Password</label>
								<div class="input-group">
									<input type="password" class="form-control" id="editorUserAdmin-password" name="editorUserAdmin-password" autocomplete="new-password" placeholder="New Password">
									<button type="button" class="btn btn-secondary inputPassword-toggleVisibility"><i class="ri-eye-off-line"></i></button>
								</div>
							</div>
							<div class="mb-3">
								<label for="editorUserAdmin-repeatPassword" class="form-label">Repeat Password</label>
								<div class="input-group">
									<input type="password" class="form-control" id="editorUserAdmin-repeatPassword" name="editorUserAdmin-repeatPassword" autocomplete="new-password" placeholder="Repeat Password">
									<button type="button" class="btn btn-secondary inputPassword-toggleVisibility"><i class="ri-eye-off-line"></i></button>
								</div>
							</div>
						</div>
					</div>
					<div class="row mt-2 pt-4 border-top" id="editorUserAdmin-containerDateTimeDetails">
						<div class="col-lg-6 col-sm-12 mb-2">
							<label class="form-label mb-0">Last Login</label>
							<div class="d-flex align-items-center">
								<i class="ri-time-line me-2"></i>
								<span id="userAdminDetails-lastLogin">Not Available</span>
							</div>
						</div>
						<div class="col-lg-6 col-sm-12 mb-2">
							<label class="form-label mb-0">Last Activity</label>
							<div class="d-flex align-items-center">
								<i class="ri-calendar-event-line me-2"></i>
								<span id="userAdminDetails-lastActivity">Not Available</span>
							</div>
						</div>
					</div>
				</div>
				<div class="card-footer">
					<div class="d-flex justify-content-end">
                        <input type="hidden" id="editorUserAdmin-idUserAdminInternal" name="editorUserAdmin-idUserAdminInternal" value="">
						<input type="hidden" name="editorUserAdmin-idUserAdmin" id="editorUserAdmin-idUserAdmin" value="0">
						<button type="button" class="btn btn-info btn-sm me-2" id="editorUserAdmin-btnCancel"><i class="ri-close-line"></i> Cancel</button>
						<button type="submit" class="btn btn-primary btn-sm" id="editorUserAdmin-btnSave"><i class="ri-check-line"></i> Save</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="modal-chooseLinkedAccount" tabindex="-1" role="dialog" aria-labelledby="modal-chooseLinkedAccountLabel" aria-modal="true">
	<div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content" id="modalChooseLinkedAccount-form" method="post">
			<div class="modal-header">
				<h5 class="modal-title font-size-16" id="modal-chooseLinkedAccountLabel">Link With Internal User Account</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-4">
				<ul class="list-group" id="modalChooseLinkedAccount-listAccount"></ul>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-info" data-bs-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-primary" id="modalChooseLinkedAccount-btnSelectAccount">Select Account</button>
			</div>
		</form>
	</div>
</div>
<script>
	var jsFileUrl = "<?=BASE_URL_ASSETS_JS?>menu/settings/userAdmin.js?<?=date("YmdHis")?>";
	$.getScript(jsFileUrl);
</script>