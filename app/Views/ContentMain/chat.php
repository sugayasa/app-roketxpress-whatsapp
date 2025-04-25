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
                            <div class="chat-user-img align-self-center me-3 ms-0">
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
<script>
	var jsFileUrl = "<?=BASE_URL_ASSETS_JS?>menu/chat.js?<?=date("YmdHis")?>";
	$.getScript(jsFileUrl);
</script>