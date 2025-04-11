<div class="tab-pane fade show active" id="pills-chat" role="tabpanel" aria-labelledby="pills-chat-tab">
    <div>
        <div class="px-3 pt-3">
            <h4 class="mb-3">Chats</h4>
            <div class="mb-3 search-box chat-search-box">
                <div class="input-group bg-light input-group-lg border rounded-3">
                    <div class="input-group-prepend">
                            <button class="btn btn-link text-decoration-none text-muted pe-1 ps-3" type="button">
                                <i class="ri-search-line search-icon font-size-14"></i>
                            </button>
                        </button>
                    </div>
                    <input type="hidden" id="filter-idContact" name="filter-idContact" value="">
                    <input type="text" class="form-control bg-light" placeholder="Search messages or users" aria-label="Search messages or contact" aria-describedby="basic-addon1" id="filter-searchKeyword" name="filter-searchKeyword">
                </div>
            </div>
        </div>
        <div>
            <div class="chat-message-list py-3 px-2 pb-2 chat-group-list simplebar-scrollable-y" id="simpleBar-list-chatList" data-simplebar>
                <ul class="list-unstyled chat-list chat-user-list" id="list-chatListData"></ul>
            </div>
        </div>
    </div>
</div>