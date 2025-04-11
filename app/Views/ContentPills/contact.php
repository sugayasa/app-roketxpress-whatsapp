<div class="tab-pane fade show active" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
    <div>
        <div class="px-3 pt-3">
            <h4 class="mb-3">Contacts</h4>
            <div class="mb-2">
                <select class="form-control form-select bg-light rounded-3" id="filter-optionContactType" name="filter-optionContactType">
                    <option value="1">Recently Add</option>
                    <option value="2">Tomorrow's Reservation</option>
                    <option value="3">Today's Reservation</option>
                    <option value="4">Yesterday's Reservation</option>
                    <option value="5">All contact</option>
                </select>
            </div>
            <div class="mb-3 search-box chat-search-box">
                <div class="input-group bg-light input-group-lg border rounded-3">
                    <div class="input-group-prepend">
                            <button class="btn btn-link text-decoration-none text-muted pe-1 ps-3" type="button">
                                <i class="ri-search-line search-icon font-size-14"></i>
                            </button>
                        </button>
                    </div>
                    <input type="text" class="form-control bg-light" placeholder="Search contact.." id="filter-searchKeyword" name="filter-searchKeyword">
                </div>
            </div>
        </div>
        <div>
            <div class="chat-message-list py-3 px-2 chat-group-list simplebar-scrollable-y" id="simpleBar-list-contactData" data-simplebar>
                <ul class="list-unstyled chat-list chat-user-list" id="list-contactData"></ul>
            </div>
        </div>
    </div>
</div>