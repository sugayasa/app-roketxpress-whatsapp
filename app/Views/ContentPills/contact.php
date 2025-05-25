<div class="tab-pane fade show active" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
    <div>
        <div class="px-3 pt-3">
            <div class="user-chat-nav float-end">
                <div data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="Add New Contact" data-bs-original-title="Add New Contact">
                    <button type="button" class="btn btn-link text-decoration-none text-muted font-size-18 py-0" data-bs-toggle="modal" data-bs-target="#modal-editorContact">
                        <i class="ri-user-add-line"></i>
                    </button>
                </div>
            </div>
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
<div class="modal fade" id="modal-editorContact" tabindex="-1" aria-labelledby="modal-editorContactLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="modalEditorContact-form">
            <div class="modal-header">
                <h5 class="modal-title font-size-16" id="modal-editorContactLabel">Contact Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="editorContact-name" class="form-label">Name</label>
                    <div class="input-group mb-3">
                        <select class="form-control form-select w-40" id="editorContact-nameTitle" name="editorContact-nameTitle" required></select>
                        <input type="text" class="form-control w-60" id="editorContact-name" name="editorContact-name" placeholder="Enter contact name" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="editorContact-phoneNumber" class="form-label">Phone Number</label>
                    <div class="input-group mb-3">
                        <select class="form-control form-select select2 w-60" id="editorContact-country" name="editorContact-country" required></select>
                        <input type="text" class="form-control w-40" id="editorContact-phoneNumber" name="editorContact-phoneNumber" placeholder="Enter phone number" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="editorContact-email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="editorContact-email" name="editorContact-email" placeholder="Enter contact email">
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="editorContact-idContact" name="editorContact-idContact" value="">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<script>
    var defaultCountryCode = '<?= $defaultCountryCode ?>';
</script>