<div class="tab-pane fade show active" id="pills-userAdmin" role="tabpanel" aria-labelledby="pills-userAdmin-tab">
    <div>
        <div class="px-3 pt-3">
            <h4 class="mb-3">User Admin List</h4>
            <div class="mb-2">
                <select class="form-control form-select bg-light rounded-3" id="filterUserAdmin-optionLevelUserAdmin" name="filterUserAdmin-optionLevelUserAdmin" option-all="All User Level"></select>
            </div>
            <div class="mb-3 search-box chat-search-box">
                <div class="input-group bg-light input-group-lg border rounded-3">
                    <div class="input-group-prepend">
                            <button class="btn btn-link text-decoration-none text-muted pe-1 ps-3" type="button">
                                <i class="ri-search-line search-icon font-size-14"></i>
                            </button>
                        </button>
                    </div>
                    <input type="text" class="form-control bg-light" placeholder="Search user admin.." id="filterUserAdmin-searchKeyword" name="filterUserAdmin-searchKeyword">
                </div>
            </div>
        </div>
        <div>
            <div class="chat-message-list py-3 px-2 pb-2 chat-group-list simplebar-scrollable-y" id="simpleBar-list-userAdminData" data-simplebar>
                <ul class="list-unstyled chat-list chat-user-list" id="list-userAdminData"></ul>
            </div>
        </div>
    </div>
</div>