<script>
const interval_id = window.setInterval(function(){}, Number.MAX_SAFE_INTEGER);
for (let i = 1; i < interval_id; i++) {
  window.clearInterval(i);
}
if(!window.jQuery){
    window.location = window.location.origin;
}
if(window.location.href != '<?=BASE_URL?>') window.history.replaceState({Title: '<?=APP_NAME?>', Url: '<?=BASE_URL?>'}, '<?=APP_NAME?>', '<?=BASE_URL?>');
</script>
<div class="layout-wrapper d-lg-flex">
	<div class="side-menu flex-lg-column me-lg-1 ms-lg-0">
		<div class="navbar-brand-box">
			<a href="<?=BASE_URL?>" class="logo logo-dark">
				<span class="logo-sm">
					<img src="<?=BASE_URL_ASSETS_IMG?>logo-single-2025.png" alt="" height="30">
				</span>
			</a>
			<a href="<?=BASE_URL?>" class="logo logo-light">
				<span class="logo-sm">
					<img src="<?=BASE_URL_ASSETS_IMG?>logo-single-2025.png" alt="" height="30">
				</span>
			</a>
		</div>
		<div class="flex-lg-column my-auto">
			<ul class="nav nav-pills side-menu-nav justify-content-center" role="tablist">
				<?=$menuElement?>
				<li class="nav-item dropdown profile-user-dropdown d-inline-block d-lg-none">
					<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span style="font-size: 18px;width: 80%;height: 80%;" class="avatar-title rounded-circle bg-primary-subtle text-primary mx-auto mt-1">G</span>
					</a>
					<div class="dropdown-menu mb-2">
						<a data-toggle="modal" data-target="#modal-userProfile" class="dropdown-item" href="#">Profile <i class="ri-profile-line float-end text-muted"></i></a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="#" onclick="clearAppData()">Clear Cache <i class="ri-delete-bin-line float-end text-muted"></i></a>
						<a class="dropdown-item linkLogout" href="#">Log out <i class="ri-logout-circle-r-line float-end text-muted"></i></a>
					</div>
				</li>
			</ul>
		</div>
		<div class="flex-lg-column d-none d-lg-block">
			<ul class="nav side-menu-nav justify-content-center">
				<li class="nav-item">
					<a class="nav-link light-dark-mode" href="#" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" title="Dark / Light Mode">
						<i class='ri-sun-fill theme-mode-icon'></i>
					</a>
				</li>
				<li class="nav-item btn-group dropup profile-user-dropdown">
					<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span style="font-size: 18px;width: 80%;height: 80%;" class="avatar-title rounded-circle bg-primary-subtle text-primary mx-auto">G</span>
					</a>
					<div class="dropdown-menu mb-2">
						<a data-toggle="modal" data-target="#modal-userProfile" class="dropdown-item" href="#">Profile <i class="ri-profile-line float-end text-muted"></i></a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="#" onclick="clearAppData()">Clear Cache <i class="ri-delete-bin-line float-end text-muted"></i></a>
						<a class="dropdown-item linkLogout" href="#">Log out <i class="ri-logout-circle-r-line float-end text-muted"></i></a>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<div class="chat-leftsidebar me-lg-1 ms-lg-0">
		<div class="tab-content" id="tab-content-pills"></div>
	</div>
	<div class="user-chat w-100 overflow-hidden" id="content-container"></div>
