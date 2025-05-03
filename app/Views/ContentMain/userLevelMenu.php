<div class="d-lg-flex content-menu px-4 py-4" id="content-userLevelMenu">
    <div class="w-100 overflow-hidden position-relative">
        <div class="px-2 pb-2" id="wrapper-userlevelDetails">
            <div class="card bg-light2 mb-3 w-100 rounded-3">
				<div class="card-header d-flex align-items-center">
					<span class="user-chat-remove text-muted d-flex align-items-center">
						<i class="ri-arrow-left-s-line font-size-22 me-2 d-block d-lg-none"></i> 
						<h4 class="card-title mb-0">User Level Details</h4>
					</span>
                    <button type="button" class="btn btn-primary btn-sm ms-auto" id="userLevelMenu-btnAddNewLevel" data-bs-toggle="modal" data-bs-target="#modal-addNewUserLevel"><i class="ri-add-line"></i> New Level</button>
				</div>
                <div class="card-body p-4">
                    <div class="row" id="userLevelDetails-containerNameDescriptionForm">
                        <div class="col-lg-4 col-sm-12 mb-3">
                            <label for="levelName" class="form-label">Level Name</label>
                            <input type="text" class="form-control" id="userlevelDetails-levelName" name="userlevelDetails-levelName" placeholder="Level name">
                        </div>
                        <div class="col-lg-8 col-sm-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="userlevelDetails-description" name="userlevelDetails-description" placeholder="Description">
                        </div>
                    </div>
                    <div class="row">
                        <hr/>
                        <div class="col-12 mb-1" id="userLevelDetails-containerTableMenuList">
                            <table id="userLevelDetails-tableMenuList" class="table table-bordered table-hover table-sm mb-0">
                                <thead class="table-dark">
                                    <tr class="p-2">
                                        <th>Menu Name</th>
                                        <th width="180">Open Access</th>
                                        <th width="300">Allow Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="p-2">
                                        <td colspan="3" class="text-center">No data available</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <input type="hidden" id="userlevelDetails-idUserLevel" name="userlevelDetails-idUserLevel" value="">
                        <button type="button" class="btn btn-primary btn-sm" id="userlevelDetails-btnSaveMenuLevelAdmin"><i class="ri-save-line"></i> Save Change</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-addNewUserLevel" tabindex="-1" role="dialog" aria-labelledby="modal-addNewUserLevelLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="modalAddNewUserLevel-form" method="post">
            <div class="modal-header">
                <h5 class="modal-title font-size-16" id="modal-addNewUserLevelLabel">Add New User Level</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="modalAddNewUserLevel-userLevelName" class="form-label">Level Name</label>
                    <input type="text" class="form-control" id="modalAddNewUserLevel-userLevelName" placeholder="Level Name">
                </div>
                <div class="mb-3">
                    <label for="modalAddNewUserLevel-description" class="form-label">Description</label>
                    <textarea class="form-control" id="modalAddNewUserLevel-description" rows="3" placeholder="Description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<script>
	var jsFileUrl = "<?=BASE_URL_ASSETS_JS?>menu/settings/userLevelMenu.js?<?=date("YmdHis")?>";
	$.getScript(jsFileUrl);
</script>