<div class="d-lg-flex content-menu" id="content-chat">
    <div class="w-100 overflow-hidden position-relative" id="container-chatForm">
        <div id="content-chat-landing" class="text-center" style="margin-top:50px">
            <img src="<?=BASE_URL_ASSETS_IMG?>logo-single-2025.png" class="img-fluid mb-3 text-muted" style="height:100px">
            <h5 class="text-muted"><?=APP_NAME_FORMAL?></h5>
            <p class="text-muted">Conversation system for integrated tour travel reservations</p>
        </div>
        <div id="chat-topbar" class="p-3 p-lg-4 border-bottom user-chat-topbar d-none">
            <div class="row align-items-center">
                <div class="col-sm-4 col-8">
                    <div class="d-flex align-items-center">
                        <div class="d-block d-lg-none me-2 ms-0">
                            <span class="user-chat-remove text-muted p-2 mb-3"><i class="ri-arrow-left-s-line font-size-22"></i></span>
                        </div>
                        <div class="me-3 ms-0">
                            <div class="chat-user-img align-self-center me-0 ms-0">
                                <div class="avatar-xs">
                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary" id="chat-topbar-initial">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h5 class="font-size-16 mb-0 text-truncate">
                                <a id="chat-topbar-fullName" href="#" class="text-reset user-profile-show">-</a><br/>
                                <span id="chat-topbar-badgeSession" class="badge text-white font-size-12 align-middle">-</span>
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8 col-4">
                    <ul class="list-inline user-chat-nav text-end mb-0">                                        
                        <li class="list-inline-item">
                            <div class="dropdown">
                                <button class="btn nav-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="ri-search-line"></i>
                                </button>
                                <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-md">
                                    <div class="search-box p-2">
                                        <input type="text" class="form-control bg-light border-0" placeholder="Search..">
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>                                    
                </div>
            </div>
        </div>
        <div id="chat-conversation" class="chat-conversation p-3 p-lg-4 d-none" data-simplebar>
            <ul id="chat-conversation-ul" class="list-unstyled mb-0"></ul>
        </div>
        <div id="chat-input-section" class="chat-input-section p-2 p-lg-3 border-top mb-0 d-none">
            <form class="row g-0" id="chat-formMessage" name="chat-formMessage" method="post" enctype="multipart/form-data">
                <div class="col">
                    <textarea type="text" class="form-control form-control-lg bg-light border-light" id="chat-inputTextMessage" placeholder="Enter Message..." rows="1" autofocus></textarea>
                </div>
                <div class="col-auto d-flex flex-column">
                    <div class="chat-input-links ms-md-2 me-md-0 mt-auto">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item">
                                <input type="hidden" id="chat-idContact" name="chat-idContact" value="">
                                <input type="hidden" id="chat-idChatList" name="chat-idChatList" value="">
                                <input type="hidden" id="chat-timeStampLastReply" name="chat-timeStampLastReply" value="0">
                                <input type="hidden" id="chat-threadPage" name="chat-threadPage" value="1">
                                <input type="hidden" id="chat-isMaximumChatThreadContent" name="chat-isMaximumChatThreadContent" value="false">
                                <button type="submit" class="btn btn-primary font-size-16 btn-lg chat-send waves-effect waves-light" id="chat-btnSendMessage"><i class="ri-send-plane-2-fill"></i></button>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="user-profile-sidebar">
        <div class="px-3 px-lg-4 pt-3 pt-lg-4">
            <div class="user-chat-nav text-end">
                <button type="button" class="btn nav-btn" id="user-profile-hide">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        </div>
        <div class="text-center p-4 border-bottom">
            <div class="mb-4">
                <div class="chat-user-img align-self-center me-3 ms-0">
                    <div class="avatar-xs mx-auto">
                        <span id="profile-sidebar-initial" class="avatar-title rounded-circle bg-primary-subtle text-primary font-size-24">-</span>
                    </div>
                </div>
            </div>
            <h5 class="font-size-24 mb-1 text-truncate" id="profile-sidebar-fullName">-</h5>
            <p class="text-muted text-truncate mb-1" id="profile-sidebar-phoneNumber">-</p>
        </div>
        <div class="p-4 user-profile-desc" data-simplebar>
            <div class="text-muted text-center">
                <p class="mb-4"><span id="profile-sidebar-countryContinent">-</span><br/><span id="profile-sidebar-email">-</span></p>
            </div>
            <div class="accordion" id="profile-sidebar-reservationList"></div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-messageACKDetails" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <dl class="row mb-0" id="messageACKDetails-rowData"></dl>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-editReservation" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="modalEditReservation-form">
                    <div class="row border-bottom p-2 mb-2">
                        <div class="col-md-6 col-12">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="modalEditReservation-title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="modalEditReservation-title" name="modalEditReservation-title" placeholder="Enter reservation title" required>
                                </div>
                                <div class="col-lg-4 col-md-5 mb-3">
                                    <label for="modalEditReservation-durationDay" class="form-label">Duration (Day)</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-light btn-number fw-bold px-2" data-field="modalEditReservation-durationDay" data-type="minus"><i class="ri-subtract-line"></i></button>
                                        <input type="text" class="form-control input-number text-end" id="modalEditReservation-durationDay" name="modalEditReservation-durationDay" value="1" min="1" max="99" required>
                                        <button type="button" class="btn btn-light btn-number fw-bold px-2" data-field="modalEditReservation-durationDay" data-type="plus"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-7 mb-3">
                                    <label for="modalEditReservation-date" class="form-label">Date</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control input-date-single" id="modalEditReservation-date" name="modalEditReservation-date" value="<?=date('d-m-Y')?>" required readonly>
                                        <span class="input-group-text bg-light fw-bold px-2"><i class="ri-calendar-line"></i></span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-6 mb-3">
                                    <label for="modalEditReservation-timeHour" class="form-label">Time</label>
                                    <select class="form-control form-select" id="modalEditReservation-timeHour" name="modalEditReservation-timeHour" required></select>
                                </div>
                                <div class="col-lg-2 col-md-6 mb-3">
                                    <label for="modalEditReservation-timeMinute" class="form-label">&nbsp;</label>
                                    <select class="form-control form-select" id="modalEditReservation-timeMinute" name="modalEditReservation-timeMinute" required></select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="modalEditReservation-pickUpArea" class="form-label">Pick Up Area</label>
                                    <select class="form-control form-select" id="modalEditReservation-pickUpArea" name="modalEditReservation-pickUpArea" option-all="Without Transfer" option-all-value="-1"></select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="modalEditReservation-hotelName" class="form-label">Hotel Name</label>
                                    <input type="text" class="form-control" id="modalEditReservation-hotelName" name="modalEditReservation-hotelName" placeholder="Enter hotel name">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="modalEditReservation-pickupLocation" class="form-label">Pick Up Location</label>
                                    <input type="text" class="form-control" id="modalEditReservation-pickupLocation" name="modalEditReservation-pickupLocation" placeholder="Enter pick up location">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="modalEditReservation-pickupLocationLinkUrl" class="form-label">Pick Up Location Url/Link</label>
                                    <input type="text" class="form-control" id="modalEditReservation-pickupLocationLinkUrl" name="modalEditReservation-pickupLocationLinkUrl" placeholder="Enter pick up location url/link">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="modalEditReservation-dropOffLocation" class="form-label">Drop Off Location</label>
                                    <input type="text" class="form-control" id="modalEditReservation-dropOffLocation" name="modalEditReservation-dropOffLocation" placeholder="Enter drop off location">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="row">
                                <div class="col-4 mb-3">
                                    <label for="modalEditReservation-paxAdult" class="form-label">Adult</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-light btn-number fw-bold px-2" data-field="modalEditReservation-paxAdult" data-type="minus"><i class="ri-subtract-line"></i></button>
                                        <input type="text" class="form-control input-number text-end" id="modalEditReservation-paxAdult" name="modalEditReservation-paxAdult" value="1" min="1" max="99" required>
                                        <button type="button" class="btn btn-light btn-number fw-bold px-2" data-field="modalEditReservation-paxAdult" data-type="plus"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                                <div class="col-4 mb-3">
                                    <label for="modalEditReservation-paxChild" class="form-label">Child</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-light btn-number fw-bold px-2" data-field="modalEditReservation-paxChild" data-type="minus"><i class="ri-subtract-line"></i></button>
                                        <input type="text" class="form-control input-number text-end" id="modalEditReservation-paxChild" name="modalEditReservation-paxChild" value="0" min="0" max="99" required>
                                        <button type="button" class="btn btn-light btn-number fw-bold px-2" data-field="modalEditReservation-paxChild" data-type="plus"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                                <div class="col-4 mb-3">
                                    <label for="modalEditReservation-paxInfant" class="form-label">Infant</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-light btn-number fw-bold px-2" data-field="modalEditReservation-paxInfant" data-type="minus"><i class="ri-subtract-line"></i></button>
                                        <input type="text" class="form-control input-number text-end" id="modalEditReservation-paxInfant" name="modalEditReservation-paxInfant" value="0" min="0" max="99" required>
                                        <button type="button" class="btn btn-light btn-number fw-bold px-2" data-field="modalEditReservation-paxInfant" data-type="plus"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-5 mb-3">
                                    <label for="modalEditReservation-incomeCurrency" class="form-label">Currency</label>
                                    <select class="form-control form-select" id="modalEditReservation-incomeCurrency" name="modalEditReservation-incomeCurrency" required>
										<option value="IDR">IDR</option>
										<option value="USD">USD</option>
									</select>
                                </div>
                                <div class="col-lg-6 col-md-12 mb-3">
                                    <label for="modalEditReservation-incomeInteger" class="form-label">Integer</label>
                                    <input type="text" class="form-control text-end" id="modalEditReservation-incomeInteger" name="modalEditReservation-incomeInteger" value="1" required maxlength="12"  onkeyup="calculateReservationIncomeIDR()" onkeypress="maskNumberInput(0, 999999999, 'modalEditReservation-incomeInteger');">
                                </div>
                                <div class="col-lg-2 col-md-4 mb-3">
                                    <label for="modalEditReservation-incomeComma" class="form-label">Comma</label>
                                    <input type="text" class="form-control text-end decimalInput" id="modalEditReservation-incomeComma" name="modalEditReservation-incomeComma" value="0" required maxlength="2" onkeyup="calculateReservationIncomeIDR()" onkeypress="maskNumberInput(0, 99, 'modalEditReservation-incomeComma');">
                                </div>
                                <div class="col-lg-4 col-md-5 mb-3">
                                    <label for="modalEditReservation-incomeCurrencyExchange" class="form-label">Currency Exchange</label>
                                    <input type="text" class="form-control text-end" id="modalEditReservation-incomeCurrencyExchange" name="modalEditReservation-incomeCurrencyExchange" value="1" required readonly onkeyup="calculateReservationIncomeIDR()" onkeypress="maskNumberInput(1, 999999999, 'modalEditReservation-incomeCurrencyExchange')">
                                </div>
                                <div class="col-lg-8 col-md-7 mb-3">
                                    <label for="modalEditReservation-incomeTotalIDR" class="form-label">Total Income (IDR)</label>
                                    <input type="text" class="form-control text-end" id="modalEditReservation-incomeTotalIDR" name="modalEditReservation-incomeTotalIDR" value="0" readonly>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="modalEditReservation-tourPlan" class="form-label">Tour Plan</label>
                                    <textarea class="form-control fixHeightTextArea" id="modalEditReservation-tourPlan" name="modalEditReservation-tourPlan" placeholder="Enter tour plan"></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="modalEditReservation-remark" class="form-label">Remark</label>
                                    <textarea class="form-control fixHeightTextArea" id="modalEditReservation-remark" name="modalEditReservation-remark" placeholder="Enter remark"></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="modalEditReservation-specialRequest" class="form-label">Special Request</label>
                                    <textarea class="form-control fixHeightTextArea" id="modalEditReservation-specialRequest" name="modalEditReservation-specialRequest" placeholder="Enter special request"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end p-2">
                        <input type="hidden" id="modalEditReservation-idReservation" name="modalEditReservation-idReservation" value="">
                        <input type="hidden" id="modalEditReservation-bookingCode" name="modalEditReservation-bookingCode" value="">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
.fixHeightTextArea{
	height: 60px !important;
}
</style>
<script>
	var jsFileUrl   =   "<?=BASE_URL_ASSETS_JS?>menu/chat.js?<?=date("YmdHis")?>";
        localStorage.setItem('idUserAdminMenuChat', "<?=$idUserAdmin?>");
	$.getScript(jsFileUrl);
</script>