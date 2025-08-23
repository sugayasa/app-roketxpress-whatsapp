<script type="module">
	import { initializeApp } from "https://www.gstatic.com/firebasejs/9.13.0/firebase-app.js";
	import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/9.13.0/firebase-database.js";

	const firebaseConfig = {
		apiKey: '<?=FIREBASE_PUBLIC_API_KEY?>',
		authDomain: '<?=FIREBASE_PUBLIC_AUTH_DOMAIN?>',
		databaseURL: "<?=FIREBASE_RTDB_URI?>",
		projectId: '<?=FIREBASE_PUBLIC_PROJECT_ID?>',
		storageBucket: '<?=FIREBASE_PUBLIC_STORAGE_BUCKET?>',
		messagingSenderId: '<?=FIREBASE_PUBLIC_MESSAGING_SENDER_ID?>',
		appId: '<?=FIREBASE_PUBLIC_APP_ID?>',
		measurementId: '<?=FIREBASE_PUBLIC_MEASUREMENT_ID?>'
	};

	const app				=	initializeApp(firebaseConfig),
		  database			= 	getDatabase(app),
		  rtdb_appPath		=	'<?=FIREBASE_RTDB_MAINREF_NAME?>/',
		  currentACK		=	ref(database, rtdb_appPath + 'currentACK'),
		  lastUpdateChat	=	ref(database, rtdb_appPath + 'lastUpdateChat'),
		  unreadChatNumber	=	ref(database, rtdb_appPath + 'unreadChatNumber'),
		  forceHandleNumber	=	ref(database, rtdb_appPath + 'forceHandleNumber');

	onValue(currentACK, (snapshot) => {
		const lastAlias = localStorage.getItem('lastAlias'),
			dataCurrentACK = snapshot.val();

		if (
			dataCurrentACK !== undefined &&
			dataCurrentACK != "" &&
			dataCurrentACK !== null
		) {
			const idMessage = dataCurrentACK.idMessage,
				timestamp = dataCurrentACK.timestamp,
				type = dataCurrentACK.type;

			if (lastAlias == 'CHT') {
				let elemIconACK = $(".chatContentWrap-iconACK[data-idMessage='" + idMessage + "']");

				if (elemIconACK.length > 0) {
					let newIconACK = '',
						dateTimeStr = moment.unix(timestamp).tz(timezoneOffset).format('DD MMM YYYY HH:mm');

					switch (type) {
						case 'read': newIconACK = 'ri-check-double-line text-primary'; break;
						case 'delivered': newIconACK = 'ri-check-double-line text-muted'; break;
						case 'sent': newIconACK = 'ri-check-line text-muted'; break;
						default: newIconACK = 'ri-hourglass-2-fill text-muted'; break;
					}
					elemIconACK.removeClass('ri-hourglass-2-fill ri-check-line ri-check-double-line text-muted text-primary').addClass(newIconACK).parent().attr('data-ack-' + type, dateTimeStr);
				}
			}
		}
	});

	onValue(lastUpdateChat, (snapshot) => {
		const activeMenu = $(".nav-link.active").closest('li').attr('id'),
			lastUpdateChat = snapshot.val();

		if (
			lastUpdateChat !== undefined &&
			lastUpdateChat != "" &&
			lastUpdateChat !== null
		) {
			const contactInitial = lastUpdateChat.contactInitial,
				contactName = lastUpdateChat.contactName,
				idChatList = lastUpdateChat.idChatList,
				idUserAdmin = lastUpdateChat.idUserAdmin,
				isNewMessage = lastUpdateChat.isNewMessage,
				handleStatus = parseInt(lastUpdateChat.handleStatus),
				handleForce = parseInt(lastUpdateChat.handleForce),
				messageBodyTrim = lastUpdateChat.messageBodyTrim,
				timestamp = lastUpdateChat.timestamp,
				dateTimeLastReply = lastUpdateChat.dateTimeLastReply,
				totalUnreadMessage = lastUpdateChat.totalUnreadMessage,
				arrReservationType = lastUpdateChat.arrReservationType,
				messageDetail = lastUpdateChat.messageDetail,
				senderFirstName = messageDetail.senderFirstName,
				lastNotifTimeStamp = localStorage.getItem('lastNotifTimeStamp');

			if (activeMenu == 'menuCHT') {
				let chatListItem = $(".chatList-item[data-idChatList='" + idChatList + "']"),
					chatListItemActiveId = $("#chat-idChatList").val(),
					containerConversation = $("#chat-conversation-ul");

				if(chatListItem.length > 0){
					let chatListItemCounter = chatListItem.find('.unread-message'),
						chatListItemBadgeHandleStatus = chatListItem.find('.chatList-item-badgeHandleStatus'),
						badgeHandleStatusIcon = 'ri-robot-2-line',
						badgeHandleStatusTextColor = 'primary',
						elemBadgeHandleStatus = '';
					chatListItem.find('.chat-user-message').html(senderFirstName+': '+messageBodyTrim);

					if(totalUnreadMessage > 0){
						if(chatListItemCounter.length > 0){
							chatListItemCounter.find('.badge').text(totalUnreadMessage);
						} else {
							let chatListItemCounterHtml = '<div class="unread-message"><span class="badge badge-soft-danger rounded-pill py-0">'+totalUnreadMessage+'</span></div>';
							chatListItem.find('div.d-flex').append(chatListItemCounterHtml);
						}
					} else {
						chatListItemCounter.remove();
					}

					if(isNewMessage)  {
						chatListItem.attr('data-timestamp', timestamp).attr('data-datetimelastreply', dateTimeLastReply);
						chatListItem.find('div.chatList-item-time').text('Now');
						chatListItem.prependTo("#list-chatListData");
					}

					if(chatListItemBadgeHandleStatus.length > 0){
						if(handleForce == 1){
                            elemBadgeHandleStatus = '<i class="spinner-grow spinner-grow-sm text-success"></i>';
						} else {
							switch (handleStatus) {
								case 2:
									badgeHandleStatusIcon = 'ri-user-voice-line';
									badgeHandleStatusTextColor = 'success';
									break;
								default:
									badgeHandleStatusIcon = 'ri-robot-2-line';
									badgeHandleStatusTextColor = 'primary';
									break;
							}
                            elemBadgeHandleStatus = '<i class="text-' + badgeHandleStatusTextColor + ' font-size-18 ' + badgeHandleStatusIcon + '"></i>';
						}
						chatListItemBadgeHandleStatus.html(elemBadgeHandleStatus);
					}
				} else {
					let isSearchActive = $("#filter-isSearchActive").val();
					if(isNewMessage && !isSearchActive){
						let chatListItemCounterHtml = totalUnreadMessage > 0 ? '<div class="unread-message"><span class="badge badge-soft-danger rounded-pill py-0">'+totalUnreadMessage+'</span></div>' : '',
							arrCheckedReservationType = $('.reservationTypeCheckbox:checked').map(function () {
								return this.value;
							}).get(),
							elemReservatioTypeTag = '',
							addChatList = false;

						if(isValidArray(arrReservationType) && isValidArray(dataReservationTypeClass)){
							elemReservatioTypeTag += '<div class="chatList-item ps-1">';
                            for (var i = 0; i < arrReservationType.length; i++) {
                                let reservationTypeChat	= arrReservationType[i],
									tagClass = dataReservationTypeClass[reservationTypeChat];
								
								if (arrCheckedReservationType.includes(reservationTypeChat)) addChatList = true;
                                if (typeof tagClass != 'undefined' && tagClass != null) elemReservatioTypeTag += '<div class="bg-' + tagClass + ' width-5px">&nbsp;</div>';
                            }
                            elemReservatioTypeTag += '</div>';
						}

						let chatListItemHtml =	'<li class="unread chatList-item" data-idchatlist="'+idChatList+'" data-timestamp="'+timestamp+'" data-datetimelastreply="'+dateTimeLastReply+'">\
													<a href="#" class="px-2">\
														<div class="d-flex">\
															<div class="chat-user-img align-self-center me-3 ms-0">\
																<div class="avatar-xs">\
																	<span class="avatar-title rounded-circle bg-primary-subtle text-primary">'+contactInitial+'</span>\
																</div>\
                                                				<span class="user-status"></span>\
															</div>\
															<div class="flex-grow-1 overflow-hidden">\
																<h5 class="text-truncate font-size-15 mb-1">'+contactName+'</h5>\
																<p class="chat-user-message text-truncate mb-0">'+senderFirstName+': '+messageBodyTrim+'</p>\
															</div>\
															<div class="chatList-item-time font-size-11">Now</div>\
                                            				' + chatListItemCounterHtml + '\
                                            				' + elemReservatioTypeTag + '\
														</div>\
													</a>\
												</li>';
						
						if(addChatList) {
							$("#list-chatListData").prepend(chatListItemHtml);
							activateOnClickChatListItem();
							counterTimeChatList();
						}
					}
				}
				counterTimeChatList();

				if(containerConversation.length > 0){
					if(chatListItemActiveId == idChatList){
						let senderName = messageDetail.senderName,
							chatThreadPosition = messageDetail.chatThreadPosition,
							arrayChatThread = messageDetail.arrayChatThread,
							chatTime = moment.unix(timestamp).tz(timezoneOffset).format('HH:mm'),
							idMessage = arrayChatThread.IDMESSAGE;

						if($(".ctext-wrap[data-idMessage='" + idMessage + "']").length <= 0){
							let elemLastChatThread = containerConversation.find('li:last-child'),
								lastSenderName = elemLastChatThread.find('.conversation-name').html(),
                            	textStartClass = arrayChatThread.ISTEMPLATE ? 'text-start' : '',
                            	isBot = parseInt(arrayChatThread.ISBOT),
								chatContent = generateChatContent(arrayChatThread),
                        		chatContentWrap = generateChatContentWrap(chatThreadPosition, arrayChatThread, chatContent, chatTime, textStartClass);

							if(senderName == lastSenderName){
								let elemContentWrapContainer = $("#chat-conversation-ul").find('li:last-child').find('.user-chat-content'),
									elemConversationName = elemContentWrapContainer.find('.conversation-name');
								
								elemConversationName.before(chatContentWrap);
							} else {
								let classRight = chatThreadPosition == 'L' ? '' : 'right',
									senderNameInitial = isBot == 1 ? '<i class="text-primary font-size-18 ri-robot-2-line"></i>' : senderName.charAt(0).toUpperCase(),
									chatThread = generateRowChatThread(classRight, senderNameInitial, chatContentWrap, senderName);
								$('#chat-conversation-ul').append(chatThread);
							}
							
							activateChatContentOptionButton();
							activateScrollToTopChatThread();
    						activateQuotedMessageClick();
							recalculateSimpleBar('chat-conversation', true);
							activateMagnificPopup();
						}

						$("#chat-timeStampLastReply").val(dateTimeLastReply);
    					$("#chat-handleStatus").val(handleStatus);
    					$("#chat-handleForce").val(handleForce);

						if(handleForce == 0) {
							if(handleStatus == 1) {
								$("#chat-actionButton-activateBOT").prop('disabled', true).addClass('d-none');
								$("#chat-actionButton-activateHuman").prop('disabled', false).removeClass('d-none');
							}
							
							if(handleStatus == 2) {
								$("#chat-actionButton-activateBOT").prop('disabled', false).removeClass('d-none');
								$("#chat-actionButton-activateHuman").prop('disabled', true).addClass('d-none');
							}
						} else {
							$("#chat-actionButton-activateBOT, #chat-actionButton-activateHuman").prop('disabled', true).addClass('d-none');
						}
					}
				}

				if (isNewMessage && document.visibilityState === 'visible') updateUnreadMessageCountOnActiveVisibilityWindow();
			} else if(activeMenu == 'menuCNCT') {
				let contactListItem = $(".contact-item[data-idChatList='" + idChatList + "']");
				if(contactListItem.length > 0){
					contactListItem.attr('data-timeStampLastReply', dateTimeLastReply);
				}
				
				let idChatListActiveContact = $("#wrapper-contactDetails").attr("data-idChatList");
				if(idChatList == idChatListActiveContact){
					$("#detailContact-iconSession").attr('data-timeStampLastReply', dateTimeLastReply);
					activateCounterChatSession();
				}
			}

			if(isNewMessage && lastNotifTimeStamp != timestamp){
				if (document.visibilityState === 'visible') {
					let chatListItemActiveId = $("#chat-idChatList").val(),
						idUserAdminMenuChat = localStorage.getItem('idUserAdminMenuChat');
					if(chatListItemActiveId == idChatList) {
						if(idUserAdminMenuChat != idUserAdmin) playStoredAudio("message_received_active");
					} else {
						playStoredAudio("message_received_background");
					}
				} else {
					let appVisibility = localStorage.getItem('appVisibility');
					if(appVisibility == false || appVisibility == 'false') playStoredAudio("message_received_background");
				}
				localStorage.setItem('lastNotifTimeStamp', timestamp);
			}
		}
	});

	onValue(unreadChatNumber, (snapshot) => {
		const elemChatUnreadCounter = $("#chatUnreadCounter");
		let chatUnreadNumber = snapshot.val();

		if(chatUnreadNumber > 0){
			chatUnreadNumber = chatUnreadNumber > 99 ? '99+' : chatUnreadNumber;
			if(elemChatUnreadCounter.length > 0){
				elemChatUnreadCounter.html(chatUnreadNumber);
			} else {
				$("#menuCHT a.nav-link").append('<span id="chatUnreadCounter" class="badge bg-primary rounded-pill font-size-12 position-absolute mt-0 ms-1 translate-middle">'+chatUnreadNumber+'</span>');
			}
		} else {
			$("#chatUnreadCounter").remove();
		}
	});

	onValue(forceHandleNumber, (snapshot) => {
		let chatForceHandleNumber = snapshot.val(),
			alarmForceHandleTabId = localStorage.getItem("intervalIdForceHandleChatList-tabId");

		if(chatForceHandleNumber > 0){
			playStoredAudio("warning_alarm");

			if(alarmForceHandleTabId === null || alarmForceHandleTabId === tabId) {
				intervalIdForceHandleChatList = setInterval(function () {
					playStoredAudio("warning_alarm");
				}, 30000);
				localStorage.setItem("intervalIdForceHandleChatList-tabId", tabId);
			}

			intervalIdForceHandleChatMenu = setInterval(function () {
				$("#menuCHT a").addClass('splashed-border');
				setTimeout(() => {
					$("#menuCHT a").removeClass('splashed-border');
				}, 500);
			}, 1000);
		} else {
			localStorage.removeItem("intervalIdForceHandleChatList-tabId");
			if (intervalIdForceHandleChatList && intervalIdForceHandleChatList !== null) clearInterval(intervalIdForceHandleChatList);
			if (intervalIdForceHandleChatMenu && intervalIdForceHandleChatMenu !== null) clearInterval(intervalIdForceHandleChatMenu);
		}
	});
</script>