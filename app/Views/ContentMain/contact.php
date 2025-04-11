<div class="d-lg-flex content-menu px-4 pt-4 pb-2" id="content-contact">
    <div class="w-100 overflow-hidden position-relative">
        <div class="px-2 pb-2" id="wrapper-contactDetails">
            <div class="d-block d-lg-none mx-0 mb-3 pb-2 border-bottom">
                <span class="user-chat-remove text-muted d-flex align-items-center">
                    <i class="ri-arrow-left-s-line font-size-22 me-2"></i> 
                    <h5 class="font-size-16 mb-0 text-truncate">Contact Details</h5>
                </span>
            </div>
            <div class="card bg-light2 mb-2 w-100 rounded-3">
                <div class="card-body p-4">
                    <h4 class="mb-3 d-flex justify-content-between align-items-center">
                        <span id="detailContact-fullname">-</span>
                    </h4>
                    <p class="small mb-0">
                        <i class="font-size-17 text-muted ri-phone-fill"></i><span class="font-size-15 text-muted mx-2" id="detailContact-phoneNumberCountry">| -</span>
                    </p>
                    <p class="small mb-0">
                        <i class="font-size-17 text-muted ri-mail-fill"></i><span class="font-size-15 text-muted mx-2" id="detailContact-email">| -</span>
                    </p>
                    <p class="small mb-3 d-flex">
                        <i class="font-size-17 text-muted ri-time-line"></i><span class="font-size-15 text-muted mx-2" id="detailContact-lastReplyDateTime">| -</span>
                        <button type="button" id="detailContact-btnSendMessage" class="btn btn-sm btn-success ms-auto"><span><i class="ri-chat-1-line me-1"></i>Send Message</span></button>
                    </p>
                    <div class="alert alert-warning d-flex align-items-center d-none" role="alert" id="detailContact-invalidWhatsAppAcountAlert">
                        <i class="ri-error-warning-line me-2"></i>
                        <div>Sending WhatsApp messages is not possible as this contact's number is not associated with a valid WhatsApp account.</div>
                    </div>
                    <hr class="my-4" />
                    <div class="d-flex justify-content-start align-items-center">
                        <p class="mb-0">
                            <i class="font-size-17 text-muted ri-coupon-fill"></i>
                            <span class="font-size-15 text-muted small" id="detailContact-totalReservation">0 Total reservation(s)</span>
                        </p>
                        <div class="d-flex ms-auto" id="detailContact-badgeSources"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-2" id="simpleScrollBar-listReservation"></div>
    </div>
</div>
<div class="modal fade" id="modal-askQuestion" tabindex="-1" role="dialog" aria-labelledby="modal-askQuestionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-size-16" id="modal-askQuestionLabel">Ask About <span id="askQuestion-bookingCode"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-lg-7 col-sm-12 mb-2 border-lg-right border-sm-right-0 border-lg-bottom-0 border-sm-bottom">
                        <dl class="row border-bottom pb-2 mb-2">
                            <dt class="col-lg-3 col-sm-4 mb-2 fw-bold">Customer Name</dt>
                            <dd class="col-lg-9 col-sm-8 mb-2 text-muted" id="askQuestion-customerName"></dd>
                            <dt class="col-lg-3 col-sm-4 mb-2 fw-bold">Reservation Title</dt>
                            <dd class="col-lg-9 col-sm-8 mb-2 text-muted" id="askQuestion-reservationTitle"></dd>
                            <dt class="col-lg-3 col-sm-4 mb-2 fw-bold">Date & Duration</dt>
                            <dd class="col-lg-9 col-sm-8 mb-2 text-muted" id="askQuestion-reservationDateDuration"></dd>
                            <dt class="col-lg-3 col-sm-4 mb-2 fw-bold">Remark</dt>
                            <dd class="col-lg-9 col-sm-8 mb-2 text-muted" id="askQuestion-remark"></dd>
                        </dl>
                        <div class="mt-2 mb-3">
                            <form class="text-end" id="askQuestion-formAddQuestion">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="askQuestion-inputQuestion" placeholder="Add question to message" autocomplete="off">
                                    <button class="btn btn-success" type="submit"><i class="ri-add-fill"></i></button>
                                </div>
                            </form>
                        </div>
                        <ul class="list-group" id="askQuestion-questionList">
                            <li class="list-group-item text-muted text-center" id="askQuestion-emptyQuestionText">
                                <div role="alert" class="alert alert-warning text-center py-2 mb-0">
                                    <span class="text-muted">Insert question to send message to customer</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-5 col-sm-12">
                        <div class="card">
                            <div class="card-header fw-bold">Message Preview</div>
                            <div class="card-body" id="askQuestion-messagePreview"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer align-items-center">
                <div class="alert alert-danger d-flex align-items-center py-2 flex-grow-1 d-none" role="alert" id="askQuestion-insertQuestionWarning">
                    <i class="ri-information-line me-2"></i> Please insert at least 1 question to be asked
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <button type="button" class="btn btn-info" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="askQuestion-btnSendMessage">Send Message</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-listTemplateMessage" tabindex="-1" role="dialog" aria-labelledby="modal-listTemplateMessageLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-size-16" id="modal-listTemplateMessageLabel">Send Template Message [<span id="listTemplateMessage-bookingCode"></span>]</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-lg-7 col-sm-12 mb-2 border-lg-right border-sm-right-0 border-lg-bottom-0 border-sm-bottom">
                        <dl class="row border-bottom pb-2 mb-2">
                            <dt class="col-lg-3 col-sm-4 mb-2 fw-bold">Customer Name</dt>
                            <dd class="col-lg-9 col-sm-8 mb-2 text-muted" id="listTemplateMessage-customerName"></dd>
                            <dt class="col-lg-3 col-sm-4 mb-2 fw-bold">Reservation Title</dt>
                            <dd class="col-lg-9 col-sm-8 mb-2 text-muted" id="listTemplateMessage-reservationTitle"></dd>
                            <dt class="col-lg-3 col-sm-4 mb-2 fw-bold">Date & Duration</dt>
                            <dd class="col-lg-9 col-sm-8 mb-2 text-muted" id="listTemplateMessage-reservationDateDuration"></dd>
                            <dt class="col-lg-3 col-sm-4 mb-2 fw-bold">Remark</dt>
                            <dd class="col-lg-9 col-sm-8 mb-2 text-muted" id="listTemplateMessage-remark"></dd>
                        </dl>
                        <ul class="list-group" id="listTemplateMessage-templateList">
                            <li class="list-group-item text-muted text-center" id="listTemplateMessage-emptyTemplateList">
                                <div role="alert" class="alert alert-warning text-center py-2 mb-0"><span class="text-muted">No template available for this reservation</span></div>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-5 col-sm-12">
                        <div class="card">
                            <div class="card-header fw-bold">Message Preview</div>
                            <div class="card-body" id="listTemplateMessage-messagePreview"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer align-items-center">
                <div class="alert alert-danger d-flex align-items-center py-2 flex-grow-1 d-none animate__animated animate__fadeIn" role="alert" id="listTemplateMessage-selectTemplateOptionWarning">
                    <i class="ri-information-line me-2"></i> Please select a template to send message
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <button type="button" class="btn btn-info" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="listTemplateMessage-btnSendTemplateMessage">Send Template Message</button>
            </div>
        </div>
    </div>
</div>
<script>
	var jsFileUrl = "<?=BASE_URL_ASSETS_JS?>menu/contact.js?<?=date("YmdHis")?>";
	$.getScript(jsFileUrl);
</script>