</div>
<div class="modal fade" id="modal-userProfile" aria-labelledby="User Profile" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form class="modal-content form-horizontal was-validated" id="form-userProfile">
			<div class="modal-header">
				<h4 class="modal-title" id="editor-userProfile">Account Settings</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="mb-4">
					<label for="userProfile-name" class="form-label">Name</label>
					<input type="text" class="form-control" id="userProfile-name" name="userProfile-name" placeholder="Name" autocomplete="off" required>
				</div>
				<div class="mb-4">
					<label for="userProfile-email" class="form-label">Email Address</label>
					<input type="text" class="form-control" id="userProfile-email" name="userProfile-email" placeholder="Email">
				</div>
				<div class="mb-4">
					<label for="userProfile-username" class="form-label">Username</label>
					<div class="input-group">
						<span class="input-group-text" id="userProfile-prefixUsername">@</span>
  						<input type="text" class="form-control" id="userProfile-username"" name="userProfile-username" placeholder="Username" autocomplete="off" aria-label="Username" aria-describedby="userProfile-prefixUsername" required>
					</div>
				</div><br/>
				<p>Fill in the form below to change your password</p>
				<hr class="mb-4"/>
				<div class="mb-4">
					<label for="userProfile-password" class="form-label">Old Password</label>
					<input type="password" class="form-control" id="userProfile-oldPassword" name="userProfile-oldPassword" autocomplete="new-password" placeholder="Old Password">
				</div>
				<div class="mb-4">
					<label for="userProfile-newPassword" class="form-label">New Password</label>
					<input type="password" class="form-control" id="userProfile-newPassword" name="userProfile-newPassword" autocomplete="new-password" placeholder="New Password">
				</div>
				<div class="mb-4">
					<label for="userProfile-repeatNewPassword" class="form-label">Repeat New Password</label>
					<input type="password" class="form-control" id="userProfile-repeatNewPassword" name="userProfile-repeatNewPassword" autocomplete="new-password" placeholder="Repeat New Password">
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary" id="saveSetting">Save</button>
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="modalWarning" aria-labelledby="Warning-Information" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalWarningTitle">Warning</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="modalWarningBody">-</div>
			<div class="modal-footer">
				<button class="btn btn-primary" id="modalWarningBtnOK" data-bs-dismiss="modal">OK</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-confirm-action" aria-labelledby="Confirmation" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="modal-confirm-title">Confirmation</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="modal-confirm-body"></div>
           <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="confirmBtn" data-idData="" data-function="">Yes</button>
           </div>
        </div>
    </div>
</div>
<div class="modal loader-modal" id="window-loader" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<div class="modal-body">
				<div class="d-flex justify-content-center">
					<div class="spinner-border text-success">
						<span class="visually-hidden">Loading...</span>
					</div>
				</div><br/>
				<div class="row">
					<div class="col-12 text-center">
						<span>Loading, please wait..</span>
					</div>
				</div>
			</div>
        </div>
    </div>
</div>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 999">
	<div id="liveToast" class="toast hide bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
		<div class="toast-body d-flex align-items-center">
			<i id="liveToast-icon" class="ri-check-line me-3" style="font-size: 2rem; opacity: 0.8;"></i>
			<span id="liveToast-body" style="font-size: .8rem;"></span>
		</div>
	</div>
</div>
<input type="hidden" id="lastMenuAlias" name="lastMenuAlias" value="">
<style>
@keyframes animate{
   30%{
     opacity: 0.4;
   }
   60%{
     opacity: 0.6;
   }
   90%{
     opacity: 0.8;
   }
}
</style>
<script>
	localStorage.setItem('lastApplicationLoadTime', '<?=gmdate("YmdHis")?>');
	localStorage.setItem('allowNotifList', '<?=json_encode($allowNotifList)?>');
	localStorage.setItem('appVisibility', true);
	var baseURL				=	'<?=BASE_URL?>',
		baseURLAssetsSound	=	'<?=BASE_URL_ASSETS_SOUND?>',
		loaderElem			=	"<center class='mt-5 font-size-14' id='loaderElem'>"+
								"	<img src='<?=BASE_URL_ASSETS_IMG?>loader_content.gif' class='height-25'/><br/><br/>"+
								"	Loading Content..."+
								"</center>";
		
	$.ajaxSetup({ cache: true });

	function getAllFunctionName() {
		var allFunctionName = [];
		for (var i in window) {
			if ((typeof window[i]).toString() == "function") {
				allFunctionName.push(window[i].name);
			}
		}

		return allFunctionName;
	}
	
	function clearAppData(showWarning = true){
		var localStorageKeys	=	Object.keys(localStorage),
			localStorageIdx		=	localStorageKeys.length,
			allFunctionName		=	getAllFunctionName();
		for(var i=0; i<localStorageIdx; i++){
			var keyName			=	localStorageKeys[i];
			if(keyName.substring(0, 5) == "form_"){
				localStorage.removeItem(keyName);
			}
		}

		for(var i=0; i<allFunctionName.length; i++){
			var functionName	=	allFunctionName[i];
			if(functionName.slice(-4) === "Func"){
				window[functionName]	=	null;
			}
		}

		if(showWarning){
			$("#modalWarning").on("show.bs.modal", function () {
				$("#modalWarningBody").html("App data has been cleared");
			});
			$("#modalWarning").modal("show");
		}
	}
	clearAppData(false);
</script>
<script>
	var intervalId;
	var timezoneOffset	=	moment.tz.guess(),
		dateToday		=	moment().format('DD-MM-YYYY'),
		url				=	"<?=BASE_URL_ASSETS_JS?>app.js?<?=date('YmdHis')?>";
	$.getScript(url);
</script>
<?=$firebaseScript?>