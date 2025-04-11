<div class="account-pages my-5 pt-sm-5">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8 col-lg-6 col-xl-5">
				<div class="text-center mb-4">
					<a href="index.html" class="auth-logo mb-5 d-block"><img src="<?=BASE_URL_ASSETS_IMG?>logo-single-2025.png" width="60px" /></a>
					<h4>Sign in</h4>
					<p class="text-muted mb-4">Sign in to continue to chat application</p>
				</div>
				<div class="card">
					<div class="card-body p-4">
						<div class="p-3">
							<form id="login-form" method="POST">
								<div class="mb-3" id="container-warning-element">
									<div class="alert alert-dark alert-dismissible fade show" role="alert" id="warning-element">
										<p class="mb-0"></p>
										<button type="button" class="btn-close mt-1" data-bs-dismiss="alert" aria-label="Close"></button>
									</div>
								</div>
								<div class="mb-3">
									<label class="form-label">Username</label>
									<div class="input-group mb-3 bg-light-subtle rounded-3">
										<span class="input-group-text text-muted" id="basic-addon3">
											<i class="ri-user-2-line"></i>
										</span>
										<input type="text" class="form-control form-control-lg border-light bg-light-subtle" id="username" name="username" placeholder="Enter Username" aria-label="Enter Username" aria-describedby="basic-addon3">
									</div>
								</div>
								<div class="mb-3">
									<label class="form-label">Password</label>
									<div class="input-group mb-3 bg-light-subtle rounded-3">
										<span class="input-group-text text-muted" id="basic-addon4">
											<i class="ri-lock-2-line"></i>
										</span>
										<input type="password" class="form-control form-control-lg border-light bg-light-subtle" id="password" name="password" placeholder="Enter Password" aria-label="Enter Password" aria-describedby="basic-addon4">
										<i class="ri-eye-line position-absolute" id="togglePassword" style="cursor: pointer; top: 50%; right: 15px; transform: translateY(-50%);"></i>
									</div>
								</div>
								<div class="mb-3">
									<label class="form-label">Captcha</label>
									<div class="input-group mb-3 bg-light-subtle rounded-3">
										<span class="input-group-text text-muted" id="basic-addon5">
											<i class="ri-keyboard-fill"></i>
										</span>
										<input type="text" class="form-control form-control-lg border-light bg-light-subtle" id="captcha" name="captcha" placeholder="Enter Captcha" aria-label="Enter Captcha" aria-describedby="basic-addon5">
										<img id="captchaImage" class="mx-auto border-light" style="max-width: 150px; max-height: 46px; border: 1px solid;"/>
										<button type="button" class="btn btn-primary waves-effect waves-light" id="btnRefreshCaptcha"><i class="ri-refresh-line"></i></button>
									</div>
								</div>
								<div class="mb-4">
								</div>
								<div class="d-grid text-center">
									<button class="btn btn-primary waves-effect waves-light" type="submit">Sign in</button>
									<a class="mt-3" id="clearCacheReloadLink" href="#">Clear Cache & Reload</a>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="mt-5 text-center">
					<p>© 2024 - Bali SUN Tours</p>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="<?=BASE_URL_ASSETS_JS?>login.js?<?=date('YmdHis')?>"></script>